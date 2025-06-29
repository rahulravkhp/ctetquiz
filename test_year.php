<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// वर्ष की जांच करें
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
if (!in_array($year, range(2018, 2024))) {
    header("Location: previous_papers.php");
    exit();
}

// वर्ष-विशिष्ट प्रश्नों को लोड करें
$stmt = $conn->prepare("SELECT COUNT(*) FROM questions WHERE year = ?");
$stmt->bind_param("i", $year);
$stmt->execute();
$stmt->bind_result($total_available_questions);
$stmt->fetch();
$stmt->close();

$limit = min($total_available_questions, 150);
$stmt = $conn->prepare("SELECT id, question_text, option1, option2, option3, option4, correct_option FROM questions WHERE year = ? LIMIT ?");
$stmt->bind_param("ii", $year, $limit);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$score = null;
$total_questions = count($questions);
$answers = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    $answers_json = [];
    foreach ($questions as $question) {
        $question_id = $question['id'];
        if (isset($_POST['answer'][$question_id])) {
            $user_answer = $_POST['answer'][$question_id];
            $answers[$question_id] = $user_answer;
            $answers_json[$question_id] = $user_answer;
            if ($user_answer == $question['correct_option']) {
                $score++;
            }
        } else {
            $answers[$question_id] = null;
            $answers_json[$question_id] = null;
        }
    }
    // रिजल्ट और यूजर उत्तरों को डेटाबेस में स्टोर करें
    $answers_json = json_encode($answers_json);
    $stmt = $conn->prepare("INSERT INTO results (student_id, score, total_questions, user_answers, created_at, year) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iiisi", $_SESSION['user_id'], $score, $total_questions, $answers_json, $year);
    $stmt->execute();
    $result_id = $conn->insert_id;
    $stmt->close();
    
    // सेशन में रिजल्ट डेटा स्टोर करें PDF जनरेशन के लिए
    $_SESSION['last_result'] = [
        'score' => $score,
        'total_questions' => $total_questions,
        'answers' => $answers,
        'questions' => $questions,
        'result_id' => $result_id,
        'year' => $year
    ];
}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTET मॉक टेस्ट <?php echo $year; ?> - टाइमर के साथ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">CTET मॉक टेस्ट <?php echo $year; ?> (मोबाइल रेस्पॉन्सिव)</h2>
        <?php if ($score !== null) { ?>
            <div class="score">
                <p>आपका स्कोर: <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total_questions); ?></p>
                <p>प्रतिशत: <?php echo number_format(($score / $total_questions) * 100, 2); ?>%</p>
            </div>
            <h3 class="mt-4">उत्तर समीक्षा</h3>
            <table class="review-table table table-bordered">
                <thead>
                    <tr>
                        <th>प्रश्न</th>
                        <th>आपका उत्तर</th>
                        <th>सही उत्तर</th>
                        <th>स्थिति</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $index => $question) { ?>
                        <tr>
                            <td><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?></td>
                            <td>
                                <?php
                                $user_answer = $answers[$question['id']];
                                if ($user_answer) {
                                    echo htmlspecialchars($question['option' . $user_answer]);
                                } else {
                                    echo 'कोई जवाब नहीं';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($question['option' . $question['correct_option']]); ?></td>
                            <td>
                                <?php
                                if ($user_answer == $question['correct_option']) {
                                    echo '<span class="correct">सही</span>';
                                } elseif ($user_answer !== null) {
                                    echo '<span class="incorrect">गलत</span>';
                                } else {
                                    echo '<span class="incorrect">उत्तर नहीं दिया</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="test_year.php?year=<?php echo $year; ?>" class="btn btn-primary">टेस्ट पुनः शुरू करें</a>
                <a href="history.php" class="btn btn-primary">टेस्ट हिस्ट्री देखें</a>
                <a href="feedback.php" class="btn btn-primary">फीडबैक दें</a>
                <a href="generate_result_pdf.php?result_id=<?php echo $result_id; ?>" class="btn btn-primary">रिजल्ट PDF डाउनलोड</a>
                <a href="dashboard.php" class="btn btn-primary">वापस डैशबोर्ड पर जाएं</a>
            </div>
        <?php } else { ?>
            <div class="timer">
                <p>शेष समय: <span id="timer">2:30:00</span></p>
            </div>
            <form id="testForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?year=' . $year; ?>" method="POST">
                <?php foreach ($questions as $index => $question) { ?>
                    <div class="test-question <?php echo $index === 0 ? 'active' : ''; ?>" id="question-<?php echo $index; ?>">
                        <div class="question">
                            <p><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?></p>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>-1" value="1" required>
                                <label class="form-check-label" for="q<?php echo $question['id']; ?>-1">A. <?php echo htmlspecialchars($question['option1']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>-2" value="2">
                                <label class="form-check-label" for="q<?php echo $question['id']; ?>-2">B. <?php echo htmlspecialchars($question['option2']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>-3" value="3">
                                <label class="form-check-label" for="q<?php echo $question['id']; ?>-3">C. <?php echo htmlspecialchars($question['option3']); ?></label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>-4" value="4">
                                <label class="form-check-label" for="q<?php echo $question['id']; ?>-4">D. <?php echo htmlspecialchars($question['option4']); ?></label>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="test-navigation">
                    <button type="button" class="btn btn-secondary" id="prevBtn" disabled>पिछला</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">अगला</button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">जवाब सबमिट करें</button>
                </div>
            </form>
            <p class="text-center mt-3"><a href="dashboard.php">वापस डैशबोर्ड पर जाएं</a></p>
            <script>
                // टाइमर लॉजिक (2 घंटे 30 मिनट = 9000 सेकंड)
                let timeLeft = 9000;
                const timerElement = document.getElementById('timer');
                const form = document.getElementById('testForm');
                function updateTimer() {
                    const hours = Math.floor(timeLeft / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;
                    timerElement.textContent = `${hours}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                    if (timeLeft <= 0) {
                        form.submit();
                    } else {
                        timeLeft--;
                        setTimeout(updateTimer, 1000);
                    }
                }
                updateTimer();

                // प्रश्न नेविगेशन लॉजिक
                const questions = document.querySelectorAll('.test-question');
                let currentQuestion = 0;
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');

                function updateNavigation() {
                    prevBtn.disabled = currentQuestion === 0;
                    nextBtn.style.display = currentQuestion === questions.length - 1 ? 'none' : 'inline-block';
                    submitBtn.style.display = currentQuestion === questions.length - 1 ? 'inline-block' : 'none';
                }

                prevBtn.addEventListener('click', () => {
                    if (currentQuestion > 0) {
                        questions[currentQuestion].classList.remove('active');
                        currentQuestion--;
                        questions[currentQuestion].classList.add('active');
                        updateNavigation();
                    }
                });

                nextBtn.addEventListener('click', () => {
                    if (currentQuestion < questions.length - 1) {
                        questions[currentQuestion].classList.remove('active');
                        currentQuestion++;
                        questions[currentQuestion].classList.add('active');
                        updateNavigation();
                    }
                });

                updateNavigation();
            </script>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
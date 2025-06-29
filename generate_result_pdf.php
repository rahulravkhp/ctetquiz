<?php
session_start();
require_once 'vendor/autoload.php'; // TCPDF लाइब्रेरी लोड करें

// चेक करें कि result सेशन में है या नहीं
if (!isset($_SESSION['last_result'])) {
    die("कोई परिणाम उपलब्ध नहीं है।");
}

$result = $_SESSION['last_result'];
$score = $result['score'];
$total = $result['total_questions'];
$questions = $result['questions'];
$answers = $result['answers'];

// Output से पहले कोई भी स्पेस या टेक्स्ट न भेजें
ob_clean(); // इससे कोई accidental output हट जाता है

// PDF इनिशियलाइज़ करें
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CTET Quiz App');
$pdf->SetTitle('CTET Result');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

// Fix: Set the correct font for Hindi language support
$pdf->SetFont('dejavusans', '', 12, '', true); // हिंदी सपोर्ट के लिए

$pdf->AddPage();

// PDF के लिए HTML बनाएँ
$html = '<h2>CTET मॉक टेस्ट परिणाम</h2>';
$html .= '<p><strong>कुल अंक:</strong> ' . $score . ' / ' . $total . '</p>';
$html .= '<p><strong>प्रतिशत:</strong> ' . number_format(($score / $total) * 100, 2) . '%</p>';

$html .= '<h3>उत्तर समीक्षा</h3>';
$html .= '<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>प्रश्न</th>
            <th>आपका उत्तर</th>
            <th>सही उत्तर</th>
            <th>स्थिति</th>
        </tr>
    </thead>
    <tbody>';

foreach ($questions as $q) {
    $q_text = $q['question_text'];
    $correct = $q['correct_option'];
    $user = $answers[$q['id']] ?? null;

    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($q_text) . '</td>';
    $html .= '<td>' . ($user ? htmlspecialchars($q['option' . $user]) : 'कोई उत्तर नहीं') . '</td>';
    $html .= '<td>' . htmlspecialchars($q['option' . $correct]) . '</td>';
    $html .= '<td>' . ($user == $correct ? '✅ सही' : ($user === null ? '❌ नहीं दिया' : '❌ गलत')) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// HTML को PDF में जोड़ें
$pdf->writeHTML($html, true, false, true, false, '');

// ब्राउज़र में आउटपुट भेजें
$pdf->Output('result.pdf', 'I'); // 'I' = इनलाइन ब्राउज़र में खोलो
exit;
?>

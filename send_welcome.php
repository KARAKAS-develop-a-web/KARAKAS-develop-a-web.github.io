<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$code = trim($data['code'] ?? '');
$senderName = trim($data['senderName'] ?? 'Üçler Gül Kuyumculuk');
$replyTo = trim($data['replyTo'] ?? 'info@uclergukuyumculuk.com');

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçerli bir ad ve e-posta gerekli']);
    exit;
}

$subject = $data['subject'] ?? ($senderName . ' - Hoş Geldiniz');

$lineBreak = "\r\n";
$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=UTF-8';
$headers[] = 'From: ' . $senderName . ' <' . $replyTo . '>';
$headers[] = 'Reply-To: ' . $replyTo;
$headers[] = 'X-Mailer: PHP/' . phpversion();

$message = '<html><body style="font-family:Arial,sans-serif;background:#f7f7f7;padding:24px;">';
$message .= '<div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px;border:1px solid #e5e7eb;">';
$message .= '<h2 style="margin:0 0 16px;color:#111827;">Hoş geldiniz ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>';
$message .= '<p style="margin:0 0 12px;color:#374151;">Üyeliğiniz başarıyla oluşturuldu.</p>';
if ($code !== '') {
    $message .= '<p style="margin:0 0 12px;color:#374151;">İndirim kodunuz: <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong></p>';
}
$message .= '<p style="margin:0;color:#6b7280;">Gelen kutunuzu ve spam klasörünü kontrol etmeyi unutmayın.</p>';
$message .= '</div></body></html>';

$sent = @mail($email, $subject, $message, implode($lineBreak, $headers));

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mail gönderilemedi. Sunucuda mail() desteği veya e-posta yapılandırması gerekiyor.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Welcome email sent'
]);

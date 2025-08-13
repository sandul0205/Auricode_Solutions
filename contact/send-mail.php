<?php
// contact/send-mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
  exit;
}

// Basic validation & sanitization
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$agree   = isset($_POST['agree']);

if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$subject || !$message || !$agree) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Please fill all fields correctly.']);
  exit;
}

// Load PHPMailer (no Composer)
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'auricodesolutions@gmail.com';         // your Gmail
  $mail->Password   = '1234567891234567';              // 16-char App Password
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 587;

  // From must be your Gmail when using Gmail SMTP
  $mail->setFrom('auricodesolutions@gmail.com', 'Website Contact');
  $mail->addAddress('auricodesolutions@gmail.com', 'Auricode Solutions');
  // Let you reply directly to the sender
  $mail->addReplyTo($email, $name);

  $mail->isHTML(true);
  $mail->Subject = "New inquiry: " . $subject;
  $mail->Body    = "
    <h3>New Contact Form Submission</h3>
    <p><strong>Name:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Subject:</strong> {$subject}</p>
    <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
  ";
  $mail->AltBody = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\n{$message}";

  $mail->send();
  echo json_encode(['ok' => true, 'msg' => 'Thanks! We will be in touch within 1 business day.']);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Could not send message. Please try again later.']);
}

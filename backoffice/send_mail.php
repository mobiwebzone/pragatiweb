<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);  // Passing `true` enables exceptions

try {
    //Server settings
    $mail->isSMTP();  // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through (example: Gmail)
    $mail->SMTPAuth = true;  // Enable SMTP authentication
    $mail->Username = "info.mobiwebzone@gmail.com";  // SMTP username (your email address)
    $mail->Password = "ught lkob etho quwk";  // SMTP password (use app password if 2FA enabled)
    $mail->SMTPSecure = 'tls';  // Enable TLS encryption
    $mail->Port = 587;  // TCP port to connect to (587 is common for TLS)

    //Recipients
    $mail->setFrom('info.mobiwebzone@gmail.com', 'Mobiwebzone');  // Sender email and name
    $mail->addAddress('nautiyalar@gmail.com', '');  // Recipient's email and name

    //Content
    $mail->isHTML(true);  // Set email format to HTML
    $mail->Subject = 'Test Email from PHPMailer';  // Subject line
    $mail->Body    = 'This is a <b>test email</b> sent via PHPMailer!';  // HTML message body
    $mail->AltBody = 'This is a test email sent via PHPMailer!';  // Plain text body for non-HTML email clients

    // Send the email
    $mail->send();
    echo 'Email has been sent successfully!';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>

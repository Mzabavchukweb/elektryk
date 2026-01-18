<?php
/**
 * RS ELECTRICS - Form Handler
 * Prosty skrypt do wysyłki formularzy kontaktowych
 */

// Zabezpieczenie przed bezpośrednim dostępem (jeśli nie ma POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Ustawienia
$to_email = 'kontakt@elektrykgorzow.com';
$redirect_url = 'https://elektrykgorzow.com/dziekujemy.html';

// Pobierz dane z formularza
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Brak tematu';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Honeypot spam protection
if (isset($_POST['_honey']) && $_POST['_honey'] !== '') {
    // To jest bot, nie wysyłamy
    header('Location: ' . $redirect_url);
    exit;
}

// Walidacja podstawowa
if (empty($name) || empty($phone) || empty($email)) {
    header('Location: ' . $redirect_url);
    exit;
}

// Walidacja emaila
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirect_url);
    exit;
}

// Przygotuj temat i treść maila
$email_subject = 'Nowe zapytanie ze strony: ' . ($_POST['_subject'] ?? 'Kontakt');

$email_body = "Nowe zapytanie ze strony RS ELECTRICS\n\n";
$email_body .= "─────────────────────────────────\n\n";
$email_body .= "IMIĘ I NAZWISKO:\n" . $name . "\n\n";
$email_body .= "TELEFON:\n" . $phone . "\n\n";
$email_body .= "E-MAIL:\n" . $email . "\n\n";

if (!empty($subject)) {
    $email_body .= "TEMAT ZAPYTANIA:\n" . $subject . "\n\n";
}

if (!empty($message)) {
    $email_body .= "OPIS ZLECENIA:\n" . $message . "\n\n";
}

$email_body .= "─────────────────────────────────\n\n";
$email_body .= "Data: " . date('Y-m-d H:i:s') . "\n";
$email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

// Nagłówki maila
$headers = "From: RS ELECTRICS <noreply@elektrykgorzow.com>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Wysyłka maila
$mail_sent = @mail($to_email, $email_subject, $email_body, $headers);

// Przekierowanie (zawsze przekieruj, niezależnie od wyniku)
header('Location: ' . $redirect_url);
exit;
?>

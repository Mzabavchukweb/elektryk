<?php
/**
 * RS ELECTRICS - Form Handler
 * Obsługa formularzy kontaktowych
 */

// Włącz logowanie błędów
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/form-errors.log');

// Funkcja do logowania
function logError($message, $data = []) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($data)) {
        $log .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    $log .= "\n";
    error_log($log, 3, __DIR__ . '/form-errors.log');
}

// Loguj każdy request (diagnostyka)
logError("=== REQUEST ===", [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'post' => !empty($_POST) ? 'YES' : 'NO',
    'post_keys' => !empty($_POST) ? implode(', ', array_keys($_POST)) : 'none'
]);

// Zabezpieczenie przed bezpośrednim dostępem
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Nieprawidłowa metoda HTTP", ['method' => $_SERVER['REQUEST_METHOD']]);
    header('Location: /');
    exit;
}

// Ustawienia
$to_email = 'kontakt@elektrykgorzow.com';
$redirect_url = '/dziekujemy.html';
$error_url = '/kontakt.html?error=1';

// Rate limiting - sprawdź czy nie za dużo requestów z tego IP
$rate_limit_file = __DIR__ . '/rate-limit.json';
$rate_limit_window = 300; // 5 minut
$rate_limit_max = 5; // max 5 formularzy na 5 minut

$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_data = [];

if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}

// Wyczyść stare wpisy
$current_time = time();
foreach ($rate_data as $ip => $times) {
    $rate_data[$ip] = array_filter($times, function($t) use ($current_time, $rate_limit_window) {
        return ($current_time - $t) < $rate_limit_window;
    });
    if (empty($rate_data[$ip])) {
        unset($rate_data[$ip]);
    }
}

// Sprawdź limit dla tego IP
if (isset($rate_data[$client_ip])) {
    $recent_count = count($rate_data[$client_ip]);
    if ($recent_count >= $rate_limit_max) {
        logError("Rate limit exceeded", ['ip' => $client_ip, 'count' => $recent_count]);
        header('Location: ' . $error_url . '&reason=rate_limit');
        exit;
    }
    $rate_data[$client_ip][] = $current_time;
} else {
    $rate_data[$client_ip] = [$current_time];
}

file_put_contents($rate_limit_file, json_encode($rate_data));

// Honeypot spam protection
if (isset($_POST['_honey']) && $_POST['_honey'] !== '') {
    logError("Honeypot triggered - bot detected", ['ip' => $client_ip]);
    header('Location: ' . $redirect_url); // Nie pokazuj błędu botom
    exit;
}

// Pobierz dane z formularza
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$form_subject = isset($_POST['_subject']) ? trim($_POST['_subject']) : 'Kontakt';

// Walidacja podstawowa
if (empty($name) || empty($phone) || empty($email)) {
    logError("Validation failed - missing required fields", [
        'name' => !empty($name),
        'phone' => !empty($phone),
        'email' => !empty($email)
    ]);
    header('Location: ' . $error_url . '&reason=validation');
    exit;
}

// Walidacja emaila
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logError("Validation failed - invalid email", ['email' => $email]);
    header('Location: ' . $error_url . '&reason=email');
    exit;
}

// Przygotuj temat i treść maila
$email_subject = 'Nowe zapytanie ze strony: ' . $form_subject;

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
$email_body .= "IP: " . $client_ip . "\n";
$email_body .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";

// Nagłówki maila
$from_email = 'noreply@elektrykgorzow.com';
$headers = "From: RS ELECTRICS <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "X-Priority: 3\r\n";
$headers .= "X-MSMail-Priority: Normal\r\n";

// Wysyłka maila przez mail()
// Parametr -f wymusza użycie adresu From (pomaga na niektórych hostingach)
$additional_params = '-f' . $from_email;

$mail_sent = mail($to_email, $email_subject, $email_body, $headers, $additional_params);

// Sprawdź błędy PHP
$last_error = error_get_last();
if ($last_error && strpos($last_error['message'], 'mail') !== false) {
    logError("PHP error during mail() call", ['error' => $last_error['message']]);
    header('Location: ' . $error_url . '&reason=server');
    exit;
}

if ($mail_sent) {
    // Sukces - przekieruj na stronę dziękujemy
    header('Location: ' . $redirect_url);
    exit;
} else {
    logError("Email sending failed", ['ip' => $client_ip]);
    
    // Backup - zapisz dane do pliku jeśli mail nie został wysłany
    $backup_file = __DIR__ . '/form-submissions-backup.txt';
    $backup_data = date('Y-m-d H:i:s') . " | " . $name . " | " . $phone . " | " . $email . " | " . $subject . "\n";
    @file_put_contents($backup_file, $backup_data, FILE_APPEND);
    
    header('Location: ' . $error_url . '&reason=server');
    exit;
}
?>

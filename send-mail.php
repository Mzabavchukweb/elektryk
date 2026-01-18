<?php
/**
 * RS ELECTRICS - Form Handler
 * Wersja z logowaniem błędów i PHPMailer fallback
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

// Zabezpieczenie przed bezpośrednim dostępem
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Nieprawidłowa metoda HTTP", ['method' => $_SERVER['REQUEST_METHOD']]);
    header('Location: /');
    exit;
}

// Ustawienia
$to_email = 'kontakt@elektrykgorzow.com';
$redirect_url = 'https://elektrykgorzow.com/dziekujemy.html';
$error_url = 'https://elektrykgorzow.com/kontakt.html?error=1';

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
// WAŻNE: Na wielu hostingach trzeba użyć adresu email z domeny hostingu
$from_email = 'noreply@elektrykgorzow.com';
$headers = "From: RS ELECTRICS <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";

// Dodatkowe nagłówki dla lepszej kompatybilności
$headers .= "X-Priority: 3\r\n";
$headers .= "X-MSMail-Priority: Normal\r\n";

// Próba wysyłki przez mail()
// Uwaga: mail() może zwrócić true nawet jeśli mail nie został wysłany!
// Na wielu hostingach mail() jest zablokowany lub wymaga specjalnej konfiguracji

logError("Attempting to send email via mail()", [
    'to' => $to_email,
    'from' => 'noreply@elektrykgorzow.com',
    'subject' => $email_subject,
    'ip' => $client_ip
]);

// Próba 1: Standardowe mail() z dodatkowym parametrem -f (często pomaga)
// Parametr -f wymusza użycie adresu From i czasem pomaga na hostingach
$additional_params = '-f' . $from_email;

// Wyłącz error suppression żeby zobaczyć błędy w logach
$mail_sent = mail($to_email, $email_subject, $email_body, $headers, $additional_params);

// Sprawdź błędy PHP
$last_error = error_get_last();
if ($last_error && strpos($last_error['message'], 'mail') !== false) {
    logError("PHP error during mail() call", [
        'error' => $last_error['message'],
        'file' => $last_error['file'],
        'line' => $last_error['line']
    ]);
}

if ($mail_sent) {
    logError("mail() returned TRUE - email may or may not have been sent", [
        'to' => $to_email,
        'subject' => $form_subject,
        'ip' => $client_ip,
        'note' => 'mail() can return true even if email was blocked by server'
    ]);
    
    // UWAGA: Na wielu hostingach mail() zwraca true ale nie wysyła!
    // Sprawdź czy mail faktycznie przyszedł do skrzynki
    // Jeśli nie - użyj PHPMailer z SMTP
    
    header('Location: ' . $redirect_url);
    exit;
} else {
    logError("mail() returned FALSE - email definitely not sent", [
        'to' => $to_email,
        'ip' => $client_ip
    ]);
}

// Jeśli mail() nie zadziałało lub zwróciło false, spróbuj alternatywne metody
logError("mail() failed or returned false, attempting alternative methods", [
    'to' => $to_email,
    'ip' => $client_ip,
    'mail_function_exists' => function_exists('mail')
]);

// Metoda 1: Spróbuj użyć ini_set dla sendmail_path (jeśli dostępne)
$sendmail_available = false;
if (function_exists('ini_set')) {
    // Niektóre hostingi wymagają ustawienia sendmail_path
    $current_sendmail = ini_get('sendmail_path');
    logError("Current sendmail_path", ['path' => $current_sendmail]);
}

// Metoda 2: Spróbuj przez PHPMailer (jeśli dostępny)
$phpmailer_path = __DIR__ . '/PHPMailer/PHPMailer.php';
if (file_exists($phpmailer_path)) {
    require_once $phpmailer_path;
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    require_once __DIR__ . '/PHPMailer/Exception.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $phpmailer = new PHPMailer(true);
    
    try {
        // Konfiguracja SMTP (dostosuj do swojego hostingu)
        $phpmailer->isSMTP();
        $phpmailer->Host = 'smtp.seohost.pl'; // lub inny SMTP hostingu
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'kontakt@elektrykgorzow.com';
        $phpmailer->Password = 'HASŁO_TUTAJ'; // TODO: ustaw hasło
        $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $phpmailer->Port = 587;
        $phpmailer->CharSet = 'UTF-8';
        
        // Nadawca i odbiorca
        $phpmailer->setFrom('noreply@elektrykgorzow.com', 'RS ELECTRICS');
        $phpmailer->addAddress($to_email);
        $phpmailer->addReplyTo($email, $name);
        
        // Treść
        $phpmailer->Subject = $email_subject;
        $phpmailer->Body = $email_body;
        $phpmailer->isHTML(false);
        
        $phpmailer->send();
        
        logError("Email sent successfully via PHPMailer", [
            'to' => $to_email,
            'subject' => $form_subject,
            'ip' => $client_ip
        ]);
        
        header('Location: ' . $redirect_url);
        exit;
        
    } catch (Exception $e) {
        logError("PHPMailer failed", [
            'error' => $phpmailer->ErrorInfo,
            'ip' => $client_ip
        ]);
    }
}

// Metoda 3: Spróbuj prostego SMTP przez socket (fallback)
logError("Attempting simple SMTP fallback", ['to' => $to_email]);

// Prosty SMTP dla SEOHost (dostosuj dane)
$smtp_host = 'smtp.seohost.pl'; // lub inny SMTP hostingu
$smtp_port = 587;
$smtp_user = 'kontakt@elektrykgorzow.com';
$smtp_pass = ''; // TODO: ustaw hasło jeśli potrzebne

// Próba prostego SMTP (tylko jeśli inne metody nie działają)
$smtp_success = false;
if (!empty($smtp_user) && !empty($smtp_pass)) {
    try {
        $smtp = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
        if ($smtp) {
            // Podstawowa komunikacja SMTP (uproszczona)
            fgets($smtp, 515);
            fputs($smtp, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
            fgets($smtp, 515);
            fputs($smtp, "AUTH LOGIN\r\n");
            fgets($smtp, 515);
            fputs($smtp, base64_encode($smtp_user) . "\r\n");
            fgets($smtp, 515);
            fputs($smtp, base64_encode($smtp_pass) . "\r\n");
            $auth_response = fgets($smtp, 515);
            
            if (strpos($auth_response, '235') !== false) {
                // Autoryzacja OK - wyślij mail
                fputs($smtp, "MAIL FROM: <" . $from_email . ">\r\n");
                fgets($smtp, 515);
                fputs($smtp, "RCPT TO: <" . $to_email . ">\r\n");
                fgets($smtp, 515);
                fputs($smtp, "DATA\r\n");
                fgets($smtp, 515);
                fputs($smtp, "Subject: " . $email_subject . "\r\n");
                fputs($smtp, "From: " . $from_email . "\r\n");
                fputs($smtp, "To: " . $to_email . "\r\n");
                fputs($smtp, $headers . "\r\n");
                fputs($smtp, $email_body . "\r\n.\r\n");
                fgets($smtp, 515);
                fputs($smtp, "QUIT\r\n");
                fclose($smtp);
                
                $smtp_success = true;
                logError("Email sent successfully via simple SMTP", [
                    'to' => $to_email,
                    'subject' => $form_subject,
                    'ip' => $client_ip
                ]);
                
                header('Location: ' . $redirect_url);
                exit;
            } else {
                fclose($smtp);
                logError("SMTP authentication failed", ['response' => $auth_response]);
            }
        } else {
            logError("SMTP connection failed", ['error' => "$errstr ($errno)"]);
        }
    } catch (Exception $e) {
        logError("SMTP exception", ['error' => $e->getMessage()]);
    }
}

// Jeśli wszystko się nie powiodło, przekieruj z błędem
logError("All email sending methods failed - CRITICAL", [
    'to' => $to_email,
    'ip' => $client_ip,
    'mail_function_exists' => function_exists('mail'),
    'phpmailer_available' => file_exists($phpmailer_path),
    'smtp_attempted' => !empty($smtp_user) && !empty($smtp_pass)
]);

// ZAPISZ DANE DO PLIKU JAKO BACKUP (jeśli mail nie działa)
$backup_file = __DIR__ . '/form-submissions-backup.txt';
$backup_data = date('Y-m-d H:i:s') . " | " . $name . " | " . $phone . " | " . $email . " | " . $subject . "\n";
@file_put_contents($backup_file, $backup_data, FILE_APPEND);
logError("Saved form submission to backup file", ['file' => $backup_file]);

header('Location: ' . $error_url . '&reason=server');
exit;
?>

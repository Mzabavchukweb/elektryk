<?php
/**
 * Test wysyłki email - sprawdza czy mail() faktycznie wysyła maile
 * Usuń po testach!
 */

header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$test_email = 'kontakt@elektrykgorzow.com';
$test_subject = 'Test wysyłki email - ' . date('Y-m-d H:i:s');
$test_body = "To jest test wysyłki email z serwera.\n\n";
$test_body .= "Jeśli otrzymujesz ten mail, oznacza to że mail() działa poprawnie.\n\n";
$test_body .= "Data: " . date('Y-m-d H:i:s') . "\n";
$test_body .= "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
$test_body .= "PHP: " . phpversion() . "\n";

$headers = "From: Test <noreply@elektrykgorzow.com>\r\n";
$headers .= "Reply-To: noreply@elektrykgorzow.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test wysyłki email</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 10px 5px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>📧 Test wysyłki email</h1>
    
    <?php
    if (isset($_GET['send'])) {
        echo '<div class="info"><h3>Wysyłanie testowego maila...</h3></div>';
        
        // Wyłącz error suppression żeby zobaczyć błędy
        $result = mail($test_email, $test_subject, $test_body, $headers);
        
        $last_error = error_get_last();
        
        echo '<div class="' . ($result ? 'success' : 'error') . '">';
        echo '<h3>' . ($result ? '✅ mail() zwróciło TRUE' : '❌ mail() zwróciło FALSE') . '</h3>';
        
        if ($result) {
            echo '<p><strong>UWAGA:</strong> mail() zwróciło TRUE, ale to NIE oznacza że mail faktycznie został wysłany!</p>';
            echo '<p>Na wielu hostingach mail() zwraca true, ale maile są blokowane lub nie dochodzą.</p>';
            echo '<p><strong>Sprawdź skrzynkę:</strong> ' . $test_email . '</p>';
            echo '<p><strong>Sprawdź folder SPAM!</strong></p>';
        }
        
        if ($last_error) {
            echo '<p><strong>Ostatni błąd PHP:</strong></p>';
            echo '<pre>' . print_r($last_error, true) . '</pre>';
        }
        
        echo '</div>';
        
        // Sprawdź konfigurację sendmail
        echo '<div class="info">';
        echo '<h3>Konfiguracja sendmail:</h3>';
        echo '<pre>';
        echo 'sendmail_path: ' . ini_get('sendmail_path') . "\n";
        echo 'SMTP: ' . ini_get('SMTP') . "\n";
        echo 'smtp_port: ' . ini_get('smtp_port') . "\n";
        echo 'sendmail_from: ' . ini_get('sendmail_from') . "\n";
        echo '</pre>';
        echo '</div>';
        
        echo '<div class="warning">';
        echo '<h3>⚠️ Co dalej?</h3>';
        echo '<p>Jeśli mail NIE przyszedł do skrzynki (nawet w SPAM), oznacza to że:</p>';
        echo '<ul>';
        echo '<li>mail() jest zablokowane przez hosting</li>';
        echo '<li>lub wymagana jest konfiguracja SMTP</li>';
        echo '</ul>';
        echo '<p><strong>Rozwiązanie:</strong> Skonfiguruj PHPMailer z SMTP (zobacz README-FORMULARZE.md)</p>';
        echo '</div>';
    }
    ?>
    
    <div class="info">
        <h3>Test wysyłki:</h3>
        <p>Kliknij przycisk poniżej aby wysłać testowy mail na: <strong><?php echo $test_email; ?></strong></p>
        <p><strong>UWAGA:</strong> Sprawdź skrzynkę i folder SPAM po wysłaniu!</p>
        <a href="?send=1"><button>Wyślij testowy email</button></a>
    </div>
    
    <div class="info">
        <h3>Informacje o serwerze:</h3>
        <pre>
PHP Version: <?php echo phpversion(); ?>
Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? __DIR__; ?>

Funkcja mail() dostępna: <?php echo function_exists('mail') ? 'TAK ✅' : 'NIE ❌'; ?>

Konfiguracja sendmail:
sendmail_path: <?php echo ini_get('sendmail_path') ?: 'nie ustawione'; ?>
SMTP: <?php echo ini_get('SMTP') ?: 'nie ustawione'; ?>
smtp_port: <?php echo ini_get('smtp_port') ?: 'nie ustawione'; ?>
        </pre>
    </div>
    
    <div class="warning">
        <p><strong>⚠️ WAŻNE:</strong> Usuń ten plik (test-email.php) po zakończeniu testów!</p>
    </div>
</body>
</html>

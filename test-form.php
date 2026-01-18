<?php
/**
 * Test formularza - sprawdza czy PHP działa i czy send-mail.php jest dostępny
 * Usuń ten plik po testach!
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test formularza - RS ELECTRICS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 Test formularza kontaktowego</h1>
    
    <?php
    $checks = [];
    
    // Test 1: PHP działa
    $checks['php'] = [
        'name' => 'PHP działa',
        'status' => true,
        'info' => 'Wersja PHP: ' . phpversion()
    ];
    
    // Test 2: send-mail.php istnieje
    $send_mail_exists = file_exists(__DIR__ . '/send-mail.php');
    $checks['send_mail_file'] = [
        'name' => 'Plik send-mail.php istnieje',
        'status' => $send_mail_exists,
        'info' => $send_mail_exists ? 'Plik znaleziony' : 'Plik NIE znaleziony!'
    ];
    
    // Test 3: Funkcja mail() dostępna
    $mail_function = function_exists('mail');
    $checks['mail_function'] = [
        'name' => 'Funkcja mail() dostępna',
        'status' => $mail_function,
        'info' => $mail_function ? 'Funkcja dostępna' : 'Funkcja NIE dostępna - użyj PHPMailer'
    ];
    
    // Test 4: Uprawnienia do zapisu (dla logów)
    $log_dir_writable = is_writable(__DIR__);
    $checks['log_dir'] = [
        'name' => 'Katalog zapisywalny (dla logów)',
        'status' => $log_dir_writable,
        'info' => $log_dir_writable ? 'Można zapisywać logi' : 'Brak uprawnień do zapisu!'
    ];
    
    // Test 5: PHPMailer dostępny (opcjonalnie)
    $phpmailer_exists = file_exists(__DIR__ . '/PHPMailer/PHPMailer.php');
    $checks['phpmailer'] = [
        'name' => 'PHPMailer dostępny (opcjonalnie)',
        'status' => $phpmailer_exists,
        'info' => $phpmailer_exists ? 'PHPMailer znaleziony' : 'PHPMailer nie jest wymagany jeśli mail() działa'
    ];
    
    // Wyświetl wyniki
    foreach ($checks as $check) {
        if ($check['status']) {
            echo '<div class="success">✅ ' . $check['name'] . '<br><small>' . $check['info'] . '</small></div>';
        } else {
            echo '<div class="error">❌ ' . $check['name'] . '<br><small>' . $check['info'] . '</small></div>';
        }
    }
    
    // Test formularza
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_submit'])) {
        echo '<div class="info"><h3>Wynik testu wysyłki:</h3>';
        
        // Sprawdź czy send-mail.php istnieje
        if ($send_mail_exists) {
            echo '<p>✅ Formularz został przesłany. Sprawdź:</p>';
            echo '<ul>';
            echo '<li>Skrzynkę email: kontakt@elektrykgorzow.com</li>';
            echo '<li>Plik logów: form-errors.log</li>';
            echo '<li>Czy nastąpiło przekierowanie na dziekujemy.html</li>';
            echo '</ul>';
        } else {
            echo '<p>❌ Plik send-mail.php nie istnieje!</p>';
        }
        echo '</div>';
    }
    ?>
    
    <div class="info">
        <h3>Informacje o serwerze:</h3>
        <pre>
PHP Version: <?php echo phpversion(); ?>
Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? __DIR__; ?>
Current File: <?php echo __FILE__; ?>
        </pre>
    </div>
    
    <form method="POST" action="send-mail.php">
        <h3>Test formularza:</h3>
        <input type="hidden" name="_subject" value="Test formularza">
        <input type="text" name="_honey" style="display:none">
        
        <label>Imię i nazwisko:</label>
        <input type="text" name="name" value="Test User" required>
        
        <label>Telefon:</label>
        <input type="tel" name="phone" value="600000000" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="test@example.com" required>
        
        <label>Wiadomość:</label>
        <textarea name="message" rows="3">To jest test formularza</textarea>
        
        <button type="submit">Wyślij test</button>
    </form>
    
    <div class="info">
        <p><strong>⚠️ WAŻNE:</strong> Usuń ten plik (test-form.php) po zakończeniu testów!</p>
    </div>
</body>
</html>

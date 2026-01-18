<?php
/**
 * Test send-mail.php - sprawdza czy skrypt jest wywoływany
 * Usuń po testach!
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test send-mail.php</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔍 Test send-mail.php</h1>
    
    <?php
    // Sprawdź czy send-mail.php istnieje
    $send_mail_exists = file_exists(__DIR__ . '/send-mail.php');
    
    if ($send_mail_exists) {
        echo '<div class="success">✅ Plik send-mail.php istnieje</div>';
    } else {
        echo '<div class="error">❌ Plik send-mail.php NIE istnieje!</div>';
    }
    
    // Sprawdź logi
    $log_file = __DIR__ . '/form-errors.log';
    if (file_exists($log_file)) {
        echo '<div class="info">';
        echo '<h3>Ostatnie wpisy z form-errors.log:</h3>';
        $log_content = file_get_contents($log_file);
        $log_lines = explode("\n", $log_content);
        $recent_lines = array_slice(array_filter($log_lines), -10); // Ostatnie 10 linii
        echo '<pre>' . htmlspecialchars(implode("\n", $recent_lines)) . '</pre>';
        echo '</div>';
    } else {
        echo '<div class="info">Plik form-errors.log nie istnieje jeszcze (zostanie utworzony przy pierwszym wywołaniu)</div>';
    }
    ?>
    
    <div class="info">
        <h3>Test wysyłki formularza:</h3>
        <p>Wypełnij formularz poniżej i wyślij. Następnie sprawdź:</p>
        <ul>
            <li>Czy nastąpiło przekierowanie na <code>dziekujemy.html</code>?</li>
            <li>Czy mail przyszedł do <code>kontakt@elektrykgorzow.com</code>?</li>
            <li>Czy w <code>form-errors.log</code> jest wpis o wysyłce?</li>
        </ul>
    </div>
    
    <form action="send-mail.php" method="POST">
        <input type="hidden" name="_subject" value="Test z test-send-mail.php">
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
        <h3>Jak sprawdzić czy działa:</h3>
        <ol>
            <li>Wyślij formularz powyżej</li>
            <li>Sprawdź czy nastąpiło przekierowanie</li>
            <li>Sprawdź skrzynkę <code>kontakt@elektrykgorzow.com</code></li>
            <li>Sprawdź plik <code>form-errors.log</code> na serwerze</li>
        </ol>
    </div>
    
    <div class="error">
        <p><strong>⚠️ WAŻNE:</strong> Usuń ten plik (test-send-mail.php) po zakończeniu testów!</p>
    </div>
</body>
</html>

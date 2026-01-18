# 🔧 Instrukcja konfiguracji SMTP dla SEOHost

## Problem

`mail()` zwraca `true`, ale maile nie dochodzą do skrzynki. To częsty problem na hostingach - funkcja `mail()` jest dostępna, ale serwer blokuje wysyłkę lub wymaga konfiguracji SMTP.

## Rozwiązanie: Konfiguracja SMTP przez PHPMailer

### Krok 1: Pobierz PHPMailer

**Opcja A: Przez Composer (jeśli masz dostęp)**
```bash
cd /path/to/your/website
composer require phpmailer/phpmailer
```

**Opcja B: Pobierz ręcznie**
1. Pobierz z: https://github.com/PHPMailer/PHPMailer/releases
2. Rozpakuj do folderu `PHPMailer/` w głównym katalogu strony
3. Struktura powinna być:
   ```
   /PHPMailer/
     /PHPMailer.php
     /SMTP.php
     /Exception.php
   ```

### Krok 2: Sprawdź dane SMTP w panelu SEOHost

1. Zaloguj się do panelu SEOHost
2. Przejdź do sekcji **"Poczta"** lub **"Email"**
3. Znajdź ustawienia dla `kontakt@elektrykgorzow.com`
4. Sprawdź:
   - **Host SMTP:** (np. `smtp.seohost.pl` lub `mail.elektrykgorzow.com`)
   - **Port:** (zwykle `587` dla STARTTLS lub `465` dla SSL)
   - **Użytkownik:** `kontakt@elektrykgorzow.com`
   - **Hasło:** hasło do skrzynki email

### Krok 3: Zaktualizuj send-mail.php

Otwórz `send-mail.php` i znajdź sekcję PHPMailer (około linii 200). Zaktualizuj:

```php
$phpmailer->Host = 'smtp.seohost.pl'; // ZMIEŃ na właściwy host SMTP
$phpmailer->SMTPAuth = true;
$phpmailer->Username = 'kontakt@elektrykgorzow.com';
$phpmailer->Password = 'TWOJE_HASŁO_TUTAJ'; // ZMIEŃ na hasło do skrzynki
$phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // lub ENCRYPTION_SMTPS dla portu 465
$phpmailer->Port = 587; // lub 465 dla SSL
```

### Krok 4: Przetestuj

1. Wyślij formularz ze strony
2. Sprawdź `form-errors.log` - powinien być wpis "Email sent successfully via PHPMailer"
3. Sprawdź skrzynkę `kontakt@elektrykgorzow.com`

---

## Alternatywne rozwiązanie: Prosty SMTP bez PHPMailer

Jeśli nie chcesz używać PHPMailer, możesz użyć prostego SMTP przez socket (już dodane w `send-mail.php`).

Znajdź w `send-mail.php` sekcję "Metoda 3: Prosty SMTP" (około linii 250) i zaktualizuj:

```php
$smtp_host = 'smtp.seohost.pl'; // ZMIEŃ
$smtp_port = 587; // ZMIEŃ jeśli inny
$smtp_user = 'kontakt@elektrykgorzow.com';
$smtp_pass = 'TWOJE_HASŁO_TUTAJ'; // ZMIEŃ
```

---

## Najczęstsze problemy

### Problem: "SMTP authentication failed"
**Rozwiązanie:** Sprawdź czy hasło jest poprawne i czy używasz właściwego hosta SMTP.

### Problem: "Connection refused" lub timeout
**Rozwiązanie:** 
- Sprawdź czy port jest poprawny (587 lub 465)
- Sprawdź czy hosting pozwala na połączenia SMTP z zewnątrz
- Spróbuj użyć `mail.elektrykgorzow.com` zamiast `smtp.seohost.pl`

### Problem: "Could not instantiate mail function"
**Rozwiązanie:** PHPMailer nie jest poprawnie zainstalowany - sprawdź ścieżki do plików.

---

## Testowanie

Po konfiguracji:

1. Otwórz `test-email.php` na serwerze
2. Kliknij "Wyślij testowy email"
3. Sprawdź skrzynkę i folder SPAM
4. Sprawdź `form-errors.log` - powinien być wpis o sukcesie

---

## Kontakt z supportem SEOHost

Jeśli nadal nie działa, skontaktuj się z supportem SEOHost i zapytaj:

1. Jaki jest host SMTP dla domeny `elektrykgorzow.com`?
2. Jaki port SMTP należy użyć? (587 czy 465)
3. Czy wymagana jest autoryzacja SMTP?
4. Czy są jakieś ograniczenia dotyczące wysyłki maili przez PHP?

---

## Bezpieczeństwo

⚠️ **WAŻNE:** Nigdy nie commituj hasła do gita!

Jeśli używasz gita, dodaj do `.gitignore`:
```
send-mail.php
```

LUB użyj zmiennych środowiskowych lub pliku konfiguracyjnego poza repozytorium.

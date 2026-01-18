# Instrukcja konfiguracji formularzy kontaktowych

## Diagnoza problemów

### 1. Sprawdź logi błędów

Po wdrożeniu sprawdź plik `form-errors.log` w głównym katalogu strony. Zawiera on szczegółowe informacje o:
- Błędach wysyłki maili
- Przekroczeniach rate limit
- Problemach z walidacją
- Wykrytych botach (honeypot)

**Lokalizacja:** `/form-errors.log` (w katalogu głównym strony)

### 2. Sprawdź czy mail() działa

Wiele hostingów blokuje funkcję `mail()`. Jeśli w logach widzisz "mail() failed", musisz skonfigurować PHPMailer.

## Konfiguracja PHPMailer (jeśli mail() nie działa)

### Krok 1: Pobierz PHPMailer

```bash
cd /path/to/your/website
composer require phpmailer/phpmailer
```

LUB pobierz ręcznie z: https://github.com/PHPMailer/PHPMailer

### Krok 2: Skonfiguruj SMTP w send-mail.php

Otwórz `send-mail.php` i znajdź sekcję PHPMailer (około linii 120). Zaktualizuj:

```php
$phpmailer->Host = 'smtp.seohost.pl'; // lub inny SMTP hostingu
$phpmailer->Username = 'kontakt@elektrykgorzow.com';
$phpmailer->Password = 'TWOJE_HASŁO_EMAIL'; // Hasło do skrzynki email
```

**Dane SMTP dla SEOHost:**
- Host: `smtp.seohost.pl` (lub sprawdź w panelu)
- Port: `587` (STARTTLS) lub `465` (SSL)
- Użytkownik: `kontakt@elektrykgorzow.com`
- Hasło: hasło do skrzynki email

### Krok 3: Sprawdź w panelu SEOHost

1. Zaloguj się do panelu SEOHost
2. Znajdź sekcję "Poczta" lub "Email"
3. Sprawdź ustawienia SMTP dla `kontakt@elektrykgorzow.com`
4. Użyj tych samych danych w `send-mail.php`

## Testowanie formularzy

### Test 1: Podstawowy test wysyłki

1. Otwórz stronę główną: `https://elektrykgorzow.com`
2. Wypełnij formularz prawdziwymi danymi
3. Kliknij "Wyślij zapytanie"
4. Sprawdź czy nastąpiło przekierowanie na `dziekujemy.html`
5. Sprawdź skrzynkę `kontakt@elektrykgorzow.com`

### Test 2: Test walidacji

1. Spróbuj wysłać formularz bez wypełnienia pól wymaganych
2. Powinien pojawić się komunikat błędu w przeglądarce (HTML5 validation)

### Test 3: Test rate limiting

1. Wyślij formularz 6 razy w ciągu 5 minut z tego samego IP
2. 6. próba powinna przekierować na `kontakt.html?error=1&reason=rate_limit`
3. Sprawdź logi - powinien być wpis "Rate limit exceeded"

### Test 4: Test honeypot (antyspam)

1. W DevTools otwórz konsolę
2. Znajdź pole `_honey` w formularzu
3. Wypełnij je wartością
4. Wyślij formularz
5. Powinno przekierować na `dziekujemy.html` (bez wysyłki)
6. W logach powinien być wpis "Honeypot triggered"

### Test 5: Sprawdź logi błędów

1. Po każdym teście sprawdź plik `form-errors.log`
2. Jeśli są błędy, skopiuj je i prześlij do supportu

## Rozwiązywanie problemów

### Problem: Formularz nie wysyła się

**Sprawdź:**
1. Czy `script.js` jest załadowany? (DevTools → Network)
2. Czy w konsoli są błędy JavaScript?
3. Czy formularz ma `action="send-mail.php"`?
4. Czy `send-mail.php` istnieje na serwerze?

### Problem: Mail nie dochodzi

**Sprawdź:**
1. Plik `form-errors.log` - co jest w logach?
2. Czy funkcja `mail()` działa? (sprawdź w phpinfo)
3. Czy PHPMailer jest skonfigurowany?
4. Czy dane SMTP są poprawne?
5. Sprawdź folder SPAM w skrzynce email

### Problem: Błąd 500 lub biała strona

**Sprawdź:**
1. Czy PHP jest włączone na hostingu?
2. Czy są błędy składni w `send-mail.php`?
3. Sprawdź logi błędów PHP w panelu SEOHost

### Problem: Rate limit działa zbyt agresywnie

**Rozwiązanie:**
Otwórz `send-mail.php` i zmień:
```php
$rate_limit_window = 300; // czas w sekundach (5 minut)
$rate_limit_max = 5; // max formularzy
```

## Checklist przed wdrożeniem

- [ ] `send-mail.php` jest w głównym katalogu strony
- [ ] Wszystkie formularze mają `action="send-mail.php"`
- [ ] Wszystkie formularze mają `method="POST"`
- [ ] Pole `_honey` jest ukryte w każdym formularzu
- [ ] `form-errors.log` jest w `.gitignore`
- [ ] `rate-limit.json` jest w `.gitignore`
- [ ] PHPMailer jest skonfigurowany (jeśli potrzebny)
- [ ] Dane SMTP są poprawne
- [ ] Test wysyłki formularza działa
- [ ] Mail dochodzi do skrzynki

## Kontakt w razie problemów

Jeśli formularze nadal nie działają:
1. Sprawdź `form-errors.log`
2. Sprawdź logi PHP w panelu SEOHost
3. Skontaktuj się z supportem SEOHost w sprawie konfiguracji SMTP

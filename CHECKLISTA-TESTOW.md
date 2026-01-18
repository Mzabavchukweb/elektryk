# ✅ Checklista testów formularzy kontaktowych

## Przed testami

- [ ] Wgrałeś wszystkie pliki na serwer (HTML, CSS, JS, PHP)
- [ ] `send-mail.php` jest w głównym katalogu strony
- [ ] PHP jest włączone w panelu SEOHost
- [ ] SSL/HTTPS jest włączone

---

## Test 1: Sprawdzenie podstawowej konfiguracji

**Kroki:**
1. Otwórz w przeglądarce: `https://elektrykgorzow.com/test-form.php`
2. Sprawdź czy strona się ładuje
3. Sprawdź wszystkie zielone ✅ - powinny być wszystkie zaznaczone

**Oczekiwany wynik:**
- ✅ PHP działa
- ✅ Plik send-mail.php istnieje
- ✅ Funkcja mail() dostępna (lub PHPMailer dostępny)
- ✅ Katalog zapisywalny

**Jeśli coś nie działa:**
- Sprawdź czy pliki są w odpowiednim katalogu
- Sprawdź uprawnienia do plików (chmod 644 dla plików, 755 dla katalogów)

---

## Test 2: Test wysyłki formularza (podstawowy)

**Kroki:**
1. Otwórz: `https://elektrykgorzow.com`
2. Przewiń do formularza kontaktowego
3. Wypełnij formularz:
   - Imię: `Jan Testowy`
   - Telefon: `600000000`
   - Email: `test@example.com`
   - Temat: `Test formularza`
4. Zaznacz checkbox "Akceptuję politykę prywatności"
5. Kliknij "Wyślij zapytanie"

**Oczekiwany wynik:**
- Formularz się wysyła (przycisk pokazuje "Wysyłanie...")
- Następuje przekierowanie na `dziekujemy.html`
- W skrzynce `kontakt@elektrykgorzow.com` przychodzi mail

**Jeśli nie działa:**
- Sprawdź konsolę przeglądarki (F12) - czy są błędy JavaScript?
- Sprawdź Network tab - czy request do `send-mail.php` został wysłany?
- Sprawdź plik `form-errors.log` na serwerze

---

## Test 3: Test walidacji HTML5

**Kroki:**
1. Otwórz formularz na stronie głównej
2. **NIE wypełniaj żadnych pól**
3. Kliknij "Wyślij zapytanie"

**Oczekiwany wynik:**
- Przeglądarka pokazuje komunikaty błędu przy polach wymaganych
- Formularz NIE wysyła się
- Nie ma przekierowania

**Jeśli nie działa:**
- Sprawdź czy pola mają atrybut `required`
- Sprawdź czy typy inputów są poprawne (`type="email"`, `type="tel"`)

---

## Test 4: Test walidacji emaila

**Kroki:**
1. Wypełnij formularz
2. W polu email wpisz: `niepoprawny-email`
3. Kliknij "Wyślij zapytanie"

**Oczekiwany wynik:**
- Przeglądarka pokazuje błąd "Podaj prawidłowy adres e-mail"
- Formularz NIE wysyła się

---

## Test 5: Test honeypot (antyspam)

**Kroki:**
1. Otwórz DevTools (F12)
2. Przejdź do zakładki Elements/Inspector
3. Znajdź w formularzu pole: `<input type="text" name="_honey" style="display:none">`
4. Zmień `display:none` na `display:block` (lub usuń style)
5. Wypełnij to pole jakąkolwiek wartością
6. Wypełnij resztę formularza poprawnie
7. Wyślij formularz

**Oczekiwany wynik:**
- Formularz się wysyła
- Następuje przekierowanie na `dziekujemy.html`
- **ALE** mail NIE przychodzi do skrzynki
- W pliku `form-errors.log` jest wpis: "Honeypot triggered - bot detected"

---

## Test 6: Test rate limiting

**Kroki:**
1. Wyślij formularz 5 razy z tego samego IP (możesz odświeżać stronę)
2. Za 6. razem spróbuj wysłać formularz

**Oczekiwany wynik:**
- Pierwsze 5 formularzy: działają normalnie
- 6. formularz: przekierowanie na `kontakt.html?error=1&reason=rate_limit`
- Na stronie kontakt pojawia się czerwony komunikat błędu
- W logach: "Rate limit exceeded"

**Uwaga:** Rate limit resetuje się po 5 minutach

---

## Test 7: Test komunikatów błędów

**Kroki:**
1. Otwórz: `https://elektrykgorzow.com/kontakt.html?error=1`
2. Sprawdź czy pojawia się czerwony komunikat błędu

**Oczekiwany wynik:**
- Pojawia się komunikat: "Błąd wysyłki formularza. Prosimy spróbować ponownie..."
- Komunikat znika po 10 sekundach (lub można go zamknąć)

---

## Test 8: Test na różnych stronach

**Kroki:**
Przetestuj formularze na różnych stronach:
- [ ] Strona główna (`index.html`)
- [ ] Kontakt (`kontakt.html`)
- [ ] Instalacje elektryczne (`instalacje-elektryczne.html`)
- [ ] Pogotowie elektryczne (`pogotowie-elektryczne.html`)
- [ ] Inna strona usługi (dowolna)

**Oczekiwany wynik:**
- Wszystkie formularze działają identycznie
- Wszystkie przekierowują na `dziekujemy.html`
- Maile przychodzą z odpowiednim tematem (np. "Zapytanie: Instalacje elektryczne")

---

## Test 9: Sprawdzenie logów błędów

**Kroki:**
1. Zaloguj się przez FTP/SFTP do serwera
2. Otwórz plik `form-errors.log` w głównym katalogu
3. Sprawdź ostatnie wpisy

**Oczekiwany wynik:**
- Jeśli wszystko działa: logi są puste lub zawierają tylko informacje o sukcesie
- Jeśli są błędy: każdy błąd ma timestamp i szczegóły

**Przykładowe wpisy:**
```
2026-01-18 15:30:45 - Email sent successfully via mail() | Data: {"to":"kontakt@elektrykgorzow.com","subject":"Zapytanie: Instalacje elektryczne","ip":"192.168.1.1"}
```

---

## Test 10: Test na urządzeniu mobilnym

**Kroki:**
1. Otwórz stronę na telefonie
2. Wypełnij formularz
3. Wyślij

**Oczekiwany wynik:**
- Formularz działa identycznie jak na desktop
- Przekierowanie działa
- Mail przychodzi

---

## Test 11: Test z różnymi przeglądarkami

**Kroki:**
Przetestuj w:
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

**Oczekiwany wynik:**
- Wszystkie przeglądarki działają identycznie

---

## Rozwiązywanie problemów

### Problem: Formularz nie wysyła się wcale

**Sprawdź:**
1. Konsola przeglądarki (F12) - czy są błędy JS?
2. Network tab - czy request do `send-mail.php` jest wysyłany?
3. Czy `script.js` jest załadowany?

**Rozwiązanie:**
- Sprawdź czy `script.src.js` został skompilowany do `script.js`
- Sprawdź czy nie ma błędów składni w JavaScript

---

### Problem: Mail nie dochodzi

**Sprawdź:**
1. Plik `form-errors.log` - co jest w logach?
2. Folder SPAM w skrzynce email
3. Czy funkcja `mail()` działa? (sprawdź `test-form.php`)

**Rozwiązanie:**
- Jeśli `mail()` nie działa: skonfiguruj PHPMailer (zobacz `README-FORMULARZE.md`)
- Sprawdź dane SMTP w panelu SEOHost

---

### Problem: Błąd 500 lub biała strona

**Sprawdź:**
1. Logi błędów PHP w panelu SEOHost
2. Czy `send-mail.php` ma poprawne uprawnienia (chmod 644)?
3. Czy nie ma błędów składni w PHP?

**Rozwiązanie:**
- Sprawdź składnię PHP: `php -l send-mail.php`
- Sprawdź logi błędów w panelu hostingu

---

### Problem: Rate limit zbyt agresywny

**Rozwiązanie:**
Otwórz `send-mail.php` i zmień:
```php
$rate_limit_window = 300; // zwiększ czas (np. 600 = 10 minut)
$rate_limit_max = 5; // zwiększ limit (np. 10)
```

---

## Po zakończeniu testów

- [ ] Usuń plik `test-form.php` z serwera
- [ ] Sprawdź czy `form-errors.log` nie zawiera wrażliwych danych
- [ ] Dodaj `form-errors.log` i `rate-limit.json` do `.gitignore` (już dodane)
- [ ] Zaktualizuj dokumentację jeśli znalazłeś problemy

---

## Kontakt w razie problemów

Jeśli po wykonaniu wszystkich testów formularze nadal nie działają:

1. **Zbierz informacje:**
   - Zrzut ekranu z konsoli przeglądarki
   - Zawartość `form-errors.log`
   - Zawartość logów PHP z panelu SEOHost

2. **Sprawdź:**
   - Czy PHP działa? (`test-form.php`)
   - Czy `send-mail.php` jest dostępny?
   - Czy funkcja `mail()` działa?

3. **Skontaktuj się z:**
   - Supportem SEOHost (konfiguracja SMTP)
   - Deweloperem (jeśli problem w kodzie)

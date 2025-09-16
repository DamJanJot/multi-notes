# Notatnik (autosave, SPA)

Lekka aplikacja notatników/dokumentów z logowaniem (tabela `uzytkownicy`), zapisem w `dokumenty` i podglądem na żywo — bez przeładowań.

## Wymagania
- PHP 8.0+
- MySQL / MariaDB
- Włączone sesje w PHP

## Instalacja
1. Utwórz bazę i tabele:
   ```sql
   -- plik: schema.sql
   ```
   Uruchom `schema.sql` w Twoim kliencie MySQL.
2. Skonfiguruj połączenie DB w `config.php`.
3. Wgraj katalog `notatnik/` na serwer (np. `public_html/notatnik`).
4. Wejdź w przeglądarce i zaloguj się:
   - **Email:** `demo@demo.pl`
   - **Hasło:** `demo12345`

> Uwaga: w kodzie logowania wspieram zarówno hashe `password_hash(...)`, jak i przejściowo czysty tekst (dla demo). W produkcji zalecam przejście wyłącznie na hashe.

## Struktura
```
notatnik/
├─ assets/ (frontend: CSS + JS)
├─ api/ (endpointy JSON: login, logout, documents)
├─ lib/ (DB + sesje)
├─ config.php
└─ index.php
```

## Funkcje
- Autosave (debounce 600 ms) i podgląd po prawej w czasie rzeczywistym
- Lista dokumentów (ukryta pod przyciskiem 📄 „Dokumenty”) z wyszukiwarką
- Tworzenie / edycja tytułu i treści / usuwanie
- Prosty polling (4 s) dla odświeżeń między kartami
- Brak przeładowań — SPA na `fetch` + JSON

## Produkcyjny „hardening” (do rozważenia)
- Zmiana fallbacku logowania na wyłącznie `password_hash` + `password_verify`
- CSRF tokeny dla metod modyfikujących (POST/PUT/DELETE)
- Paginate listy + limit długości `tresc`
- Backupy i indeksy fulltext (jeśli dużo treści)

-- schema.sql
-- UTF-8 i porządne sortowanie znaków PL
CREATE DATABASE IF NOT EXISTS notatnik_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE notatnik_db;

-- Tabela użytkowników (jeśli już masz, pomiń ten CREATE i tylko upewnij się, że kolumny pasują)
CREATE TABLE IF NOT EXISTS uzytkownicy (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  haslo VARCHAR(255) NOT NULL,         -- może być HASH (password_hash) albo na czas dev: czysty tekst
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela dokumentów
CREATE TABLE IF NOT EXISTS dokumenty (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  tytul VARCHAR(255) NOT NULL,
  tresc LONGTEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE,
  INDEX(user_id)
) ENGINE=InnoDB;

-- Przykładowy użytkownik: email demo@demo.pl, hasło demo12345 (TU wstawione w czystym tekście dla łatwego startu)
-- W produkcji zalecam: UPDATE uzytkownicy SET haslo = password_hash('demo12345', PASSWORD_DEFAULT); i usunięcie czystego tekstu.
INSERT INTO uzytkownicy(email, haslo)
VALUES ('demo@demo.pl', 'demo12345')
ON DUPLICATE KEY UPDATE email=email;

-- Startowe dokumenty (trafią do listy po zalogowaniu jako user_id=1)
INSERT INTO dokumenty(user_id, tytul, tresc) VALUES
  (1, 'Witamy w Notatniku', 'To jest przykładowy dokument. Edytuj mnie i zobacz autosave.'),
  (1, 'Pomysły', '- [ ] Zrobić kawę\n- [ ] Zaplanować sprint\n- [ ] Dokończyć moduł powiadomień'),
  (1, 'Linki', 'Możesz tu trzymać krótkie notatki, linki, checklisty.')
;

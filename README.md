# Multi Notes (autosave, SPA)

A lightweight notes/documents application with login system (`users` table), autosave to the `documents` table, and real-time preview — all without page reloads.

## Requirements
- PHP 8.0+
- MySQL / MariaDB
- PHP sessions enabled

## Installation

1. **Create the database and tables:**
   ```sql
   -- File: schema.sql
   ```
   Run `schema.sql` using your MySQL client.

2. **Configure database connection using `.env`:**  
   Create a copy of the `.env.example` file and rename it to `.env`.  
   Fill it with your real database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=your_database
   DB_USER=your_user
   DB_PASS=your_password
   ```

3. **Upload the `multi-notes/` directory** to your server (e.g., `public_html/multi-notes`).

4. **Open the application in your browser** and log in using demo credentials:
   - **Email:** `demo@demo.pl`  
   - **Password:** `demo12345`

> **Note:** For demo purposes, the login system supports both `password_hash(...)` and plain text passwords.  
> In production, it is strongly recommended to use `password_hash` + `password_verify` only.

---

## Project Structure
```
multi-notes/
├─ assets/      (Frontend: CSS + JS)
├─ api/         (JSON endpoints: login, logout, documents)
├─ lib/         (Database + sessions)
├─ .env         (Your local DB configuration, not committed to GitHub)
├─ .env.example (Template for setting up a new environment)
├─ .gitignore
└─ index.php
```

---

## Features
- Autosave with a 600 ms debounce and real-time preview on the right
- Document list with a search bar (hidden under 📄 "Documents" button)
- Create, edit (title and content), and delete documents
- Simple 4-second polling for live updates across tabs
- SPA-like experience using `fetch` + JSON (no page reloads)

---

## Production Hardening (recommended)
- Enforce secure password handling with `password_hash` and `password_verify`
- Add CSRF tokens for all modifying methods (POST/PUT/DELETE)
- Implement pagination and limit `content` field length
- Regular backups and full-text indexes for better performance with large amounts of data

<?php
// index.php
declare(strict_types=1);
require_once __DIR__ . '/lib/auth.php';
$uid = current_user_id();
?>
<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notatnik — autosave</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <header class="topbar">
    <div class="brand">📝 Notatnik</div>
    <div class="spacer"></div>
    <div id="user-actions" class="user-actions <?= $uid ? '' : 'hidden' ?>">
      <button id="btn-docs" class="ghost">📄 Dokumenty</button>
      <button id="btn-new" class="primary">➕ Nowy</button>
      <button id="btn-logout" class="ghost">Wyloguj</button>
    </div>
  </header>

  <!-- Panel logowania -->
  <section id="login-view" class="centered <?= $uid ? 'hidden' : '' ?>">
    <form id="login-form" class="card">
      <h1>Zaloguj się</h1>
      <label>Email
        <input type="email" name="email" placeholder="email@domena.pl" required />
      </label>
      <label>Hasło
        <input type="password" name="haslo" placeholder="••••••••" required />
      </label>
      <button class="primary" type="submit">Wejdź</button>
      <p class="muted">Demo: <code>demo@demo.pl</code> / <code>demo12345</code></p>
      <p id="login-error" class="error hidden"></p>
    </form>
  </section>

  <!-- Główny edytor -->
  <main id="app-view" class="<?= $uid ? '' : 'hidden' ?>">
    <aside id="drawer" class="drawer hidden">
      <div class="drawer-header">
        <input id="search" type="search" placeholder="Szukaj..." />
      </div>
      <ul id="doc-list" class="doc-list"></ul>
    </aside>

    <section class="editor-wrap">
      <input id="title" class="title" placeholder="Tytuł dokumentu" />
      <div class="status">
        <span id="save-state">—</span>
        <span id="timestamps"></span>
      </div>
      <textarea id="editor" placeholder="Pisz tutaj…"></textarea>
    </section>

    <!-- <section class="preview-wrap">
      <div class="preview-header"><strong>Podgląd (live)</strong></div>
      <div id="preview" class="preview"></div>
    </section> -->
  </main>

  <script>window.__LOGGED__ = <?= $uid ? 'true' : 'false' ?>;</script>
  <script src="assets/app.js"></script>
</body>
</html>

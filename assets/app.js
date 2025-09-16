// assets/app.js
const qs = (s, el=document) => el.querySelector(s);
const qsa = (s, el=document) => [...el.querySelectorAll(s)];

const UI = {
  loginView: qs('#login-view'),
  appView: qs('#app-view'),
  loginForm: qs('#login-form'),
  loginError: qs('#login-error'),
  btnLogout: qs('#btn-logout'),
  btnDocs: qs('#btn-docs'),
  btnNew: qs('#btn-new'),
  drawer: qs('#drawer'),
  list: qs('#doc-list'),
  search: qs('#search'),
  title: qs('#title'),
  editor: qs('#editor'),
  preview: qs('#preview'),
  saveState: qs('#save-state'),
  timestamps: qs('#timestamps'),
  userActions: qs('#user-actions'),
};

let currentDoc = null;
let saveTimer = null;
let pollTimer = null;
let isSaving = false;

async function api(path, opts={}) {
  const res = await fetch(path, {
    headers: {'Content-Type': 'application/json'},
    credentials: 'same-origin',
    ...opts,
  });
  if (!res.ok) {
    let msg = 'Błąd';
    try { const j = await res.json(); msg = j.error || msg; } catch {}
    throw new Error(msg);
  }
  return res.json();
}

function setSaveState(text, ok=false) {
  UI.saveState.textContent = text;
  UI.saveState.style.color = ok ? 'var(--muted)' : 'var(--muted)';
}

function updatePreview() {
  UI.preview.textContent = UI.editor.value;
}

function formatTs(ts) {
  if (!ts) return '';
  const d = new Date(ts.replace(' ', 'T') + 'Z'); // MySQL -> UTC
  return d.toLocaleString();
}

function renderList(docs) {
  UI.list.innerHTML = '';
  docs.forEach(d => {
    const li = document.createElement('li');
    li.dataset.id = d.id;
    const left = document.createElement('div');
    left.className = 'doc-title';
    left.textContent = d.tytul;
    const right = document.createElement('div');
    right.className = 'doc-actions';
    const time = document.createElement('span');
    time.className = 'doc-time';
    time.textContent = new Date(d.updated_at.replace(' ', 'T') + 'Z').toLocaleString();
    const del = document.createElement('button');
    del.textContent = 'Usuń';
    del.addEventListener('click', async (ev) => {
      ev.stopPropagation();
      if (!confirm('Usunąć dokument?')) return;
      await api(`api/documents.php?id=${d.id}`, { method: 'DELETE' });
      if (currentDoc && currentDoc.id === d.id) {
        currentDoc = null;
        UI.title.value = '';
        UI.editor.value = '';
        updatePreview();
        UI.timestamps.textContent = '';
      }
      loadList();
    });
    right.append(time, ' ', del);
    li.append(left, right);
    li.addEventListener('click', () => openDoc(d.id));
    UI.list.appendChild(li);
  });
}

async function loadList(q='') {
  try {
    const data = await api(`api/documents.php${q ? `?q=${encodeURIComponent(q)}` : ''}`);
    renderList(data.docs || []);
  } catch (e) {
    console.error(e);
  }
}

async function openDoc(id) {
  try {
    const data = await api(`api/documents.php?id=${id}`);
    currentDoc = data.doc;
    UI.title.value = currentDoc.tytul || '';
    UI.editor.value = currentDoc.tresc || '';
    updatePreview();
    UI.timestamps.textContent = `Utworzono: ${formatTs(currentDoc.created_at)} • Ostatnia zmiana: ${formatTs(currentDoc.updated_at)}`;
    setSaveState('Załadowano', true);
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(checkRemoteUpdate, 4000);
  } catch (e) {
    alert(e.message);
  }
}

async function checkRemoteUpdate() {
  if (!currentDoc) return;
  try {
    const data = await api(`api/documents.php?id=${currentDoc.id}`);
    const server = data.doc;
    if (server.updated_at !== currentDoc.updated_at) {
      currentDoc = server;
      if (document.activeElement !== UI.editor) {
        UI.editor.value = server.tresc;
        updatePreview();
      }
      UI.title.value = server.tytul;
      UI.timestamps.textContent = `Utworzono: ${formatTs(server.created_at)} • Ostatnia zmiana: ${formatTs(server.updated_at)}`;
      setSaveState('Zaktualizowano z serwera', true);
    }
  } catch {}
}

function scheduleSave() {
  if (!currentDoc) return;
  if (saveTimer) clearTimeout(saveTimer);
  setSaveState('Zapisywanie…');
  saveTimer = setTimeout(doSave, 600);
}

async function doSave() {
  if (!currentDoc) return;
  if (isSaving) return;
  isSaving = true;
  try {
    const payload = {
      tytul: UI.title.value.trim() || 'Bez tytułu',
      tresc: UI.editor.value,
    };
    const data = await api(`api/documents.php?id=${currentDoc.id}`, {
      method: 'PUT',
      body: JSON.stringify(payload)
    });
    currentDoc = data.doc;
    UI.timestamps.textContent = `Utworzono: ${formatTs(currentDoc.created_at)} • Ostatnia zmiana: ${formatTs(currentDoc.updated_at)}`;
    setSaveState('Zapisano ✔︎', true);
    const li = qs(`li[data-id="${currentDoc.id}"]`);
    if (li) li.querySelector('.doc-title').textContent = currentDoc.tytul;
  } catch (e) {
    setSaveState('Błąd zapisu');
    console.error(e);
  } finally {
    isSaving = false;
  }
}

async function createDoc() {
  try {
    const data = await api('api/documents.php', {
      method: 'POST',
      body: JSON.stringify({ tytul: 'Nowy dokument', tresc: '' })
    });
    await loadList();
    await openDoc(data.doc.id);
  } catch (e) {
    alert(e.message);
  }
}

async function doLogin(email, haslo) {
  await api('api/login.php', { method: 'POST', body: JSON.stringify({ email, haslo }) });
  UI.loginView.classList.add('hidden');
  UI.appView.classList.remove('hidden');
  UI.userActions.classList.remove('hidden');
  await loadList();
  const first = qs('#doc-list li');
  if (first) first.click();
  else createDoc();
}

async function doLogout() {
  await api('api/logout.php', { method: 'POST' });
  location.reload();
}

function toggleDrawer() {
  UI.drawer.classList.toggle('hidden');
}

function bindEvents() {
  if (UI.loginForm) {
    UI.loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      UI.loginError.classList.add('hidden');
      const email = e.target.email.value.trim();
      const haslo = e.target.haslo.value;
      try {
        await doLogin(email, haslo);
      } catch (err) {
        UI.loginError.textContent = err.message || 'Błąd logowania';
        UI.loginError.classList.remove('hidden');
      }
    });
  }
  UI.btnLogout?.addEventListener('click', doLogout);
  UI.btnDocs?.addEventListener('click', toggleDrawer);
  UI.btnNew?.addEventListener('click', createDoc);

  UI.search?.addEventListener('input', (e) => loadList(e.target.value));

  UI.title.addEventListener('input', scheduleSave);
  UI.editor.addEventListener('input', () => {
    updatePreview();
    scheduleSave();
  });

  if (window.__LOGGED__) {
    loadList().then(() => {
      const first = qs('#doc-list li');
      if (first) first.click();
    });
  }
}

document.addEventListener('DOMContentLoaded', bindEvents);

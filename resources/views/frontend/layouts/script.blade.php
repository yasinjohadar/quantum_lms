<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>
(() => {
  const storageKey = 'site_theme';
  const legacyStorageKey = 'header_theme';
  const root = document.documentElement;
  const header = document.getElementById('siteHeader');
  const btn = document.getElementById('headerThemeToggle');
  const icon = btn ? btn.querySelector('i') : null;

  const applyTheme = (theme) => {
    const t = theme === 'dark' ? 'dark' : 'light';
    root.setAttribute('data-bs-theme', t);
    if (header) header.setAttribute('data-bs-theme', t);
    if (icon) {
      icon.classList.remove('fa-moon', 'fa-sun');
      icon.classList.add(t === 'dark' ? 'fa-sun' : 'fa-moon');
    }
  };

  try {
    const saved = localStorage.getItem(storageKey) || localStorage.getItem(legacyStorageKey);
    applyTheme(saved || root.getAttribute('data-bs-theme') || 'light');
  } catch (e) {
    applyTheme(root.getAttribute('data-bs-theme') || 'light');
  }

  if (!btn) return;
  btn.addEventListener('click', () => {
    const current = root.getAttribute('data-bs-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    try {
      localStorage.setItem(storageKey, next);
    } catch (e) {}
    applyTheme(next);
  });
})();
</script>
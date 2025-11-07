// public/assets/js/sidebar.js
(function () {
  const sidebar = document.querySelector('#sidebar');
  if (!sidebar) return;

  const storageKey = 'sidebar-open-items';

  const loadOpen = () => {
    try { return JSON.parse(localStorage.getItem(storageKey) || '[]'); }
    catch { return []; }
  };
  const saveOpen = (ids) => {
    try { localStorage.setItem(storageKey, JSON.stringify(ids)); } catch {}
  };

  const openIds = new Set(loadOpen());

  // İlk yüklemede storage'a göre aç
  sidebar.querySelectorAll('.menu-item.has-sub').forEach(li => {
    const id = li.getAttribute('data-menu-id');
    if (id && openIds.has(id)) li.classList.add('open');

    const link = li.querySelector('.menu-link');
    if (!link) return;

    link.setAttribute('role','button');
    link.setAttribute('tabindex','0');
    link.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');

    const toggle = (ev) => {
      // Alt menü satırı tıklandığında gezinme yerine aç/kapa
      if (li.classList.contains('has-sub')) {
        ev.preventDefault();
        ev.stopPropagation();
        li.classList.toggle('open');
        link.setAttribute('aria-expanded', li.classList.contains('open') ? 'true' : 'false');

        const id = li.getAttribute('data-menu-id');
        if (id) {
          if (li.classList.contains('open')) openIds.add(id);
          else openIds.delete(id);
          saveOpen(Array.from(openIds));
        }
      }
      // has-sub DEĞİLSE normal gezinmeye izin ver (href çalışır)
    };

    link.addEventListener('click', toggle);
    link.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') toggle(e);
    });
  });

  // (Opsiyonel) Akordeon davranışı: aynı anda yalnız bir menü açık olsun
  // Aşağıdaki bloğu aktif etmek isterseniz "onlyOneOpen = true" yapın:
  const onlyOneOpen = false;
  if (onlyOneOpen) {
    sidebar.addEventListener('click', (e) => {
      const link = e.target.closest('.menu-link');
      if (!link) return;
      const current = link.parentElement;
      if (!current.classList.contains('has-sub')) return;
      if (!current.classList.contains('open')) return;

      sidebar.querySelectorAll('.menu-item.has-sub.open').forEach(li => {
        if (li !== current) {
          li.classList.remove('open');
          const l = li.querySelector('.menu-link');
          if (l) l.setAttribute('aria-expanded','false');

          const id = li.getAttribute('data-menu-id');
          if (id && openIds.has(id)) {
            openIds.delete(id);
            saveOpen(Array.from(openIds));
          }
        }
      });
    });
  }
})();

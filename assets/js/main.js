// public/assets/js/main.js
(function () {
  const btn = document.getElementById('darkSwitch');
  if (!btn) return;
  const key = 'ej_dark';
  const apply = (d) => document.documentElement.classList.toggle('dark-mode', d);
  apply(localStorage.getItem(key) === '1');
  btn.addEventListener('click', () => {
      const nv = localStorage.getItem(key) === '1' ? '0' : '1';
      localStorage.setItem(key, nv);
      apply(nv === '1');
  });
})();

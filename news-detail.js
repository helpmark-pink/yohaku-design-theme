// お知らせ個別記事の動き(news-20260820.html のページ内スクリプトを移したもの。main.js は読み込まない)
'use strict';
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ==========================================================
   Lenis(慣性スクロール)
   ========================================================== */
let lenis = null;
if (window.Lenis && !reduceMotion) {
  lenis = new Lenis({ lerp: 0.09 });
  const raf = (t) => { lenis.raf(t); requestAnimationFrame(raf); };
  requestAnimationFrame(raf);
}
// 行き先のないリンク(href="#")は、押してもページの一番上へ飛ばないようにする
document.querySelectorAll('a[href="#"]').forEach((link) => {
  link.addEventListener('click', (e) => e.preventDefault());
});
document.getElementById('toTop').addEventListener('click', () => {
  if (lenis) lenis.scrollTo(0, { duration: 1.6 });
  else window.scrollTo({ top: 0, behavior: 'smooth' });
});
document.getElementById('year').textContent = new Date().getFullYear();

/* ==========================================================
   スマホのメニュー
   ========================================================== */
const menuBtn = document.getElementById('menuBtn');
const menu = document.getElementById('menu');
function openMenu() {
  document.body.classList.add('is-menu-open');
  menuBtn.setAttribute('aria-expanded', 'true');
  menu.setAttribute('aria-hidden', 'false');
  if (lenis) lenis.stop();
}
function closeMenu() {
  document.body.classList.remove('is-menu-open');
  menuBtn.setAttribute('aria-expanded', 'false');
  menu.setAttribute('aria-hidden', 'true');
  if (lenis) lenis.start();
}
menuBtn.addEventListener('click', () => {
  document.body.classList.contains('is-menu-open') ? closeMenu() : openMenu();
});
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });

/* ==========================================================
   ヘッダーのリンク:ホバーで文字が下から入れかわる(トップと同じ)
   ========================================================== */
if (!reduceMotion) {
  document.querySelectorAll('.header__nav a, .header__contact').forEach((link) => {
    const html = link.innerHTML.trim();
    link.innerHTML = `<span class="roll"><span class="roll__inner"><span class="roll__text">${html}</span><span class="roll__text" aria-hidden="true">${html}</span></span></span>`;
    link.classList.add('has-roll');
    const inner = link.querySelector('.roll__inner');
    link.addEventListener('mouseenter', () => link.classList.add('is-rolling'));
    inner.addEventListener('transitionend', () => {
      if (!link.classList.contains('is-rolling')) return;
      inner.classList.add('is-reset');
      link.classList.remove('is-rolling');
      void inner.offsetWidth;
      inner.classList.remove('is-reset');
    });
  });
}

/* ==========================================================
   スクロールで出現(下からふわっと)
   ========================================================== */
const upObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (!entry.isIntersecting) return;
    entry.target.classList.add('is-in');
    upObserver.unobserve(entry.target);
  });
}, { rootMargin: '0px 0px -10% 0px' });
// 管理画面で書いた本文は、ブロック1つずつをふわっと出す
document.querySelectorAll('.ntext--editor > *').forEach((el) => el.classList.add('js-up'));
document.querySelectorAll('.js-up, .nrow').forEach((el) => upObserver.observe(el));
document.querySelectorAll('.nrow').forEach((row, i) => row.style.setProperty('--d', `${i * 70}ms`));

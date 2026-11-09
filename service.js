'use strict';
const reduced = matchMedia('(prefers-reduced-motion: reduce)');
const menuButton = document.querySelector('.w-menu-button');
const menu = document.getElementById('service-menu');
const main = document.querySelector('main');
const footer = document.querySelector('footer');
function setMenu(open, restoreFocus = true) {
  document.body.classList.toggle('w-menu-open', open);
  menuButton.setAttribute('aria-expanded', String(open));
  menuButton.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
  menu.inert = !open; main.inert = open; footer.inert = open;
  if (open) menu.querySelector('a').focus();
  else if (restoreFocus) menuButton.focus();
}
menuButton.addEventListener('click', () => setMenu(menuButton.getAttribute('aria-expanded') !== 'true'));
menu.addEventListener('click', e => { if (e.target.closest('a')) setMenu(false, false); });
addEventListener('keydown', e => {
  if (menu.inert) return;
  if (e.key === 'Escape') setMenu(false);
  if (e.key !== 'Tab') return;
  const last = menu.querySelector('a:last-of-type');
  if (e.shiftKey && document.activeElement === menuButton) { e.preventDefault(); last.focus(); }
  else if (!e.shiftKey && document.activeElement === menu.querySelector('a')) { e.preventDefault(); menuButton.focus(); }
  else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); menuButton.focus(); }
});
matchMedia('(min-width:769px)').addEventListener('change', e => { if (e.matches) setMenu(false, false); });
document.getElementById('year').textContent = new Date().getFullYear();
document.getElementById('toTop').addEventListener('click', () => {
  scrollTo({ top:0, behavior:reduced.matches ? 'instant' : 'smooth' });
  const title = document.querySelector('h1'); title.tabIndex = -1; title.focus({preventScroll:true});
});
// 写真が未配置の間は番号入りの図版を表示。配置後は自動的に写真へ切り替える。
document.querySelectorAll('[data-image]').forEach(figure => {
  const image = new Image();
  image.alt = { '01-strategy.jpg':'ブランドの方向性を考えるリサーチ資料', '02-identity.jpg':'ロゴと印刷物によるブランドの展開', '03-digital.jpg':'ウェブサイトを表示したデジタルデバイス' }[figure.dataset.image.split('/').pop()];
  image.onload = () => { figure.querySelector('.s-photo__placeholder').hidden = true; figure.prepend(image); };
  image.src = figure.dataset.image;
});
const reveals = document.querySelectorAll('.s-reveal');
if (!reduced.matches && 'IntersectionObserver' in window) {
  document.body.classList.add('s-motion');
  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
  }), {threshold:.08});
  reveals.forEach(el => observer.observe(el));
  reduced.addEventListener('change', () => { if (reduced.matches) { document.body.classList.remove('s-motion'); observer.disconnect(); } });
}
// ヒーローの図形の色の切り替えは、画面に入っている間だけループさせる。
const heroSection = document.querySelector('.s-hero');
if (heroSection && 'IntersectionObserver' in window) {
  new IntersectionObserver(([entry]) => {
    heroSection.classList.toggle('is-looping', entry.isIntersecting && !reduced.matches);
  }, {threshold:.15}).observe(heroSection);
  // 英文は、ヒーローが画面の中央まで来たら右から左へスライドして現れる。
  // (画面の上下半分を除いた「中央の線」に重なった時。画面から完全に出たら元の位置へ戻しておく)
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) heroSection.classList.add('is-in');
  }, {rootMargin:'-50% 0px -50% 0px'}).observe(heroSection);
  new IntersectionObserver(([entry]) => {
    if (!entry.isIntersecting) heroSection.classList.remove('is-in');
  }).observe(heroSection);
}
// サービス一覧が画面に入ったら、ページ全体の背景をパッと黒にする。
// 次の .s-process が画面に入ったら、サービス一覧がまだ見えていてもパッと白に戻す。
const servicesSection = document.querySelector('.s-services');
const processSection = document.querySelector('.s-process');
if (servicesSection && 'IntersectionObserver' in window) {
  let servicesIn = false;
  let processIn = false;
  const syncDark = () => {
    document.body.classList.toggle('s-dark', servicesIn && !processIn);
    // .s-process が入ったら、サービス一覧の部分も含めて画面全体を白にする
    document.body.classList.toggle('s-light', processIn);
  };
  new IntersectionObserver(([entry]) => { servicesIn = entry.isIntersecting; syncDark(); }).observe(servicesSection);
  if (processSection) new IntersectionObserver(([entry]) => { processIn = entry.isIntersecting; syncDark(); }).observe(processSection);
}
// お問い合わせの大きな英文も、画面の中央に来たら右から左へスライドして現れる。
const contactSection = document.querySelector('.s-contact');
if (contactSection && 'IntersectionObserver' in window) {
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) contactSection.classList.add('is-in');
  }, {rootMargin:'-50% 0px -50% 0px'}).observe(contactSection);
  new IntersectionObserver(([entry]) => {
    if (!entry.isIntersecting) contactSection.classList.remove('is-in');
  }).observe(contactSection);
  // ページの最後付近にあるため、画面が縦に長いと中央まで来ないことがある。フッターまで来たら必ず表示する。
  if (footer) new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) contactSection.classList.add('is-in');
  }, {threshold:.3}).observe(footer);
}
// よくある質問:開くときは答えが高さを広げながらふわっと現れ、閉じるときは静かにたたむ。
document.querySelectorAll('.s-faq details').forEach(item => {
  const answer = item.querySelector('p');
  let animation = null;
  item.querySelector('summary').addEventListener('click', e => {
    if (reduced.matches || !answer.animate) return; // 動きを減らす設定では、ブラウザ標準の開閉のまま
    e.preventDefault();
    if (animation) animation.cancel();
    const closing = item.open && !item.classList.contains('is-closing');
    if (!closing) { item.classList.remove('is-closing'); item.open = true; }
    else item.classList.add('is-closing');
    const style = getComputedStyle(answer);
    const full = { height:answer.scrollHeight + 'px', paddingBottom:style.paddingBottom, opacity:1, transform:'none' };
    const empty = { height:'0px', paddingBottom:'0px', opacity:0, transform:'translateY(-10px)' };
    answer.style.overflow = 'hidden';
    animation = answer.animate(closing ? [full, empty] : [empty, full], closing
      ? { duration:380, easing:'cubic-bezier(.4,0,.2,1)' }
      : { duration:650, easing:'cubic-bezier(.22,1,.36,1)' });
    animation.onfinish = () => {
      if (closing) { item.open = false; item.classList.remove('is-closing'); }
      answer.style.overflow = ''; animation = null;
    };
  });
});
let frame = 0;
function render() {
  frame = 0;
  if (reduced.matches) return;
  const hero = document.querySelector('.s-hero');
  hero.style.setProperty('--hero-shift', Math.max(-100, Math.min(100, -hero.getBoundingClientRect().top * .12)) + 'px');
  document.querySelectorAll('.s-photo').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.bottom > 0 && rect.top < innerHeight) el.style.setProperty('--photo-shift', Math.max(-rect.height * .05, Math.min(rect.height * .05, (innerHeight / 2 - rect.top - rect.height / 2) * .08)) + 'px');
  });
}
function schedule() { if (!frame) frame = requestAnimationFrame(render); }
addEventListener('scroll', schedule, {passive:true}); addEventListener('resize', schedule); schedule();

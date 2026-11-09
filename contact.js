// Contact ページの動き(contact.html のページ内スクリプトを移したもの。main.js は読み込まない)
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
const scrollToEl = (el) => {
  const y = el.getBoundingClientRect().top + window.scrollY - 120; // ヘッダーの分だけ上に余裕をとる
  if (lenis) lenis.scrollTo(y, { duration: 1 });
  else window.scrollTo({ top: y, behavior: reduceMotion ? 'auto' : 'smooth' });
};
// ページ内リンク(#privacy など)はなめらかに移動
document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener('click', (e) => {
    const target = document.querySelector(link.getAttribute('href'));
    if (!target) return;
    e.preventDefault();
    scrollToEl(target);
  });
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
   お問い合わせフォーム:入力 → 確認 → 完了
   - 項目から離れたとき・ボタンを押したときにチェックし、項目の下にエラーを出す
   - 「同意する」にチェックが入るまで、確認ボタンは押せない
   ========================================================== */
const form = document.getElementById('contactForm');
const agree = document.getElementById('fAgree');
const toConfirm = document.getElementById('toConfirm');
const steps = {
  input: document.getElementById('stepInput'),
  confirm: document.getElementById('stepConfirm'),
  done: document.getElementById('stepDone'),
};
const stepMarks = Array.from(document.querySelectorAll('#cSteps li'));
const fields = Array.from(form.querySelectorAll('.cfield__input'));

const MAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const KANA_RE = /^[ァ-ヶー\s　]+$/;

// ひらがな → カタカナ、全角英数 → 半角(メール欄)に整える
const toKatakana = (s) => s.replace(/[ぁ-ゖ]/g, (c) => String.fromCharCode(c.charCodeAt(0) + 0x60));
const toHalfWidth = (s) => s.replace(/[！-～]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0xFEE0)).replace(/　/g, '');

// 1つの項目をチェックして、エラー文(なければ空文字)を返す
const check = (el) => {
  const v = el.value.trim();
  if (el.required && !v) {
    return el.dataset.match ? '確認用のメールアドレスを入力してください' : `${el.dataset.label}を入力してください`;
  }
  if (!v) return '';
  if (el.hasAttribute('data-kana') && !KANA_RE.test(v)) return 'フリガナは全角カタカナで入力してください';
  if (el.type === 'email' && !MAIL_RE.test(v)) return 'メールアドレスの形式が正しくありません';
  if (el.dataset.match && v !== document.getElementById(el.dataset.match).value.trim()) {
    return 'メールアドレスが一致しません';
  }
  return '';
};

// エラーの表示(メール欄は2つで1つのエラー欄を使うので、先に見つかったエラーを出す)
const show = (el) => {
  const msg = check(el);
  el.setAttribute('aria-invalid', msg ? 'true' : 'false');
  const errId = el.getAttribute('aria-describedby');
  if (!errId) return !msg;
  const group = fields.filter((f) => f.getAttribute('aria-describedby') === errId);
  const first = group.map(check).find(Boolean) || '';
  document.getElementById(errId).textContent = first;
  return !msg;
};

fields.forEach((el) => {
  // 一度エラーになった項目は、直した瞬間にエラーを消す
  el.addEventListener('input', () => {
    if (el.getAttribute('aria-invalid') === 'true') show(el);
  });
  el.addEventListener('blur', () => {
    if (el.hasAttribute('data-kana')) el.value = toKatakana(el.value);
    if (el.type === 'email') el.value = toHalfWidth(el.value);
    if (el.value.trim() || el.getAttribute('aria-invalid')) show(el);
    // 1つ目のメールを直したら、確認用もチェックし直す
    if (el.id === 'fMail' && document.getElementById('fMail2').value) show(document.getElementById('fMail2'));
  });
});

agree.addEventListener('change', () => { toConfirm.disabled = !agree.checked; });

// ステップの切りかえ
const goStep = (name) => {
  Object.entries(steps).forEach(([key, el]) => {
    el.hidden = key !== name;
    el.classList.toggle('is-in', key === name);
  });
  const idx = Object.keys(steps).indexOf(name);
  stepMarks.forEach((li, i) => {
    li.classList.toggle('is-current', i === idx);
    if (i === idx) li.setAttribute('aria-current', 'step');
    else li.removeAttribute('aria-current');
  });
  scrollToEl(document.querySelector('.chead'));
};

// 確認画面の中身を作る(textContent で入れるので、入力された文字がHTMLとして動くことはない)
const buildConfirm = () => {
  const list = document.getElementById('confirmList');
  list.textContent = '';
  fields.filter((el) => el.dataset.label).forEach((el) => {
    const row = document.createElement('div');
    row.className = 'cconfirm__row';
    const dt = document.createElement('dt');
    dt.textContent = el.dataset.label;
    const dd = document.createElement('dd');
    const v = el.value.trim();
    dd.textContent = v || '(未入力)';
    if (!v) dd.classList.add('is-empty');
    row.append(dt, dd);
    list.appendChild(row);
  });
};

form.addEventListener('submit', (e) => {
  e.preventDefault();
  const results = fields.map(show);
  const firstBad = fields[results.indexOf(false)];
  if (firstBad) {
    scrollToEl(firstBad.closest('.cfield'));
    firstBad.focus({ preventScroll: true });
    return;
  }
  buildConfirm();
  goStep('confirm');
});

document.getElementById('backToInput').addEventListener('click', () => goStep('input'));

// 送信
// ※このサイトには受け取り用のサーバーがないので、今は完了画面を出すだけ。
//   実際に届くようにするには、下の fetch の行を有効にして、送信先(フォームサービスのURLなど)を書きかえる
const sendBtn = document.getElementById('sendBtn');
sendBtn.addEventListener('click', async () => {
  sendBtn.disabled = true;
  try {
    // await fetch('https://example.com/your-form-endpoint', { method: 'POST', body: new FormData(form) });
    form.reset();
    toConfirm.disabled = true;
    fields.forEach((el) => el.removeAttribute('aria-invalid'));
    goStep('done');
  } catch (err) {
    alert('送信できませんでした。時間をおいて、もう一度お試しください。');
  } finally {
    sendBtn.disabled = false;
  }
});

// 入力途中でページを離れようとしたら確認する(送信後は出さない)
window.addEventListener('beforeunload', (e) => {
  if (steps.done.hidden && fields.some((el) => el.value.trim())) e.preventDefault();
});

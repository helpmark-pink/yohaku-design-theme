// News ページの動き(news.html のページ内スクリプトを移したもの。main.js は読み込まない)
'use strict';
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const root = document.documentElement;

/* ==========================================================
   ヒーローの演出(ローダーは出さず、開いたらすぐ始める)
   - タイルの波は p5.js の準備ができてから(下の newsHero の setup で開始)
   - 見出しと情報バーは順番に出す
   ========================================================== */
// 見出しと情報バーの出現の演出は使わない(intro-wait を付けないので最初から表示。タイルの波は intro:start で始まる)
const startIntro = () => {
  document.dispatchEvent(new Event('intro:start'));
  setTimeout(() => root.classList.add('intro-header'), 200);
  setTimeout(() => root.classList.add('intro-text'), 400);
  setTimeout(() => root.classList.add('intro-info'), 1200);
};

/* ==========================================================
   Lenis(慣性スクロール)
   ========================================================== */
let lenis = null;
if (window.Lenis && !reduceMotion) {
  lenis = new Lenis({ lerp: 0.09 });
  const raf = (t) => { lenis.raf(t); requestAnimationFrame(raf); };
  requestAnimationFrame(raf);
}
// ページ内リンク(#〜)はなめらかに移動
document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener('click', (e) => {
    const id = link.getAttribute('href');
    // 行き先のないリンク(href="#")は、押してもページの一番上へ飛ばないようにする
    if (id === '#') {
      e.preventDefault();
      return;
    }
    const target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    closeMenu();
    if (lenis) lenis.scrollTo(target, { duration: 1.4 });
    else target.scrollIntoView({ behavior: 'smooth' });
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
   スクロールで出現(下からふわっと)
   ========================================================== */
const upObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (!entry.isIntersecting) return;
    entry.target.classList.add('is-in');
    upObserver.unobserve(entry.target);
  });
}, { rootMargin: '0px 0px -12% 0px' });
document.querySelectorAll('.js-up, .nrow, .js-pick').forEach((el) => upObserver.observe(el));

// 一覧の行は、上から順番に少しずつ遅らせて出す
document.querySelectorAll('.nrow').forEach((row, i) => row.style.setProperty('--d', `${i * 70}ms`));
document.querySelectorAll('.icard').forEach((card, i) => card.style.setProperty('--d', `${i * 120}ms`));

/* ==========================================================
   カテゴリーで絞り込み
   ========================================================== */
const filterBtns = document.querySelectorAll('#newsFilter button');
const rows = document.querySelectorAll('#newsRows .nrow');
filterBtns.forEach((btn) => {
  btn.addEventListener('click', () => {
    const cat = btn.dataset.filter;
    filterBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
    let n = 0;
    rows.forEach((row) => {
      const show = cat === 'all' || row.dataset.cat === cat;
      row.classList.toggle('is-hidden', !show);
      if (show) {
        // 表示する行を、もう一度順番にふわっと出す
        row.classList.remove('is-in');
        row.style.setProperty('--d', `${n++ * 60}ms`);
        requestAnimationFrame(() => requestAnimationFrame(() => row.classList.add('is-in')));
      }
    });
  });
});
// news.html?cat=works のように開かれたら、そのカテゴリーで絞り込んだ状態にする(トップの「View Works」から)
const startCat = new URLSearchParams(location.search).get('cat');
const startBtn = startCat && document.querySelector(`#newsFilter button[data-filter="${CSS.escape(startCat)}"]`);
if (startBtn) startBtn.click();

/* ==========================================================
   Pick up の画像:スクロールに合わせて、枠の中で画像だけ少しずれる(パララックス)
   ========================================================== */
const parallaxImgs = Array.from(document.querySelectorAll('.pick__media img'));
const updateParallax = () => {
  const vh = window.innerHeight;
  parallaxImgs.forEach((img) => {
    const box = img.parentElement.getBoundingClientRect();
    if (box.bottom < 0 || box.top > vh) return;
    // 枠の中央が画面の中央にあるとき 0。上下にずれるほど画像を逆方向へ
    const offset = (box.top + box.height / 2 - vh / 2) * Number(img.dataset.speed || 0.1);
    img.style.setProperty('--py', `${offset.toFixed(1)}px`);
  });
};
if (!reduceMotion) {
  const loop = () => { updateParallax(); requestAnimationFrame(loop); };
  requestAnimationFrame(loop);
}

/* ==========================================================
   ヒーロー:p5.js「めくれるタイル」
   - トップと同じ図形(四分円・半円・三角形など)と色を、マス目に並べる
   - マスがときどき縦にパタンとめくれて、別の図形・色に変わる(フラップ表示)
   - マウスが乗ったマスもめくれる
   - 最初は暗いマスばかりで、左から順番にめくれて色が付いていく
   ========================================================== */
const newsHero = (p) => {
  const COLORS = ['#2C3E9E', '#EBB4BE', '#DCA62A', '#BDB8AC', '#17181C', '#EEEBE3'];
  const TYPES = ['quarter', 'quarter', 'half', 'tri', 'rect', 'bar', 'circle', 'ring', 'dot', 'none'];
  const DARK = '#1E1F24';
  const FLIP = 520;           // 1回めくれるのにかかる時間(ms)
  const hero = document.getElementById('hero');
  let cell, cols, rows, offX, offY;
  let tiles = [];
  let hoverKey = '';
  let nextAuto = 0;
  let started = reduceMotion;

  const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
  const randomFace = () => {
    const bg = pick(COLORS);
    let fg = pick(COLORS);
    while (fg === bg) fg = pick(COLORS);
    return { type: pick(TYPES), bg, fg, rot: Math.floor(Math.random() * 4) * Math.PI / 2 };
  };
  const darkFace = () => ({ type: 'none', bg: DARK, fg: DARK, rot: 0 });

  // マスの大きさは、トップより少し小さめ(数を多く)にする
  const cellSize = () => {
    const w = p.width;
    const h = p.height;
    if (w > 1024) return Math.max(h / 3.4, 120);
    if (w > 768) return Math.max(Math.min(h / 4.2, w / 5.5), 80);
    return Math.max(Math.min(h / 7, w / 4.5), 56);
  };

  const build = () => {
    cell = cellSize();
    cols = Math.ceil(p.width / cell);
    rows = Math.ceil(p.height / cell);
    offX = (p.width - cols * cell) / 2;
    offY = (p.height - rows * cell) / 2;
    tiles = [];
    for (let j = 0; j < rows; j++) {
      for (let i = 0; i < cols; i++) {
        const face = started ? randomFace() : darkFace();
        tiles.push({ i, j, cur: face, next: null, t0: 0 });
      }
    }
  };

  // マスをめくる(delay ms 後にめくり始める)
  const flip = (tile, delay = 0, face = randomFace()) => {
    if (tile.next) return;
    tile.next = face;
    tile.t0 = p.millis() + delay;
  };

  const drawShape = (f, s) => {
    p.fill(f.bg);
    p.rect(-s / 2, -s / 2, s + 1, s + 1);
    p.push();
    p.rotate(f.rot);
    p.fill(f.fg);
    switch (f.type) {
      case 'quarter': p.arc(-s / 2, -s / 2, s * 2, s * 2, 0, p.HALF_PI, p.PIE); break;
      case 'half': p.arc(0, -s / 2, s, s, 0, p.PI, p.PIE); break;
      case 'tri': p.triangle(-s / 2, -s / 2, s / 2, -s / 2, -s / 2, s / 2); break;
      case 'rect': p.rect(-s / 2, -s / 2, s / 2, s); break;
      case 'bar': p.rect(-s / 2, -s / 6, s, s / 3); break;
      case 'circle': p.circle(0, 0, s * 0.8); break;
      case 'ring': p.circle(0, 0, s * 0.88); p.fill(f.bg); p.circle(0, 0, s * 0.44); break;
      case 'dot': p.circle(s * 0.22, -s * 0.22, s * 0.28); break;
      default: break;
    }
    p.pop();
  };

  // めくれる途中の絵:前半は今の面が上から縦につぶれ、後半は新しい面が下から開く
  const drawTile = (tile) => {
    const s = cell;
    const x = offX + tile.i * s;
    const y = offY + tile.j * s;
    p.push();
    p.translate(x + s / 2, y + s / 2);
    p.drawingContext.save();
    p.drawingContext.beginPath();
    p.drawingContext.rect(-s / 2, -s / 2, s + 1, s + 1);
    p.drawingContext.clip();
    p.fill(DARK);
    p.rect(-s / 2, -s / 2, s + 1, s + 1);

    let k = 0;
    if (tile.next && p.millis() >= tile.t0) k = Math.min((p.millis() - tile.t0) / FLIP, 1);
    if (k < 0.5) {
      const sy = Math.cos(k * Math.PI);           // 1 → 0
      p.scale(1, Math.max(sy, 0.001));
      drawShape(tile.cur, s);
      // めくれるときの陰
      if (k > 0) { p.fill(0, 0, 0, 120 * k * 2); p.rect(-s / 2, -s / 2, s + 1, s + 1); }
    } else {
      const sy = -Math.cos(k * Math.PI);          // 0 → 1
      p.scale(1, Math.max(sy, 0.001));
      drawShape(tile.next, s);
      p.fill(0, 0, 0, 120 * (1 - k) * 2);
      p.rect(-s / 2, -s / 2, s + 1, s + 1);
    }
    p.drawingContext.restore();
    p.pop();

    if (k >= 1) { tile.cur = tile.next; tile.next = null; }
  };

  p.setup = () => {
    const c = p.createCanvas(hero.clientWidth, hero.clientHeight);
    c.parent('nhero-canvas');
    p.pixelDensity(Math.min(2, window.devicePixelRatio || 1));
    p.noStroke();
    build();
    // 画面外では描画を止める
    new IntersectionObserver(([e]) => (e.isIntersecting ? p.loop() : p.noLoop())).observe(hero);
    // 演出の開始が先に来ていたら、マスができたこのタイミングで波を始める
    if (waveWaiting) wave();
  };

  // オープニング:左上から右下へ、波のように順番にめくれて色が付く
  let waveWaiting = false;
  const wave = () => {
    waveWaiting = false;
    started = true;
    tiles.forEach((t) => flip(t, (t.i + t.j * 0.6) * 70 + Math.random() * 160));
  };
  document.addEventListener('intro:start', () => {
    if (tiles.length) wave();
    else waveWaiting = true; // まだ setup 前(マスがない)
  }, { once: true });

  p.windowResized = () => {
    const changed = hero.clientWidth !== p.width;
    p.resizeCanvas(hero.clientWidth, hero.clientHeight);
    if (changed) build();
  };

  p.draw = () => {
    p.background(DARK);

    if (started && !reduceMotion) {
      // ときどき、ランダムなマスを1〜2枚めくる
      if (p.millis() > nextAuto) {
        nextAuto = p.millis() + 180 + Math.random() * 420;
        for (let n = 0; n < 1 + Math.floor(Math.random() * 2); n++) flip(pick(tiles));
      }
      // マウスが新しいマスに入ったらめくる
      if (p.mouseX > 0 && p.mouseX < p.width && p.mouseY > 0 && p.mouseY < p.height) {
        const i = Math.floor((p.mouseX - offX) / cell);
        const j = Math.floor((p.mouseY - offY) / cell);
        const key = `${i}-${j}`;
        const tile = tiles[j * cols + i];
        if (key !== hoverKey && tile) { flip(tile); hoverKey = key; }
      }
    }

    tiles.forEach(drawTile);

    // マス目の細い線(設計図のような補助線)
    p.stroke(238, 235, 227, 60);
    p.strokeWeight(1);
    for (let i = 0; i <= cols; i++) p.line(offX + i * cell, 0, offX + i * cell, p.height);
    for (let j = 0; j <= rows; j++) p.line(0, offY + j * cell, p.width, offY + j * cell);
    p.noStroke();
  };
};
if (window.p5) new p5(newsHero);
startIntro(); // p5.js の読み込みに失敗していても、見出しは出す

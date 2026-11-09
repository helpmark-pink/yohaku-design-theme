'use strict';

// 大見出しの「回転する筒」の文字表示。トップページ(About. / Selected Works.)と About ページ(Next.)で共用する。
/* ==========================================================
   大見出し(About. / Selected Works.):Three.js(WebGL)シェーダーで動かす
   1. 文字を2Dキャンバスに描く
   2. そのキャンバスをテクスチャ(CanvasTexture)としてシェーダーに渡す
   3. 画面いっぱいの板(PlaneGeometry)にシェーダーで横ループ+動きをかける
   文字の描き方・動き方(回転する筒+波打ち)は、下の「回転する筒」の処理で決めている
   ========================================================== */
// 描画の解像度の倍率。スマホは負荷を下げるため 1.5倍まで(細かさの差はほとんど見えない)
const renderDensity = () => Math.min(window.innerWidth <= 768 ? 1.5 : 2, window.devicePixelRatio || 1);

const DISTORT_VERT = `
varying vec2 vUv;
void main() {
  vUv = uv;
  gl_Position = vec4(position, 1.0); // 板をそのまま画面いっぱいに
}
`;

class DistortText {
  constructor(el) {
    this.el = el;
    const item = el.querySelector('.marquee__item');
    const accentEl = item.querySelector('em');
    this.mainText = item.firstChild.textContent.trim();
    this.accentText = accentEl ? accentEl.textContent : '';
    this.isItalic = el.hasAttribute('data-italic');
    this.dir = Number(el.dataset.dir || -1);
    this.colors = { bg: el.dataset.bg, fg: el.dataset.fg, accent: el.dataset.accent };

    this.scroll = 0;     // 横スクロール量
    this.rot = 0;        // 筒の回転角
    this.rollDir = 1;    // 最後に回った向き
    this.wave = 0;       // 波の強さ(0 = 元の文字)
    this.visible = false;
    this.width = 0;

    // 文字を描く2Dキャンバス → テクスチャ
    this.textCanvas = document.createElement('canvas');
    this.texture = new THREE.CanvasTexture(this.textCanvas);
    this.texture.flipY = false;                // 画像の上を v=0 にする
    this.texture.minFilter = THREE.LinearFilter;
    this.texture.generateMipmaps = false;

    // レンダラー・カメラ・シーン
    this.renderer = new THREE.WebGLRenderer({ antialias: false });
    this.renderer.setPixelRatio(renderDensity());
    this.scene = new THREE.Scene();
    this.camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);

    this.material = new THREE.ShaderMaterial({
      vertexShader: DISTORT_VERT,
      fragmentShader: ROLL_FRAG,
      uniforms: {
        uTex: { value: this.texture },
        uRes: { value: new THREE.Vector2() },
        uUnit: { value: 1 },
        uScroll: { value: 0 },
        uRot: { value: 0 },
        uSpan: { value: ROLL_SPAN },
        uRows: { value: ROLL_ROWS },
        uTime: { value: 0 },
        uAmount: { value: 0 },
        uBg: { value: new THREE.Color(this.colors.bg) },
      },
    });
    this.scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), this.material));

    el.appendChild(this.renderer.domElement);
    el.classList.add('is-distorted');
    this.resize();

    // 画面外では描画しない
    new IntersectionObserver(([entry]) => {
      this.visible = entry.isIntersecting;
    }).observe(el);
  }

  resize() {
    const w = this.el.clientWidth;
    if (w === this.width) return; // 高さだけの変化(スマホのアドレスバー)は無視
    this.width = w;
    const h = this.buildTexture();
    this.renderer.setSize(w, h);
    this.material.uniforms.uRes.value.set(w, h);
    this.render(0);
  }
}

/* ==========================================================
   【追加】大見出し(About. / Selected Works.):見本動画と同じ「回転する筒」の動き
   - 文字を横向きの筒(円柱)の表面に巻きつけたように表示する
     (筒の上下の端に近いほど、文字が縦につぶれて見える)
   - 筒には3種類の文字を順に巻いている
       1段目:縦に細長くのばした文字(止まっているときに見える形)
       2段目:ふつうの文字
       3段目:イタリックの文字
   - 下にスクロールすると筒が上へ回り、上にスクロールすると下へ回る
   - スクロールが止まると、回っていた向きのまま1段目の位置まで回って止まる
   - 横方向は、これまでどおりゆっくり流れ続ける
   - スクロール中だけ文字が波打つ(止まると波が引くように、なめらかに元に戻る)
   ========================================================== */
const ROLL_ROWS = 3;                       // 筒に巻く文字の段数
const ROLL_SPAN = (Math.PI * 2) / ROLL_ROWS; // 1段ぶんの角度
const ROLL_POWER = 0.004;                  // スクロール量に対する回りやすさ
const ROLL_SETTLE = 0.03;                  // 止まった後、1段目に戻る速さ(小さいほどゆっくり)
const ROLL_FLOW = 0.06;                    // 横に流れる速さ
const ROLL_WAVE_IN = 0.1;                  // スクロールし始めた時、波が強くなる速さ
const ROLL_WAVE_OUT = 0.02;               // 止まった後、波が引いていく速さ(小さいほどゆっくり)

const ROLL_FRAG = `
varying vec2 vUv;
uniform sampler2D uTex;  // 3段の文字を縦に並べた画像
uniform vec2 uRes;       // キャンバスの大きさ(px)
uniform float uUnit;     // 文字1セット分の幅(px)
uniform float uScroll;   // 横に流れた量(px)
uniform float uRot;      // 筒の回転角(ラジアン)
uniform float uSpan;     // 1段ぶんの角度
uniform float uRows;     // 段数
uniform float uTime;     // 経過時間(秒)
uniform float uAmount;   // 波の強さ 0〜1(スクロール中だけ大きくなる)
uniform vec3 uBg;        // 背景色

void main() {
  vec2 uv = vec2(vUv.x, 1.0 - vUv.y);       // 上を0にする
  // スクロール中だけ波打つ:大きなうねり+小さな波を重ねて、文字を上下・左右にゆらす
  float a = uAmount;
  float px = uv.x * uRes.x;
  float wave = sin(px * 0.006 + uTime * 2.4) * 0.14
             + sin(px * 0.017 - uTime * 3.4) * 0.05;
  uv.y += wave * a;
  uv.x += sin(uv.y * 6.0 + uTime * 2.6) * (uRes.y / uRes.x) * 0.05 * a;
  // 波で上下にはみ出たところは背景色にする
  if (uv.y < 0.0 || uv.y > 1.0) {
    gl_FragColor = vec4(uBg, 1.0);
    return;
  }
  float yn = uv.y * 2.0 - 1.0;              // 上 -1 〜 下 1
  // 筒の表面の角度(端ほど文字が詰まる)。見える範囲をちょうど1段ぶんにして、止まると1行だけ見える
  float th = asin(clamp(yn, -1.0, 1.0) * sin(uSpan * 0.5));
  float s = (th + uRot) / uSpan + 0.5;      // 何段目の、どの高さか
  float row = mod(floor(s), uRows);
  float v = (row + fract(s)) / uRows;       // 画像の中の縦位置
  float x = fract((uv.x * uRes.x + uScroll) / uUnit);
  gl_FragColor = vec4(texture2D(uTex, vec2(x, v)).rgb, 1.0);
}
`;

// 大見出しの文字サイズと、1段の高さ(= キャンバスの高さ)
const rollFontSize = () => Math.min(150, Math.max(64, window.innerWidth * 0.1));
const rollRowHeight = () => Math.round(rollFontSize() * 1.6);

// 文字の画像:3段(細長い / ふつう / イタリック)を縦に並べて作る
DistortText.prototype.buildTexture = function () {
  const label = `${this.mainText}.`;             // 見本と同じく「About.」「Selected Works.」
  const { bg, fg } = this.colors;
  const fs = rollFontSize();
  const rowH = rollRowHeight();
  const dpr = renderDensity();
  // Bodoni Moda は文字サイズが大きいほど細い線が細くなる書体。
  // 小さいサイズで描いて拡大し、細い線もしっかり太い形で表示する(about の Next. と同じ見え方)。
  const baseSize = 8;
  const k = fs / baseSize;
  const rows = [
    { font: `600 ${baseSize}px "Bodoni Moda"`, sx: 0.78, sy: 1.55 },        // 1段目:縦に細長く
    { font: `600 ${baseSize}px "Bodoni Moda"`, sx: 1, sy: 1 },              // 2段目:ふつう
    { font: `italic 600 ${baseSize}px "Bodoni Moda"`, sx: 1, sy: 1.1 },     // 3段目:イタリック
  ];

  const ctx = this.textCanvas.getContext('2d');
  const widths = rows.map((r) => {
    ctx.font = r.font;
    return ctx.measureText(label).width * k * r.sx;
  });
  const gap = fs * 0.55;                            // くり返しの文字のあいだ
  const unitW = Math.ceil(Math.max(...widths) + gap);

  this.textCanvas.width = unitW * dpr;
  this.textCanvas.height = rowH * ROLL_ROWS * dpr;
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.fillStyle = bg;
  ctx.fillRect(0, 0, unitW, rowH * ROLL_ROWS);
  ctx.fillStyle = fg;
  ctx.textBaseline = 'alphabetic';
  rows.forEach((r, i) => {
    ctx.save();
    // 文字の下端が段の下寄り(線の少し上)に来るように置く
    ctx.translate(gap * 0.5, rowH * i + rowH * 0.86);
    ctx.scale(r.sx * k, r.sy * k);
    ctx.font = r.font;
    ctx.fillText(label, 0, 0);
    ctx.restore();
  });

  this.texture.dispose();
  this.texture.needsUpdate = true;
  this.material.uniforms.uUnit.value = unitW;
  return rowH;
};

DistortText.prototype.update = function (time, dt, velocity) {
  if (!this.visible) return;
  const turn = Math.PI * 2;
  const step = dt / 16.7; // フレームレートに左右されないように

  if (Math.abs(velocity) > 0.6) {
    // スクロール中:速さに合わせて筒を回す
    this.rot += velocity * ROLL_POWER * step;
    this.rollDir = Math.sign(velocity);
  } else {
    // 止まったら:回っていた向きのまま、次の1段目(1周ぶん)の位置へ
    const target = (this.rollDir < 0 ? Math.floor(this.rot / turn) : Math.ceil(this.rot / turn)) * turn;
    this.rot += (target - this.rot) * ROLL_SETTLE * step;
    if (Math.abs(target - this.rot) < 0.0005) this.rot = target;
  }

  // 波の強さ:スクロール中はなめらかに強くなり、止まると波が引くようにゆっくり0に戻る
  const waveTarget = Math.min(Math.abs(velocity) / 40, 1);
  this.wave += (waveTarget - this.wave) * (waveTarget > this.wave ? ROLL_WAVE_IN : ROLL_WAVE_OUT) * step;
  if (this.wave < 0.001) this.wave = 0;

  // 横の流れ
  const unitW = this.material.uniforms.uUnit.value;
  this.scroll += this.dir * ROLL_FLOW * dt;
  this.scroll = ((this.scroll % unitW) + unitW) % unitW;

  this.render(time);
};

DistortText.prototype.render = function (time) {
  const u = this.material.uniforms;
  u.uScroll.value = this.scroll;
  u.uRot.value = this.rot;
  u.uTime.value = (time || 0) % 1000;
  u.uAmount.value = this.wave;
  this.renderer.render(this.scene, this.camera);
};

// 背景色・文字色を変えて、文字の画像を描き直す
DistortText.prototype.setColors = function (bg, fg) {
  if (this.colors.bg === bg && this.colors.fg === fg) return;
  this.colors.bg = bg;
  this.colors.fg = fg;
  this.material.uniforms.uBg.value.set(bg);
  this.buildTexture();
  this.render(performance.now() / 1000);
};

/* ==========================================================
   【追加】フッター:背景にヒーローと同じ図形を色の濃淡で敷き、パララックスで動かす
   - トップは藍色、About / Works は黒の濃淡(colors で指定)
   - 設計図のような細い補助線(グリッド・円)を重ねる
   - スクロールすると、背景だけが文字より遅れてゆっくり動く
   ========================================================== */
function initFooterBg(colors) {
  const footerBg = document.getElementById('footerBg');
  if (!footerBg) return Promise.resolve();
  const footer = footerBg.parentElement;
  const FOOTER_COLORS = colors; // マスの背景に使う色の濃淡(差を小さくして控えめに)
  const SHAPE_COLOR = '#ffffff'; // 図形は白
  const SHAPE_OPACITY = 0.14;    // 白の濃さ(上に重なる文字が読めるよう、少し透かす)
  const HEIGHT_RATE = 2;    // フッターの高さ(元の高さの何倍にするか)
  const HEIGHT_RATE_SP = 1; // スマホ(768px以下)の高さ(PCの半分 = 元の高さのまま)
  const TYPES = ['quarter', 'quarter', 'quarter', 'half', 'tri', 'rect', 'bar', 'circle', 'ring', 'none'];
  const PARALLAX = 0.7; // 文字に対して背景が遅れる割合(CSSで上に70%はみ出させている分)
  const pick = (list) => list[Math.floor(Math.random() * list.length)];

  // 図形の形(マスの中心を 0,0 とした座標)。ヒーローの drawTile と同じ形
  const shapeSvg = (type, s, bg) => {
    const h = s / 2;
    switch (type) {
      case 'quarter': return `<path d="M${-h} ${-h}H${h}A${s} ${s} 0 0 1 ${-h} ${h}Z"/>`;
      case 'half': return `<path d="M${h} ${-h}A${h} ${h} 0 0 1 ${-h} ${-h}Z"/>`;
      case 'tri': return `<path d="M${-h} ${-h}H${h}L${-h} ${h}Z"/>`;
      case 'rect': return `<rect x="${-h}" y="${-h}" width="${h}" height="${s}"/>`;
      case 'bar': return `<rect x="${-h}" y="${-s / 6}" width="${s}" height="${s / 3}"/>`;
      case 'circle': return `<circle r="${s * 0.41}"/>`;
      case 'ring': return `<circle r="${s * 0.45}"/><circle r="${s * 0.225}" fill="${bg}" fill-opacity="1"/>`;
      default: return '';
    }
  };

  let builtWidth = 0;
  const build = () => {
    const w = footer.clientWidth;
    if (w === builtWidth) return; // 横幅が変わった時だけ作り直す
    builtWidth = w;

    // 中身だけの高さを測って、その倍率の高さにする(PCは2倍、スマホは元のまま)
    const rate = window.innerWidth <= 768 ? HEIGHT_RATE_SP : HEIGHT_RATE;
    footer.style.minHeight = '';
    footer.style.minHeight = `${footer.offsetHeight * rate}px`;
    const h = footerBg.clientHeight;

    const cell = w > 768 ? Math.max(w / 5.7, 160) : Math.max(w / 2.6, 110);
    const cols = Math.ceil(w / cell);
    const rows = Math.ceil(h / cell);
    let tiles = '';
    let lines = '';
    for (let i = 0; i < cols; i++) {
      for (let j = 0; j < rows; j++) {
        const x = i * cell;
        const y = j * cell;
        const bg = pick(FOOTER_COLORS);
        const type = pick(TYPES);
        tiles += `<rect x="${x}" y="${y}" width="${cell + 1}" height="${cell + 1}" fill="${bg}"/>`;
        if (type !== 'none') {
          const rot = Math.floor(Math.random() * 4) * 90;
          tiles += `<g transform="translate(${x + cell / 2} ${y + cell / 2}) rotate(${rot})" fill="${SHAPE_COLOR}" fill-opacity="${SHAPE_OPACITY}">${shapeSvg(type, cell, bg)}</g>`;
        }
      }
    }
    // 補助線:マスの区切り+ところどころの円+大きな円
    for (let i = 0; i <= cols; i++) lines += `<path d="M${i * cell} 0V${h}"/>`;
    for (let j = 0; j <= rows; j++) lines += `<path d="M0 ${j * cell}H${w}"/>`;
    for (let i = 0; i <= cols; i++) {
      for (let j = 0; j <= rows; j++) {
        if (Math.random() < 0.15) lines += `<circle cx="${i * cell}" cy="${j * cell}" r="${cell}"/>`;
      }
    }
    lines += `<ellipse cx="${w * 0.5}" cy="${h * 0.55}" rx="${w * 0.55}" ry="${h * 0.42}"/>`;
    lines += `<circle cx="${w * 0.2}" cy="${h * 0.6}" r="${cell * 2.2}"/>`;

    footerBg.innerHTML =
      `<svg width="${w}" height="${h}" viewBox="0 0 ${w} ${h}">` +
      `<g>${tiles}</g>` +
      `<g fill="none" stroke="#fff" stroke-opacity=".1" stroke-width="1">${lines}</g>` +
      '</svg>';
  };

  window.addEventListener('resize', build);

  // パララックス:フッターが画面に入ってからスクロールした量の70%だけ、背景を下へずらす
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let running = false;
    let lastTransform = '';
    const update = () => {
      const rect = footer.getBoundingClientRect();
      const scrolled = Math.max(0, window.innerHeight - rect.top);
      const transform = `translate3d(0, ${(scrolled * PARALLAX).toFixed(1)}px, 0)`;
      if (transform !== lastTransform) {
        footerBg.style.transform = transform;
        lastTransform = transform;
      }
      if (running) requestAnimationFrame(update);
    };
    // 画面に見えている間だけ動かす
    new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting && !running) {
        running = true;
        requestAnimationFrame(update);
      } else if (!entry.isIntersecting) {
        running = false;
      }
    }).observe(footer);
  }
  return document.fonts.ready.then(build); // 文字の大きさが決まってから作る
}

// About / Works のフッター背景は、トップと同じ図形を黒の濃淡で敷く。
const FOOTER_BLACK = ['#151613', '#1c1d1a', '#10110e'];
initFooterBg(FOOTER_BLACK);

// About とトップページの処理を分離する。
if (document.body.classList.contains('page-about')) {
  // リロード時はスクロール位置を復元せず、常にページの先頭から表示する。
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  const navEntry = performance.getEntriesByType('navigation')[0];
  if (navEntry && navEntry.type === 'reload') {
    if (location.hash) history.replaceState(null, '', location.pathname + location.search);
    window.scrollTo(0, 0);
    window.addEventListener('load', () => window.scrollTo(0, 0), { once: true });
  }
  initAboutPage();
} else if (document.body.classList.contains('page-works')) {
  initHeaderRoll();
  initWorksPage();
} else if (document.body.classList.contains('page-service')) {
  // Service はページ固有の処理を service.js で行う。ここではフッター背景とヘッダーの演出だけ。
  initHeaderRoll();
} else {
'use strict';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ==========================================================
   ローダー
   ========================================================== */
const loader = document.getElementById('loader');
const loaderBar = document.getElementById('loaderBar');
const loaderNum = document.getElementById('loaderNum');
const loaderProgress = document.getElementById('loaderProgress');
const CIRCLE_LENGTH = 289; // 2π × 46

// ローダーの表示時間:円が 0.55秒で一周し、0.1秒見せて、0.35秒(style.css の .loader)で閉じる
// 準備が終わらないときは 90% で待つ(ページを開いてから 2.55秒で待つのをやめる)
const LOADER_PROGRESS_MS = 550;
const LOADER_HOLD_MS = 100;
const LOADER_READY_LIMIT_MS = 2550;

let pageLoaded = false;

// ロード画面を閉じる前に終わらせておく準備(フォント・WebGLの見出し・画像のデコードなど)
// 表示されてから重い処理が走ると、スクロールや動きがカクつくため
const readyTasks = [];

// 画像を前もってデコードしておく(初めて画面に出た瞬間のカクつきを防ぐ)
const decodeImage = (src) => {
  const img = new Image();
  img.src = src;
  return img.decode().catch(() => {});
};

window.addEventListener('load', () => {
  // ページ内の画像(Worksの切り替え画像・Newsの画像など)をすべてデコード
  const images = Array.from(document.images).map((img) => img.decode().catch(() => {}));
  Promise.all([...readyTasks, ...images])
    .catch(() => {})
    .then(() => {
      // 準備でたまった描画を1フレーム済ませてから閉じる
      requestAnimationFrame(() => requestAnimationFrame(() => { pageLoaded = true; }));
    });
});
// 何かが終わらなくても、上限の時間が過ぎたら閉じる(読み込みの完了も待たない)
setTimeout(() => { pageLoaded = true; }, Math.max(0, LOADER_READY_LIMIT_MS - performance.now()));

let progress = 0;
let loaderLastTime = performance.now();
const loaderTick = (now) => {
  // 円が必ず一周して見えるよう、描画のたびに少しずつ進める(処理で止まっていた時間は飛ばす)。準備が終わるまでは90%で止めておく
  const dt = Math.max(0, Math.min(now - loaderLastTime, 100));
  loaderLastTime = now;
  const limit = pageLoaded ? 100 : 90;
  progress = Math.min(limit, progress + dt / LOADER_PROGRESS_MS * 100);
  loaderBar.style.strokeDashoffset = CIRCLE_LENGTH * (1 - progress / 100);
  loaderNum.textContent = Math.floor(progress);
  loaderProgress.setAttribute('aria-valuenow', String(Math.floor(progress)));

  if (progress < 100) {
    requestAnimationFrame(loaderTick);
    return;
  }
  setTimeout(() => {
    loader.classList.add('is-done');
    // ローダーが消え始めたら、ヒーローのオープニング演出を開始
    document.dispatchEvent(new Event('intro:start'));
  }, LOADER_HOLD_MS); // 100%とロゴが見える時間を確保してから開く
};
requestAnimationFrame(loaderTick);

/* ==========================================================
   ヒーロー:オープニング演出(文字・ヘッダー・情報バー)
   1. ヘッダーがふわっと現れる
   2. 大きな「YOHAKU design」が1文字ずつ、波打ちながら下からせり上がる
   3. 下の情報バーの文字がタイプ表示される
   (図形の動きは下の p5.js 側で行う)
   ========================================================== */
if (!reduceMotion) {
  const root = document.documentElement;
  root.classList.add('intro-wait'); // 演出が始まるまで隠しておく

  // 大きな文字を1文字ずつ <span class="char"> に分ける(マーキーの複製より先に行う)
  let charIndex = 0;
  document.querySelectorAll('.hero__marquee .marquee__item > *').forEach((el) => {
    const text = el.textContent;
    el.textContent = '';
    [...text].forEach((ch) => {
      const span = document.createElement('span');
      span.className = 'char';
      span.textContent = ch;
      span.style.setProperty('--i', charIndex++);
      el.appendChild(span);
    });
  });

  // 情報バーの文字はいったん空にして、あとでタイプ表示する
  const infoItems = Array.from(document.querySelectorAll('.hero__info p')).map((el) => {
    const text = el.textContent;
    el.textContent = '';
    return { el, text };
  });
  const typeText = ({ el, text }) => {
    let n = 0;
    const timer = setInterval(() => {
      n++;
      el.textContent = text.slice(0, n);
      if (n >= text.length) clearInterval(timer);
    }, 28);
  };

  document.addEventListener('intro:start', () => {
    setTimeout(() => root.classList.add('intro-header'), 1400);
    setTimeout(() => root.classList.add('intro-text'), 2300);
    setTimeout(() => {
      root.classList.add('intro-info');
      infoItems.forEach((item, i) => setTimeout(() => typeText(item), i * 180));
    }, 3200);
  }, { once: true });
}

/* ==========================================================
   Lenis(慣性スクロール)
   ========================================================== */
let lenis = null;
if (window.Lenis && !reduceMotion) {
  lenis = new Lenis({ lerp: 0.09 });
  const raf = (time) => {
    lenis.raf(time);
    requestAnimationFrame(raf);
  };
  requestAnimationFrame(raf);
}

// クリック後に下線・色の動きが残らないよう、マウスが離れるまでホバー表示を止める
document.querySelectorAll('.header a, .menu a').forEach((link) => {
  link.addEventListener('click', () => {
    link.blur();
    link.classList.add('is-clicked');
  });
  link.addEventListener('mouseleave', () => link.classList.remove('is-clicked'));
});

// ページ内リンク
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

/* ==========================================================
   全画面メニュー
   ========================================================== */
const menuBtn = document.getElementById('menuBtn');
const menu = document.getElementById('menu');

function openMenu() {
  document.body.classList.add('is-menu-open');
  menuBtn.setAttribute('aria-expanded', 'true');
  menuBtn.setAttribute('aria-label', 'メニューを閉じる');
  menu.setAttribute('aria-hidden', 'false');
  if (lenis) lenis.stop();
}
function closeMenu() {
  document.body.classList.remove('is-menu-open');
  menuBtn.setAttribute('aria-expanded', 'false');
  menuBtn.setAttribute('aria-label', 'メニューを開く');
  menu.setAttribute('aria-hidden', 'true');
  if (lenis) lenis.start();
}
menuBtn.addEventListener('click', () => {
  document.body.classList.contains('is-menu-open') ? closeMenu() : openMenu();
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeMenu();
});
// メニューはスマホだけ。画面が広くなったら閉じる
matchMedia('(min-width: 769px)').addEventListener('change', (e) => {
  if (e.matches) closeMenu();
});

/* ==========================================================
   マーキー(画面幅の2倍以上になるまで複製してループ)
   ========================================================== */
function setupMarquee(el) {
  const track = el.querySelector('.marquee__track');
  const original = Array.from(track.children);
  // 1セット分を画面幅以上に
  while (track.scrollWidth < window.innerWidth + 200) {
    original.forEach((item) => track.appendChild(item.cloneNode(true)));
  }
  // 同じものをもう1セット(-50%移動でつなぎ目なくループ)
  Array.from(track.children).forEach((item) => {
    const clone = item.cloneNode(true);
    clone.setAttribute('aria-hidden', 'true');
    track.appendChild(clone);
  });
  track.style.setProperty('--dur', `${el.dataset.speed || 30}s`);
  // 画面外では流すのを止める
  new IntersectionObserver(([entry]) => {
    el.classList.toggle('is-offscreen', !entry.isIntersecting);
  }).observe(el);
}
readyTasks.push(document.fonts.ready.then(() => {
  document.querySelectorAll('.marquee').forEach(setupMarquee);
}));

/* ==========================================================
   About:図形(オブジェクト)がスクロールで現れる
   - 画面の下から入ってきた時は、オブジェクトが大きい状態(2倍)
   - オブジェクトの中央が画面の中央に来た時に、いちばん小さくなる
   - さらに上へ流れると、元の大きさに戻る
   - 戻ると逆の動きになる(スクロール位置に連動)
   - 中の画像は動かさない
   - スクロール中は画像が縦の帯に割れて、帯ごとに上下へずれる
     (止まると帯がそろって、元の1枚の画像に戻る)
   ========================================================== */
const aboutVisual = document.getElementById('aboutVisual');
const aboutSvg = aboutVisual.querySelector('.about-visual__svg');
const aboutDepth = document.getElementById('aboutDepth');

// 中身(下地+画像)を縦の帯に切り分ける
const STRIP_COUNT = 6; // 多いほど細かく割れるが、帯の数だけ画像を描き直すので重くなる
const SVG_NS = 'http://www.w3.org/2000/svg';
const strips = [];

function buildStrips() {
  const src = document.getElementById('aboutSrc');
  const defs = aboutSvg.querySelector('defs');
  defs.appendChild(src); // 元の中身は「型」として defs にしまい、<use> で何度も使う

  const w = 1200 / STRIP_COUNT;
  for (let i = 0; i < STRIP_COUNT; i++) {
    // 帯の形(縦長の長方形)で切り抜く。すき間が出ないよう少しだけ重ねる
    const clip = document.createElementNS(SVG_NS, 'clipPath');
    clip.id = `aboutStrip${i}`;
    const rect = document.createElementNS(SVG_NS, 'rect');
    rect.setAttribute('x', i * w - 0.5);
    rect.setAttribute('y', -400);
    rect.setAttribute('width', w + 1);
    rect.setAttribute('height', 1320);
    clip.appendChild(rect);
    defs.appendChild(clip);

    const group = document.createElementNS(SVG_NS, 'g');
    group.setAttribute('clip-path', `url(#aboutStrip${i})`);
    const use = document.createElementNS(SVG_NS, 'use');
    use.setAttribute('href', '#aboutSrc');
    group.appendChild(use);
    aboutDepth.appendChild(group);

    strips.push({
      use,
      seed: Math.random(),            // 帯ごとのずれ方のばらつき
      stretch: 0.5 + Math.random(),   // 帯ごとの縦の伸び方
    });
  }
}

if (!reduceMotion) {
  buildStrips();
  readyTasks.push(decodeImage(aboutSvg.querySelector('#aboutSrc image').getAttribute('href')));

  const START_SCALE = 2;  // 画面に入ってきた時の倍率(大きい)
  // いちばん小さくなった時の倍率:横幅が section-bar とぴったり同じになる倍率
  const aboutBar = aboutVisual.parentElement.querySelector('.section-bar');
  let MIN_SCALE = 0.85;
  const updateMinScale = () => {
    MIN_SCALE = aboutBar.offsetWidth / aboutVisual.offsetWidth;
  };
  updateMinScale();
  window.addEventListener('resize', updateMinScale);
  let scale = START_SCALE; // 今の大きさ(なめらかに追いかける)
  let split = 0;       // 帯の割れ具合(向き付き -1〜1)
  let lastY = window.scrollY;
  let running = false;

  const clamp01 = (v) => Math.min(Math.max(v, 0), 1);
  const easeOut = (t) => 1 - Math.pow(1 - t, 3);
  const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);

  // 形の出方:図形のフチ(stroke)を太く → 細くして、四角い画像から形を浮かび上がらせる
  const aboutShapes = document.getElementById('aboutShapes');
  const aboutHoles = document.getElementById('aboutHoles');
  const MAX_STROKE = 320; // これだけ太いと、図形どうしがくっついて全面が四角になる
  let form = 0;           // 形のでき具合(0 = 四角い画像だけ → 1 = 完成した形)
  let lastScaleCss = '';  // 最後に書きこんだ値(変わらない時は書きこまない)
  let lastStroke = '';
  let lastHoles = '';

  // 最初は大きい状態+形がない状態にしておく
  aboutSvg.style.transform = `scale(${START_SCALE})`;
  aboutShapes.setAttribute('stroke-width', MAX_STROKE);
  aboutHoles.setAttribute('opacity', 0);

  // 帯ごとに上下へずらす
  let stripsFlat = true; // 帯がそろっている(transform なし)状態か
  const updateStrips = (time) => {
    const a = Math.abs(split);
    // そろったまま止まっている間は、何も書きかえない(再描画させない)
    if (a < 0.002 && stripsFlat) return;
    stripsFlat = a < 0.002;
    strips.forEach((s, i) => {
      if (a < 0.002) {
        s.use.removeAttribute('transform');
        return;
      }
      const x = i / (STRIP_COUNT - 1);
      // 左から右へ階段状にずれていく+帯ごとのばらつき+ゆらぎ
      const stair = (x - 0.5) * 1.2;
      const jitter = (s.seed - 0.5) * 0.9;
      const wave = Math.sin(time * 0.004 + i * 0.7) * 0.25;
      const dy = split * 160 * (stair + jitter + wave);
      const sy = 1 + a * 0.6 * s.stretch;  // 縦に伸ばす
      // 中心(y=260)を基準に伸ばしてから、上下にずらす
      s.use.setAttribute('transform', `translate(0 ${dy + 260}) scale(1 ${sy}) translate(0 -260)`);
    });
  };

  const updateDepth = (time) => {
    const rect = aboutVisual.getBoundingClientRect();
    const vh = window.innerHeight;
    const center = rect.top + rect.height / 2; // オブジェクトの中央の位置

    // grow  :画面の下から入ってきた瞬間 0 → オブジェクトの中央が画面の中央に来た時 1
    const grow = clamp01((vh + rect.height / 2 - center) / (vh / 2 + rect.height / 2));
    // settle:画面の中央から、さらに画面の30%分上がるまでに 0 → 1
    const settle = clamp01((vh / 2 - center) / (vh * 0.3));

    // 大きい(START_SCALE)→ いちばん小さく(MIN_SCALE)→ 元の大きさ(1)
    const target = grow < 1
      ? START_SCALE - (START_SCALE - MIN_SCALE) * easeOut(grow)
      : MIN_SCALE + (1 - MIN_SCALE) * easeInOut(settle);
    scale += (target - scale) * 0.18; // 大きいほどスクロールに早く追いつく
    // 値が変わった時だけ書きかえる(同じ値の書きこみでも再描画が起きるため)
    // (細かすぎる桁は見た目が変わらないのに書きこみが続くので、小数3桁まで)
    const scaleCss = `scale(${scale.toFixed(3)})`;
    if (scaleCss !== lastScaleCss) {
      aboutSvg.style.transform = scaleCss;
      lastScaleCss = scaleCss;
    }

    // 形:画面に入ってから中央に来るまでに、四角い画像 → 完成した形へ
    form += (easeInOut(grow) - form) * 0.18;
    // フチの太さが変わるたびにマスク全体を描き直すので、1px 単位でだけ書きかえる
    const strokeVal = String(Math.round(MAX_STROKE * (1 - form)));
    if (strokeVal !== lastStroke) {
      aboutShapes.setAttribute('stroke-width', strokeVal);
      lastStroke = strokeVal;
    }
    const holesVal = (form * form).toFixed(2); // 穴は最後の方で現れる
    if (holesVal !== lastHoles) {
      aboutHoles.setAttribute('opacity', holesVal);
      lastHoles = holesVal;
    }

    // スクロールの速さ(向き付き)で帯の割れ具合を決める。止まると0に戻る
    const velocity = lenis ? lenis.velocity : window.scrollY - lastY;
    lastY = window.scrollY;
    const targetSplit = Math.max(-1, Math.min(1, velocity / 35));
    split += (targetSplit - split) * (Math.abs(targetSplit) > Math.abs(split) ? 0.2 : 0.1);
    if (Math.abs(split) < 0.002) split = 0;
    updateStrips(time);

    if (running) requestAnimationFrame(updateDepth);
  };

  // 画面に見えている間だけ動かす
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting && !running) {
      running = true;
      requestAnimationFrame(updateDepth);
    } else if (!entry.isIntersecting) {
      running = false;
    }
  }).observe(aboutVisual);
}

/* ==========================================================
   スクロールで出現
   ========================================================== */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-in');
      revealObserver.unobserve(entry.target);
    }
  });
}, { rootMargin: '0px 0px -10% 0px' });
document.querySelectorAll('.js-reveal').forEach((el) => revealObserver.observe(el));

/* ==========================================================
   Works:セクションに入ったら、画面全体を生成り → 墨色にパッと切り替える
   - Worksが画面の上下15%を除いた範囲にかかっている間だけ墨色
   - 上に戻る / 下の Service へ進むと、生成りに戻る
   - Works自体はいつも墨色の背景なので、切り替わる前後でも文字が消えない
   ========================================================== */
const worksSection = document.getElementById('works');
if (worksSection) {
  new IntersectionObserver(([entry]) => {
    document.body.classList.toggle('is-dark', entry.isIntersecting);
  }, { rootMargin: '-15% 0px -15% 0px' }).observe(worksSection);
}

document.getElementById('year').textContent = new Date().getFullYear();

/* ==========================================================
   Works:3枚のパネルで画像を順番に切り替える
   - 3枚とも同じ画像を表示し、同時に切り替える
   - 新しい画像は右端から左へ広がり、古い画像は左端へ縮んで消える
   - 画面に見えている間だけ切り替える
   ========================================================== */
const worksSlider = document.getElementById('worksSlider');
if (worksSlider) {
  const srcs = worksSlider.dataset.images.split(',').map((s) => s.trim());
  const interval = Number(worksSlider.dataset.interval || 4000); // 切り替えの間隔(ms)
  const DURATION = 1100; // 切り替えにかかる時間(CSSのアニメーションと同じ)
  const panels = Array.from(worksSlider.querySelectorAll('.works-slider__panel'));
  // 画像が右から左へ流れる時間(入ってきてから消え終わるまで)
  worksSlider.style.setProperty('--works-drift', `${interval + DURATION}ms`);

  // 各パネルに画像を全部入れておく(最初の1枚だけ表示)
  const imgsByPanel = panels.map((panel) => srcs.map((src, i) => {
    const img = document.createElement('img');
    img.src = src;
    img.alt = '';
    img.decoding = 'async'; // 次の画像がすぐ出せるよう、最初から読み込んでおく(lazyにしない)
    if (i === 0) img.classList.add('is-current');
    panel.appendChild(img);
    return img;
  }));

  let index = 0;
  let timer = null;

  // スライダー下の番号(01 - 03)と作品名
  const worksNum = document.getElementById('worksNum');
  const worksTitles = Array.from(document.querySelectorAll('#worksTitles li'));
  const pad = (n) => String(n).padStart(2, '0');
  document.getElementById('worksTotal').textContent = pad(srcs.length);
  const updateMeta = () => {
    worksNum.textContent = pad(index + 1);
    worksTitles.forEach((li, i) => li.classList.toggle('is-current', i === index));
  };

  const next = () => {
    const prev = index;
    index = (index + 1) % srcs.length;
    updateMeta();
    imgsByPanel.forEach((imgs) => {
      imgs[prev].classList.remove('is-current');
      if (reduceMotion) {
        imgs[index].classList.add('is-current');
        return;
      }
      imgs[prev].classList.add('is-leaving');
      imgs[index].classList.add('is-entering', 'is-moving');
      // 切り替えが終わったら、新しい画像を「今の画像」にする
      setTimeout(() => {
        imgs[prev].classList.remove('is-leaving', 'is-moving'); // 流れた位置を元に戻す
        imgs[index].classList.remove('is-entering');
        imgs[index].classList.add('is-current');
      }, DURATION);
    });
  };

  // 画面外ではパネルの列を流すのも止める
  new IntersectionObserver(([entry]) => {
    worksSlider.classList.toggle('is-offscreen', !entry.isIntersecting);
  }).observe(worksSlider);

  if (srcs.length > 1) {
    new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting && !timer) {
        // 最初の画像は、画面に入ったときに流し始める
        if (!reduceMotion) imgsByPanel.forEach((imgs) => imgs[index].classList.add('is-moving'));
        timer = setInterval(next, interval);
      } else if (!entry.isIntersecting && timer) {
        clearInterval(timer);
        timer = null;
      }
    }).observe(worksSlider);
  }
}

/* ==========================================================
   ヒーロー:p5.js 幾何学タイルのループ
   大きな正方形のマスに、四分円・半円・三角形などを並べて
   横方向に流し続ける。マウスが乗ったマスは90度回転する。
   ========================================================== */
const heroSketch = (p) => {
  const COLORS = ['#2C3E9E', '#EBB4BE', '#DCA62A', '#BDB8AC', '#17181C', '#EEEBE3'];
  const TYPES = ['quarter', 'quarter', 'quarter', 'half', 'tri', 'rect', 'bar', 'circle', 'ring', 'none'];
  const hero = document.getElementById('hero');

  let cell;          // 1マスのサイズ
  let rows;          // 行数
  let columns = [];  // 列ごとのタイル配列
  let offsetX = 0;   // 横方向のずれ
  let shiftCount = 0; // 何列流れたか(マウス判定用)
  let hoverKey = '';

  // 回しても見た目が変わらない図形(これらは色を変える)
  const SYMMETRIC = ['circle', 'ring', 'none'];

  const hexToRgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));

  // ===== オープニング演出 =====
  // 0.0s〜 補助線が伸びる/暗いモノクロの図形が、傾いて大きい状態から元の位置へ
  // 1.3s〜 図形が左下から右上へ波のように「明るいモノクロ → カラー」に変わる
  const INTRO = !reduceMotion;
  let introStart = -1;       // 演出の開始時刻(ms)。-1 = まだ始まっていない
  let introDone = !INTRO;    // 演出が終わったら true(通常の動きに戻る)
  const INTRO_CAM = 2.8;     // 拡大・傾き → 元に戻るまでの秒数
  const INTRO_LINE = 1.8;    // 補助線が伸びきるまでの秒数
  const INTRO_END = 4.4;     // 演出全体の秒数
  document.addEventListener('intro:start', () => { introStart = p.millis(); }, { once: true });
  const introTime = () => (introStart < 0 ? 0 : (p.millis() - introStart) / 1000);
  const easeOutQuart = (x) => 1 - Math.pow(1 - Math.min(Math.max(x, 0), 1), 4);

  // ===== シーンの切り替え(オープニングのあと、ずっとくり返す) =====
  // 1つの絵を HOLD 秒見せたら、次の2種類の切り替えを交互に行う
  //  fade :図形が右上から波のように「グレー → 暗く → 真っ暗」になり、
  //         別の絵がぼやけて傾いた状態から近づいてきて、左下からカラーに戻る
  //  swipe:画面が斜めにすばやく流れて(ブレながら)、別の絵に切り替わる
  const HOLD = 2.0;          // 1つの絵を見せる秒数
  const FADE_SWAP = 1.9;   // fade:真っ暗になって絵を入れかえる時刻(秒)
  const FADE_END = 2.8;    // fade:切り替え全体の秒数
  const SWIPE_END = 1.1;   // swipe:切り替え全体の秒数
  let sceneStart = -1;     // 今の絵を見せ始めた時刻(秒)
  let trans = null;        // 切り替え中の情報 { id, type, start, tt, swapped, dir }
  let transCount = 0;
  let canvasEl = null;     // ぼかし(CSSのfilter)をかけるキャンバス
  let lastBlur = '';
  const clamp01 = (v) => Math.min(Math.max(v, 0), 1);
  const easeInCubic = (x) => x * x * x;
  const easeOutCubic = (x) => 1 - Math.pow(1 - x, 3);

  // 色をモノクロにする(light = false:暗い / true:明るい)
  function mono(rgb, light, fade = 1) {
    const l = rgb[0] * 0.3 + rgb[1] * 0.59 + rgb[2] * 0.11;
    const v = light ? 70 + l * 0.7 : 23 + l * 0.2 * fade;
    return [v, v, v];
  }
  // タイルが今目指す色(オープニング中・fade切り替え中はモノクロを経由する)
  function introColor(t, hex, T) {
    const rgb = hexToRgb(hex);
    if (!introDone) {
      if (t.delay === undefined || T < t.delay) return mono(rgb, false, easeOutQuart(T / 1.2));
      if (T < t.delay + 0.45) return mono(rgb, true);
      return rgb;
    }
    if (!trans || trans.type !== 'fade' || t.tid !== trans.id) return rgb;
    const tt = trans.tt;
    if (!trans.swapped) {
      // 暗くなる:カラー → 明るいグレー → 暗いグレー → 真っ暗
      if (tt < t.dd) return rgb;
      if (tt < t.dd + 0.3) return mono(rgb, true);
      if (tt < t.dd + 0.6) return mono(rgb, false);
      return mono(rgb, false, 0);
    }
    // 戻る:暗いグレーで浮かび上がる → 明るいグレー → カラー
    const s = tt - FADE_SWAP;
    if (s < t.rd) return mono(rgb, false, easeOutQuart(s / 0.8));
    if (s < t.rd + 0.4) return mono(rgb, true);
    return rgb;
  }

  function pickPair(exclude = []) {
    const list = COLORS.filter((c) => !exclude.includes(c));
    const bg = p.random(list);
    let fg = p.random(COLORS);
    while (fg === bg) fg = p.random(COLORS);
    return { bg, fg };
  }

  function makeTile() {
    const { bg, fg } = pickPair();
    const rot = p.floor(p.random(4)) * p.HALF_PI;
    return {
      type: p.random(TYPES),
      bg,
      fg,
      // 今表示している色(なめらかに変えるため)。演出前は真っ暗から始める
      bgNow: introDone ? hexToRgb(bg) : mono(hexToRgb(bg), false, 0),
      fgNow: introDone ? hexToRgb(fg) : mono(hexToRgb(fg), false, 0),
      rot,
      target: rot,
      pulse: 0, // 円・輪が縮んで戻る動き(1 → 0 で元の大きさ)
      guide: p.random() < 0.3, // 補助線の円を描くか
    };
  }

  // 絵を入れかえる(今あるマスに、新しい図形と色を配りなおす)
  // dark = true のときは真っ暗な状態から始める(fade用)
  function recompose(dark) {
    columns.forEach((col) => col.forEach((t) => {
      const fresh = makeTile();
      Object.assign(t, { type: fresh.type, bg: fresh.bg, fg: fresh.fg, rot: fresh.rot, target: fresh.rot, pulse: 0 });
      t.bgNow = dark ? mono(hexToRgb(t.bg), false, 0) : hexToRgb(t.bg);
      t.fgNow = dark ? mono(hexToRgb(t.fg), false, 0) : hexToRgb(t.fg);
    }));
  }

  // マスに動きを加える:図形を回す or 色を変える
  function nudge(t) {
    if (SYMMETRIC.includes(t.type)) {
      const { bg, fg } = pickPair([t.bg]); // 今と違う背景色にする
      t.bg = bg;
      t.fg = fg;
    } else {
      t.target += p.HALF_PI;
    }
  }

  // 【自動】図形の色だけを変える(空白のマスは背景色を変える)
  function autoColor(t) {
    if (t.type === 'none') {
      t.bg = pickPair([t.bg]).bg;
      return;
    }
    let fg = p.random(COLORS);
    while (fg === t.fg || fg === t.bg) fg = p.random(COLORS);
    t.fg = fg;
  }

  // 【自動】図形を動かす(円・輪は縮んで戻る、空白は色を変える)
  function autoMove(t) {
    if (t.type === 'none') {
      autoColor(t);
    } else if (SYMMETRIC.includes(t.type)) {
      t.pulse = 1;
    } else {
      t.target += p.HALF_PI * (p.random() < 0.3 ? 2 : 1); // ときどき半回転
    }
  }

  // 【自動】ランダムな時間ごとに、ランダムなマスを1〜3個動かす
  let nextAutoAt = 0;
  function autoPlay() {
    if (p.millis() < nextAutoAt) return;
    nextAutoAt = p.millis() + p.random(250, 700);
    const count = p.floor(p.random(1, 4));
    for (let k = 0; k < count; k++) {
      const t = p.random(p.random(columns));
      p.random() < 0.5 ? autoColor(t) : autoMove(t);
    }
  }

  // 色を目標の色に少しずつ近づける
  function easeColor(now, hex) {
    const to = Array.isArray(hex) ? hex : hexToRgb(hex); // 色は [r,g,b] でも受け取れる
    for (let i = 0; i < 3; i++) now[i] += (to[i] - now[i]) * 0.12;
  }

  function makeColumn() {
    return Array.from({ length: rows }, makeTile);
  }

  // 画面幅に合わせてマスの大きさを決める
  // 画面が小さくなるほどマスを小さくして、数を増やす
  function cellSize() {
    const w = p.width;
    const h = p.height;
    if (w > 1024) return Math.max(h / 2.2, 150);      // デスクトップ(従来どおり)
    if (w > 768) return Math.max(Math.min(h / 3.2, w / 4.5), 80); // タブレット
    if (w > 480) return Math.max(Math.min(h / 5, w / 3.5), 60);   // スマホ
    return Math.max(Math.min(h / 6, w / 4), 50);                  // 小さいスマホ
  }

  function build() {
    cell = cellSize();
    rows = Math.ceil(p.height / cell) + 1;
    const need = Math.ceil(p.width / cell) + 2;
    columns = Array.from({ length: need }, makeColumn);
    offsetX = 0;
  }

  function drawTile(t, x, y, s) {
    p.push();
    p.translate(x + s / 2, y + s / 2);
    p.noStroke();
    // 正方形(背景)は回さない
    p.fill(t.bgNow);
    p.rect(-s / 2, -s / 2, s + 1, s + 1);
    // 中の図形だけを回す。はみ出した部分はマスの外に出ないよう切り抜く
    p.drawingContext.save();
    p.drawingContext.beginPath();
    p.drawingContext.rect(-s / 2, -s / 2, s + 1, s + 1);
    p.drawingContext.clip();
    p.rotate(t.rot);
    // 縮んで戻る動き(sin で 1 → 小さく → 1 となめらかに)
    if (t.pulse > 0) p.scale(1 - 0.45 * Math.sin(t.pulse * Math.PI));
    p.fill(t.fgNow);
    switch (t.type) {
      case 'quarter': // 四分円
        p.arc(-s / 2, -s / 2, s * 2, s * 2, 0, p.HALF_PI, p.PIE);
        break;
      case 'half': // 半円
        p.arc(0, -s / 2, s, s, 0, p.PI, p.PIE);
        break;
      case 'tri': // 直角三角形
        p.triangle(-s / 2, -s / 2, s / 2, -s / 2, -s / 2, s / 2);
        break;
      case 'rect': // 半分の長方形
        p.rect(-s / 2, -s / 2, s / 2, s);
        break;
      case 'bar': // 横棒
        p.rect(-s / 2, -s / 6, s, s / 3);
        break;
      case 'circle':
        p.circle(0, 0, s * 0.82);
        break;
      case 'ring': // 太い輪
        p.circle(0, 0, s * 0.9);
        p.fill(t.bgNow);
        p.circle(0, 0, s * 0.45);
        break;
      default:
        break;
    }
    p.drawingContext.restore();
    p.pop();
  }

  p.setup = () => {
    const canvas = p.createCanvas(hero.clientWidth, hero.clientHeight);
    canvas.parent('hero-canvas');
    canvasEl = canvas.elt;
    p.pixelDensity(renderDensity());
    build();

    // 画面外では描画を止めて負荷を下げる
    new IntersectionObserver(([entry]) => {
      entry.isIntersecting ? p.loop() : p.noLoop();
    }).observe(hero);
  };

  p.windowResized = () => {
    // スマホのアドレスバー開閉(高さだけの変化)では作り直さない
    const widthChanged = hero.clientWidth !== p.width;
    p.resizeCanvas(hero.clientWidth, hero.clientHeight);
    if (widthChanged) build();
  };

  p.draw = () => {
    p.background('#17181C');

    // 横に流す(フレームレートに左右されないよう deltaTime を使う)
    if (!reduceMotion) {
      const speed = cell / 3200; // 1msあたりの移動量
      offsetX -= speed * Math.min(p.deltaTime, 50);
    }
    // 1列分流れたら先頭の列を捨てて、末尾に新しい列を足す(無限ループ)
    if (offsetX <= -cell) {
      columns.shift();
      columns.push(makeColumn());
      offsetX += cell;
      shiftCount++;
    }

    const top = (p.height - rows * cell) / 2;

    // オープニング演出の進み具合
    const T = introTime();
    const now = p.millis() / 1000;
    if (!introDone && T >= INTRO_END) {
      introDone = true;
      sceneStart = now;
    }

    // シーンの切り替えを進める(fade と swipe を交互に)
    if (introDone && !reduceMotion) {
      if (!trans && now - sceneStart >= HOLD) {
        transCount++;
        trans = {
          id: transCount,
          type: transCount % 2 ? 'fade' : 'swipe',
          start: now,
          tt: 0,
          swapped: false,
          dir: p.random([[-1, -0.45], [1, 0.45], [-1, 0.45], [1, -0.45]]), // swipeで流れる向き
        };
      }
      if (trans) {
        trans.tt = now - trans.start;
        const swapAt = trans.type === 'fade' ? FADE_SWAP : SWIPE_END / 2;
        if (!trans.swapped && trans.tt >= swapAt) {
          trans.swapped = true;
          recompose(trans.type === 'fade'); // いちばん暗い/いちばん速いところで絵を入れかえる
        }
        if (trans.tt >= (trans.type === 'fade' ? FADE_END : SWIPE_END)) {
          trans = null;
          sceneStart = now;
        }
      }
    }
    const busy = !introDone || !!trans; // オープニング中・切り替え中

    // 自動演出:ランダムに図形の色が変わる・図形が動く(絵を見せている間だけ)
    if (!reduceMotion && !busy) autoPlay();

    // マウスが新しいマスに入ったら、図形を回す or 色を変える
    if (!busy && p.mouseX > 0 && p.mouseX < p.width && p.mouseY > 0 && p.mouseY < p.height) {
      const ci = Math.floor((p.mouseX - offsetX) / cell);
      const ri = Math.floor((p.mouseY - top) / cell);
      const key = `${ci + shiftCount}-${ri}`;
      if (key !== hoverKey && columns[ci] && columns[ci][ri]) {
        nudge(columns[ci][ri]);
        hoverKey = key;
      }
    }

    // カメラ(画面全体の拡大・傾き・移動・ぼかし)
    let cam = 1;
    let camScale = 1;
    let camRot = 0;
    let camX = 0;
    let camY = 0;
    let blur = 0;
    if (!introDone) {
      // オープニング:「傾いて大きい」状態から元に戻す
      cam = easeOutQuart(T / INTRO_CAM);
      camScale = 1 + 1.2 * (1 - cam);
      camRot = -0.35 * (1 - cam);
    } else if (trans && trans.type === 'fade' && trans.swapped) {
      // fade:新しい絵が、ぼやけて傾いた状態から近づいてくる
      cam = easeOutQuart((trans.tt - FADE_SWAP) / 2.4);
      const side = trans.id % 4 === 1 ? 1 : -1; // 傾きの向きを毎回変える
      camScale = 1 + 0.9 * (1 - cam);
      camRot = 0.28 * side * (1 - cam);
      blur = 10 * Math.pow(1 - cam, 2);
    } else if (trans && trans.type === 'swipe') {
      // swipe:前半は加速しながら流れ出て、後半は新しい絵が減速しながら流れ込む
      const u = Math.min(trans.tt / SWIPE_END, 1);
      const D = p.width * 0.8;
      const m = u < 0.5 ? easeInCubic(u * 2) : -(1 - easeOutCubic((u - 0.5) * 2));
      camX = trans.dir[0] * D * m;
      camY = trans.dir[1] * D * m;
      blur = 14 * Math.sin(u * Math.PI);
    }

    // ぼかしはキャンバス全体にCSSでかける(端が透けないよう少しだけ拡大)
    const blurCss = blur > 0.3 ? `blur(${blur.toFixed(1)}px)` : '';
    if (canvasEl && blurCss !== lastBlur) {
      canvasEl.style.filter = blurCss;
      canvasEl.style.transform = blurCss ? `scale(${(1 + blur * 0.005).toFixed(3)})` : '';
      lastBlur = blurCss;
    }

    // 傾けたり動かしたりしても画面の端にすき間が出ないよう、外側にもタイルを敷く
    const E = busy ? 3 + Math.ceil((Math.abs(camX) + Math.abs(camY)) / cell) : 0;
    const n = columns.length;
    const tileAt = (i, j) => columns[((i % n) + n) % n][((j % rows) + rows) % rows];

    p.push();
    if (busy) {
      p.translate(p.width / 2 + camX, p.height / 2 + camY);
      p.rotate(camRot);
      p.scale(camScale);
      p.translate(-p.width / 2, -p.height / 2);
    }

    // タイルの状態を進める
    columns.forEach((col, i) => {
      col.forEach((t, j) => {
        if (!introDone) {
          // 色が変わる順番:左下 → 右上へ波のように(+少しばらつき)
          if (t.delay === undefined) {
            const nx = (offsetX + i * cell) / p.width;
            const ny = (top + j * cell) / p.height;
            t.delay = 1.3 + Math.min(Math.max((nx + (1 - ny)) / 2, 0), 1) * 1.6 + p.random(0.35);
          }
          // カラーになった瞬間に、図形をくるっと回す(円・輪は縮んで戻る)
          if (!t.kicked && T >= t.delay + 0.45) {
            t.kicked = true;
            if (SYMMETRIC.includes(t.type)) t.pulse = 1;
            else t.target += p.HALF_PI;
          }
        } else if (trans && trans.type === 'fade') {
          const nx = clamp01((offsetX + i * cell) / p.width);
          const ny = clamp01((top + j * cell) / p.height);
          // この切り替えでの順番を決める:右上から暗くなり、左下からカラーに戻る
          if (t.tid !== trans.id) {
            t.tid = trans.id;
            t.dd = ((1 - nx) + ny) / 2 * 1.0 + p.random(0.25);
            t.rd = 0.3 + (nx + (1 - ny)) / 2 * 1.2 + p.random(0.3);
          }
          // カラーに戻った瞬間に、図形をくるっと回す
          if (trans.swapped && t.kickId !== trans.id && trans.tt - FADE_SWAP >= t.rd + 0.4) {
            t.kickId = trans.id;
            if (SYMMETRIC.includes(t.type)) t.pulse = 1;
            else t.target += p.HALF_PI;
          }
        }
        t.rot = p.lerp(t.rot, t.target, 0.08);
        t.pulse = Math.max(0, t.pulse - Math.min(p.deltaTime, 50) / 900); // 約0.9秒で元の大きさに
        easeColor(t.bgNow, introColor(t, t.bg, T));
        easeColor(t.fgNow, introColor(t, t.fg, T));
      });
    });

    // タイル
    for (let i = -E; i < n + E; i++) {
      for (let j = -E; j < rows + E; j++) {
        drawTile(tileAt(i, j), offsetX + i * cell, top + j * cell, cell);
      }
    }

    // 補助線(設計図のような細い線)。オープニング中は線が伸びていく
    const lp = introDone ? 1 : easeOutQuart(T / INTRO_LINE);
    if (lp > 0.001) {
      p.noFill();
      p.stroke(238, 235, 227, busy ? 90 + 60 * (1 - cam) : 90);
      p.strokeWeight(1 / camScale);
      const x0 = -E * cell;
      const x1 = p.width + E * cell;
      const y0 = top - E * cell;
      const y1 = top + (rows + E) * cell;
      for (let j = -E; j <= rows + E; j++) {
        const y = top + j * cell;
        p.line(x0, y, x0 + (x1 - x0) * lp, y);
      }
      for (let i = -E; i < n + E; i++) {
        const x = offsetX + i * cell;
        p.line(x, y0, x, y0 + (y1 - y0) * lp);
        for (let j = -E; j < rows + E; j++) {
          if (!tileAt(i, j).guide) continue;
          if (lp >= 1) p.circle(x, top + j * cell, cell * 2);
          else p.arc(x, top + j * cell, cell * 2, cell * 2, -p.HALF_PI, -p.HALF_PI + p.TWO_PI * lp);
        }
      }
    }
    p.pop();
  };
};

if (window.p5) new p5(heroSketch);


if (window.THREE) {
  // Webフォント(通常体・イタリック体)の読み込みを待ってから文字を描く
  // (シェーダーの準備まで済ませてからロード画面を閉じる)
  readyTasks.push(Promise.all([
    document.fonts.load('600 100px "Bodoni Moda"'),
    document.fonts.load('italic 600 100px "Bodoni Moda"'),
  ]).catch(() => {}).then(() => {
    const items = [];
    const waiting = new Set(); // まだ作っていない見出し
    const reserve = (el) => { el.style.height = `${rollRowHeight()}px`; }; // 作ったときに高さが変わらないよう、先に確保する
    const start = (el) => {
      el.style.height = '';
      items.push(new DistortText(el));
    };
    // WebGLの準備は重いので、遠くの見出しは画面に近づいてから作る
    const nearObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        nearObserver.unobserve(entry.target);
        waiting.delete(entry.target);
        start(entry.target);
      });
    }, { rootMargin: '100% 0px' });
    document.querySelectorAll('.js-distort').forEach((el) => {
      if (el.getBoundingClientRect().top < window.innerHeight * 2) {
        start(el); // 開いた時点で近い見出しは、ロード画面のあいだに作る
        return;
      }
      el.classList.add('is-distorted');
      reserve(el);
      waiting.add(el);
      nearObserver.observe(el);
    });
    window.addEventListener('resize', () => {
      items.forEach((item) => item.resize());
      waiting.forEach(reserve);
    });
    if (reduceMotion) return; // 動きを減らす設定では、元の文字のまま止めておく

    // すべての見出しを1つのループでまとめて動かす
    let lastTime = performance.now();
    let lastY = window.scrollY;
    const tick = (now) => {
      const dt = Math.min(now - lastTime, 50);
      lastTime = now;
      const velocity = lenis ? lenis.velocity : window.scrollY - lastY;
      lastY = window.scrollY;
      items.forEach((item) => item.update(now / 1000, dt, velocity));
      requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }));
}

/* ==========================================================
   【追加】ヘッダーのリンク:ホバーで文字が下から入れかわる
   - 同じ文字を2段に重ね、ホバーで上に1段分ずらす
   - 上の文字は上へ抜け、下の文字が下からせり上がってくる
   ========================================================== */
if (!reduceMotion) {
  document.querySelectorAll('.header__nav a, .header__contact').forEach((link) => {
    const html = link.innerHTML.trim();
    link.innerHTML =
      `<span class="roll"><span class="roll__inner">` +
      `<span class="roll__text">${html}</span>` +
      `<span class="roll__text" aria-hidden="true">${html}</span>` +
      `</span></span>`;
    link.classList.add('has-roll');

    // ホバーで1回だけ入れかえる。終わったら動きを付けずに元の段へ戻す(上へ戻る動きは見せない)
    const inner = link.querySelector('.roll__inner');
    link.addEventListener('mouseenter', () => link.classList.add('is-rolling'));
    inner.addEventListener('transitionend', () => {
      if (!link.classList.contains('is-rolling')) return;
      inner.classList.add('is-reset');
      link.classList.remove('is-rolling');
      void inner.offsetWidth; // 戻した位置をいったん確定させる
      inner.classList.remove('is-reset');
    });
  });
}
/* ==========================================================
   【追加】News:記事が1件ずつ開いて、読んだ記事は細い帯にたたまれて積み重なる
   - 一覧は画面に固定され、スクロールすると次の記事が開き、前の記事は細い帯にたたまれる
   - 上にスクロールすると逆の動きになる
   ========================================================== */
const newsStack = document.getElementById('newsStack');
if (newsStack) {
  const sticky = newsStack.querySelector('.news-stack__sticky');
  const items = Array.from(newsStack.querySelectorAll('.news-item'));
  const n = items.length;
  const MIN_H = 34;          // たたんだときの画像の高さ(px)
  const RATIO = 1.15;        // 開いたときの画像の縦横比(高さ ÷ 幅)
  let full = 320;            // 開いたときの画像の高さ(px)
  let step = 400;            // 1件分の切り替えに使うスクロール量(px)
  let current = 0;           // 今どの記事が開いているか(なめらかに追いかける)
  let running = false;
  let stickyTop = 72;        // 一覧が固定される位置(CSSの top。measure で読む)
  const lastH = [];          // 最後に書きこんだ高さ(変わらない時は書きこまない)
  const isMobile = window.matchMedia('(max-width: 768px)'); // スマホは画像が上・テキストが下の縦並び(style.css と同じ幅)
  const MIN_IMAGE_H = 120;   // スマホで開いたときの画像の最小の高さ。これも取れない低い画面では動きをやめる
  let isStatic = false;      // 動きをやめて全部開いて並べているか
  let metaH = 0;             // スマホでたたんだときの文章の高さ(日付とカテゴリーの行)
  let bodyHs = [];           // スマホで開いたときの、記事ごとの文章の高さ
  let measuredW = 0;         // 最後に測ったときの画面の幅

  const clamp = (v, a, b) => Math.min(Math.max(v, a), b);
  const smooth = (t) => t * t * (3 - 2 * t);

  // 画面の大きさに合わせて、画像の高さとスクロール量を決める
  const measure = () => {
    measuredW = window.innerWidth;
    lastH.length = 0;
    stickyTop = parseFloat(getComputedStyle(sticky).top);
    step = window.innerHeight * 0.6;
    if (isMobile.matches) {
      const style = getComputedStyle(items[0]);
      const padY = parseFloat(style.paddingTop) + parseFloat(style.paddingBottom);
      const gap = parseFloat(style.rowGap);
      const w = items[0].querySelector('.news-item__thumb').offsetWidth;
      metaH = items[0].querySelector('.news-item__meta').offsetHeight;
      bodyHs = items.map((item) => item.querySelector('.news-item__body').scrollHeight);
      const openExtra = padY + gap + Math.max(...bodyHs);               // 開いた記事の、画像以外の高さ
      const closed = padY + gap + MIN_H + metaH;                        // たたんだ記事の高さ
      const others = (n - 1) * closed + n + 1;                          // ほかの記事と線の高さ
      const space = window.innerHeight - stickyTop - others - openExtra; // 画像に使える高さ
      isStatic = space < MIN_IMAGE_H;
      newsStack.classList.toggle('is-static', isStatic);
      if (isStatic) {
        newsStack.style.height = '';
        newsStack.style.removeProperty('--full');
        items.forEach((item) => {
          item.style.removeProperty('--h');
          item.style.removeProperty('--b');
        });
        return;
      }
      full = Math.min(Math.round(w * 2 / 3), Math.floor(space));
      newsStack.style.setProperty('--full', `${full}px`);
      newsStack.style.height = `${full + openExtra + others + (n - 1) * step}px`;
      return;
    }
    isStatic = false;
    newsStack.classList.remove('is-static');
    items.forEach((item) => item.style.removeProperty('--b'));
    const w = items[0].querySelector('.news-item__thumb').offsetWidth;
    // 画像の縦横比で決めた高さと、文章がすべて入る高さの大きいほう(スマホで文章が切れないように)
    const textH = Math.max(...items.map((item) => item.querySelector('.news-item__body').scrollHeight));
    full = Math.max(Math.round(w * RATIO), Math.ceil(textH));
    newsStack.style.setProperty('--full', `${full}px`);
    // 固定した一覧の高さ + 切り替えに使うスクロール量 = 全体の高さ
    const pad = parseFloat(getComputedStyle(items[0]).paddingTop);
    const stickyH = full + pad + (n - 1) * (MIN_H + pad) + n;
    newsStack.style.height = `${stickyH + (n - 1) * step}px`;
  };

  const update = () => {
    if (isStatic) {
      if (running) requestAnimationFrame(update); // 画面の向きを変えたときに動きを再開できるよう、ループは止めない
      return;
    }
    const rect = newsStack.getBoundingClientRect();
    const target = clamp((stickyTop - rect.top) / step, 0, n - 1);
    current += (target - current) * (reduceMotion ? 1 : 0.14);
    if (Math.abs(target - current) < 0.0005) current = target;

    items.forEach((item, i) => {
      const open = smooth(clamp(1 - Math.abs(current - i), 0, 1));
      const h = `${(MIN_H + (full - MIN_H) * open).toFixed(1)}px`;
      if (h === lastH[i]) return; // 変わっていなければレイアウトをやり直させない
      item.style.setProperty('--h', h);
      if (isMobile.matches) item.style.setProperty('--b', `${(metaH + (bodyHs[i] - metaH) * open).toFixed(1)}px`);
      lastH[i] = h;
    });

    if (running) requestAnimationFrame(update);
  };

  measure();
  update();
  window.addEventListener('resize', () => {
    // スマホはスクロールでアドレスバーが出入りして高さだけ変わる。そのたびに測り直すと表示が跳ねるので、幅が変わったときだけ測る
    if (isMobile.matches && window.innerWidth === measuredW) return;
    measure();
    update();
  });
  window.addEventListener('load', measure);
  readyTasks.push(document.fonts.ready.then(() => { measure(); update(); })); // 文字の大きさが決まってから測り直す

  // 画面に見えている間だけ動かす
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting && !running) {
      running = true;
      requestAnimationFrame(update);
    } else if (!entry.isIntersecting) {
      running = false;
    }
  }).observe(newsStack);
}
}


// About / Works / Service のヘッダー:トップと同じく、ホバーで文字が下から一段せり上がる。
// (メニューの文字は「開いたときに下からせり上がる」動きなので対象外)
function initHeaderRoll() {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reduced.matches) return;
  document.querySelectorAll('.header__nav a, .header__contact, .w-nav a, .w-nav__contact').forEach(link => {
    const html = link.innerHTML.trim();
    link.innerHTML =
      `<span class="roll"><span class="roll__inner">` +
      `<span class="roll__text">${html}</span>` +
      `<span class="roll__text" aria-hidden="true">${html}</span>` +
      `</span></span>`;
    link.classList.add('has-roll');
    const inner = link.querySelector('.roll__inner');
    const start = () => {
      if (!reduced.matches) link.classList.add('is-rolling');
    };
    link.addEventListener('mouseenter', start);
    link.addEventListener('focus', start);
    inner.addEventListener('transitionend', event => {
      if (event.propertyName !== 'transform' || !link.classList.contains('is-rolling')) return;
      inner.classList.add('is-reset');
      link.classList.remove('is-rolling');
      void inner.offsetWidth;
      inner.classList.remove('is-reset');
    });
    reduced.addEventListener('change', () => {
      if (reduced.matches) link.classList.remove('is-rolling');
    });
  });
}

/** About: reference-matched scroll scenes, isolated from the home page. */
function initAboutPage() {
  const body = document.body;
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  const mobile = window.matchMedia('(max-width: 640px)');
  const button = document.getElementById('menuBtn');
  const menu = document.getElementById('aboutMenu');
  const header = document.querySelector('.header');
  const title = document.getElementById('about-title');
  const main = document.getElementById('about-content');
  const footer = document.querySelector('.footer');

  // トップページと同じ、文字が下から一段せり上がるヘッダーの演出。
  initHeaderRoll();


  let lastFocus = null;
  const setMenu = (open, restoreFocus = false) => {
    if (open) lastFocus = document.activeElement;
    body.classList.toggle('is-menu-open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
    menu.inert = !open;
    main.inert = open;
    footer.inert = open;
    if (open) menu.querySelector('a').focus();
    else if (restoreFocus && lastFocus) lastFocus.focus();
  };
  button.addEventListener('click', () => setMenu(!body.classList.contains('is-menu-open')));
  menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', event => {
    if (!body.classList.contains('is-menu-open')) return;
    if (event.key === 'Escape') { event.preventDefault(); setMenu(false, true); }
    if (event.key === 'Tab') {
      const links = [...header.querySelectorAll('a, button'), ...menu.querySelectorAll('a')]
        .filter(el => el.getClientRects().length);
      const first = links[0], last = links[links.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
  });
  // メニューはトップ・News・Contact と同じく 768px 以下で使うので、それより広くなったら閉じる。
  window.matchMedia('(max-width: 768px)').addEventListener('change', () => setMenu(false));

  document.getElementById('year').textContent = new Date().getFullYear();
  document.getElementById('toTop').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: reduced.matches ? 'instant' : 'smooth' });
    title.setAttribute('tabindex', '-1');
    title.focus({ preventScroll: true });
  });
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', event => {
      const target = document.getElementById(link.hash.slice(1));
      if (!target) return;
      event.preventDefault();
      target.scrollIntoView({ behavior: reduced.matches ? 'instant' : 'smooth' });
      target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
      history.replaceState(null, '', link.hash);
    });
  });

  if (!reduced.matches && 'IntersectionObserver' in window) {
    const reveal = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        reveal.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -5% 0px', threshold: .06 });
    body.classList.add('a-motion');
    document.querySelectorAll('.a-reveal').forEach(el => reveal.observe(el));
    // プロセス図の円を、矢印が先端について進むように描いていく。
    const loopGroup = document.querySelector('.a-process__loop');
    if (loopGroup) {
      const loopSvg = loopGroup.ownerSVGElement;
      const loopPaths = [...loopGroup.querySelectorAll('path')].map(path => {
        const length = path.getTotalLength();
        const marker = path.getAttribute('marker-end');
        path.style.strokeDasharray = length;
        path.style.strokeDashoffset = length;
        let arrow = null;
        if (marker) {
          path.removeAttribute('marker-end');
          arrow = document.createElementNS('http://www.w3.org/2000/svg', 'path');
          arrow.setAttribute('d', 'M-7.5 -5L2.5 0L-7.5 5Z');
          arrow.setAttribute('fill', getComputedStyle(path).stroke);
          arrow.style.opacity = '0';
          loopSvg.appendChild(arrow);
        }
        return { path, length, marker, arrow, delay: Number(path.dataset.delay || 0) * 1000 };
      });
      const drawLoop = () => {
        const duration = 1400;
        const start = performance.now();
        const ease = t => 1 - Math.pow(1 - t, 3);
        const tick = now => {
          let running = false;
          loopPaths.forEach(item => {
            const t = Math.min(Math.max((now - start - item.delay) / duration, 0), 1);
            if (t < 1) running = true;
            const drawn = item.length * ease(t);
            item.path.style.strokeDashoffset = item.length - drawn;
            if (!item.arrow) return;
            if (t === 0) return;
            if (t === 1) {
              item.arrow.remove();
              item.path.setAttribute('marker-end', item.marker);
              return;
            }
            const tip = item.path.getPointAtLength(drawn);
            const back = item.path.getPointAtLength(Math.max(drawn - 1, 0));
            const angle = Math.atan2(tip.y - back.y, tip.x - back.x) * 180 / Math.PI;
            item.arrow.style.opacity = '1';
            item.arrow.setAttribute('transform', `translate(${tip.x} ${tip.y}) rotate(${angle})`);
          });
          if (running) requestAnimationFrame(tick);
          else loopPaths.forEach(item => { item.path.style.strokeDasharray = ''; item.path.style.strokeDashoffset = ''; });
        };
        requestAnimationFrame(tick);
      };
      const loopObserver = new IntersectionObserver(entries => {
        if (!entries[0].isIntersecting) return;
        loopObserver.disconnect();
        drawLoop();
      }, { threshold: .35 });
      loopObserver.observe(loopSvg);
    }
    // 渦の図が画面に入ったら、背景を中央から左右へ広げる。
    const vortexSvg = document.querySelector('.a-vortex__svg');
    if (vortexSvg) {
      const vortexBg = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          vortexSvg.closest('.a-vortex__diagram').classList.add('is-bg-in');
          vortexBg.disconnect();
        });
      }, { rootMargin: '0px 0px -15% 0px', threshold: 0 });
      vortexBg.observe(vortexSvg);
    }
    reduced.addEventListener('change', () => {
      if (reduced.matches) {
        body.classList.remove('a-motion');
        reveal.disconnect();
      }
    });
  }

  const geometry = document.querySelector('.a-geometry');
  const artwork = document.querySelector('.a-geometry__art');
  const geometryTitle = document.querySelector('.a-geometry__title');
  const statementHeading = document.querySelector('.a-statement h2');
  const geometryTop = artwork.querySelector('.a-geometry__top');
  if (geometryTop && !reduced.matches && 'IntersectionObserver' in window) {
    const wipeObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        geometry.classList.toggle('is-top-wiping', entry.isIntersecting);
      });
    }, { threshold: 0 });
    wipeObserver.observe(geometryTop);
    reduced.addEventListener('change', () => {
      if (reduced.matches) {
        wipeObserver.disconnect();
        geometry.classList.remove('is-top-wiping');
      }
    });
  }
  const story = document.querySelector('.a-photo-story');
  const storyBack = document.querySelector('.a-photo-story__back');
  const storyLogo = document.querySelector('.a-photo-story__logo');
  const vortex = document.querySelector('.a-vortex__diagram');
  const coil = document.querySelector('.a-vortex__coil');

  // 色の帯ごとに監視し、下側の色も実際に画面へ入ってから明るくする。
  if (!reduced.matches && 'IntersectionObserver' in window) {
    geometry.classList.add('is-color-ready');
    const colorObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const band = entry.target.dataset.colorBand;
        geometry.classList.toggle(band ? 'is-' + band + '-visible' : 'is-color-visible', entry.isIntersecting);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
    // 初期配色を先に描画し、その後に表示判定を開始する。
    requestAnimationFrame(() => requestAnimationFrame(() => {
      if (!reduced.matches) {
        colorObserver.observe(document.querySelector('.a-geometry__sticky'));
        artwork.querySelectorAll('[data-color-band]').forEach(band => colorObserver.observe(band));
      }
    }));
    reduced.addEventListener('change', () => {
      if (reduced.matches) {
        colorObserver.disconnect();
        geometry.classList.remove('is-color-ready', 'is-color-visible', 'is-lime-visible', 'is-stone-visible', 'is-warm-visible');
      }
    });
  }

  // A conical spiral, rendered as a single vector path rather than a bitmap.
  const spiral = [];
  const turns = 5.25;
  const points = 560;
  for (let i = 0; i <= points; i++) {
    const p = i / points;
    const angle = p * Math.PI * 2 * turns;
    const radius = 158 * (1 - p) + 14;
    const x = 500 + Math.cos(angle) * radius;
    const y = 280 + p * 160 + Math.sin(angle) * radius * .28;
    spiral.push((i ? 'L' : 'M') + x.toFixed(2) + ' ' + y.toFixed(2));
  }
  coil.setAttribute('d', spiral.join(' '));
  coil.style.transformOrigin = '500px 360px';

  // Next の文字が画面に入ったら画面を黒に、フッターの半分まで来たら元の白に戻す。
  const nextMarquee = document.querySelector('.a-next__marquee');
  const NEXT_DARK = { bg: '#0d0e0c', fg: '#f2f1eb' };
  const NEXT_LIGHT = { bg: '#f2f1eb', fg: '#1a1c19' };
  let nextText = null;
  // Next. の文字の画像を、画面の白黒に合わせた色で描き直す。
  const syncNextColors = () => {
    if (!nextText) return;
    const c = body.classList.contains('is-next-dark') ? NEXT_DARK : NEXT_LIGHT;
    nextText.setColors(c.bg, c.fg);
  };
  if (nextMarquee && footer) {
    let darkFrame = 0;
    const updateDark = () => {
      darkFrame = 0;
      const vh = window.innerHeight;
      const marqueeTop = nextMarquee.getBoundingClientRect().top;
      const footerRect = footer.getBoundingClientRect();
      const footerHalf = footerRect.top + footerRect.height / 2;
      // 画面に少しでも入った時点で黒にし、フッターの半分まで来たら白(文字は黒)に戻す。
      body.classList.toggle('is-next-dark', marqueeTop < vh && footerHalf > vh);
      syncNextColors();
    };
    const queueDark = () => { if (!darkFrame) darkFrame = requestAnimationFrame(updateDark); };
    window.addEventListener('scroll', queueDark, { passive: true });
    window.addEventListener('resize', queueDark);
    updateDark();
  }

  // Next. をトップページの About. と同じ「回転する筒」の動きで表示する。
  if (nextMarquee && window.THREE) {
    Promise.all([
      document.fonts.load('600 100px "Bodoni Moda"'),
      document.fonts.load('italic 600 100px "Bodoni Moda"'),
    ]).catch(() => {}).then(() => {
      nextText = new DistortText(nextMarquee);
      syncNextColors();
      window.addEventListener('resize', () => nextText.resize());
      if (reduced.matches) return;
      let lastTime = performance.now();
      let lastY = window.scrollY;
      const tick = now => {
        const dt = Math.min(now - lastTime, 50);
        lastTime = now;
        const velocity = window.scrollY - lastY;
        lastY = window.scrollY;
        nextText.update(now / 1000, dt, velocity);
        requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }

  const clamp = value => Math.max(0, Math.min(1, value));
  const smooth = value => { const t = clamp(value); return t * t * (3 - 2 * t); };

  // タイトルを1文字ずつに分け、順番に跳ね上がらせる。読み上げは元の文のまま。
  geometryTitle.setAttribute('aria-label', geometryTitle.textContent.replace(/\s+/g, ' ').trim());
  let charIndex = 0;
  const splitChars = node => {
    [...node.childNodes].forEach(child => {
      if (child.nodeType !== Node.TEXT_NODE) { splitChars(child); return; }
      const fragment = document.createDocumentFragment();
      [...child.textContent].forEach(char => {
        if (char === ' ') { fragment.append(' '); return; }
        const span = document.createElement('span');
        span.className = 'a-char';
        span.setAttribute('aria-hidden', 'true');
        span.style.setProperty('--ci', charIndex++);
        span.textContent = char;
        fragment.append(span);
      });
      child.replaceWith(fragment);
    });
  };
  splitChars(geometryTitle);

  // 各図形は回転・縮小して散らばった状態から、画面に入るにつれて定位置へ組み上がる。
  const shapes = [...artwork.querySelectorAll('[data-color-band] > path, [data-color-band] > circle')].map((el, i) => {
    const box = el.getBBox();
    el.style.transformBox = 'fill-box';
    el.style.transformOrigin = 'center';
    const dir = box.x + box.width / 2 < 600 ? -1 : 1;
    return {
      el,
      cy: box.y + box.height / 2,
      dx: dir * (180 + (i * 53) % 160),
      dy: 240 + (i * 71) % 200,
      rot: (i % 2 ? 1 : -1) * (80 + (i * 37) % 120),
      s0: .08 + (i % 3) * .1,
      done: false
    };
  });
  const ring = document.querySelector('.a-geometry__ring');
  const ringInner = ring && ring.querySelector('.a-geometry__ring-inner');
  const lines = artwork.querySelector('.a-geometry__lines');
  let metrics;
  const measure = () => {
    const y = window.scrollY;
    const geometryDocTop = geometry.getBoundingClientRect().top + y;
    // タイトルは画面に固定したまま進み、「創造力を呼び覚ます。」の上で止まる。
    // 見出しと本文の間隔(h2 の margin-bottom)と同じだけ空ける。
    const titleGap = parseFloat(getComputedStyle(statementHeading).marginBottom) || 0;
    const titleStop = statementHeading.getBoundingClientRect().top + y - titleGap - geometryTitle.offsetHeight;
    metrics = {
      geometryTop: geometryDocTop,
      titleTravel: Math.max(0, titleStop - geometryDocTop - geometryTitle.offsetTop),
      geometryTravel: Math.max(1, geometry.offsetHeight - window.innerHeight),
      artTravel: Math.max(0, artwork.getBoundingClientRect().height - window.innerHeight),
      artHeight: parseFloat(getComputedStyle(artwork).height) || 0,
      storyTop: story.getBoundingClientRect().top + y,
      storyTravel: Math.max(1, story.offsetHeight - window.innerHeight),
      vortexTop: vortex.getBoundingClientRect().top + y,
      vortexHeight: vortex.offsetHeight,
      viewport: window.innerHeight
    };
  };
  let frame = 0;
  let needsMeasure = true;
  const render = () => {
    frame = 0;
    if (needsMeasure) { measure(); needsMeasure = false; }
    if (reduced.matches) {
      shapes.forEach(s => { s.el.style.transform = ''; s.done = true; });
      return;
    }
    const y = window.scrollY, m = metrics;
    const g = clamp((y - m.geometryTop) / m.geometryTravel);
    const p = clamp((y - m.storyTop) / m.storyTravel);
    // The tall geometric mural passes through a fixed viewport; its frame gently bends.
    const bend = Math.sin(g * Math.PI * 2) * (mobile.matches ? 1 : 1.9);
    artwork.style.transform = 'translate3d(0,' + (-g * m.artTravel).toFixed(2) + 'px,0) skewY(' + bend.toFixed(3) + 'deg)';
    // 図形ごとの画面上の位置から、組み上がりの進み具合を決める。
    const stickyOffset = Math.max(0, m.geometryTop - y);
    shapes.forEach(s => {
      const screenY = stickyOffset + s.cy / 1500 * m.artHeight - g * m.artTravel;
      const a = smooth((m.viewport * 1.02 - screenY) / (m.viewport * .55));
      if (a >= 1) {
        if (!s.done) { s.el.style.transform = ''; s.done = true; }
        return;
      }
      s.done = false;
      const k = 1 - a;
      s.el.style.transform = 'translate(' + (s.dx * k).toFixed(1) + 'px,' + (s.dy * k).toFixed(1) + 'px) rotate(' + (s.rot * k).toFixed(1) + 'deg) scale(' + (s.s0 + (1 - s.s0) * a).toFixed(3) + ')';
    });
    if (lines) lines.style.setProperty('--line-draw', (1 - smooth(g * 1.5)).toFixed(3));
    if (ring) {
      ring.style.opacity = (smooth(g / .08) * (1 - smooth((g - .72) / .22))).toFixed(3);
      ring.style.transform = 'rotate(' + (g * 320).toFixed(2) + 'deg) scale(' + (.5 + smooth(g / .6) * .85).toFixed(3) + ')';
      ringInner.style.transform = 'rotate(' + (-g * 760).toFixed(2) + 'deg)';
    }
    const titleY = Math.max(0, Math.min(m.titleTravel, y - m.geometryTop));
    geometryTitle.style.transform = 'translate3d(0,' + titleY.toFixed(2) + 'px,0) scale(' + (1 + g * .08).toFixed(3) + ')';
    // Start small, accelerate through the middle, then settle full bleed.
    const expand = smooth((p - .06) / .66);
    const verticalInset = (mobile.matches ? 31 : 27) * (1 - expand);
    const horizontalInset = (mobile.matches ? 20 : 34) * (1 - expand);
    story.style.setProperty('--photo-inset', verticalInset.toFixed(3) + '% ' + horizontalInset.toFixed(3) + '%');
    story.style.setProperty('--photo-zoom', (1 + smooth((p - .65) / .35) * .08).toFixed(3));
    storyBack.style.transform = 'scale(' + (1.1 - p * .1).toFixed(3) + ')';
    // The mark travels toward the left as the foreground photograph opens.
    storyLogo.style.transform = 'translate3d(' + (-expand * window.innerWidth * (mobile.matches ? .025 : .115)).toFixed(2) + 'px,' + (-expand * 24).toFixed(2) + 'px,0)';
    const v = clamp((y + m.viewport - m.vortexTop) / (m.viewport + m.vortexHeight));
    coil.style.transform = 'translateY(' + ((.5 - v) * 12).toFixed(2) + 'px) scaleY(' + (.92 + v * .12).toFixed(3) + ')';
  };
  const schedule = (remeasure = false) => {
    if (remeasure) needsMeasure = true;
    if (!frame) frame = requestAnimationFrame(render);
  };
  window.addEventListener('scroll', () => schedule(), { passive: true });
  window.addEventListener('resize', () => schedule(true));
  window.addEventListener('load', () => schedule(true), { once: true });
  reduced.addEventListener('change', () => schedule(true));
  document.fonts.ready.then(() => schedule(true));
  // ResizeObserver handles late image/font layout changes without per-frame layout reads.
  if ('ResizeObserver' in window) {
    const resize = new ResizeObserver(() => schedule(true));
    [geometry, story, vortex, document.querySelector('.a-intro')].forEach(el => resize.observe(el));
  }
  schedule(true);
}

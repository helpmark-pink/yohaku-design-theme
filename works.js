'use strict';

// 素材の差し替え：画像は images/works/ の同名ファイルを置き換えてください。
// gallery の順番が、スクロール中に表示される写真の順番です。
const WORKS_CONTENT = [
  { gallery: ['01-01.jpg', '01-02.jpg', '01-03.jpg', '01-04.jpg'], alt: ['夕陽を受ける球体のアートオブジェ', '壁面に作品が並ぶギャラリー', 'アート作品と向き合う二人', '光と影の広がる展示空間'] },
  { gallery: ['02-01.jpg', '1.jpg', '2.jpg', '3.jpg'], alt: ['上階へ続くエスカレーターと光', '窓辺の光とニットに置かれた白いかぼちゃ', '空へ伸びるガラス張りのビル群', '青空の下に建つレンガ造りの建物'] },
  { gallery: ['03-01.jpg', '03-02.jpg', '03-03.jpg', '03-04.jpg'], alt: ['窓辺の緑とラウンジのソファ', '自然光に包まれた椅子', 'ラウンジで過ごす穏やかな時間', '空間に広がる光と影'] },
];

// 画像の場所は works.js と同じフォルダが基準(WordPress の /works/ からでも works.html からでも同じ画像を読む)。
const WORKS_BASE = document.currentScript ? new URL('.', document.currentScript.src).href : '';

function initWorksPage() {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const body = document.body;
  const button = document.querySelector('.w-menu-button');
  const menu = document.querySelector('.w-menu');
  const main = document.querySelector('main');
  const footer = document.querySelector('.footer');
  const asset = name => `${WORKS_BASE}images/works/${name}`;
  const clamp = (v, min = 0, max = 1) => Math.max(min, Math.min(max, v));
  const smooth = v => { v = clamp(v); return v * v * (3 - 2 * v); };


  function setMenu(open, restoreFocus = true) {
    body.classList.toggle('w-menu-open', open);
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
    menu.inert = !open;
    main.inert = open;
    footer.inert = open;
    if (open) menu.querySelector('a').focus();
    else if (restoreFocus) button.focus();
  }
  button.addEventListener('click', () => setMenu(button.getAttribute('aria-expanded') !== 'true'));
  menu.addEventListener('click', e => { if (e.target.closest('a')) setMenu(false, false); });
  window.addEventListener('keydown', e => {
    if (!body.classList.contains('w-menu-open')) return;
    if (e.key === 'Escape') setMenu(false);
    if (e.key !== 'Tab') return;
    const links = [...menu.querySelectorAll('a')];
    const first = button;
    const last = links[links.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });
  matchMedia('(min-width: 769px)').addEventListener('change', e => { if (e.matches && body.classList.contains('w-menu-open')) setMenu(false, false); });
  document.getElementById('year').textContent = new Date().getFullYear();
  document.getElementById('toTop').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: reduced.matches ? 'instant' : 'smooth' });
    document.getElementById('works-title').setAttribute('tabindex', '-1');
    document.getElementById('works-title').focus({ preventScroll: true });
  });

  // Three original wire forms: radial rays, a twisted grid, and nested orbits.
  document.querySelectorAll('[data-symbol]').forEach(el => {
    const kind = Number(el.dataset.symbol);
    let paths = '';
    if (kind === 0) {
      for (let i = 0; i < 40; i++) {
        const a = i * Math.PI / 20;
        const x = Math.cos(a), y = Math.sin(a);
        paths += `<path d="M${100 + 19*x} ${100 + 19*y}L${100 + 92*x} ${100 + 92*y}m${-8*x + 4*y} ${-8*y - 4*x}l${8*x - 4*y} ${8*y + 4*x}l${-8*x - 4*y} ${-8*y + 4*x}"/>`;
      }
    } else if (kind === 1) {
      for (let i = 0; i < 13; i++) {
        const y = 15 + i * 14;
        paths += `<path d="M20 ${y}C70 ${y-26} 130 ${y+26} 180 ${y}"/>`;
      }
      for (let i = 0; i < 10; i++) paths += `<path d="M${30+i*15} 15Q${160-i*12} 100 ${30+i*15} 185" opacity=".55"/>`;
    } else {
      paths += '<circle cx="100" cy="100" r="90"/>';
      for (let i = 0; i < 7; i++) paths += `<ellipse cx="100" cy="100" rx="${18+i*12}" ry="90" transform="rotate(${i*15} 100 100)"/>`;
    }
    el.innerHTML = `<svg viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width=".8">${paths}</svg>`;
  });

  const ribbons = document.querySelector('.w-ribbons');
  const ribbonGroups = [...document.querySelectorAll('.w-ribbon')].map((el, row) => {
    const sources = row ? ['03-01.jpg', '01-01.jpg', '02-01.jpg'] : ['02-01.jpg', '03-01.jpg', '01-01.jpg'];
    const track = el.querySelector('.w-ribbon__track');
    const alt = track.querySelector('img').alt;
    track.replaceChildren();
    const panels = Array.from({length:5}, (_, index) => {
      const panel = document.createElement('div');
      panel.className = 'w-ribbon__panel';
      const base = new Image(); base.src = asset(sources[0]); base.alt = index === 0 ? alt : ''; base.width = 1400; base.height = 1000;
      const next = new Image(); next.className = 'w-ribbon__next'; next.alt = ''; next.src = asset(sources[1]);
      if (index) panel.setAttribute('aria-hidden', 'true');
      panel.append(base, next); track.append(panel); return {base, next};
    });
    return {el, panels, sources, index:0};
  });
  let ribbonsVisible = true;
  new IntersectionObserver(([entry]) => {
    ribbonsVisible = entry.isIntersecting;
    ribbons.classList.toggle('is-offscreen', !ribbonsVisible);
  }).observe(ribbons);
  let ribbonTimer;
  let ribbonCommit;
  function changeRibbons() {
    if (!reduced.matches && ribbonsVisible && !document.hidden) {
      ribbonGroups.forEach(group => {
        const nextIndex = (group.index + 1) % group.sources.length;
        group.panels.forEach(panel => { panel.next.src = asset(group.sources[nextIndex]); });
        group.el.classList.add('is-changing');
        group.index = nextIndex;
      });
      ribbonCommit = setTimeout(() => {
        ribbonGroups.forEach(group => {
          group.panels.forEach(panel => { panel.base.src = asset(group.sources[group.index]); });
          group.el.classList.remove('is-changing');
        });
      }, 1600);
    }
    ribbonTimer = setTimeout(changeRibbons, 6500);
  }
  ribbonTimer = setTimeout(changeRibbons, 6500);

  const entries = [...document.querySelectorAll('.w-entry')].map(el => ({
    el, frame:el.querySelector('.w-entry__frame'), echo:el.querySelector('.w-entry__echo'), symbol:el.querySelector('.w-entry__symbol'),
    labels:el.querySelectorAll('.w-location, .w-project-title'),
  }));
  // 固定表示が終わってスクロールで流れ出す直前に、場所と作品名を消す(上のヘッダー・メニューボタンと重ならないように)。
  const fadeLabels = (labels, p) => { const o = String(1 - smooth((p - .9) / .1)); labels.forEach(label => { label.style.opacity = o; }); };
  const galleries = [...document.querySelectorAll('[data-gallery]')].map(el => {
    const content = WORKS_CONTENT[Number(el.dataset.gallery)];
    el.style.setProperty('--gallery-length', `${Math.max(2.5, content.gallery.length * 1.1) * 100}svh`);
    const container = el.querySelector('.w-gallery__images');
    const shots = content.gallery.map((name, i) => {
      const shot = document.createElement('figure'); shot.className = 'w-shot'; shot.setAttribute('role', 'img'); shot.setAttribute('aria-label', content.alt[i]);
      const fallback = new Image(); fallback.src = asset(name); fallback.alt = content.alt[i]; fallback.loading = 'lazy';
      shot.append(fallback);
      const bands = Array.from({length:4}, (_, j) => {
        const band = document.createElement('div'); band.className = 'w-shot__band'; band.style.setProperty('--band', j); band.setAttribute('aria-hidden', 'true');
        const image = new Image(); image.src = asset(name); image.alt = ''; image.loading = 'lazy'; band.append(image); shot.append(band); return band;
      });
      container.append(shot); return {shot, bands};
    });
    el.querySelector('.w-gallery__fallback').remove();
    return {el, shots, progress:el.querySelector('.w-gallery__progress span'), labels:el.querySelectorAll('.w-location, .w-project-title')};
  });

  let nextText;
  const nextMarquee = document.querySelector('.w-next__marquee');
  if (typeof THREE !== 'undefined' && !reduced.matches) {
    Promise.all([document.fonts.load('600 100px "Bodoni Moda"'), document.fonts.load('italic 600 100px "Bodoni Moda"')]).catch(() => {}).then(() => {
      try { nextText = new DistortText(nextMarquee); if (body.classList.contains('w-next-dark')) nextText.setColors('#0c0d0b', '#f2f1eb'); requestRender(); } catch (error) { console.info('Next heading uses its text fallback.', error); }
    });
  }

  const revealObserver = new IntersectionObserver(changes => {
    changes.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); revealObserver.unobserve(entry.target); } });
  }, {threshold:.08});
  document.querySelectorAll('.w-reveal, .w-creator').forEach(el => revealObserver.observe(el));

  let viewportHeight = innerHeight;
  let lastY = scrollY;
  let lastTime = performance.now();
  let animationFrame = 0;
  let active = new Set();
  const visibilityObserver = new IntersectionObserver(changes => changes.forEach(entry => {
    if (entry.isIntersecting) active.add(entry.target); else active.delete(entry.target);
    requestRender();
  }), {rootMargin:'100px'});
  [...entries, ...galleries].forEach(item => visibilityObserver.observe(item.el));

  function render(now) {
    animationFrame = 0;
    const y = scrollY;
    const velocity = y - lastY;
    const dt = Math.min(now - lastTime, 50);
    if (!reduced.matches) {
      // Read layout together before applying scene transforms.
      const entryPositions = entries.filter(item => active.has(item.el)).map(item => ({item, rect:item.el.getBoundingClientRect()}));
      const galleryPositions = galleries.filter(item => active.has(item.el)).map(item => ({item, rect:item.el.getBoundingClientRect()}));
      entryPositions.forEach(({item, rect}) => {
        const p = clamp(-rect.top / Math.max(1, rect.height - viewportHeight));
        const grow = smooth((p - .12) / .75);
        const start = innerWidth <= 640 ? .47 : .23;
        const scale = start + (1 - start) * grow;
        item.frame.style.transform = `scale(${scale.toFixed(5)}) rotate(${(-4 * (1-grow)).toFixed(3)}deg)`;
        item.echo.style.transform = `translateY(${(viewportHeight * .31 * (1-grow)).toFixed(2)}px) scale(${(scale * .94).toFixed(5)}) rotate(${(3 * (1-grow)).toFixed(3)}deg)`;
        item.echo.style.opacity = String(1 - smooth(p / .65));
        item.symbol.style.transform = `translateY(${(-45 * grow).toFixed(1)}px) rotate(${(y * .035).toFixed(2)}deg) scale(${(1 + grow * .25).toFixed(3)})`;
        item.symbol.style.opacity = String(1 - smooth((p - .2) / .45));
        fadeLabels(item.labels, p);
      });
      galleryPositions.forEach(({item, rect}) => {
        const p = clamp(-rect.top / Math.max(1, rect.height - viewportHeight));
        // Keep each photograph still, then reveal the next in four staggered horizontal shutters.
        const travel = p * item.shots.length;
        item.shots.forEach(({shot, bands}, i) => {
          const local = i === 0 ? 1 : travel - i + .15;
          shot.style.visibility = local <= 0 ? 'hidden' : 'visible';
          bands.forEach((band, j) => {
            const t = i === 0 ? 1 : smooth((local - j * .055) / .36);
            band.style.clipPath = `inset(0 0 ${((1-t)*100).toFixed(3)}%)`;
          });
        });
        if (item.progress) item.progress.style.transform = `scaleX(${p.toFixed(4)})`;
        fadeLabels(item.labels, p);
      });
      const statementSymbol = document.querySelector('.w-symbol--statement');
      statementSymbol.style.transform = `rotate(${(y * .025).toFixed(2)}deg)`;
    }
    // Next の文字が画面に入ったら一瞬で黒い画面に、フッターが画面に入ったら元に戻す。
    const dark = nextMarquee.getBoundingClientRect().top < viewportHeight && footer.getBoundingClientRect().top > viewportHeight;
    if (dark !== body.classList.contains('w-next-dark')) {
      body.classList.toggle('w-next-dark', dark);
      if (dark) nextText?.setColors('#0c0d0b', '#f2f1eb'); else nextText?.setColors('#f2f1eb', '#191a17');
    }
    if (nextText && !document.hidden && !reduced.matches) nextText.update(now / 1000, dt, velocity);
    lastY = y; lastTime = now;
    if (nextText?.visible && !document.hidden && !reduced.matches) requestRender();
  }
  function requestRender() { if (!animationFrame) animationFrame = requestAnimationFrame(render); }
  function resize() { viewportHeight = innerHeight; nextText?.resize(); requestRender(); }
  function syncMotion() {
    body.classList.toggle('w-motion', !reduced.matches);
    entries.forEach(item => { item.frame.style.transform = ''; item.echo.style.transform = ''; item.symbol.style.opacity = ''; item.labels.forEach(label => { label.style.opacity = ''; }); });
    galleries.forEach(item => { item.shots.forEach(({shot}) => { shot.style.visibility = ''; }); item.labels.forEach(label => { label.style.opacity = ''; }); });
    requestRender();
  }
  reduced.addEventListener('change', syncMotion);
  addEventListener('scroll', requestRender, {passive:true});
  addEventListener('resize', resize, {passive:true});
  addEventListener('load', resize, {once:true});
  document.addEventListener('visibilitychange', requestRender);
  new IntersectionObserver(requestRender).observe(nextMarquee);
  syncMotion();
  // Native hash links preserve keyboard navigation and browser history through pinned scenes.
  if (location.hash) requestAnimationFrame(() => document.getElementById(location.hash.slice(1))?.scrollIntoView());
  addEventListener('pagehide', () => { clearTimeout(ribbonTimer); clearTimeout(ribbonCommit); cancelAnimationFrame(animationFrame); });
  addEventListener('pageshow', e => {
    if (!e.persisted) return;
    animationFrame = 0;
    ribbonGroups.forEach(group => { group.panels.forEach(panel => { panel.base.src = asset(group.sources[group.index]); }); group.el.classList.remove('is-changing'); });
    ribbonTimer = setTimeout(changeRibbons, 6500);
    requestRender();
  });
}

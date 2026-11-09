<?php
/**
 * About ページ(about.html を元にしたテンプレート)
 */
get_header('about');
?>

  <main id="about-content">
    <section class="a-intro" aria-labelledby="about-title">
      <h1 id="about-title">About</h1>
      <div class="a-intro__copy">
        <h2><span>Yohaku designが考える、デザインの可能性。</span><br>人とブランドのあいだに、新しい関係をつくる。<br>余白からはじまる、次のかたち。</h2>
        <p>デザインを、単に見た目を整えるものとしてではなく、想いを伝え、可能性をひらくものとして。対話と観察を重ね、まだ言葉になっていない本質を見つめながら、人の心に届く表現と体験をつくります。</p>
      </div>
    </section>

    <section class="a-geometry" aria-label="Work with design">
      <div class="a-geometry__sticky">
        <!-- 写真素材ではなくSVGで描いた幾何学模様。スクロールで図形全体が歪みながら流れます。 -->
        <svg class="a-geometry__art" viewBox="0 0 1200 1500" preserveAspectRatio="none" aria-hidden="true">
          <defs>
            <linearGradient id="a-lime" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#c9ff35"/><stop offset="1" stop-color="#b5ef21"/></linearGradient>
            <linearGradient id="a-stone" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#bfc1b4"/><stop offset="1" stop-color="#a7ab9e"/></linearGradient>
            <clipPath id="a-art-clip"><path d="M0 0H1200V1500H0Z"/></clipPath>
            <linearGradient id="a-top-yellow" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#c9ff35"/><stop offset="1" stop-color="#b5ef21"/></linearGradient>
            <clipPath id="a-top-wipe" clipPathUnits="userSpaceOnUse"><rect class="a-geometry__wipe" width="800" height="375"/></clipPath>
          </defs>
          <g clip-path="url(#a-art-clip)">
            <path class="a-geometry__base" fill="#181917" d="M0 0H1200V1500H0Z"/>
            <g fill="url(#a-lime)" data-color-band="lime">
              <g class="a-geometry__top" fill="url(#a-top-yellow)">
                <path d="M0 0H400V175A200 200 0 0 1 0 175Z"/>
                <path d="M400 0H800V175A200 200 0 0 1 400 175Z"/>
                <g fill="#b4b6ad" clip-path="url(#a-top-wipe)">
                  <path d="M0 0H400V175A200 200 0 0 1 0 175Z"/>
                  <path d="M400 0H800V175A200 200 0 0 1 400 175Z"/>
                </g>
              </g>
              <circle cx="1000" cy="180" r="200"/>
              <path d="M0 750V575A200 200 0 0 1 400 575H200L400 750Z"/>
              <path d="M400 375H600V750H400Z"/>
              <path d="M800 375A187.5 187.5 0 0 0 800 750Z"/>
            </g>
            <g fill="url(#a-stone)" data-color-band="stone">
              <path d="M800 375H1000V562.5A187.5 187.5 0 0 1 812.5 750H800Z"/>
              <path d="M1000 375H1200V750A200 200 0 0 1 1000 550Z"/>
              <path d="M0 1125V950A200 200 0 0 1 200 750H400V1125H300V937.5H200V1125Z"/>
              <path d="M400 750H950V1125H800V937.5H700V1125H600V937.5H500V1125H400Z"/>
              <path d="M1000 750H1200V1125H1100V1025H1000V1125H900V850H1000Z"/>
            </g>
            <g class="a-geometry__warm" fill="#e94700" data-color-band="warm">
              <path d="M0 1500V1325A200 200 0 0 1 400 1325V1500Z"/>
              <path d="M400 1500V1325A200 200 0 0 1 800 1325H600L800 1500Z"/>
              <path d="M800 1125H1200V1350H1100V1500H1000V1350H900V1500H800V1350H750V1250H800Z"/>
            </g>
            <g class="a-geometry__lines" fill="none" stroke="#ffffff" stroke-width=".8" opacity=".35">
              <path d="M0 0V1500M200 0V1500M400 0V1500M600 0V1500M800 0V1500M1000 0V1500M1200 0V1500" pathLength="1"/>
              <path d="M0 375H1200M0 750H1200M0 1125H1200" pathLength="1"/>
              <ellipse cx="600" cy="750" rx="615" ry="700" pathLength="1"/><ellipse cx="600" cy="750" rx="610" ry="385" pathLength="1"/>
              <path d="M-50-100C160 200 400 450 1200 750M1250-100C1050 350 900 800-50 1125" pathLength="1"/>
            </g>
          </g>
        </svg>
        <!-- タイトルを囲む回転リング。スクロールに合わせて回転・拡大し、背景色を反転させて重なる。 -->
        <svg class="a-geometry__ring" viewBox="0 0 600 600" aria-hidden="true">
          <defs><path id="a-ring-path" d="M45 300a255 255 0 1 1 510 0a255 255 0 1 1-510 0"/></defs>
          <circle cx="300" cy="300" r="292" fill="none" stroke="currentColor" stroke-width="1" opacity=".55"/>
          <circle class="a-geometry__ring-inner" cx="300" cy="300" r="222" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="2 12"/>
          <text fill="currentColor"><textPath href="#a-ring-path" textLength="1590" lengthAdjust="spacing">THOUGHT × FEELING ✦ WORK WITH DESIGN ✦ THOUGHT × FEELING ✦ WORK WITH DESIGN ✦</textPath></text>
        </svg>
        <span class="a-geometry__index" aria-hidden="true">THOUGHT × FEELING</span>
      </div>
      <h2 class="a-geometry__title">WORK <span>with</span> DESIGN</h2>
    </section>

    <section class="a-statement">
      <div class="a-reveal"><h2>創造力を呼び覚ます。</h2><p>理性だけでは、見つけられない答えがある。<br>感性だけでは、伝えきれない想いがある。<br>思考と感性が共存し、響き合う。<br>そのあいだに、新しいデザインが生まれる。</p></div>
    </section>

    <section class="a-photo-story" aria-label="日常から、新しい視点へ">
      <div class="a-photo-story__sticky">
        <img class="a-photo-story__back" src="<?php echo esc_url(get_theme_file_uri('images/about-space.jpg')); ?>" alt="自然光が差し込む、アーチと白い椅子のある空間" width="2400" height="1600">
        <div class="a-photo-story__front"><img src="<?php echo esc_url(get_theme_file_uri('images/about-art.jpg')); ?>" alt="ギャラリーで写真作品を鑑賞する二人の後ろ姿" width="2400" height="1600" loading="lazy"></div>
        <div class="a-photo-story__tint" aria-hidden="true"></div>
        <p class="a-photo-story__logo"><svg class="a-photo-story__mark" viewBox="0 0 64 64" aria-hidden="true"><path d="M8 56V16A40 40 0 0 1 48 56Z" fill="currentColor"/><circle cx="50" cy="14" r="8" fill="#DCA62A"/></svg><span>Yohaku<span class="a-photo-story__design">design</span></span></p>
        <span class="a-photo-story__caption">Look closer. Think further.</span>
      </div>
    </section>

    <section class="a-vortex" aria-labelledby="vortex-title">
      <div class="a-vortex__heading a-reveal"><h2 id="vortex-title">CREATIVE VORTEX</h2><p>思考と感性が共存する場所。<br>その往復を重ねることで、視点をひらき、<br>新しいアイデアの渦を生み出していく。</p></div>
      <div class="a-vortex__diagram a-reveal" role="img" aria-label="思考と感性がめぐりあい、創造力へとつながる渦の図">
        <div class="a-vortex__top"><h3>CREATIVITY</h3><p>新しい価値をつくる力</p></div>
        <div class="a-vortex__side a-vortex__side--work"><span></span><h3>THINK</h3><p>本質を捉える / 思考</p></div>
        <div class="a-vortex__side a-vortex__side--art"><span></span><h3>FEEL</h3><p>心を動かす / 感性</p></div>
        <svg class="a-vortex__svg" viewBox="0 0 1000 480" aria-hidden="true">
          <defs><linearGradient id="a-coil-color"><stop stop-color="#77786c"/><stop offset="1" stop-color="#dc4a12"/></linearGradient><marker id="a-up-arrow" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" fill="#242623"/></marker></defs>
          <path class="a-vortex__axis" d="M500 435V155" fill="none" stroke="#343730" stroke-width="1.5" marker-end="url(#a-up-arrow)"/>
          <path class="a-vortex__coil" d="" fill="none" stroke="url(#a-coil-color)" stroke-width="1.4"/>
        </svg>
      </div>
    </section>

    <section class="a-process" aria-labelledby="process-title">
      <div class="a-process__copy a-reveal"><h2 id="process-title">自分たちの視点から、<br>まだ見ぬ価値につなげる。</h2><p>ひとつの問いを、さまざまな角度から見つめる。<br>思考と感性を行き来しながら、<br>ブランドの可能性を立体的にひらいていきます。</p><p>聴くこと、考えること、つくること。<br>それぞれのプロセスをめぐりながら、<br>想いを、人の心に届く体験へとつなげます。</p></div>
      <div class="a-process__visual a-reveal">
        <svg viewBox="0 0 900 900" role="img" aria-labelledby="a-process-svg-title a-process-svg-desc">
          <title id="a-process-svg-title">Yohaku designのクリエイティブプロセス</title><desc id="a-process-svg-desc">対話と観察、思考と探索、表現と体験の3つの段階を、循環する円と立体的な階層で表しています。</desc>
          <defs><marker id="a-loop-arrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" fill="#d84a11"/></marker><marker id="a-loop-arrow-gray" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" fill="#5f605b"/></marker></defs>
          <path d="M115 680L510 450L900 675L500 910Z" fill="#d65119" opacity=".08"/>
          <g class="a-process__tower" stroke="#30332e" stroke-width="1.2" fill="none" stroke-linejoin="round">
            <path d="M475 120L565 65L760 177V700L670 752L475 640Z"/>
            <path d="M565 65V588L760 700M475 120L670 232L760 177M670 232V752"/>
            <path d="M475 245L565 190L760 302L670 357ZM475 370L565 315L760 427L670 482ZM475 495L565 440L760 552L670 607ZM475 620L565 565L760 677L670 732Z"/>
            <path d="M475 170L565 115L760 227M475 295L565 240L760 352M475 420L565 365L760 477M475 545L565 490L760 602" opacity=".45"/>
          </g>
          <g fill="#e64b0a" stroke="#d84507" stroke-width="1">
            <path d="M475 245L565 190L760 302L670 357Z" opacity=".55"/>
            <path d="M475 495L565 440L760 552L670 607Z" opacity=".65"/>
            <path d="M475 620L565 565L760 677L670 732Z" opacity=".85"/>
            <path d="M540 115L575 95L610 115V678L575 698L540 678Z" opacity=".5"/>
          </g>
          <g class="a-process__loop" fill="none" stroke-width="1.25" stroke-linecap="round">
            <path d="M190.8 314A88 88 0 1 1 343.2 314" stroke="#5f605b" marker-end="url(#a-loop-arrow-gray)" data-delay="0"/>
            <g stroke="#d84a11">
              <path d="M190.8 314A88 88 0 0 0 216.5 342.1C228 350.1 237.9 355.7 251.7 353.3A88 88 0 0 1 289.8 355" data-delay="0"/>
              <path d="M317.5 537.9C306 529.9 296.1 524.3 282.3 526.7A88 88 0 0 1 244.2 525" data-delay="1.2"/>
              <path d="M317.5 537.9A88 88 0 1 1 184.3 579.9" marker-end="url(#a-loop-arrow)" data-delay="1.2"/>
              <path d="M251.7 353.3A88 88 0 0 0 216.5 512.1" marker-end="url(#a-loop-arrow)" data-delay=".6"/>
              <path d="M282.3 526.7A88 88 0 0 0 317.5 367.9" marker-end="url(#a-loop-arrow)" data-delay=".6"/>
            </g>
          </g>
          <g fill="none" stroke="#d84a11" stroke-width=".7">
            <path d="M355 270H420V260L475 245M355 440H420V480L475 495M355 610H420V620H475"/>
            <circle cx="355" cy="270" r="4" fill="#f2f1eb" stroke="#5f605b"/><circle cx="355" cy="440" r="4" fill="#f2f1eb"/><circle cx="355" cy="610" r="4" fill="#f2f1eb"/>
          </g>
          <g class="a-process__diagram-labels" text-anchor="middle" fill="#242623">
            <g class="a-process__label"><text x="267" y="241" font-size="14">01 / DISCOVER</text><text x="267" y="270" font-size="17">対話と観察</text><text x="267" y="297" font-size="12">本質に出会う</text></g>
            <g class="a-process__label"><text x="267" y="411" font-size="14" fill="#d84a11">02 / EXPLORE</text><text x="267" y="440" font-size="17" fill="#d84a11">思考と探索</text><text x="267" y="467" font-size="12">可能性をひろげる</text></g>
            <g class="a-process__label"><text x="267" y="581" font-size="14" fill="#d84a11">03 / CREATE</text><text x="267" y="610" font-size="17" fill="#d84a11">表現と体験</text><text x="267" y="637" font-size="12">想いをかたちにする</text></g>
          </g>
          <g font-size="13" fill="#242623"><g class="a-process__label"><text x="675" y="88">YOHAKU DESIGN</text><text x="675" y="109" font-size="10">CREATIVE PROCESS</text></g><text class="a-process__label" x="785" y="308">THINK</text><text class="a-process__label" x="785" y="558">FEEL</text><text class="a-process__label" x="785" y="686">CREATE</text></g>
        </svg>
      </div>
    </section>

    <section class="a-gallery" aria-labelledby="gallery-title">
      <h2 id="gallery-title">EVERYDAY PERSPECTIVES</h2>
      <div class="a-gallery__grid">
        <article class="a-gallery__card a-reveal"><figure><img src="<?php echo esc_url(get_theme_file_uri('images/about-gallery-01.jpg')); ?>" alt="大きな窓から光と影が差し込むミニマルな建築空間" width="1400" height="1800" loading="lazy"></figure><h3>余白を見つける</h3><p>光の入り方、空間の広がり、素材の表情。日常にある小さな違いに目を向けることが、新しい発想の入口になります。私たちは、見過ごしてしまいそうな気づきを大切にしています。</p><a href="<?php echo esc_url(home_url('/#about')); ?>">Our perspective <span>↗</span></a></article>
        <article class="a-gallery__card a-reveal"><figure><img src="<?php echo esc_url(get_theme_file_uri('images/about-gallery-02.jpg')); ?>" alt="色見本をそばに置き、赤い服のデザイン画を描く手元" width="1400" height="1800" loading="lazy"></figure><h3>手を動かし、考える</h3><p>描いて、並べて、試してみる。頭の中にあるアイデアを外に出すことで、思いがけない発見が生まれます。思考と実践を重ねながら、伝わる表現を探っていきます。</p><a href="<?php echo esc_url(home_url('/works/')); ?>">Selected works <span>↗</span></a></article>
        <article class="a-gallery__card a-reveal"><figure><img src="<?php echo esc_url(get_theme_file_uri('images/about-gallery-03.jpg')); ?>" alt="石造りの柱と階段に落ちる光と影" width="1400" height="1800" loading="lazy"></figure><h3>視点をひらく</h3><p>いつもの場所も、少し角度を変えると違って見える。異なる分野や価値観に触れることで、デザインの可能性はひろがります。人と社会のあいだに、新しいつながりをつくります。</p><a href="<?php echo esc_url(home_url('/service/')); ?>">Our service <span>↗</span></a></article>
      </div>
    </section>

    <a class="a-next" href="<?php echo esc_url(home_url('/works/')); ?>" aria-label="次のコンテンツ：制作実績を見る"><div class="a-next__marquee js-distort" data-dir="-1" data-bg="#0d0e0c" data-fg="#f2f1eb" aria-hidden="true"><span class="marquee__item">Next<em>.</em></span></div><div class="a-next__meta"><span class="a-next__bar"></span><span>Contents</span><span>—</span><span>№02</span><span class="a-next__destination">Selected works ↗</span></div></a>
  </main>

<?php get_footer(); ?>

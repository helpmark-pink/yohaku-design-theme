<?php get_header(); ?>

  <main>
    <!-- ヒーロー(p5.js) -->
    <section class="hero" id="hero">
      <div class="hero__canvas" id="hero-canvas"></div>
      <div class="hero__marquee marquee" data-speed="28">
        <div class="marquee__track">
          <span class="marquee__item"><b>YOHAKU</b><i>design</i></span>
        </div>
      </div>
      <div class="hero__info">
        <p class="hero__scroll">Scroll Down</p>
        <p class="hero__coord">00°00'00.0"N&emsp;00°00'00.0"E</p>
        <p class="hero__place">Aimachi, Tokyo</p>
      </div>
    </section>

    <!-- About -->
    <section class="section about" id="about">
      <!-- p5.jsシェーダーで歪ませる大見出し(読み込み失敗時はそのまま文字を表示) -->
      <div class="big-marquee js-distort" data-dir="-1" data-bg="#EEEBE3" data-fg="#17181C" data-accent="#2C3E9E">
        <span class="marquee__item">About<em>.</em></span>
      </div>
      <div class="inner">
        <div class="section-bar">
          <p class="section-bar__label"><span class="dot"></span>Yohaku design</p>
          <p class="section-bar__num">Section<span>·</span>№01</p>
          <p class="section-bar__side">About</p>
        </div>

        <!-- 図形を組み合わせた形の中に、1枚の画像を入れる -->
        <!-- 画像は images/about-baked.jpg(about.jpg をモノクロにして、黄緑と赤の光を焼きこんだもの) -->
        <!-- 画像を変えるときは、同じ加工をした画像に差し替える(ブラウザでは加工しない) -->
        <div class="about-visual" id="aboutVisual">
          <svg class="about-visual__svg" viewBox="0 0 1200 520" preserveAspectRatio="xMidYMid slice" role="img" aria-label="間町のスタジオの風景">
            <defs>
              <!-- 形の型:白い部分だけ画像が見える / 黒い部分は穴になる -->
              <mask id="aboutMask" maskUnits="userSpaceOnUse" x="0" y="0" width="1200" height="520">
                <!-- stroke(フチ)を太くすると図形どうしがくっついて四角になる。JSで細くして形を出す -->
                <g fill="#fff" id="aboutShapes" stroke="#fff" stroke-width="0" stroke-linejoin="round">
                  <!-- 上段:小さな図形を並べた帯 -->
                  <path d="M0 0H130V65A65 65 0 0 1 0 65Z" />
                  <circle cx="205" cy="65" r="65" />
                  <path d="M280 0H410A130 130 0 0 1 280 130Z" />
                  <path d="M420 0H550L420 130Z" />
                  <path d="M560 130V65A65 65 0 0 1 690 65V130Z" />
                  <rect x="700" y="0" width="56" height="130" />
                  <path d="M766 0A65 65 0 0 1 766 130Z" />
                  <rect x="846" y="0" width="120" height="130" />
                  <path d="M976 130V0A130 130 0 0 1 1106 130Z" />
                  <rect x="1116" y="0" width="84" height="130" />
                  <!-- 下段:大きな図形 -->
                  <path d="M0 520V330A190 190 0 0 1 380 330V520Z" />
                  <rect x="390" y="140" width="90" height="380" />
                  <path d="M490 520V140A380 380 0 0 1 870 520Z" />
                  <rect x="880" y="140" width="220" height="380" />
                  <path d="M1110 140H1200V340H1160V520H1110Z" />
                </g>
                <g fill="#000" id="aboutHoles">
                  <!-- 穴(抜き) -->
                  <rect x="886" y="35" width="40" height="60" />
                  <rect x="1140" y="35" width="36" height="36" />
                  <rect x="1140" y="80" width="36" height="20" />
                  <path d="M610 400L780 520H610Z" />
                  <rect x="960" y="210" width="60" height="70" />
                  <rect x="1040" y="420" width="60" height="100" />
                </g>
              </mask>
              <!-- 画像がないときの下地 -->
              <linearGradient id="aboutBase" x1="0" y1="0" x2="1" y2="0.4">
                <stop offset="0" stop-color="#DCA62A" />
                <stop offset=".45" stop-color="#EBB4BE" />
                <stop offset=".8" stop-color="#2C3E9E" />
                <stop offset="1" stop-color="#17181C" />
              </linearGradient>
            </defs>

            <g mask="url(#aboutMask)">
              <!-- この g を拡大・縮小して、画像が近づく/遠ざかる -->
              <g class="about-visual__depth" id="aboutDepth">
                <!-- 中身(下地+画像)。JSで縦の帯に切り分けて、スクロール中に上下へずらす -->
                <g id="aboutSrc">
                  <rect x="-100" y="-100" width="1400" height="720" fill="url(#aboutBase)" />
                  <image href="<?php echo esc_url(get_theme_file_uri('images/about-baked.jpg')); ?>" x="0" y="-60" width="1200" height="640" preserveAspectRatio="xMidYMid slice" />
                </g>
              </g>
            </g>
          </svg>
        </div>

        <div class="about__body js-reveal">
          <h2 class="about__title">詰め込まない。<br>伝わる余白を、設計する。</h2>
          <p class="about__en">We design the space between things —<br>where ideas breathe and brands are remembered.</p>
          <p class="about__text">
            Yohaku design は、余白区間町の小さなビルで活動するデザインスタジオです。<br>
            情報を足すのではなく、削ることで本当に伝えたいことを浮かび上がらせる。
            ロゴやWebサイト、印刷物まで、ブランドの「間」を一緒に考えます。
          </p>
          <a href="<?php echo esc_url(home_url('/about/')); ?>" class="link-arrow">View About <span>↘</span></a>
        </div>
      </div>
    </section>

    <!-- Works -->
    <section class="section works" id="works">
      <div class="big-marquee big-marquee--italic js-distort" data-dir="1" data-italic data-bg="#17181C" data-fg="#EEEBE3" data-accent="#DCA62A">
        <span class="marquee__item">Selected Works<em>—</em></span>
      </div>
      <div class="inner">
        <div class="section-bar section-bar--dark">
          <p class="section-bar__label"><span class="dot"></span>Our Works</p>
          <p class="section-bar__num">Section<span>·</span>№02</p>
          <p class="section-bar__side">Branding / Web / Editorial</p>
        </div>
      </div>

      <!-- 同じ幅のパネルを画面いっぱいに横に並べ、ゆっくり左へ流しながら画像を順番に切り替える -->
      <!-- 画像を変える・増やすときは data-images の中を書きかえる(カンマ区切り) -->
      <!-- パネルは画面の外まで続くように4枚並べている(全部同じ画像を表示する) -->
      <!-- 38vw × 4枚 = 152vw なので、1枚分(38vw)流れても画面(100vw)は埋まったまま -->
      <div class="works-slider" id="worksSlider" data-images="<?php echo esc_attr(implode(',', array(
          get_theme_file_uri('images/works-slide-03.jpg'),
          get_theme_file_uri('images/works-slide-01.jpg'),
          get_theme_file_uri('images/works-slide-02.jpg'),
      ))); ?>" data-interval="4000">
        <div class="works-slider__track">
          <div class="works-slider__panel"></div>
          <div class="works-slider__panel"></div>
          <div class="works-slider__panel"></div>
          <div class="works-slider__panel"></div>
        </div>
      </div>

      <!-- スライダーの説明。右側の番号と作品名は、画像の切り替えに合わせて変わる -->
      <!-- 作品名は data-images の順番と合わせて並べる -->
      <div class="inner works__intro">
        <div class="works__copy">
          <p class="works__lead">We design the quiet between things,<br> so that what truly matters can be heard.</p>
          <p class="works__text">
            Every project begins with listening, not with a template.<br>
            We start each one from a blank page with our clients,<br>
            leaving room for ideas to breathe and for brands to be remembered.
          </p>
          <a href="<?php echo esc_url(home_url('/works/')); ?>" class="link-arrow">View Works <span>↘</span></a>
        </div>
        <div class="works__meta">
          <p class="works__count"><span id="worksNum">01</span><span class="works__dash">-</span><span id="worksTotal">03</span></p>
          <ul class="works__titles" id="worksTitles">
            <li class="is-current"><p>(KOKAN GALLERY)</p><p>Art &amp; Communication</p></li>
            <li><p>(MEGURI NO MORI)</p><p>Space &amp; Identity</p></li>
            <li><p>(NAGI NO MA)</p><p>Interior &amp; Experience</p></li>
          </ul>
        </div>
      </div>

      <div class="works__strip marquee" data-speed="45">
        <div class="marquee__track">
          <!-- 図形だけのデザインとして流す(説明文なし) -->
          <div class="work-card work-card--a" aria-hidden="true"><div class="work-card__art"></div></div>
          <div class="work-card work-card--b" aria-hidden="true"><div class="work-card__art"></div></div>
          <div class="work-card work-card--c" aria-hidden="true"><div class="work-card__art"></div></div>
          <div class="work-card work-card--d" aria-hidden="true"><div class="work-card__art"></div></div>
          <div class="work-card work-card--e" aria-hidden="true"><div class="work-card__art"></div></div>
        </div>
      </div>
    </section>

    <!-- Service -->
    <section class="section service" id="service">
      <div class="inner">
        <div class="section-bar">
          <p class="section-bar__label"><span class="dot"></span>What we do</p>
          <p class="section-bar__num">Section<span>·</span>№03</p>
          <p class="section-bar__side">Service</p>
        </div>
        <h2 class="heading-serif">Service<em>.</em></h2>
        <div class="service__grid">
          <article class="service__item js-reveal">
            <div class="service__icon service__icon--1"></div>
            <p class="service__num">01</p>
            <h3>Branding</h3>
            <p class="service__ja">ブランディング</p>
            <p>ロゴ、カラー、トーン&マナーまで。言葉にしにくい「らしさ」を、形と余白で整理します。</p>
          </article>
          <article class="service__item js-reveal">
            <div class="service__icon service__icon--2"></div>
            <p class="service__num">02</p>
            <h3>Web Design</h3>
            <p class="service__ja">Webデザイン・制作</p>
            <p>設計からデザイン、コーディングまで一貫して担当。動きのある表現も、読みやすさを最優先に。</p>
          </article>
          <article class="service__item js-reveal">
            <div class="service__icon service__icon--3"></div>
            <p class="service__num">03</p>
            <h3>Editorial</h3>
            <p class="service__ja">エディトリアル</p>
            <p>パンフレット、冊子、ZINE。紙の手触りまで含めて、手に取りたくなる一冊をつくります。</p>
          </article>
        </div>
      </div>
    </section>

    <!-- News -->
    <section class="section news" id="news">
      <div class="inner">
        <div class="section-bar">
          <p class="section-bar__label"><span class="dot"></span>News</p>
          <p class="section-bar__num">Section<span>·</span>№04</p>
          <p class="section-bar__side">Award / Event / Recruit</p>
        </div>
        <p class="news__lead">間町のスタジオから、制作実績やイベント、<br>採用などの最新情報をお届けします。</p>
      </div>

      <!-- スクロールすると、記事が1件ずつ大きく開き、読み終えた記事は細い帯にたたまれて上に積み重なる -->
      <!-- 管理画面の「投稿」から新しい順に3件を出し、それぞれの記事ページへリンクする -->
      <!-- 画像はアイキャッチ画像(縦長 800×1000px がおすすめ)。ないときは images/news-1.jpg 〜 news-3.jpg を使う -->
      <!-- 文章は投稿の「抜粋」。空のときは本文の最初を短く切って使う -->
      <?php
      $top_news = get_posts(array('posts_per_page' => 3));
      $top_news_count = count($top_news);
      ?>
      <?php if ($top_news) : ?>
      <div class="news-stack" id="newsStack">
        <div class="news-stack__sticky inner">
          <ul class="news-stack__list">
            <?php foreach ($top_news as $i => $news_post) :
                $news_cats = get_the_category($news_post->ID);
                $news_cat  = $news_cats ? $news_cats[0] : null;
                $news_text = has_excerpt($news_post) ? get_the_excerpt($news_post) : wp_html_excerpt(wp_strip_all_tags(strip_shortcodes($news_post->post_content)), 60, '…');
                $news_link = get_permalink($news_post);
            ?>
            <li class="news-item">
              <div class="news-item__thumb">
                <?php if (has_post_thumbnail($news_post)) : ?>
                  <?php echo get_the_post_thumbnail($news_post, 'large', array('alt' => '')); ?>
                <?php else : ?>
                  <img src="<?php echo esc_url(get_theme_file_uri('images/news-' . ($i + 1) . '.jpg')); ?>" alt="">
                <?php endif; ?>
              </div>
              <p class="news-item__num"><?php echo esc_html(sprintf('%02d', $top_news_count - $i)); ?></p>
              <div class="news-item__body">
                <p class="news-item__meta"><time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $news_post)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $news_post)); ?></time><?php if ($news_cat) : ?><span class="news-item__dash">-</span><span><?php echo esc_html($news_cat->name); ?></span><?php endif; ?></p>
                <div class="news-item__detail">
                  <p class="news-item__title"><a href="<?php echo esc_url($news_link); ?>">[ <?php echo esc_html(get_the_title($news_post)); ?> ]</a></p>
                  <?php if ($news_text) : ?>
                  <p class="news-item__text"><?php echo esc_html($news_text); ?></p>
                  <?php endif; ?>
                  <?php if ($i === $top_news_count - 1) : ?>
                  <a href="<?php echo esc_url(home_url('/news/')); ?>" class="link-arrow news-item__more">View All News <span>↘</span></a>
                  <?php endif; ?>
                </div>
              </div>
              <span class="news-item__arrow" aria-hidden="true">↘</span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php else : ?>
      <!-- 投稿がまだないときは、一覧ページへのリンクだけ出す -->
      <div class="inner">
        <a href="<?php echo esc_url(home_url('/news/')); ?>" class="link-arrow news-item__more">View All News <span>↘</span></a>
      </div>
      <?php endif; ?>
    </section>

    <!-- Contact -->
    <section class="contact" id="contact">
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="contact__link">
        <span class="contact__small">Contact — お仕事のご相談はこちら</span>
        <span class="contact__big">Let's make<br><i>some space.</i></span>
        <span class="contact__circle">↗</span>
      </a>
    </section>
  </main>


<?php get_footer(); ?>
<?php
/**
 * News ページ(news.html を元にしたテンプレート)
 */
get_header('news');

// お知らせ一覧は、管理画面の「投稿」から新しい順に出す(1ページ10件。11件目からは /news/page/2/ に続く)
$paged      = max(1, (int) get_query_var('paged'));
$news_query = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'paged'          => $paged,
));
$latest_post = get_posts(array('posts_per_page' => 1));
?>

  <main>
    <!-- ==========================================================
         ヒーロー(p5.js)
         トップと同じ図形タイルを使うが、こちらは「パタパタ時計(フラップ表示)」のように
         マスが1枚ずつ縦にめくれて、図形と色が入れかわる
         ========================================================== -->
    <section class="nhero" id="hero">
      <div class="nhero__canvas" id="nhero-canvas"></div>
      <div class="nhero__title">
        <p class="nhero__sub">Information<span>/</span>お知らせ</p>
        <h1 class="nhero__head" aria-label="News &amp; Topics">
          <span class="nhero__line"><b>News</b></span>
          <span class="nhero__line"><i>&amp; Topics</i></span>
        </h1>
      </div>
      <div class="nhero__info">
        <p class="nhero__scroll">Scroll Down</p>
        <?php if ($latest_post) : ?>
        <p class="nhero__latest">Latest Update&emsp;<span><?php echo esc_html(get_the_date('Y.m.d', $latest_post[0])); ?></span></p>
        <?php endif; ?>
        <p class="nhero__place">Aimachi, Tokyo</p>
      </div>
    </section>

    <!-- ==========================================================
         お知らせ一覧
         ========================================================== -->
    <section class="nlist" id="list">
      <div class="inner">
        <div class="section-bar">
          <p class="section-bar__label"><span class="dot"></span>News</p>
          <p class="section-bar__num">Section<span>·</span>№01</p>
          <p class="section-bar__side">All Topics</p>
        </div>

        <div class="nlist__head js-up">
          <h2 class="nlist__title">お知らせ</h2>
          <!-- カテゴリーで絞り込む(押すと、そのカテゴリーの記事だけ表示) -->
          <ul class="nlist__filter" id="newsFilter">
            <li><button type="button" class="is-active" data-filter="all">All</button></li>
            <li><button type="button" data-filter="award">Award</button></li>
            <li><button type="button" data-filter="event">Event</button></li>
            <li><button type="button" data-filter="works">Works</button></li>
            <li><button type="button" data-filter="recruit">Recruit</button></li>
          </ul>
        </div>

        <!-- 記事は管理画面の「投稿」で追加する(カテゴリーのスラッグが絞り込みボタンの data-filter と対応) -->
        <ul class="nlist__rows" id="newsRows">
          <?php while ($news_query->have_posts()) : $news_query->the_post();
              $row_cats = get_the_category();
              $row_cat  = $row_cats ? $row_cats[0] : null;
          ?>
          <li class="nrow" data-cat="<?php echo esc_attr($row_cat ? $row_cat->slug : ''); ?>"><a href="<?php the_permalink(); ?>">
            <time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time><span class="nrow__tag"><?php echo esc_html($row_cat ? $row_cat->name : ''); ?></span>
            <p class="nrow__title"><?php the_title(); ?></p><span class="nrow__arrow">→</span>
          </a></li>
          <?php endwhile; wp_reset_postdata(); ?>
        </ul>

        <?php if ($news_query->max_num_pages > 1) : ?>
        <!-- ページ送り(記事が10件を超えたら表示) -->
        <nav class="nlist__pager js-up" aria-label="ページ送り">
          <?php for ($i = 1; $i <= $news_query->max_num_pages; $i++) : ?>
            <?php if ($i === $paged) : ?>
          <span class="is-current" aria-current="page"><?php echo esc_html(sprintf('%02d', $i)); ?></span>
            <?php else : ?>
          <a href="<?php echo esc_url(get_pagenum_link($i)); ?>"><?php echo esc_html(sprintf('%02d', $i)); ?></a>
            <?php endif; ?>
          <?php endfor; ?>
          <?php if ($paged < $news_query->max_num_pages) : ?>
          <a class="nlist__next" href="<?php echo esc_url(get_pagenum_link($paged + 1)); ?>">Next <span>→</span></a>
          <?php else : ?>
          <span class="nlist__next is-disabled" aria-disabled="true">Next <span>→</span></span>
          <?php endif; ?>
        </nav>
        <?php endif; ?>
      </div>
    </section>

    <!-- ==========================================================
         Pick up:注目の記事を、画像と文章を左右交互に大きく見せる
         画像は images/news-1.jpg 〜 news-3.jpg(トップのNewsと同じ画像)
         ========================================================== -->
    <section class="pickup" id="pickup">
      <div class="inner">
        <div class="section-bar">
          <p class="section-bar__label"><span class="dot"></span>Pick up</p>
          <p class="section-bar__num">Section<span>·</span>№02</p>
          <p class="section-bar__side">Featured Topics</p>
        </div>
      </div>

      <article class="pick js-pick">
        <div class="pick__media"><img src="<?php echo esc_url(get_theme_file_uri('images/news-1.jpg')); ?>" alt="受賞したトロフィー" data-speed="0.12"></div>
        <div class="pick__text">
          <p class="pick__label">Award</p>
          <h3 class="pick__title">ひとつの街の手しごとを、<br>ひとつのサイトに。</h3>
          <p class="pick__body">「Aimachi Craft Week」公式サイトが Design Grid Award 2026 に入選。工房ごとの“間”を伝えるため、写真の余白と文字の大きさを一から設計しました。</p>
          <a href="<?php echo esc_url(home_url('/news8/')); ?>" class="pick__more">More <span>→</span></a>
        </div>
      </article>

      <article class="pick pick--reverse js-pick" id="pick-event">
        <div class="pick__media"><img src="<?php echo esc_url(get_theme_file_uri('images/news-2.jpg')); ?>" alt="ギャラリーの展示風景" data-speed="0.12"></div>
        <div class="pick__text">
          <p class="pick__label">Event</p>
          <h3 class="pick__title">展示「余白のかたち」<br>10.18 — 11.03</h3>
          <p class="pick__body">これまで手がけたロゴや冊子を、「何を削ったか」という視点で読み解く展示です。間町のギャラリーにて、入場無料で開催します。</p>
          <a href="<?php echo esc_url(home_url('/news7/')); ?>" class="pick__more">More <span>→</span></a>
        </div>
      </article>

      <article class="pick js-pick" id="pick-recruit">
        <div class="pick__media"><img src="<?php echo esc_url(get_theme_file_uri('images/news-3.jpg')); ?>" alt="デザイナーの作業風景" data-speed="0.12"></div>
        <div class="pick__text">
          <p class="pick__label">Recruit</p>
          <h3 class="pick__title">一緒に、<br>余白をつくる人を探しています。</h3>
          <p class="pick__body">Webデザイナー(正社員・業務委託)を募集しています。動きのある表現が好きな方、読みやすさにこだわれる方を歓迎します。</p>
          <a href="<?php echo esc_url(home_url('/news6/')); ?>" class="pick__more">More <span>→</span></a>
        </div>
      </article>
    </section>

    <!-- ==========================================================
         Interview(墨色の背景):スタッフ3人のインタビュー
         画像は images/interview-1.jpg 〜 interview-3.jpg
         ========================================================== -->
    <section class="interview" id="interview">
      <div class="inner">
        <div class="interview__head js-up">
          <h2 class="interview__title">Staff Interview<span>スタッフインタビュー</span></h2>
          <p class="interview__side">余白をつくる人たちの、ふだんの考えごと</p>
        </div>
        <ul class="interview__list">
          <!-- インタビューの個別ページはまだないので、それぞれが担当した仕事へつなぐ -->
          <li class="icard js-up">
            <a href="<?php echo esc_url(home_url('/works/')); ?>">
              <div class="icard__img"><img src="<?php echo esc_url(get_theme_file_uri('images/interview-1.jpg')); ?>" alt="間宮 しおりのポートレート"></div>
              <p class="icard__num" aria-hidden="true">01</p>
              <p class="icard__name">間宮 しおり</p>
              <p class="icard__role">アートディレクター</p>
              <p class="icard__text">足すより、削るほうがむずかしい。<br>ロゴが決まるまでの100枚のスケッチ</p>
            </a>
          </li>
          <li class="icard js-up">
            <a href="<?php echo esc_url(home_url('/works/')); ?>">
              <div class="icard__img"><img src="<?php echo esc_url(get_theme_file_uri('images/interview-2.jpg')); ?>" alt="白石 湊のポートレート"></div>
              <p class="icard__num" aria-hidden="true">02</p>
              <p class="icard__name">白石 湊</p>
              <p class="icard__role">Webデザイナー</p>
              <p class="icard__text">動きは、読むための道具。<br>スクロールの“間”をデザインする</p>
            </a>
          </li>
          <li class="icard js-up">
            <a href="<?php echo esc_url(home_url('/works/')); ?>">
              <div class="icard__img"><img src="<?php echo esc_url(get_theme_file_uri('images/interview-3.jpg')); ?>" alt="小野寺 圭のポートレート"></div>
              <p class="icard__num" aria-hidden="true">03</p>
              <p class="icard__name">小野寺 圭</p>
              <p class="icard__role">エディトリアルデザイナー</p>
              <p class="icard__text">紙をめくる手の速さまで考える。<br>季刊「間」ができるまで</p>
            </a>
          </li>
        </ul>
      </div>
    </section>

    <!-- Contact(トップページと同じ) -->
    <section class="contact" id="contact">
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="contact__link">
        <span class="contact__small">Contact — お仕事のご相談はこちら</span>
        <span class="contact__big">Let's make<br><i>some space.</i></span>
        <span class="contact__circle">↗</span>
      </a>
    </section>
  </main>

  <!-- フッター上のバナー(Works へのリンク。お問い合わせページと同じ) -->
  <a href="<?php echo esc_url(home_url('/works/')); ?>" class="cbanner" aria-label="Selected Works — 余白から生まれた仕事を見る">
    <div class="cbanner__art" aria-hidden="true">
      <svg viewBox="0 0 600 200" preserveAspectRatio="xMidYMid slice">
        <rect width="600" height="200" fill="#17181C"/>
        <rect x="0" y="0" width="600" height="36" fill="#BDB8AC"/>
        <rect x="70" y="0" width="110" height="64" fill="#BDB8AC"/>
        <rect x="290" y="0" width="110" height="64" fill="#BDB8AC"/>
        <rect x="500" y="0" width="100" height="64" fill="#BDB8AC"/>
        <rect x="0" y="120" width="600" height="80" fill="#2C3E9E"/>
        <path class="cb-half" d="M60 200A110 110 0 0 1 280 200Z" fill="#EBB4BE"/>
        <path class="cb-half" d="M300 200A110 110 0 0 1 520 200Z" fill="#EBB4BE"/>
        <g fill="none" stroke="#EEEBE3" stroke-opacity=".18" stroke-width="1">
          <path d="M60 0V200M280 0V200M520 0V200"/>
          <path d="M170 200L280 40M410 200L520 40"/>
        </g>
      </svg>
    </div>
    <div class="cbanner__text">
      <p class="cbanner__en">Selected <i>Works</i><span class="cbanner__arrow">→</span></p>
      <p class="cbanner__ja">余白から生まれた仕事を見る。</p>
    </div>
  </a>

<?php get_footer(); ?>

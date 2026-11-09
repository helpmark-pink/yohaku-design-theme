<?php
/**
 * Works ページ(works.html を元にしたテンプレート)
 */
get_header('works');
?>

  <main id="works-content">
    <section class="w-intro" aria-labelledby="works-title">
      <h1 id="works-title">Works</h1>
      <div class="w-intro__copy">
        <h2><span>感性を動かす、デザインで。</span><br>人とブランドの、新しい関係をつくる。</h2>
        <p>いつもの風景に、新しい視点を。<br>対話から見つけた小さな気づきを、人の心に届く体験へ。<br>Yohaku designが手がけた、3つのプロジェクトをご紹介します。</p>
      </div>
    </section>

    <!-- 写真・作品名の差し替えは works.js の WORKS_CONTENT にまとめています。 -->
    <section class="w-ribbons" aria-label="制作実績の風景">
      <div class="w-ribbon" data-ribbon="0"><div class="w-ribbon__track"><img src="<?php echo esc_url(get_theme_file_uri('images/works/02-01.jpg')); ?>" alt="光が差し込むエスカレーターのある空間" width="1400" height="1000" fetchpriority="high"></div></div>
      <div class="w-ribbon w-ribbon--reverse" data-ribbon="1"><div class="w-ribbon__track"><img src="<?php echo esc_url(get_theme_file_uri('images/works/03-01.jpg')); ?>" alt="窓辺に緑を配したラウンジ" width="1400" height="1000" fetchpriority="high"></div></div>
      <span class="w-ribbons__caption">Selected perspectives / Yohaku design</span>
    </section>

    <div class="w-dark">
      <section class="w-statement">
        <div class="w-reveal"><h2>視野がひらき、視点が変わる。<br>その出会いから、日常が動きはじめる。</h2><p>ブランドの本質を見つめ、まだ見ぬ可能性をかたちに。<br>場所、もの、人のあいだにある余白から、<br>新しいコミュニケーションを考えました。</p><p>見ること。気づくこと。心が動くこと。<br>3つのプロジェクトを通して、私たちの仕事をお伝えします。</p></div>
        <div class="w-symbol w-symbol--statement" data-symbol="0" aria-hidden="true"></div>
      </section>

      <article class="w-project" id="project-01" data-project="0" aria-labelledby="project-title-01">
        <div class="w-entry">
          <div class="w-entry__sticky">
            <div class="w-entry__echo" aria-hidden="true"><img src="<?php echo esc_url(get_theme_file_uri('images/works/01-02.jpg')); ?>" alt="" loading="lazy"></div>
            <div class="w-entry__frame"><img src="<?php echo esc_url(get_theme_file_uri('images/works/01-01.jpg')); ?>" alt="夕陽を受ける球体のアートオブジェ" width="1400" height="1000" loading="lazy"></div>
            <div class="w-symbol w-entry__symbol" data-symbol="0" aria-hidden="true"></div>
            <p class="w-location">01 / Art &amp; Communication</p>
            <div class="w-project-title"><p>01 <span>–</span> 03</p><h2 id="project-title-01">視点の、その先へ</h2><p>Beyond the Ordinary</p></div>
          </div>
        </div>
        <div class="w-gallery" data-gallery="0" aria-label="視点の、その先へ — プロジェクト写真">
          <div class="w-gallery__sticky"><div class="w-gallery__images"><img class="w-gallery__fallback" src="<?php echo esc_url(get_theme_file_uri('images/works/01-01.jpg')); ?>" alt="球体のオブジェがつくる光と影" loading="lazy"></div><p class="w-location">01 / Art &amp; Communication</p><div class="w-project-title" aria-hidden="true"><p>01 <span>–</span> 03</p><p class="w-project-title__ja">視点の、その先へ</p><p>Beyond the Ordinary</p></div></div>
        </div>
        <section class="w-detail" aria-labelledby="detail-title-01">
          <div class="w-detail__copy w-reveal"><p class="w-detail__eyebrow">01 / Art &amp; Communication<br>Beyond the Ordinary<br>光環ギャラリー / KOKAN GALLERY</p><h3 id="detail-title-01">いつもの視線を、<br>少しだけ遠くへ。</h3><p>街にひらかれた小さなギャラリーのための、アートディレクション。光の移ろいと見る人の動きによって表情を変える、球体のインスタレーションを中心に、空間とビジュアルの世界観を設計しました。</p><p>足を止める、角度を変える、誰かと言葉を交わす。鑑賞という体験を通じて、日常のなかに新たな視点が生まれる場所を目指しています。</p><dl class="w-spec"><div><dt>Client</dt><dd>光環ギャラリー</dd></div><div><dt>Scope</dt><dd>Art Direction / Visual Identity / Web</dd></div><div><dt>Location</dt><dd>東京都余白区光町2-4-8</dd></div><div><dt>Year</dt><dd>2026</dd></div></dl><a class="w-text-link" href="#creator-01">Creative partner <span>↘</span></a></div>
          <figure class="w-detail__image"><img src="<?php echo esc_url(get_theme_file_uri('images/works/01-04.jpg')); ?>" alt="光環ギャラリーの静かな展示空間" width="1400" height="1000" loading="lazy"><figcaption>KOKAN GALLERY / A different point of view.</figcaption></figure>
        </section>
      </article>

      <article class="w-project" id="project-02" data-project="1" aria-labelledby="project-title-02">
        <div class="w-entry"><div class="w-entry__sticky"><div class="w-entry__echo" aria-hidden="true"><img src="<?php echo esc_url(get_theme_file_uri('images/works/02-02.jpg')); ?>" alt="" loading="lazy"></div><div class="w-entry__frame"><img src="<?php echo esc_url(get_theme_file_uri('images/works/02-01.jpg')); ?>" alt="上階へ続くエスカレーターと光" width="1400" height="1000" loading="lazy"></div><div class="w-symbol w-entry__symbol" data-symbol="1" aria-hidden="true"></div><p class="w-location">02 / Space &amp; Identity</p><div class="w-project-title"><p>02 <span>–</span> 03</p><h2 id="project-title-02">変化のリズム</h2><p>A Change of Perspective</p></div></div></div>
        <div class="w-gallery" data-gallery="1" aria-label="変化のリズム — プロジェクト写真"><div class="w-gallery__sticky"><div class="w-gallery__images"><img class="w-gallery__fallback" src="<?php echo esc_url(get_theme_file_uri('images/works/02-01.jpg')); ?>" alt="光を受けながら上へ伸びるエスカレーター" loading="lazy"></div><p class="w-location">02 / Space &amp; Identity</p><div class="w-project-title" aria-hidden="true"><p>02 <span>–</span> 03</p><p class="w-project-title__ja">変化のリズム</p><p>A Change of Perspective</p></div></div></div>
        <section class="w-detail" aria-labelledby="detail-title-02"><div class="w-detail__copy w-reveal"><p class="w-detail__eyebrow">02 / Space &amp; Identity<br>A Change of Perspective<br>巡りの杜 / MEGURI NO MORI</p><h3 id="detail-title-02">移動する時間に、<br>発見するよろこびを。</h3><p>歩く、上る、振り返る。日々の何気ない移動を、新しい発見へとつなげる複合施設のアイデンティティ。建築の線や光の反射から着想を得て、空間に溶け込むサインとグラフィックを制作しました。</p><p>異なる時間、異なる高さから見ることで変わる景色。その連続性を、視覚表現とデジタルの体験に落とし込み、訪れるたびに小さな変化と出会える場所をつくっています。</p><dl class="w-spec"><div><dt>Client</dt><dd>巡りの杜プロジェクト</dd></div><div><dt>Scope</dt><dd>Branding / Signage / Web</dd></div><div><dt>Location</dt><dd>東京都余白区巡町1-7-3</dd></div><div><dt>Year</dt><dd>2026</dd></div></dl><a class="w-text-link" href="#creator-02">Creative partner <span>↘</span></a></div><figure class="w-detail__image"><img src="<?php echo esc_url(get_theme_file_uri('images/works/02-01.jpg')); ?>" alt="建築と光が描く、移動のリズム" width="1400" height="1000" loading="lazy"><figcaption>MEGURI NO MORI / Every step, a new perspective.</figcaption></figure></section>
      </article>

      <article class="w-project" id="project-03" data-project="2" aria-labelledby="project-title-03">
        <div class="w-entry"><div class="w-entry__sticky"><div class="w-entry__echo" aria-hidden="true"><img src="<?php echo esc_url(get_theme_file_uri('images/works/03-02.jpg')); ?>" alt="" loading="lazy"></div><div class="w-entry__frame"><img src="<?php echo esc_url(get_theme_file_uri('images/works/03-01.jpg')); ?>" alt="植物と柔らかなソファを配したラウンジ" width="1400" height="1000" loading="lazy"></div><div class="w-symbol w-entry__symbol" data-symbol="2" aria-hidden="true"></div><p class="w-location">03 / Interior &amp; Experience</p><div class="w-project-title"><p>03 <span>–</span> 03</p><h2 id="project-title-03">心地よさの余白</h2><p>A Space to Breathe</p></div></div></div>
        <div class="w-gallery" data-gallery="2" aria-label="心地よさの余白 — プロジェクト写真"><div class="w-gallery__sticky"><div class="w-gallery__images"><img class="w-gallery__fallback" src="<?php echo esc_url(get_theme_file_uri('images/works/03-01.jpg')); ?>" alt="窓辺に広がるラウンジの風景" loading="lazy"></div><p class="w-location">03 / Interior &amp; Experience</p><div class="w-project-title" aria-hidden="true"><p>03 <span>–</span> 03</p><p class="w-project-title__ja">心地よさの余白</p><p>A Space to Breathe</p></div></div></div>
        <section class="w-detail" aria-labelledby="detail-title-03"><div class="w-detail__copy w-reveal"><p class="w-detail__eyebrow">03 / Interior &amp; Experience<br>A Space to Breathe<br>凪の間 / NAGI NO MA</p><h3 id="detail-title-03">立ち止まることで、<br>次のアイデアが生まれる。</h3><p>働く時間のあいだに、思考と感性を整えるラウンジ。木漏れ日、柔らかな素材、植物の気配を手がかりに、空間のコンセプトからビジュアル、ウェブサイトまでを一貫してデザインしました。</p><p>ひとりで考える場所、誰かと話す場所、何もせず過ごす場所。それぞれに心地よい距離をつくることで、自分らしいリズムを取り戻し、新しい発想につながる体験を提案しています。</p><dl class="w-spec"><div><dt>Client</dt><dd>凪の間デザインオフィス</dd></div><div><dt>Scope</dt><dd>Concept / Art Direction / Web</dd></div><div><dt>Location</dt><dd>東京都余白区凪町3-2-6</dd></div><div><dt>Year</dt><dd>2026</dd></div></dl><a class="w-text-link" href="#creator-03">Creative partner <span>↘</span></a></div><figure class="w-detail__image"><img src="<?php echo esc_url(get_theme_file_uri('images/works/03-02.jpg')); ?>" alt="柔らかな光に包まれた、ひとりで過ごすための椅子" width="1400" height="1000" loading="lazy"><figcaption>NAGI NO MA / Make room for the next idea.</figcaption></figure></section>
      </article>
    </div>

    <section class="w-creators" aria-labelledby="creators-title">
      <h2 id="creators-title">Creators</h2>
      <?php
      // 管理画面の「クリエイター」を「順序」の小さい順に出す。1件もないときは、下の元の3件を出す
      $creators = get_posts(array(
          'post_type'      => 'creator',
          'posts_per_page' => -1,
          'orderby'        => array('menu_order' => 'ASC', 'date' => 'ASC'),
      ));
      $projects    = aa6_works_projects();
      $creator_ids = array(); // 同じプロジェクトのクリエイターが複数いても、移動先の id は1つだけにする
      ?>
      <?php if ($creators) : ?>
        <?php foreach ($creators as $creator) :
            $project_num = (int) get_post_meta($creator->ID, 'aa6_creator_project', true);
            $project     = isset($projects[$project_num]) ? $projects[$project_num] : null;
            $anchor      = '';
            if ($project && !in_array($project_num, $creator_ids, true)) {
                $anchor        = sprintf('creator-%02d', $project_num);
                $creator_ids[] = $project_num;
            }
            $sub      = get_post_meta($creator->ID, 'aa6_creator_sub', true);
            $thumb_id = get_post_thumbnail_id($creator);
            $alt      = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
            if ($alt === '') {
                $alt = get_the_title($creator) . 'のポートレート';
            }
        ?>
      <article class="w-creator"<?php echo $anchor ? ' id="' . esc_attr($anchor) . '"' : ''; ?>>
        <p class="w-creator__project"><?php if ($project) : ?><?php echo esc_html($project[0]); ?><br><?php echo esc_html($project[1]); ?><?php endif; ?></p>
        <div class="w-creator__copy">
          <h3><?php echo esc_html(get_the_title($creator)); ?><?php if ($sub !== '') : ?> <span><?php echo esc_html($sub); ?></span><?php endif; ?></h3>
          <?php echo apply_filters('the_content', $creator->post_content); ?>
          <?php if ($project) : ?>
          <a class="w-text-link" href="<?php echo esc_attr(sprintf('#project-%02d', $project_num)); ?>">View project <span>↗</span></a>
          <?php endif; ?>
        </div>
        <?php if ($thumb_id) : ?>
        <figure class="w-creator__portrait"><?php echo wp_get_attachment_image($thumb_id, 'large', false, array('alt' => $alt, 'loading' => 'lazy')); ?></figure>
        <?php endif; ?>
      </article>
        <?php endforeach; ?>
      <?php else : ?>
      <article class="w-creator" id="creator-01"><p class="w-creator__project">01 / Art &amp; Communication<br>Beyond the Ordinary</p><div class="w-creator__copy"><h3>HIKARI STUDIO <span>（ヒカリスタジオ）</span></h3><p>光と影、映像と空間の関係を探究するクリエイティブユニット。日々の小さな気づきを起点に、インスタレーションやビジュアル作品を制作しています。今回は、光環ギャラリーの空間表現とアートディレクションを担当。見る人の視点によって異なる印象が生まれる、開かれた表現を目指しました。</p><a class="w-text-link" href="#project-01">View project <span>↗</span></a></div><figure class="w-creator__portrait"><img src="<?php echo esc_url(get_theme_file_uri('images/works/creator-01.jpg')); ?>" alt="HIKARI STUDIOのメンバーのポートレート" width="600" height="800" loading="lazy"></figure></article>
      <article class="w-creator" id="creator-02"><p class="w-creator__project">02 / Space &amp; Identity<br>A Change of Perspective</p><div class="w-creator__copy"><h3>白瀬 悠 <span>Yu Shirase</span></h3><p>グラフィックデザイナー。人と場所の関係を観察しながら、アイデンティティ、サイン、エディトリアルを横断する活動を続けています。巡りの杜では、移動のリズムや建築の線をもとにしたビジュアルシステムを設計。機能のなかに、小さな発見のきっかけを織り込んでいます。</p><a class="w-text-link" href="#project-02">View project <span>↗</span></a></div><figure class="w-creator__portrait"><img src="<?php echo esc_url(get_theme_file_uri('images/works/creator-02.jpg')); ?>" alt="グラフィックデザイナー 白瀬悠のポートレート" width="600" height="800" loading="lazy"></figure></article>
      <article class="w-creator" id="creator-03"><p class="w-creator__project">03 / Interior &amp; Experience<br>A Space to Breathe</p><div class="w-creator__copy"><h3>間と景 <span>MA TO KEI</span></h3><p>空間と体験を考えるデザインスタジオ。自然の気配と人の振る舞いを丁寧に読み解き、心地よい居場所をつくります。凪の間では、コンセプトの立案と素材、色彩のディレクションを担当。働く人それぞれの過ごし方に寄り添う、穏やかな余白を設計しました。</p><a class="w-text-link" href="#project-03">View project <span>↗</span></a></div><figure class="w-creator__portrait"><img src="<?php echo esc_url(get_theme_file_uri('images/works/creator-03.jpg')); ?>" alt="間と景のメンバーのポートレート" width="600" height="800" loading="lazy"></figure></article>
      <?php endif; ?>
    </section>

    <a class="w-next" href="<?php echo esc_url(home_url('/service/')); ?>" aria-label="次のコンテンツ：Serviceへ">
      <div class="w-next__marquee js-distort" data-dir="-1" data-bg="#f2f1eb" data-fg="#191a17" data-accent="#191a17" aria-hidden="true"><span class="marquee__item">Next<em>.</em> Next. Next. Next. Next. Next.</span></div>
      <div class="w-next__meta"><span class="w-next__bar">● &nbsp; Service</span><span>Contents</span><span>–</span><span>№03</span><span class="w-next__label">Next ↗</span></div>
    </a>
  </main>

<?php get_footer(); ?>

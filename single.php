<?php
/**
 * お知らせの個別記事(news-20260820.html を元にしたテンプレート)
 * 記事は管理画面の「投稿」で書く。
 * - タイトル・公開日・カテゴリー(Award / Event / Works / Recruit)・アイキャッチ画像・本文を使う
 * - 編集画面の右の「画像の下のテキスト」が、画像の下の説明文になる(空なら画像の「キャプション」)
 */
get_header('news', array(
    'body_class'  => 'page-news-detail',
    'description' => wp_strip_all_tags(get_the_excerpt(get_queried_object_id())),
));

the_post();

$categories = get_the_category();
$category   = $categories ? $categories[0] : null;
$prev_post  = get_previous_post(); // 古い記事
$next_post  = get_next_post();     // 新しい記事

// 関連記事:同じカテゴリーの記事を優先し、足りなければ新しい記事で埋める(最大2件)
$related_ids = array();
if ($category) {
    $related_ids = get_posts(array(
        'fields'         => 'ids',
        'posts_per_page' => 2,
        'cat'            => $category->term_id,
        'post__not_in'   => array(get_the_ID()),
    ));
}
if (count($related_ids) < 2) {
    $related_ids = array_merge($related_ids, get_posts(array(
        'fields'         => 'ids',
        'posts_per_page' => 2 - count($related_ids),
        'post__not_in'   => array_merge(array(get_the_ID()), $related_ids),
    )));
}
?>

  <main class="ndetail">
    <article class="inner">

      <!-- パンくず -->
      <nav aria-label="現在地">
        <ol class="ncrumb">
          <li><a href="<?php echo esc_url(home_url('/')); ?>">Top</a></li>
          <li><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
          <li aria-current="page"><?php the_title(); ?></li>
        </ol>
      </nav>

      <!-- 見出し -->
      <header class="nhead">
        <p class="nhead__meta"><time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time><?php if ($category) : ?><span class="nhead__tag"><?php echo esc_html($category->name); ?></span><?php endif; ?></p>
        <h1 class="nhead__title"><?php the_title(); ?></h1>
        <div class="nhead__rule" aria-hidden="true"></div>
      </header>

      <!-- 本文(アイキャッチ画像がないときは、文章だけを1列で表示) -->
      <div class="nbody<?php echo has_post_thumbnail() ? '' : ' nbody--noimage'; ?>">
        <?php if (has_post_thumbnail()) : ?>
        <figure class="nbody__media js-up">
          <?php the_post_thumbnail('large'); ?>
          <?php
          // 画像の下のテキスト:編集画面の「画像の下のテキスト」を優先し、空なら画像のキャプションを使う
          $image_caption = get_post_meta(get_the_ID(), 'aa6_image_caption', true);
          if ($image_caption === '') {
              $image_caption = get_the_post_thumbnail_caption();
          }
          ?>
          <?php if ($image_caption !== '') : ?>
          <figcaption><?php echo nl2br(esc_html($image_caption)); ?></figcaption>
          <?php endif; ?>
        </figure>
        <?php endif; ?>

        <div class="ntext ntext--editor">
          <?php the_content(); ?>
        </div>
      </div>

      <!-- 前後の記事と一覧 -->
      <nav class="npager js-up" aria-label="記事の移動">
        <?php if ($prev_post) : ?>
        <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" class="npager__item npager__item--prev">
          <small>← Prev</small>
          <?php echo esc_html(get_the_title($prev_post)); ?>
        </a>
        <?php else : ?>
        <span class="npager__item npager__item--prev is-disabled" aria-disabled="true"><small>← Prev</small></span>
        <?php endif; ?>
        <a href="<?php echo esc_url(home_url('/news/')); ?>" class="npager__all">ALL</a>
        <?php if ($next_post) : ?>
        <a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="npager__item npager__item--next">
          <small>Next →</small>
          <?php echo esc_html(get_the_title($next_post)); ?>
        </a>
        <?php else : ?>
        <span class="npager__item npager__item--next is-disabled" aria-disabled="true"><small>Next →</small></span>
        <?php endif; ?>
      </nav>

      <?php if ($related_ids) : ?>
      <!-- 関連記事(一覧ページと同じ行の見た目) -->
      <section class="nrelated">
        <h2 class="nrelated__title js-up">Related</h2>
        <ul class="nlist__rows">
          <?php foreach ($related_ids as $related_id) :
              $related_cats = get_the_category($related_id);
              $related_cat  = $related_cats ? $related_cats[0] : null;
          ?>
          <li class="nrow" data-cat="<?php echo esc_attr($related_cat ? $related_cat->slug : ''); ?>"><a href="<?php echo esc_url(get_permalink($related_id)); ?>">
            <time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $related_id)); ?>"><?php echo esc_html(get_the_date('Y.m.d', $related_id)); ?></time><span class="nrow__tag"><?php echo esc_html($related_cat ? $related_cat->name : ''); ?></span>
            <p class="nrow__title"><?php echo esc_html(get_the_title($related_id)); ?></p><span class="nrow__arrow">→</span>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </section>
      <?php endif; ?>
    </article>
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

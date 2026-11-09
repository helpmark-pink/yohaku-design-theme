<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Yohaku designの制作実績。視点をひらき、日常の風景を変える、3つのデザインストーリー。">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri('images/favicon.svg')); ?>" type="image/svg+xml">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri('images/favicon-32.png')); ?>" sizes="32x32" type="image/png">
  <link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_file_uri('images/apple-touch-icon.png')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=Big+Shoulders+Display:wght@300;900&family=Zen+Kaku+Gothic+New:wght@400&display=swap" rel="stylesheet">
  <title><?php echo esc_html(wp_get_document_title()); ?></title>
  <?php wp_head(); ?>
</head>
<body <?php body_class('page-works'); ?>>
<?php wp_body_open(); ?>

<a class="w-skip" href="#works-content">本文へスキップ</a>
<header class="w-header">
  <a class="w-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Yohaku design トップページ">Yohaku<span>design</span></a>
  <nav class="w-nav" aria-label="メインナビゲーション">
    <a href="<?php echo esc_url(home_url('/about/')); ?>">About</a>
    <a href="<?php echo esc_url(home_url('/works/')); ?>" aria-current="page">Works</a>
    <a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a>
    <a href="<?php echo esc_url(home_url('/news/')); ?>">News</a>
  </nav>
  <a class="w-nav__contact" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact <span>↗</span></a>
  <button class="w-menu-button" type="button" aria-label="メニューを開く" aria-expanded="false" aria-controls="works-menu"><span></span><span></span></button>
</header>

<!-- タブレット・スマホ用メニュー(トップと同じ見た目と動き。menu-common.css) -->
<nav class="w-menu" id="works-menu" aria-label="モバイルナビゲーション" inert>
  <ul class="menu__list">
    <li><a href="<?php echo esc_url(home_url('/')); ?>">Top</a></li>
    <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></li>
    <li><a href="<?php echo esc_url(home_url('/works/')); ?>" aria-current="page">Works</a></li>
    <li><a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a></li>
    <li><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
    <li class="menu__contact"><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact<span class="menu__arrow">↗</span></a></li>
  </ul>
  <span class="menu__rule"></span>
  <div class="menu__brand">
    <svg viewBox="0 0 64 64" aria-hidden="true"><path d="M8 56V16A40 40 0 0 1 48 56Z" fill="currentColor"/><circle cx="50" cy="14" r="8" fill="currentColor"/></svg>
    <p>Yohaku<br>Design<br>Aimachi</p>
  </div>
</nav>

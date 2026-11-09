<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri('images/favicon.svg')); ?>" type="image/svg+xml">
  <link rel="icon" href="<?php echo esc_url(get_theme_file_uri('images/favicon-32.png')); ?>" sizes="32x32" type="image/png">
  <link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_file_uri('images/apple-touch-icon.png')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=Big+Shoulders+Display:wght@300;800;900&family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,600;1,6..96,400;1,6..96,600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet">
  <title><?php echo esc_html(wp_get_document_title()); ?></title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="loader" id="loader">
  <div class="loader__content">
    <p class="loader__logo">
      <svg class="loader__mark" viewBox="0 0 64 64" aria-hidden="true">
        <path d="M8 56V16A40 40 0 0 1 48 56Z" fill="currentColor"/>
        <circle cx="50" cy="14" r="8" fill="#DCA62A"/>
      </svg>
      <span>Yohaku<span class="loader__design">design</span></span>
    </p>
    <div class="loader__progress" id="loaderProgress" role="progressbar" aria-label="ページを読み込み中" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
      <svg class="loader__circle" viewBox="0 0 100 100" aria-hidden="true">
        <circle cx="50" cy="50" r="46" class="loader__track"/>
        <circle cx="50" cy="50" r="46" class="loader__bar" id="loaderBar"/>
      </svg>
      <p class="loader__num" aria-hidden="true"><span id="loaderNum">0</span>%</p>
    </div>
  </div>
</div>

<header class="header">
  <a href="<?php echo esc_url(home_url('/#hero')); ?>" class="header__logo">Yohaku<span>design</span></a>
  <nav class="header__nav" aria-label="メインナビゲーション">
    <a href="<?php echo esc_url(home_url('/about/')); ?>">About</a>
    <a href="<?php echo esc_url(home_url('/works/')); ?>">Works</a>
    <a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a>
    <a href="<?php echo esc_url(home_url('/news/')); ?>">News</a>
  </nav>
  <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="header__contact">Contact <span aria-hidden="true">↗</span></a>
  <button class="menu-btn" id="menuBtn" type="button" aria-label="メニューを開く" aria-expanded="false" aria-controls="menu">
    <span></span><span></span>
  </button>
</header>

<div class="menu" id="menu" aria-hidden="true">
  <ul class="menu__list">
    <li><a href="<?php echo esc_url(home_url('/#hero')); ?>">Top</a></li>
    <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></li>
    <li><a href="<?php echo esc_url(home_url('/works/')); ?>">Works</a></li>
    <li><a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a></li>
    <li><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
    <li class="menu__contact">
      <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact<span class="menu__arrow" aria-hidden="true">↗</span></a>
    </li>
  </ul>
  <span class="menu__rule"></span>
  <div class="menu__brand">
    <svg viewBox="0 0 64 64" aria-hidden="true">
      <path d="M8 56V16A40 40 0 0 1 48 56Z" fill="currentColor"/>
      <circle cx="50" cy="14" r="8" fill="currentColor"/>
    </svg>
    <p>Yohaku<br>Design<br>Aimachi</p>
  </div>
</div>

<footer class="footer">
  <div class="footer__bg" id="footerBg" aria-hidden="true"></div>
  <div class="footer__top">
    <div>
      <p class="footer__logo">
        <a href="<?php echo esc_url(home_url('/#hero')); ?>" aria-label="Yohaku design トップページへ">
          <svg class="footer__mark" viewBox="0 0 64 64" aria-hidden="true">
            <path d="M8 56V16A40 40 0 0 1 48 56Z" fill="currentColor"/>
            <circle cx="50" cy="14" r="8" fill="#DCA62A"/>
          </svg>
          Yohaku<span>design</span>
        </a>
      </p>
      <?php
      // 郵便番号・住所・メールアドレスは、管理画面の左メニュー「連絡先」で変更する(functions.php の aa6_contact)
      $postal  = aa6_contact('postal');
      $address = aa6_contact('address');
      $email   = aa6_contact('email');

      $footer_lines = array();
      if ($postal !== '') {
          $footer_lines[] = '〒' . esc_html($postal);
      }
      if ($address !== '') {
          $footer_lines[] = nl2br(esc_html($address), false);
      }
      if ($email !== '') {
          $footer_lines[] = '<a href="' . esc_url('mailto:' . $email) . '">' . esc_html($email) . '</a>';
      }
      ?>
      <?php if ($footer_lines) : ?>
      <p class="footer__address">
        <?php echo implode("<br>\n        ", $footer_lines); ?>
      </p>
      <?php endif; ?>
    </div>
    <ul class="footer__nav">
      <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About</a></li>
      <li><a href="<?php echo esc_url(home_url('/works/')); ?>">Works</a></li>
      <li><a href="<?php echo esc_url(home_url('/service/')); ?>">Service</a></li>
      <li><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a></li>
      <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li>
    </ul>
    <ul class="footer__sns">
      <li><a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer">Instagram</a></li>
      <li><a href="https://x.com/" target="_blank" rel="noopener noreferrer">X</a></li>
      <li><a href="https://www.behance.net/" target="_blank" rel="noopener noreferrer">Behance</a></li>
    </ul>
  </div>
  <div class="footer__bottom">
    <a href="<?php echo esc_url(home_url('/contact/#privacy')); ?>">Privacy Policy</a>
    <p>&copy; <span id="year"><?php echo esc_html(wp_date('Y')); ?></span> Yohaku design</p>
    <button class="to-top" id="toTop" type="button">Page Top ↑</button>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

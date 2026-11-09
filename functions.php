<?php

function aa6_enqueue_theme_assets() {
    $styles = array(
        'aa6-style'             => 'style.css',
        'aa6-menu-common'       => 'menu-common.css',
        'aa6-contact-style'     => 'contact.css',
        'aa6-news-style'        => 'news.css',
        'aa6-news-detail-style' => 'news-detail.css',
    );

    // About は about.css だけで完結するので、ほかのページの CSS は読み込まない(about.html と同じ構成)。
    if (is_page('about')) {
        $styles = array(
            'aa6-about-style' => 'about.css',
            'aa6-menu-common' => 'menu-common.css',
        );
    }

    // Works も works.css だけで完結する(works.html と同じ構成)。
    if (is_page('works')) {
        $styles = array(
            'aa6-works-fonts' => 'vendor/works-fonts.css',
            'aa6-works-style' => 'works.css',
            'aa6-menu-common' => 'menu-common.css',
        );
    }

    // Service は works.css を土台に service.css を重ねる(service.html と同じ構成)。
    if (is_page('service')) {
        $styles = array(
            'aa6-works-fonts'   => 'vendor/works-fonts.css',
            'aa6-works-style'   => 'works.css',
            'aa6-service-style' => 'service.css',
            'aa6-menu-common'   => 'menu-common.css',
        );
    }

    // News は style.css に news.css を重ねる(news.html と同じ構成)。
    if (is_page('news')) {
        $styles = array(
            'aa6-style'      => 'style.css',
            'aa6-news-style' => 'news.css',
        );
    }

    // お知らせの個別記事は style.css に news.css と news-detail.css を重ねる(news-20260820.html と同じ構成)。
    if (is_singular('post')) {
        $styles = array(
            'aa6-style'             => 'style.css',
            'aa6-news-style'        => 'news.css',
            'aa6-news-detail-style' => 'news-detail.css',
        );
    }

    // Contact は style.css に contact.css を重ねる(contact.html と同じ構成)。
    if (is_page('contact')) {
        $styles = array(
            'aa6-style'         => 'style.css',
            'aa6-contact-style' => 'contact.css',
        );
    }

    foreach ($styles as $handle => $file) {
        wp_enqueue_style(
            $handle,
            get_theme_file_uri($file),
            array(),
            filemtime(get_theme_file_path($file))
        );
    }

    wp_enqueue_script(
        'aa6-p5',
        'https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.9.4/p5.min.js',
        array(),
        '1.9.4',
        true
    );

    // 慣性スクロール(Lenis)は使わない。戻すときは lenis.min.js をここで読み込むだけで、各ページの JS が使い始める

    // News の動きは news.js だけで行う(main.js はトップ用の処理が走るので読み込まない)。
    if (is_page('news')) {
        wp_enqueue_script(
            'aa6-news',
            get_theme_file_uri('news.js'),
            array('aa6-p5'),
            filemtime(get_theme_file_path('news.js')),
            true
        );
        return;
    }

    // お知らせの個別記事の動きは news-detail.js だけで行う(p5.js も使わない)。
    if (is_singular('post')) {
        wp_dequeue_script('aa6-p5');
        wp_enqueue_script(
            'aa6-news-detail',
            get_theme_file_uri('news-detail.js'),
            array(),
            filemtime(get_theme_file_path('news-detail.js')),
            true
        );
        return;
    }

    // Contact の動きは contact.js だけで行う(p5.js も使わない)。
    if (is_page('contact')) {
        wp_dequeue_script('aa6-p5');
        wp_enqueue_script(
            'aa6-contact',
            get_theme_file_uri('contact.js'),
            array(),
            filemtime(get_theme_file_path('contact.js')),
            true
        );
        return;
    }

    wp_enqueue_script(
        'aa6-three',
        get_theme_file_uri('vendor/three.min.js'),
        array(),
        filemtime(get_theme_file_path('vendor/three.min.js')),
        true
    );
    wp_enqueue_script(
        'aa6-works',
        get_theme_file_uri('works.js'),
        array('aa6-three'),
        filemtime(get_theme_file_path('works.js')),
        true
    );
    wp_enqueue_script(
        'aa6-main',
        get_theme_file_uri('main.js'),
        array('aa6-three', 'aa6-p5', 'aa6-works'),
        filemtime(get_theme_file_path('main.js')),
        true
    );

    if (is_page('service')) {
        wp_enqueue_script(
            'aa6-service',
            get_theme_file_uri('service.js'),
            array(),
            filemtime(get_theme_file_path('service.js')),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'aa6_enqueue_theme_assets');

// お知らせの記事でアイキャッチ画像を使えるようにする
function aa6_theme_setup() {
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'aa6_theme_setup');

// 投稿の編集画面に「画像の下のテキスト」の入力欄を追加(記事ページでアイキャッチ画像の下に表示する)
function aa6_add_image_caption_box() {
    add_meta_box('aa6_image_caption', '画像の下のテキスト', 'aa6_render_image_caption_box', 'post', 'side');
}
add_action('add_meta_boxes', 'aa6_add_image_caption_box');

function aa6_render_image_caption_box($post) {
    wp_nonce_field('aa6_image_caption', 'aa6_image_caption_nonce');
    ?>
    <textarea name="aa6_image_caption" rows="3" style="width:100%"><?php echo esc_textarea(get_post_meta($post->ID, 'aa6_image_caption', true)); ?></textarea>
    <p class="description">アイキャッチ画像の下に表示されます。空のときは、画像の「キャプション」を表示します。</p>
    <?php
}

function aa6_save_image_caption($post_id) {
    if (!isset($_POST['aa6_image_caption_nonce']) || !wp_verify_nonce($_POST['aa6_image_caption_nonce'], 'aa6_image_caption')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $caption = isset($_POST['aa6_image_caption']) ? sanitize_textarea_field(wp_unslash($_POST['aa6_image_caption'])) : '';
    if ($caption === '') {
        delete_post_meta($post_id, 'aa6_image_caption');
    } else {
        update_post_meta($post_id, 'aa6_image_caption', $caption);
    }
}
add_action('save_post_post', 'aa6_save_image_caption');

/* ==========================================================
   連絡先(郵便番号・住所・メールアドレス)
   - 値は管理画面の左メニュー「連絡先」で変更する(オプション aa6_contact に保存)
   - テンプレートでは aa6_contact('postal') / aa6_contact('address') / aa6_contact('email') で呼び出す
   - 投稿の本文などでは [aa6_postal] / [aa6_address] / [aa6_email] と書くと、その値に置きかわる
   ========================================================== */
function aa6_contact_defaults() {
    return array(
        'postal'  => '000-0000',
        'address' => '東京都余白区間町 2-5-8 余白ビル 3F',
        'email'   => 'hello@yohaku-design.example',
    );
}

// 連絡先の値を返す。$key は 'postal'(郵便番号)/ 'address'(住所)/ 'email'(メールアドレス)
function aa6_contact($key) {
    $defaults = aa6_contact_defaults();
    if (!isset($defaults[$key])) {
        return '';
    }
    $saved = get_option('aa6_contact');
    if (is_array($saved) && isset($saved[$key])) {
        return (string) $saved[$key];
    }
    // 「連絡先」でまだ保存していないときは、前に「カスタマイズ」で入れた値、それもなければ初期値
    return (string) get_theme_mod('aa6_footer_' . $key, $defaults[$key]);
}

// ショートコード:[aa6_postal] → 000-0000 / [aa6_address] → 住所(改行もそのまま)/ [aa6_email] → メールのリンク
// [aa6_email link="no"] と書くと、リンクにせず文字だけ出す
function aa6_shortcode_postal() {
    return esc_html(aa6_contact('postal'));
}
add_shortcode('aa6_postal', 'aa6_shortcode_postal');

function aa6_shortcode_address() {
    return nl2br(esc_html(aa6_contact('address')), false);
}
add_shortcode('aa6_address', 'aa6_shortcode_address');

function aa6_shortcode_email($atts) {
    $atts  = shortcode_atts(array('link' => 'yes'), $atts, 'aa6_email');
    $email = aa6_contact('email');
    if ($email === '' || $atts['link'] === 'no') {
        return esc_html($email);
    }
    return '<a href="' . esc_url('mailto:' . $email) . '">' . esc_html($email) . '</a>';
}
add_shortcode('aa6_email', 'aa6_shortcode_email');

// 管理画面の左メニューに「連絡先」の設定画面を追加
function aa6_add_contact_page() {
    add_menu_page('連絡先', '連絡先', 'manage_options', 'aa6-contact', 'aa6_render_contact_page', 'dashicons-location', 61);
}
add_action('admin_menu', 'aa6_add_contact_page');

function aa6_register_contact_setting() {
    register_setting('aa6_contact', 'aa6_contact', array(
        'type'              => 'array',
        'sanitize_callback' => 'aa6_sanitize_contact',
    ));
}
add_action('admin_init', 'aa6_register_contact_setting');

function aa6_sanitize_contact($input) {
    $input = is_array($input) ? $input : array();
    return array(
        'postal'  => isset($input['postal']) ? sanitize_text_field($input['postal']) : '',
        'address' => isset($input['address']) ? sanitize_textarea_field($input['address']) : '',
        'email'   => isset($input['email']) ? sanitize_email($input['email']) : '',
    );
}

function aa6_render_contact_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
      <h1>連絡先</h1>
      <p>全ページのフッターに表示する郵便番号・住所・メールアドレスです。空にした項目は表示しません。<br>
      投稿の本文では <code>[aa6_postal]</code> <code>[aa6_address]</code> <code>[aa6_email]</code> と書くと、ここの値が入ります。</p>
      <form method="post" action="options.php">
        <?php settings_fields('aa6_contact'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="aa6_contact_postal">郵便番号</label></th>
            <td>
              <input type="text" id="aa6_contact_postal" name="aa6_contact[postal]" value="<?php echo esc_attr(aa6_contact('postal')); ?>" class="regular-text">
              <p class="description">「〒」はフッターで自動で付きます(例:000-0000)</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="aa6_contact_address">住所</label></th>
            <td>
              <textarea id="aa6_contact_address" name="aa6_contact[address]" rows="3" class="large-text"><?php echo esc_textarea(aa6_contact('address')); ?></textarea>
              <p class="description">改行すると、フッターでもその位置で改行します</p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="aa6_contact_email">メールアドレス</label></th>
            <td><input type="email" id="aa6_contact_email" name="aa6_contact[email]" value="<?php echo esc_attr(aa6_contact('email')); ?>" class="regular-text"></td>
          </tr>
        </table>
        <?php submit_button('保存する'); ?>
      </form>
    </div>
    <?php
}

// Works ページの3つのプロジェクト(番号 => [見出し, 英語のタイトル])。Creators の「担当プロジェクト」で使う
function aa6_works_projects() {
    return array(
        1 => array('01 / Art & Communication', 'Beyond the Ordinary'),
        2 => array('02 / Space & Identity', 'A Change of Perspective'),
        3 => array('03 / Interior & Experience', 'A Space to Breathe'),
    );
}

// Works ページの Creators を管理画面の「クリエイター」で入力できるようにする
// - タイトル → 名前 / 本文 → 紹介文 / アイキャッチ画像 → ポートレート(縦長 3:4)/ 順序 → 並び順
function aa6_register_creator_post_type() {
    register_post_type('creator', array(
        'labels'       => array(
            'name'          => 'クリエイター',
            'singular_name' => 'クリエイター',
            'add_new'       => '新規追加',
            'add_new_item'  => 'クリエイターを追加',
            'edit_item'     => 'クリエイターを編集',
            'all_items'     => 'クリエイター一覧',
        ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_rest' => true,
        'menu_position'=> 6,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => array('title', 'editor', 'thumbnail', 'page-attributes'),
    ));
}
add_action('init', 'aa6_register_creator_post_type');

// クリエイターの編集画面に「担当プロジェクト」と「名前の補足」の入力欄を追加
function aa6_add_creator_box() {
    add_meta_box('aa6_creator', 'クリエイターの情報', 'aa6_render_creator_box', 'creator', 'side');
}
add_action('add_meta_boxes', 'aa6_add_creator_box');

function aa6_render_creator_box($post) {
    wp_nonce_field('aa6_creator', 'aa6_creator_nonce');
    $project = (int) get_post_meta($post->ID, 'aa6_creator_project', true);
    ?>
    <p><label for="aa6_creator_project"><b>担当プロジェクト</b></label></p>
    <select name="aa6_creator_project" id="aa6_creator_project" style="width:100%">
      <option value="0">(なし)</option>
      <?php foreach (aa6_works_projects() as $num => $project_label) : ?>
      <option value="<?php echo esc_attr($num); ?>" <?php selected($project, $num); ?>><?php echo esc_html($project_label[0] . ' / ' . $project_label[1]); ?></option>
      <?php endforeach; ?>
    </select>
    <p class="description">Works ページの各プロジェクトの「Creative partner」から、このクリエイターへ移動します。</p>
    <p><label for="aa6_creator_sub"><b>名前の補足</b></label></p>
    <input type="text" name="aa6_creator_sub" id="aa6_creator_sub" value="<?php echo esc_attr(get_post_meta($post->ID, 'aa6_creator_sub', true)); ?>" style="width:100%">
    <p class="description">名前の横に小さく表示されます(例:Yu Shirase / （ヒカリスタジオ）)。</p>
    <?php
}

function aa6_save_creator($post_id) {
    if (!isset($_POST['aa6_creator_nonce']) || !wp_verify_nonce($_POST['aa6_creator_nonce'], 'aa6_creator')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $project = isset($_POST['aa6_creator_project']) ? (int) $_POST['aa6_creator_project'] : 0;
    update_post_meta($post_id, 'aa6_creator_project', array_key_exists($project, aa6_works_projects()) ? $project : 0);
    $sub = isset($_POST['aa6_creator_sub']) ? sanitize_text_field(wp_unslash($_POST['aa6_creator_sub'])) : '';
    update_post_meta($post_id, 'aa6_creator_sub', $sub);
}
add_action('save_post_creator', 'aa6_save_creator');

// リストブロックに「ポイント」スタイルを追加(記事ページで 01 / 02 … の番号付きリストになる)
function aa6_register_block_styles() {
    register_block_style('core/list', array(
        'name'  => 'points',
        'label' => 'ポイント',
    ));
}
add_action('init', 'aa6_register_block_styles');

<?php
/**
 * WordPress Playground のデモ用コンテンツ
 *
 * _playground/blueprint.json の runPHP ステップから読み込まれ、
 * 固定ページ・お知らせ8件・クリエイター3人・連絡先・記事のひな形を登録します。
 * 通常のテーマ表示では読み込まれません。
 *
 * @package aa6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 2回目以降は何もしない
if ( get_page_by_path( 'works' ) ) {
	return;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * テーマ内の写真をメディアライブラリへ登録し、IDを返す
 *
 * @param string $relative_img テーマフォルダからの相対パス。
 * @param int    $parent_id    添付先の投稿ID。
 * @return int 添付ファイルのID（失敗したら0）。
 */
function aa6_demo_add_image( $relative_img, $parent_id ) {
	$source = get_template_directory() . '/' . $relative_img;
	if ( ! file_exists( $source ) ) {
		return 0;
	}

	$filename = basename( $relative_img );
	$upload   = wp_upload_bits( $filename, null, file_get_contents( $source ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$filetype  = wp_check_filetype( $upload['file'], null );
	$attach_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => pathinfo( $filename, PATHINFO_FILENAME ),
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$parent_id
	);
	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );
	return (int) $attach_id;
}

// 本文の「/contact/」などのリンクを、デモの環境のURLに合わせる
function aa6_demo_fix_links( $content ) {
	return preg_replace_callback(
		'/href="(\/[^"]*)"/',
		function ( $m ) {
			return 'href="' . esc_url( home_url( $m[1] ) ) . '"';
		},
		$content
	);
}

/* ------------------------------------------------------------------
   最初から入っているサンプルの投稿と固定ページを消す
   ------------------------------------------------------------------ */
foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $aa6_sample ) {
	$aa6_sample_post = get_page_by_path( $aa6_sample[0], OBJECT, $aa6_sample[1] );
	if ( $aa6_sample_post ) {
		wp_delete_post( $aa6_sample_post->ID, true );
	}
}

/* ------------------------------------------------------------------
   固定ページ
   中身はテンプレート（page-works.php など）が持っているので、
   スラッグの合ったページを作るだけでよい。トップページは index.php。
   ------------------------------------------------------------------ */
foreach ( array( 'about' => 'About', 'works' => 'Works', 'service' => 'Service', 'news' => 'News', 'contact' => 'Contact' ) as $aa6_slug => $aa6_title ) {
	wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => $aa6_slug,
			'post_title'  => $aa6_title,
		)
	);
}

/* ------------------------------------------------------------------
   お知らせのカテゴリー
   ------------------------------------------------------------------ */
$aa6_cat_ids = array();
foreach ( array( 'award' => 'Award', 'event' => 'Event', 'works' => 'Works', 'recruit' => 'Recruit' ) as $aa6_slug => $aa6_name ) {
	$aa6_term = term_exists( $aa6_slug, 'category' );
	if ( ! $aa6_term ) {
		$aa6_term = wp_insert_term( $aa6_name, 'category', array( 'slug' => $aa6_slug ) );
	}
	if ( ! is_wp_error( $aa6_term ) ) {
		$aa6_cat_ids[ $aa6_slug ] = (int) $aa6_term['term_id'];
	}
}

/* ------------------------------------------------------------------
   お知らせ 8件（Local で作った記事と同じ内容）
   ------------------------------------------------------------------ */
$aa6_news = array(
	array(
		'title'   => '2027年度 新卒デザイナーの会社説明会を開催します',
		'slug'    => 'news1',
		'date'    => '2026-03-01 13:24:00',
		'cat'     => 'recruit',
		'image'   => '_playground/images/news-20260301.jpg',
		'caption' => '説明会の会場となる、余白ビル3階のスタジオ',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">Yohaku design では、2027年3月に卒業予定の方を対象に、新卒デザイナーの会社説明会を開催します。間町のスタジオで、私たちのふだんの仕事をそのままお見せします。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>説明会といっても、スライドを見ていただくだけの会ではありません。実際に使っている机やスケッチ、制作中のデータを前にしながら、どんなふうに考え、どんなふうに削っているのかをお話しします。デザインを仕事にすることに少しでも興味がある方は、気軽に遊びに来てください。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">説明会でお話しすること</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>スタジオの一日</strong><br>朝の打ち合わせから、スケッチ、デザイン、実装まで。5人の小さなスタジオが、1日をどう過ごしているのかをご紹介します</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>ポートフォリオの見方</strong><br>ご希望の方には、その場でポートフォリオを拝見します。「どこを削るともっと伝わるか」を、一緒に考える時間にできればと思います。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>先輩スタッフとの座談会</strong><br>入社2年目のスタッフも参加します。働きはじめて驚いたこと、大変だったことなど、なんでも聞いてください。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">先輩スタッフからのメッセージ</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>学生のころは、とにかくたくさん描いて、たくさん見せることが大事だと思っていました。この仕事をはじめてからは、1枚にしぼる勇気のほうがずっと大切だと感じています。説明会では、その「しぼり方」を少しだけお見せできたらうれしいです。</p>
<!-- /wp:paragraph --><cite><strong>小野寺 圭</strong>　エディトリアルデザイナー</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">開催概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>日時</td><td>2026年3月21日(土)・4月11日(土)<br>各回 14:00〜16:00</td></tr><tr><td>会場</td><td>余白ビル 3F Yohaku design スタジオ<br>東京都余白区間町 2-5-8(オンライン参加も可)</td></tr><tr><td>対象</td><td>2027年3月に大学・大学院・専門学校を卒業予定の方</td></tr><tr><td>定員</td><td>各回10名(先着順)</td></tr><tr><td>参加費</td><td>無料</td></tr><tr><td>持ちもの</td><td>ポートフォリオ(任意・紙でもデータでも可)</td></tr><tr><td>申し込み方法</td><td>お問い合わせページから、「説明会参加希望」と参加したい日を添えてご連絡ください</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '「Lighthouse Café」のパッケージがPackage Form Prize 2026 で銀賞を受賞しました',
		'slug'    => 'news2',
		'date'    => '2026-04-10 14:50:00',
		'cat'     => 'award',
		'image'   => '_playground/images/news-20260410.jpg',
		'caption' => '受賞した「Lighthouse Café」のコーヒー豆パッケージ',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">Yohaku design がデザインを担当した「Lighthouse Café」のコーヒー豆パッケージが、パッケージデザインのアワード「Package Form Prize 2026」で銀賞を受賞しました。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Lighthouse Café(喫茶 灯台)は、余白ビルの1階にある小さな喫茶店です。店で焙煎した豆を持ち帰れるように、2025年の秋から豆の販売をはじめることになり、そのパッケージをデザインしました。「家に帰ってからも、店の静かな時間を思い出せる箱」を目標に、形・紙・色をひとつずつ決めていきました。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">評価されたポイント</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>豆の種類を「光の色」で分けた</strong><br>浅煎りは朝の光、中煎りは昼の光、深煎りは夜の灯台の光。文字を増やすかわりに、帯の色だけで違いがわかるようにしました。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>片手で開け閉めできる形</strong><br>毎朝使うものなので、留め具を使わず、ふたをずらすだけで開く形にしました。何度開け閉めしても、角がつぶれにくい構造です。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>捨てずに使いたくなる紙</strong><br>手ざわりのある再生紙を使い、印刷は1色だけ。豆を使い終わったあとも、小物入れとして残してもらえるよう、余計な情報はのせていません。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">担当者のコメント</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>最初の案では、灯台のイラストや店の説明をたくさん入れていました。何度も試作して、最後に残ったのは1本の線と、帯の色だけです。店主さんが「これなら毎朝見ても疲れない」と言ってくださったことが、受賞と同じくらいうれしかったです。</p>
<!-- /wp:paragraph --><cite><strong>間宮 しおり</strong> アートディレクター</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>受賞の概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>賞の名前</td><td>Package Form Prize 2026 銀賞</td></tr><tr><td>受賞作品</td><td>「Lighthouse Café」コーヒー豆パッケージ</td></tr><tr><td>クライアント</td><td>喫茶 灯台(Lighthouse Café)</td></tr><tr><td>担当</td><td>アートディレクション:間宮 しおり<br>パッケージデザイン:小野寺 圭</td></tr><tr><td>発売</td><td>2025年11月</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => 'トークイベント「削るデザイン、残すデザイン」に代表が登壇します',
		'slug'    => 'news3',
		'date'    => '2026-05-28 10:52:00',
		'cat'     => 'event',
		'image'   => '_playground/images/news-20260528-1.jpg',
		'caption' => '会場となる、間町区民ホールの多目的室',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">2026年6月13日(土)に開催されるトークイベント「削るデザイン、残すデザイン」に、Yohaku design 代表の野沢 透が登壇します。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>間町デザイン協会が毎年ひらいている「Design Talk Aimachi」の第7回です。今回は「情報があふれる時代に、何を削り、何を残すのか」をテーマに、デザイナーや編集者が、それぞれの現場での判断についてお話しします。私たちからは、小さなスタジオが街の仕事とどう向き合っているかをお話しする予定です。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">お話しする内容</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>余白は「何もない場所」ではない</strong><br>ページの余白も、スケジュールの余白も、考えるための大切な場所です。余白を「空いている場所」ではなく「設計する場所」として考える方法をご紹介します。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>削る判断の順番</strong><br>何から削り、何を最後まで残すのか。これまでの仕事を例に、クライアントと一緒に決めていく手順をお話しします。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>小さなスタジオが、街とつくる仕事</strong><br>喫茶店の看板から、まちのイベントのWebサイトまで。間町という街の中で、デザインがどのように使われているのかをお伝えします。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>登壇者より</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>デザインの仕事をしていると、「もっと足してほしい」と言われることのほうが多いかもしれません。それでも削るのは、本当に届けたいものを、ちゃんと届けるためです。当日は、うまくいった話だけでなく、削りすぎて失敗した話もお持ちします。</p>
<!-- /wp:paragraph --><cite>野沢 透  Yohaku design 代表</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">開催概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>イベント名</td><td>Design Talk Aimachi vol.7<br>「削るデザイン、残すデザイン」</td></tr><tr><td>日時</td><td>2026年6月13日(土)15:00〜16:30(開場 14:30)</td></tr><tr><td>会場</td><td>間町区民ホール 2F 多目的室<br>東京都余白区間町 3-1-1</td></tr><tr><td>登壇</td><td>野沢 透(Yohaku design 代表/クリエイティブディレクター)ほか</td></tr><tr><td>参加費</td><td>1,000円(学生は無料)</td></tr><tr><td>定員</td><td>60名(要予約)</td></tr><tr><td>主催</td><td>間町デザイン協会</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '季刊「間」第12号の誌面デザインとアートディレクションを担当しました',
		'slug'    => 'news4',
		'date'    => '2026-06-15 11:22:00',
		'cat'     => 'works',
		'image'   => '_playground/images/news-20260615.jpg',
		'caption' => '季刊「間」第12号 特集「手のひらの道具」',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">間町まちづくり協議会が発行する季刊誌「間」第12号の、誌面デザインとアートディレクションを担当しました。今号の特集は「手のひらの道具」です。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>季刊「間」は、間町で暮らす人や働く人を紹介する、無料のまちの雑誌です。第12号では、職人の路地の工房を訪ね、包丁やはさみ、筆など、手のひらにおさまる道具とその使い手を取材しました。私たちは創刊号から誌面づくりに関わっていて、今号で3年目になります。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>デザインのポイント</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>道具1つに、見開き1つ</strong><br>1ページにいくつも写真を並べるのではなく、道具1つにつき見開き1つを使いました。ページをめくるたびに、ひとつの道具とゆっくり向き合えるようにしています。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>余白で「読む速さ」を変える</strong><br>職人さんの言葉は大きな余白の中に短く置き、道具の説明は小さな文字でまとめて読めるように。誌面の中で、読む速さに緩急をつけました。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>2種類の紙を使い分けた</strong><br>写真のページはつやのある紙、インタビューのページは手ざわりのある紙に。手に取ったときに、内容の違いが指先でもわかるようにしています。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">担当者のコメント</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>取材で撮った写真は300枚以上ありました。その中から、道具の「使いこまれた跡」がいちばんよく見える1枚だけを選んでいます。たくさん載せたくなる気持ちをぐっとこらえた号なので、ぜひ紙で手に取っていただけたらうれしいです。</p>
<!-- /wp:paragraph --><cite>小野寺 圭  エディトリアルデザイナー</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>制作概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>媒体</td><td>季刊「間」第12号(特集:手のひらの道具)</td></tr><tr><td>発行</td><td>間町まちづくり協議会</td></tr><tr><td>仕様</td><td>B5変形・96ページ・無線綴じ</td></tr><tr><td>担当</td><td>アートディレクション : 間宮 しおり<br>エディトリアルデザイン : 小野寺 圭</td></tr><tr><td>発行日</td><td>2026年6月15日</td></tr><tr><td>配布場所</td><td>間町の書店・喫茶店・ギャラリーほか(無料)</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '「喫茶 灯台」のブランディング(ロゴ・パッケージ・店舗サイン)を公開しました',
		'slug'    => 'news5',
		'date'    => '2026-07-03 11:34:00',
		'cat'     => 'works',
		'image'   => '_playground/images/news-20260703.jpg',
		'caption' => '余白ビル1階、「喫茶 灯台」の入口',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">余白ビルの1階にある喫茶店「喫茶 灯台(Lighthouse Café)」の、ロゴ・パッケージ・店舗サイン・メニューまで、ブランディング全体の事例を公開しました。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>喫茶 灯台は、2025年の春にオープンした小さな喫茶店です。「街の中で、少しだけ立ち止まれる場所」という店主の想いを形にするため、オープンの半年前から一緒に準備を進めてきました。4月に銀賞をいただいたコーヒー豆のパッケージも、このブランディングの一部です。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">デザインのポイント</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>灯台の光を、1本の線で描いたロゴ</strong><br>灯台の形はあえて描かず、光が伸びる1本の線だけをロゴにしました。看板、カップ、パッケージなど、どんな大きさでも同じ印象で見えるようにしています。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>店名を大きく書かない看板</strong><br>入口の看板は、ロゴの線と小さな店名だけ。にぎやかな通りの中で、かえって目にとまる静かな看板を目指しました。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>季節で変わるメニュー表</strong><br>メニュー表は1枚の紙に、季節の飲みものだけを手書き風の文字で。季節ごとに紙の色を変え、常連さんにも小さな変化を楽しんでもらえるようにしました。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">店主のコメント</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>最初の打ち合わせで「何を伝えたいですか」ではなく「何を伝えなくていいですか」と聞かれたのをよく覚えています。おかげで、店に置くものを本当に好きなものだけにしぼることができました。看板を見て入ってきてくださるお客さんが、今も少しずつ増えています。</p>
<!-- /wp:paragraph --><cite>浜野 ひかる  喫茶 灯台 店主</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">【見出し3】概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>クライアント</td><td>喫茶 灯台(Lighthouse Café)</td></tr><tr><td>制作内容</td><td>ロゴ/パッケージ/店舗サイン/メニュー/ショップカード</td></tr><tr><td>担当</td><td>アートディレクション : 間宮 しおり<br>デザイン : 小野寺 圭<br>サイン設計 : 野沢 透</td></tr><tr><td>所在地</td><td>東京都余白区間町 2-5-8 余白ビル 1F</td></tr><tr><td>公開</td><td>2026年7月</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => 'Webデザイナー(正社員・業務委託)の募集を開始しました',
		'slug'    => 'news6',
		'date'    => '2026-08-20 13:05:00',
		'cat'     => 'recruit',
		'image'   => 'images/news-3.jpg',
		'caption' => '間町のスタジオでの作業風景',
		'content' => <<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">Yohaku design では、一緒に“余白”をつくってくれるWebデザイナーを募集しています。 正社員・業務委託のどちらでも、ご経験やご希望に合わせて働き方をご相談できます。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>私たちは、間町の小さなビルで活動する5人のデザインスタジオです。ロゴや印刷物、Webサイトまで、ブランドの「間」をクライアントと一緒に考えています。 Webの仕事が増えてきたことから、設計から実装まで、動きのある表現を一緒に磨いていける仲間を探しています。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">こんな方を歓迎します</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>動きのある表現が好きな方</strong><br>スクロールやホバーの動きを、飾りではなく「読むための道具」として考えられる方。アニメーションの実装経験があれば、なおうれしいです。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>読みやすさにこだわれる方</strong><br>文字の大きさや行間、余白の量を、1px単位で気にしてしまう方。見た目の美しさと読みやすさの両方を大切にしています。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong><strong>削ることを楽しめる方</strong></strong><br>アイデアを足すだけでなく、「本当に必要なものは何か」をクライアントと一緒に考え、手放す判断ができる方を歓迎します。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>スタッフからのメッセージ</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>Yohaku design では、デザインと実装を分けずに、同じ机で話しながらつくっています。 「この動きは本当に必要？」と何度も問い直す時間は大変ですが、そのぶん仕上がったときの気持ちよさは格別です。一緒に悩んでくれる方をお待ちしています。</p>
<!-- /wp:paragraph --><cite>白石 湊  Webデザイナー</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">募集要項</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>職種</td><td>Webデザイナー</td></tr><tr><td>雇用形態</td><td>正社員/業務委託</td></tr><tr><td>仕事内容<br></td><td>Webサイトの企画・情報設計・デザイン・実装(HTML / CSS / JavaScript)<br>アニメーションやインタラクションの設計</td></tr><tr><td>必須の経験</td><td>Webサイトのデザインまたは実装の実務経験(2年以上)</td></tr><tr><td>歓迎する経験</td><td>Figma でのデザイン制作/アニメーションの実装/印刷物のデザイン</td></tr><tr><td>勤務地<br></td><td>東京都余白区間町 2-5-8 余白ビル 3F<br>(週2日まで在宅勤務可)</td></tr><tr><td>給与</td><td>正社員:月給28万円〜(経験・能力を考慮)<br>業務委託:案件ごとにご相談</td></tr><tr><td>休日</td><td>土日祝・夏季休暇・年末年始休暇</td></tr><tr><td>選考の流れ</td><td>応募(ポートフォリオ提出)→ 面談(2回)→ 内定</td></tr><tr><td>応募方法</td><td>お問い合わせページから、ポートフォリオのURLを添えてご連絡ください。</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '間町のギャラリーにて、展示「余白のかたち」を開催します(10/18〜11/3)',
		'slug'    => 'news7',
		'date'    => '2026-09-12 13:29:00',
		'cat'     => 'event',
		'image'   => 'images/news-2.jpg',
		'caption' => '会場となる、間町ギャラリー「ま」',
		'content' => <<<'EOT'
<!-- wp:paragraph -->
<p>これまで手がけてきたロゴや冊子、Webサイトを、「何を削ったか」という視点で読み解く展示「余白のかたち」を、 2026年10月18日から11月3日まで、間町ギャラリー「ま」にて開催します。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>デザインの仕事は、完成したものだけを見ると「足した結果」に見えます。けれど実際には、その何倍もの案や言葉、色や線を手放すことで形が決まっていきます。 本展では、完成品とあわせて、そこに至るまでに削ったスケッチや没案を並べ、私たちが大切にしている“余白”の考え方をご紹介します。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">展示の見どころ</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>ロゴが決まるまでの100枚のスケッチ</strong><br>「Lighthouse Café」のロゴができるまでに描いた、すべてのスケッチを壁一面に展示します。少しずつ線が減っていく過程をご覧ください。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>季刊「間」の“削った”ページ</strong><br>誌面に載らなかった写真やレイアウト案を、完成した誌面と並べて展示。手に取って見比べることができます。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>スクロールの“間”を体験するWebコーナー</strong><br>「Aimachi Craft Week」公式サイトを大きな画面で操作しながら、動きと余白の設計を体験できるコーナーを設けます。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><br>ギャラリートーク</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>会期中の10月24日(土)14:00〜15:00に、アートディレクターの間宮 しおりと、エディトリアルデザイナーの小野寺 圭によるギャラリートークを行います。 展示作品の前で、削る判断をどのように重ねてきたのかをお話しします。参加無料・予約不要です。</p>
<!-- /wp:paragraph --></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>展示名</td><td>余白のかたち ― Yohaku design の仕事展</td></tr><tr><td>会期</td><td>2026年10月18日(日)〜11月3日(火・祝)</td></tr><tr><td>開館時間<br></td><td>11:00〜19:00(最終日は17:00まで)<br>月曜休廊(10/19・10/26・11/2)</td></tr><tr><td>会場</td><td>間町ギャラリー「ま」<br>東京都余白区間町 1-3-2</td></tr><tr><td>入場料<br></td><td>無料(予約不要)</td></tr><tr><td>主催</td><td>Yohaku design</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '「Aimachi Craft Week」公式サイトがDesign Grid Award 2026 に入選しました',
		'slug'    => 'news8',
		'date'    => '2026-10-01 13:42:03',
		'cat'     => 'award',
		'image'   => 'images/news-1.jpg',
		'caption' => 'Design Grid Award 2026 入選トロフィー',
		'content' => <<<'EOT'
<!-- wp:paragraph -->
<p>Yohaku design がデザインと制作を担当した「Aimachi Craft Week」公式サイトが、 Webデザインのアワード「Design Grid Award 2026」にて入選しました。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Aimachi Craft Week は、間町に点在する工房やアトリエが一斉に扉を開き、ものづくりの現場を見て・触れて・買うことができる、年に一度のイベントです。 私たちは、参加する40以上の工房それぞれの“間”を伝えることを目標に、情報設計からビジュアル、実装までを一貫して手がけました。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">評価されたポイント</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>写真の余白を、一から設計した</strong><br>工房ごとに異なる写真の明るさや構図をそろえるのではなく、それぞれの写真に合わせて周りの余白の量を変え、ページ全体の呼吸を整えました。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>文字の大きさで「歩く速さ」を表現した</strong><br>地図や時間割などの情報は小さく静かに、工房の言葉は大きくゆったりと。読む人が街を歩くような緩急をつけています。</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>スマホで「歩きながら見る」ことを前提にした</strong><br>当日はスマホで見る人がほとんど。片手で操作でき、今いる場所の近くの工房がすぐにわかる画面を優先してつくりました。</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">担当者のコメント</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>「何を載せるか」より「何を載せないか」を、工房のみなさんと何度も話し合いました。 情報を削るほど、一つひとつの手しごとの輪郭がはっきりしていく。その手ざわりを評価していただけたことが、何よりうれしいです。</p>
<!-- /wp:paragraph --><cite>白石 湊  Webデザイナー<br><br></cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">受賞の概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>賞の名前</td><td>Design Grid Award 2026 入選</td></tr><tr><td>受賞作品</td><td>「Aimachi Craft Week」公式サイト</td></tr><tr><td>クライアント</td><td>Aimachi Craft Week 実行委員会</td></tr><tr><td>担当</td><td>アートディレクション：間宮 しおり<br>Webデザイン・実装：白石 湊</td></tr><tr><td>公開</td><td>2026年4月</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact</a></p>
<!-- /wp:paragraph -->
EOT
	),
);

foreach ( $aa6_news as $aa6_item ) {
	$aa6_post_id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $aa6_item['title'],
			'post_name'    => $aa6_item['slug'],
			'post_date'    => $aa6_item['date'],
			'post_content' => aa6_demo_fix_links( $aa6_item['content'] ),
		)
	);
	if ( ! $aa6_post_id || is_wp_error( $aa6_post_id ) ) {
		continue;
	}

	if ( isset( $aa6_cat_ids[ $aa6_item['cat'] ] ) ) {
		wp_set_post_categories( $aa6_post_id, array( $aa6_cat_ids[ $aa6_item['cat'] ] ) );
	}

	$aa6_image_id = aa6_demo_add_image( $aa6_item['image'], $aa6_post_id );
	if ( $aa6_image_id ) {
		set_post_thumbnail( $aa6_post_id, $aa6_image_id );
	}

	if ( '' !== $aa6_item['caption'] ) {
		update_post_meta( $aa6_post_id, 'aa6_image_caption', $aa6_item['caption'] );
	}
}

/* ------------------------------------------------------------------
   クリエイター 3人（Works ページの Creators）
   ------------------------------------------------------------------ */
$aa6_creators = array(
	array(
		'title'   => 'HIKARI STUDIO',
		'order'   => 1,
		'project' => 1,
		'sub'     => '（ヒカリスタジオ）',
		'image'   => '_playground/images/creator-01.jpg',
		'content' => <<<'EOT'
<!-- wp:paragraph -->
<p>光と影、映像と空間の関係を探究するクリエイティブユニット。日々の小さな気づきを起点に、インスタレーションやビジュアル作品を制作しています。今回は、光環ギャラリーの空間表現とアートディレクションを担当。見る人の視点によって異なる印象が生まれる、開かれた表現を目指しました。</p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '白瀬 悠',
		'order'   => 2,
		'project' => 2,
		'sub'     => 'Yu Shirase',
		'image'   => '_playground/images/creator-02.jpg',
		'content' => <<<'EOT'
<!-- wp:paragraph -->
<p>グラフィックデザイナー。人と場所の関係を観察しながら、アイデンティティ、サイン、エディトリアルを横断する活動を続けています。巡りの杜では、移動のリズムや建築の線をもとにしたビジュアルシステムを設計。機能のなかに、小さな発見のきっかけを織り込んでいます。</p>
<!-- /wp:paragraph -->
EOT
	),
	array(
		'title'   => '間と景',
		'order'   => 3,
		'project' => 3,
		'sub'     => 'MA TO KEI',
		'image'   => '_playground/images/creator-03.jpg',
		'content' => <<<'EOT'
<!-- wp:paragraph -->
<p>空間と体験を考えるデザインスタジオ。自然の気配と人の振る舞いを丁寧に読み解き、心地よい居場所をつくります。凪の間では、コンセプトの立案と素材、色彩のディレクションを担当。働く人それぞれの過ごし方に寄り添う、穏やかな余白を設計しました</p>
<!-- /wp:paragraph -->
EOT
	),
);

foreach ( $aa6_creators as $aa6_item ) {
	$aa6_post_id = wp_insert_post(
		array(
			'post_type'    => 'creator',
			'post_status'  => 'publish',
			'post_title'   => $aa6_item['title'],
			'post_content' => $aa6_item['content'],
			'menu_order'   => $aa6_item['order'],
		)
	);
	if ( ! $aa6_post_id || is_wp_error( $aa6_post_id ) ) {
		continue;
	}

	$aa6_image_id = aa6_demo_add_image( $aa6_item['image'], $aa6_post_id );
	if ( $aa6_image_id ) {
		set_post_thumbnail( $aa6_post_id, $aa6_image_id );
	}

	update_post_meta( $aa6_post_id, 'aa6_creator_project', $aa6_item['project'] );
	update_post_meta( $aa6_post_id, 'aa6_creator_sub', $aa6_item['sub'] );
}

/* ------------------------------------------------------------------
   連絡先（管理画面の「連絡先」と同じ値）
   ------------------------------------------------------------------ */
update_option(
	'aa6_contact',
	array(
		'postal'  => '000-0000',
		'address' => '東京都余白区間町 2-5-8 余白ビル 3F',
		'email'   => 'hello@yohaku-design.example',
	)
);

/* ------------------------------------------------------------------
   記事のひな形（再利用ブロック）
   投稿の編集画面で「パターン」から呼び出せる
   ------------------------------------------------------------------ */
wp_insert_post(
	array(
		'post_type'    => 'wp_block',
		'post_status'  => 'publish',
		'post_title'   => 'ニュース記事のひな形',
		'post_content' => aa6_demo_fix_links(
			<<<'EOT'
<!-- wp:paragraph {"className":"ntext__lead"} -->
<p class="ntext__lead">【リード文】記事の要点を2〜3行で書きます。</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>【本文】くわしい説明を書きます。段落を増やすときは、この段落の最後で Enter を押します。</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">【見出し1】評価されたポイント</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"npoints"} -->
<ul class="wp-block-list npoints"><!-- wp:list-item -->
<li><strong>【ポイント1の見出し】</strong><br>【ポイント1の説明文】</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>【ポイント2の見出し】</strong><br>【ポイント2の説明文】</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>【ポイント3の見出し】</strong><br>【ポイント3の説明文】</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">【見出し2】担当者のコメント</h2>
<!-- /wp:heading -->

<!-- wp:quote {"className":"nquote"} -->
<blockquote class="wp-block-quote nquote"><!-- wp:paragraph -->
<p>【コメント本文】</p>
<!-- /wp:paragraph --><cite>【名前】(【役職】)</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">【見出し3】概要</h2>
<!-- /wp:heading -->

<!-- wp:table {"hasFixedLayout":false,"className":"ninfo"} -->
<figure class="wp-block-table ninfo"><table><tbody><tr><td>【項目1】</td><td>【内容1】</td></tr><tr><td>【項目2】</td><td>【内容2】</td></tr><tr><td>【項目3】</td><td>【内容3】</td></tr><tr><td>【項目4】</td><td>【内容4】</td></tr><tr><td>【項目5】</td><td>【内容5】</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {"className":"nlink-p"} -->
<p class="nlink-p"><a href="/contact/">Contact →</a></p>
<!-- /wp:paragraph -->
EOT
		),
	)
);

/* ------------------------------------------------------------------
   デモの表示設定
   サイト側の上部に出る管理バーを隠す。
   （ヘッダーを position: fixed で固定しているため、管理バーが重なって見た目が崩れる。
     管理画面は /wp-admin/ から開ける）
   ------------------------------------------------------------------ */
foreach ( get_users( array( 'fields' => 'ID' ) ) as $aa6_user_id ) {
	update_user_meta( $aa6_user_id, 'show_admin_bar_front', 'false' );
}

flush_rewrite_rules();

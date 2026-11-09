<?php
/**
 * Contact ページ(contact.html を元にしたテンプレート)
 */
get_header('contact');
?>

  <main class="cpage">
    <div class="cpage__inner">

      <!-- 見出し -->
      <div class="chead">
        <p class="chead__en">CONTACT</p>
        <h1 class="chead__title">お問い合わせ</h1>
        <div class="chead__rule" aria-hidden="true"></div>
      </div>

      <!-- 今どのステップか(JSで is-current を付けかえる) -->
      <ol class="csteps" id="cSteps" aria-label="お問い合わせの手順">
        <li class="is-current" aria-current="step"><span>入力</span></li>
        <li><span>確認</span></li>
        <li><span>完了</span></li>
      </ol>

      <!-- ==========================================================
           STEP 1:入力
           ========================================================== -->
      <section class="cstep" id="stepInput">
        <div class="clead">
          <p>
            下記項目にご入力後、「入力内容を確認する」ボタンをクリックしてください。<br>
            お問い合わせの内容によっては、返信に時間を要する場合や、返信をお断りすることがございます。<br>
            あらかじめご了承ください。
          </p>
          <p class="clead__req">＊必須の入力項目になります</p>
        </div>

        <!-- novalidate:ブラウザ標準の吹き出しではなく、JSで項目の下にエラーを出す -->
        <form class="cform" id="contactForm" novalidate>
          <!-- disabled:中の項目・チェック・ボタンをすべて入力できなくする(入力できるように戻すときは disabled を消す) -->
          <fieldset class="cform__set" disabled>
          <div class="cfield">
            <label class="cfield__label" for="fName">お名前<span class="req" aria-hidden="true">＊</span></label>
            <input class="cfield__input" type="text" id="fName" name="name" placeholder="お名前を入力してください"
              autocomplete="name" required aria-describedby="fNameErr" data-label="お名前">
            <p class="cfield__error" id="fNameErr" aria-live="polite"></p>
          </div>

          <div class="cfield">
            <label class="cfield__label" for="fKana">お名前（フリガナ）<span class="req" aria-hidden="true">＊</span></label>
            <input class="cfield__input" type="text" id="fKana" name="kana" placeholder="フリガナを入力してください"
              required aria-describedby="fKanaErr" data-label="お名前（フリガナ）" data-kana>
            <p class="cfield__error" id="fKanaErr" aria-live="polite"></p>
          </div>

          <div class="cfield">
            <label class="cfield__label" for="fOrg">ご所属</label>
            <input class="cfield__input" type="text" id="fOrg" name="organization" placeholder="会社名・学校名など"
              autocomplete="organization" data-label="ご所属">
          </div>

          <div class="cfield">
            <label class="cfield__label" for="fMail">メールアドレス<span class="req" aria-hidden="true">＊</span></label>
            <input class="cfield__input" type="email" id="fMail" name="email" placeholder="メールアドレスを入力してください"
              autocomplete="email" inputmode="email" required aria-describedby="fMailErr" data-label="メールアドレス">
            <input class="cfield__input" type="email" id="fMail2" name="email_confirm" placeholder="確認のため再度入力してください"
              autocomplete="off" inputmode="email" required aria-label="メールアドレス（確認用）" aria-describedby="fMailErr" data-match="fMail">
            <p class="cfield__error" id="fMailErr" aria-live="polite"></p>
          </div>

          <div class="cfield">
            <label class="cfield__label" for="fBody">お問い合わせ内容<span class="req" aria-hidden="true">＊</span></label>
            <!-- data-lenis-prevent:慣性スクロール中でも、この中だけは普通にスクロールできるようにする -->
            <textarea class="cfield__input" id="fBody" name="message" placeholder="お問い合わせ内容を入力してください"
              required maxlength="2000" aria-describedby="fBodyErr" data-label="お問い合わせ内容" data-lenis-prevent></textarea>
            <p class="cfield__error" id="fBodyErr" aria-live="polite"></p>
          </div>

          <!-- id="privacy":各ページのフッターの「Privacy Policy」からここへ飛ぶ -->
          <!-- 個人情報保護方針・利用条件のページはまだないので、リンクにせず太字で示している(ページを作ったら <a href="..."> にする) -->
          <p class="cprivacy" id="privacy">
            お預かりした個人情報は、お問い合わせに対する返信や連絡にのみ利用し、その他の目的で利用することはありません。
            <b>個人情報保護方針</b>および<b>Webサイト利用条件</b>をご確認のうえ、同意いただける場合は「同意する」にチェックをお願いします。
          </p>

          <label class="cagree">
            <input type="checkbox" id="fAgree" name="agree">
            <span class="cagree__box" aria-hidden="true"></span>
            同意する
          </label>

          <div class="cbtns">
            <!-- 「同意する」にチェックが入るまでは押せない -->
            <button type="submit" class="cbtn" id="toConfirm" disabled>入力内容を確認する <span>→</span></button>
          </div>
          </fieldset>
        </form>
      </section>

      <!-- ==========================================================
           STEP 2:確認
           ========================================================== -->
      <section class="cstep" id="stepConfirm" hidden>
        <div class="clead">
          <p>以下の内容でよろしければ、「送信する」ボタンをクリックしてください。</p>
        </div>
        <!-- 中身はJSで入力内容から作る -->
        <dl class="cconfirm" id="confirmList"></dl>
        <div class="cbtns">
          <button type="button" class="cbtn cbtn--ghost" id="backToInput"><span>←</span> 入力画面に戻る</button>
          <button type="button" class="cbtn" id="sendBtn">送信する <span>→</span></button>
        </div>
      </section>

      <!-- ==========================================================
           STEP 3:完了
           ========================================================== -->
      <section class="cstep" id="stepDone" hidden>
        <div class="cdone">
          <svg class="cdone__mark" viewBox="0 0 64 64" aria-hidden="true">
            <path d="M8 56V16A40 40 0 0 1 48 56Z" fill="var(--ink)"/>
            <circle cx="50" cy="14" r="8" fill="var(--mustard)"/>
          </svg>
          <p class="cdone__title">Thank <i>you.</i></p>
          <p class="cdone__text">
            お問い合わせを受け付けました。<br>
            内容を確認のうえ、3営業日以内に担当者よりご連絡いたします。
          </p>
          <div class="cbtns">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="cbtn">トップページへ <span>→</span></a>
          </div>
        </div>
      </section>

    </div>
  </main>

  <!-- フッター上のバナー(Works へのリンク) -->
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

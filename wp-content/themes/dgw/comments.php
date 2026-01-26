<div id="comments">
    <?php
    if (have_comments()) :
        global $comments_by_type;
        $comments_by_type = separate_comments($comments);
        if (! empty($comments_by_type['comment'])) :
    ?>
            <section id="comments-list" class="comments">
                <h3 class="comments-title"><?php comments_number(__('No comments yet'), __('1 comment'),  '% ' . __('comments')); ?></h3>
                <?php if (get_comment_pages_count() > 1) : ?>
                    <nav id="comments-nav-above" class="comments-navigation" role="navigation">
                        <div class="paginated-comments-links"><?php paginate_comments_links(); ?></div>
                    </nav>
                <?php endif; ?>
                <ul>
                    <?php wp_list_comments(array('type' => 'comment', 'callback' => 'my_custom_comment')); ?>
                    <?php //wp_list_comments(array('type' => 'comment')); 
                    ?>
                </ul>
                <?php if (get_comment_pages_count() > 1) : ?>
                    <nav id="comments-nav-below" class="comments-navigation" role="navigation">
                        <div class="paginated-comments-links"><?php paginate_comments_links(); ?></div>
                    </nav>
                <?php endif; ?>
            </section>
        <?php
        endif;
        if (! empty($comments_by_type['pings'])) :
            $ping_count = count($comments_by_type['pings']);
        ?>
            <section id="trackbacks-list" class="comments">
                <h3 class="comments-title"><?php echo '<span class="ping-count">' . esc_html($ping_count) . '</span> ' . esc_html(_nx('Trackback or Pingback', 'Trackbacks and Pingbacks', $ping_count, 'comments count', 'blankslate')); ?></h3>
                <ul>
                    <?php wp_list_comments('type=pings&callback=blankslate_custom_pings'); ?>
                </ul>
            </section>
    <?php
        endif;
    endif;
    if (comments_open()) {

        // 產生隨機混合題（加減乘）
        $a = rand(1, 10);
        $b = rand(1, 10);

        // 隨機選擇運算符號
        // $ops = ['+', '-', '×'];
        // $op = $ops[array_rand($ops)];
        // chi ap dụng 1 phép cộng 
        $op = '+';
        // 計算答案
        switch ($op) {
            case '+':
                $ans = $a + $b;
                break;
            case '-':
                $ans = $a - $b;
                break;
            case '×':
                $ans = $a * $b;
                break;
        }

        // 存進 Session 給後端驗證
        $_SESSION['comment_captcha_answer'] = $ans;
        $_SESSION['comment_captcha_question'] = "{$a} {$op} {$b}";

        // 題目文字
        $question = "{$a} {$op} {$b}";

        //======================================================================================================
        // 自訂欄位
        $fields = array(
            'author' => '<div class="form-row">
                    <label for="author"> ' . __('Your Name', 'dgw') . ' <span class="required">*</span></label>
                    <input id="author" name="author" type="text" value="" size="30" required />
                 </div>',
            'email' => '<div class="form-row">
                    <label for="email">' . __('Your E-mail', 'dgw') . ' <span class="required">*</span></label>
                    <input id="email" name="email" type="email" value="" size="30" required />
                 </div>',
        );

        // 留言欄位
        $comment_field = '<div class="form-row-comment">
                      <label for="comment">' . __('Comment Content', 'dgw') . ' <span class="required">*</span></label>
                      <textarea id="comment" name="comment" class="comment-input" rows="5" required></textarea>
                  </div>
                  
                  <div class="form-row-math">
                  <label for="math_answer">' . __('Perform Calculations', 'dgw') . ' : <strong>' . $question . '</strong> = </label>
                  <input id="math_answer" name="math_answer" type="text"  maxlength="4" required />
                  <span id="math-check-msg" style="margin-left:10px;"></span>
                  </div>
                  ';

        // 組合所有設定
        $comments_args = array(
            'fields' => $fields,
            'comment_field' => $comment_field,
            'title_reply' => __('Please share your opinions', 'dgw'),
            // 'title_reply_to' => 'Reply to to to %s',
            // 'cancel_reply_link' => __('Cancel Reply 123', 'dgw'), // 重要：要有取消回覆連結
            'label_submit' => __('Submit Comment', 'dgw'),
            'format' => 'html5', // 確保使用正確的格式
        );
        comment_form($comments_args);
    }
    ?>
</div>
<script>
    jQuery(document).ready(function($) {
        // comment-reply-link
        // 當點擊回覆連結時，監聽 URL 變化
        jQuery(document).on('click', '.comment-reply-link', function(e) {
            e.preventDefault();

            var that = jQuery(this);
            var href = that.attr('href').replace(/\?replytocom=\d+/, ''); // ✅ 清掉 replytocom

            var replyToCom = that.parent().siblings('.comment-author').text();

            if (replyToCom) {
                var cancelText = '<?php echo __('Cancel', 'dgw') ?>';
                var replyText = '<?php echo __('Reply to', 'dgw') ?>';
                var html = "<small><a id='cancel-comment-reply-link' rel='nofollow' href='" + href + "' class='cancel-reply-link'>" + cancelText + "</a></small>";

                jQuery('#reply-title').html(replyText + " " + replyToCom + html);
            }
        });

        //=========================================================================
        let math_ok = false; // ← 全域變數，用來控制能否提交

        // 🔍 監聽輸入欄位
        $(document).on("keyup", "#math_answer", function() {

            var answer = $(this).val().trim();

            // 空值直接清空訊息
            if (answer === "") {
                math_ok = false;
                $("#math-check-msg").html("").removeClass("correct wrong");
                return;
            }

            // 如果欄位空白 → 不允許提交
            if (answer === "") {
                math_ok = false;
                $("#math-check-msg").text("").css("color", "");
                return;
            }

            // AJAX 驗證
            $.post("<?php echo admin_url('admin-ajax.php'); ?>", {
                action: "check_math_captcha",
                answer: answer
            }, function(res) {
                if (res.status === "ok") {
                    $("#math-check-msg")
                        .html('<svg style="width:20px; fill:#1c9e02" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M530.8 134.1C545.1 144.5 548.3 164.5 537.9 178.8L281.9 530.8C276.4 538.4 267.9 543.1 258.5 543.9C249.1 544.7 240 541.2 233.4 534.6L105.4 406.6C92.9 394.1 92.9 373.8 105.4 361.3C117.9 348.8 138.2 348.8 150.7 361.3L252.2 462.8L486.2 141.1C496.6 126.8 516.6 123.6 530.9 134z"/></svg>')
                        .removeClass("wrong")
                        .addClass("correct");
                    math_ok = true;
                } else {
                    $("#math-check-msg")
                        .html('<svg style="width:20px; fill:#ff0000e7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M183.1 137.4C170.6 124.9 150.3 124.9 137.8 137.4C125.3 149.9 125.3 170.2 137.8 182.7L275.2 320L137.9 457.4C125.4 469.9 125.4 490.2 137.9 502.7C150.4 515.2 170.7 515.2 183.2 502.7L320.5 365.3L457.9 502.6C470.4 515.1 490.7 515.1 503.2 502.6C515.7 490.1 515.7 469.8 503.2 457.3L365.8 320L503.1 182.6C515.6 170.1 515.6 149.8 503.1 137.3C490.6 124.8 470.3 124.8 457.8 137.3L320.5 274.7L183.1 137.4z"/></svg>')
                        .removeClass("correct")
                        .addClass("wrong");
                    math_ok = false;
                }
            }, "json");

        });



        // ⛔ 阻止提交（核心）
        $(document).on("submit", "#commentform", function(e) {
            let correctAnswer = '<?php echo __('The calculation problem is incorrect. Please enter the correct answer!', 'dgw') ?>'
            if (!math_ok) {
                e.preventDefault();
                alert(correctAnswer);
                jQuery('#math_answer').focus().val('');
                return false;
            }

            // 正確 → 允許提交
            return true;
        });
    });
</script>
<?php 
function my_custom_comment($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>" class="comment-body">
            <div class="comment-author"><i class="fa-solid fa-user"></i> <?php comment_author(); ?></div>
            <div class="comment-date">
                <?php //echo get_comment_date('m/d/Y') . '-' . get_comment_time('g:i'); 
                ?>
                <?php echo get_comment_date('d/m/Y') ?>
            </div>
            <div class="comment-text">
                <?php comment_text(); ?>
            </div>

            <div class="comment-reply">
                <?php
                comment_reply_link(array_merge($args, array(
                    'reply_text' => __('Reply', 'dwg'),      // 🔹顯示的文字
                    'depth' => $depth,            // 🔹必要參數：目前留言層級
                    'max_depth' => $args['max_depth'], // 🔹最大層級
                    'class' => 'comment-reply-link reply' // ✅ 加上你要的 class
                )));
                ?>
            </div>
        </div>
    </li>
<?php
}




// 在 comments.php 中臨時添加 kiểm tra comment-reply dc đưa vào web chưa
function check_comment_reply_script()
{
    if (wp_script_is('comment-reply', 'enqueued')) {
        echo '<!-- comment-reply.js 已載入 -->';
    } else {
        echo '<!-- comment-reply.js 未載入 -->';
    }
}
add_action('wp_footer', 'check_comment_reply_script');




add_filter('preprocess_comment', function ($comment) {

    if (!isset($_POST['math_answer'])) {
        wp_die('請回答驗證題');
    }

    $user_ans = intval($_POST['math_answer']);
    $real_ans = intval($_SESSION['comment_captcha_answer'] ?? null);

    if ($user_ans !== $real_ans) {
        wp_die('計算題錯誤！請重新填寫。');
    }

    return $comment;
});
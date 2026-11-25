<?php
declare(strict_types=1);

/**
 * [20/11/2026] - View Template: Giao diện 1 item bình luận (tách HTML khỏi CommentHandler chuẩn MVC)
 */

if (!defined('ABSPATH')) {
    exit;
}

$commentArgs = isset($args['comment_args']) && is_array($args['comment_args']) ? $args['comment_args'] : [];
$depth       = isset($args['depth']) ? (int) $args['depth'] : 1;
?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
    <div id="comment-<?php comment_ID(); ?>" class="comment-body">
        <div class="comment-author"><i class="fa-solid fa-user"></i> <?php comment_author(); ?></div>
        <div class="comment-date">
            <?php echo esc_html(get_comment_date('d/m/Y')); ?>
        </div>
        <div class="comment-text">
            <?php comment_text(); ?>
        </div>

        <div class="comment-reply">
            <?php
            comment_reply_link(array_merge($commentArgs, [
                'reply_text' => __('Reply', 'dgw'),
                'depth'      => $depth,
                'max_depth'  => $commentArgs['max_depth'] ?? 5,
                'class'      => 'comment-reply-link reply',
            ]));
            ?>
        </div>
    </div>
</li>

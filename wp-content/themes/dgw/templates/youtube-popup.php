<?php
/**
 * [02/11/2026] - Template Component: Hiển thị hình đại diện YouTube (#youtube-space) và Modal Popup phát video tại trang hiện tại.
 * Hỗ trợ tái sử dụng trên tất cả các trang single (casestudies, solutions, services, industries, active...).
 * Kiểm tra trạng thái đăng nhập thành viên: Nếu chưa đăng nhập sẽ bật Popup yêu cầu đăng nhập trước khi xem video.
 * Cách gọi: <?php get_template_part('templates/youtube-popup'); ?>
 */

$post_id = get_the_ID() ?: get_queried_object_id();
if (!$post_id) {
    return;
}

$youtube_link = get_post_meta($post_id, '_metabox_youtube', true);
if (empty($youtube_link)) {
    return;
}

// Chuyển đổi mọi định dạng link YouTube (watch?v=, youtu.be/, embed/, shorts/) sang link nhúng embed chuẩn
$youtube_embed_url = $youtube_link;
if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_link, $match)) {
    $youtube_embed_url = 'https://www.youtube.com/embed/' . $match[1] . '?autoplay=1&rel=0';
}

$post_title   = get_the_title($post_id);
$current_user = function_exists('get_current_custom_user') ? get_current_custom_user() : null;
$is_logged_in = !empty($current_user);
?>

<div id="youtube-space">
    <!-- [03/11/2026] - Gắn thêm data-post-id và data-post-title để truyền qua AJAX đồng bộ Google Sheets (sheet wytList) -->
    <a href="<?php echo esc_url($youtube_link); ?>" 
       class="youtube-space__trigger" 
       data-embed="<?php echo esc_url($youtube_embed_url); ?>"
       data-post-id="<?php echo esc_attr((string) $post_id); ?>"
       data-post-title="<?php echo esc_attr($post_title); ?>"
       aria-haspopup="dialog"
       aria-controls="youtube-popup-modal">
        <img src="<?php echo esc_url(PART_IMAGES . 'digiwin-youtube.webp'); ?>" 
             alt="<?php echo esc_attr($post_title); ?>" 
             loading="lazy" 
             decoding="async" />
    </a>
</div>

<!-- [02/11/2026] - Modal Popup phát video YouTube tại chỗ (Lazy-load iframe khi click giúp tối ưu tốc độ tải trang) -->
<div id="youtube-popup-modal" 
     class="youtube-popup" 
     role="dialog" 
     aria-modal="true" 
     aria-hidden="true" 
     aria-label="<?php echo esc_attr($post_title); ?>">
    <div class="youtube-popup__content">
        <button type="button" 
                class="youtube-popup__close" 
                aria-label="<?php echo esc_attr__('Đóng video', 'dgw'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
                <path d="M320 112C434.9 112 528 205.1 528 320C528 434.9 434.9 528 320 528C205.1 528 112 434.9 112 320C112 205.1 205.1 112 320 112zM320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM231 231C221.6 240.4 221.6 255.6 231 264.9L286 319.9L231 374.9C221.6 384.3 221.6 399.5 231 408.8C240.4 418.1 255.6 418.2 264.9 408.8L319.9 353.8L374.9 408.8C384.3 418.2 399.5 418.2 408.8 408.8C418.1 399.4 418.2 384.2 408.8 374.9L353.8 319.9L408.8 264.9C418.2 255.5 418.2 240.3 408.8 231C399.4 221.7 384.2 221.6 374.9 231L319.9 286L264.9 231C255.5 221.6 240.3 221.6 231 231z" />
            </svg>
        </button>
        <div class="youtube-popup__video-wrapper">
            <iframe id="youtube-popup-iframe" 
                    src="" 
                    title="<?php echo esc_attr($post_title); ?>" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    allowfullscreen></iframe>
        </div>
    </div>
</div>

<style>
    /* [02/11/2026] - Style chuẩn BEM cho #youtube-space và Popup Video 16:9 */
    #youtube-space .youtube-space__trigger {
        display: inline-block;
        cursor: pointer;
        margin: 1rem;
    }
    #youtube-space .youtube-space__trigger img {
        max-width: 100%;
        height: auto;
        display: block;
    }
    .youtube-popup {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.85);
        z-index: 10000000000;
        display: none;
        justify-content: center;
        align-items: center;
        padding: 1rem;
    }
    .youtube-popup.is-visible {
        display: flex;
    }
    .youtube-popup__content {
        position: relative;
        width: 100%;
        max-width: 900px;
        background-color: #000;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 30px rgba(255, 255, 255, 0.25);
    }
    .youtube-popup__video-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* Tỷ lệ chuẩn 16:9 */
        height: 0;
    }
    .youtube-popup__video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    .youtube-popup__close {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
        width: 28px;
        height: 28px;
        padding: 0;
        background-color: #ffffff;
        border: none;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .youtube-popup__close svg {
        width: 16px;
        height: 16px;
        fill: #9b0202;
        pointer-events: none;
    }
    .youtube-popup__close:hover svg {
        fill: #000000;
    }
</style>

<script>
    // [02/11/2026] - Kiểm tra đăng nhập trước khi mở Popup YouTube; nếu đăng nhập bằng popup thì refresh lại trang để bắt thông tin user
    jQuery(document).ready(function($) {
        var $modal = $('#youtube-popup-modal');
        var $iframe = $('#youtube-popup-iframe');
        var isMemberLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        function openYoutubePopup(embedUrl) {
            if (!embedUrl) return;
            $iframe.attr('src', embedUrl);
            $modal.addClass('is-visible').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }

        function closeYoutubePopup() {
            $modal.removeClass('is-visible').attr('aria-hidden', 'true');
            $iframe.attr('src', '');
            $('body').css('overflow', '');
        }

        // [03/11/2026] - Xử lý sự kiện click xem video YouTube: kiểm tra đăng nhập, mở Popup phát video và gọi AJAX ghi lịch sử vào Google Sheets (sheet wytList)
        $('#youtube-space').on('click', '.youtube-space__trigger', function(e) {
            e.preventDefault();
            var $trigger  = $(this);
            var embedUrl  = $trigger.attr('data-embed');
            var postId    = $trigger.attr('data-post-id');
            var postTitle = $trigger.attr('data-post-title');
            if (!embedUrl) return;

            // Nếu chưa đăng nhập -> bật Popup yêu cầu đăng nhập
            if (!isMemberLoggedIn) {
                if (typeof showLoginPopup === 'function') {
                    showLoginPopup();
                } else {
                    $('#auth-popup-overlay').addClass('show');
                }
                return;
            }

            // Mở ngay Popup phát video để đảm bảo trải nghiệm mượt mà
            openYoutubePopup(embedUrl);

            // Gửi AJAX bất đồng bộ ghi nhận thông tin xem video vào Google Sheets (wytList)
            var ajaxUrl   = (typeof MyAjax !== 'undefined' && MyAjax.ajax_url) ? MyAjax.ajax_url : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            var ajaxNonce = (typeof MyAjax !== 'undefined' && MyAjax.nonce) ? MyAjax.nonce : '<?php echo esc_js(wp_create_nonce('my_nonce')); ?>';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'my_watch_youtube',
                    nonce: ajaxNonce,
                    post_id: postId,
                    post_title: postTitle
                },
                success: function(res) {
                    if (!res.success && res.data && res.data.code === 'not_logged_in') {
                        closeYoutubePopup();
                        if (typeof showLoginPopup === 'function') {
                            showLoginPopup();
                        } else {
                            $('#auth-popup-overlay').addClass('show');
                        }
                    }
                }
            });
        });

        $modal.on('click', function(e) {
            if (e.target === this || $(e.target).closest('.youtube-popup__close').length) {
                closeYoutubePopup();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('is-visible')) {
                closeYoutubePopup();
            }
        });
    });
</script>

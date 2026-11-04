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

// [04/11/2026] - Tự động trích xuất Video ID để tạo link embed chuẩn và link ảnh đại diện (Thumbnail) trực tiếp từ YouTube CDN
$youtube_embed_url = $youtube_link;
$youtube_thumb_url = PART_IMAGES . 'digiwin-youtube.webp'; // Fallback nếu không khớp định dạng
if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_link, $match)) {
    $video_id          = $match[1];
    $youtube_embed_url = 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1&rel=0';
    $youtube_thumb_url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
}

$post_title = get_the_title($post_id);
?>

<div id="youtube-space">
    <!-- [03/11/2026] - Gắn thêm data-post-id và data-post-title để truyền qua AJAX đồng bộ Google Sheets (sheet wytList) -->
    <!-- [04/11/2026] - Hiển thị ảnh bìa thực tế của video YouTube kèm nút Play nổi bật, giữ nguyên toàn bộ cơ chế xác thực và popup -->
    <a href="<?php echo esc_url($youtube_link); ?>" 
       class="youtube-space__trigger" 
       data-embed="<?php echo esc_url($youtube_embed_url); ?>"
       data-post-id="<?php echo esc_attr((string) $post_id); ?>"
       data-post-title="<?php echo esc_attr($post_title); ?>"
       aria-haspopup="dialog"
       aria-controls="youtube-popup-modal">
        <div class="youtube-space__thumb-wrap">
            <img src="<?php echo esc_url($youtube_thumb_url); ?>" 
                 alt="<?php echo esc_attr($post_title); ?>" 
                 loading="lazy" 
                 decoding="async" />
            <span class="youtube-space__play-btn" aria-hidden="true">
                <svg viewBox="0 0 68 48" width="60" height="42">
                    <path class="youtube-space__play-bg" d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="#ff0000"/>
                    <path d="M45 24L27 14v20" fill="#ffffff"/>
                </svg>
            </span>
        </div>
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
    /* [04/11/2026] - Style cho khung ảnh Thumbnail YouTube và nút Play chính giữa */
    #youtube-space {
        margin: 1.5rem 0;
    }
    #youtube-space .youtube-space__trigger {
        display: inline-block;
        position: relative;
        cursor: pointer;
        max-width: 680px;
        width: 100%;
        text-decoration: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #youtube-space .youtube-space__trigger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    }
    #youtube-space .youtube-space__thumb-wrap {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* Tỷ lệ chuẩn 16:9 */
        height: 0;
        background-color: #000;
        overflow: hidden;
    }
    #youtube-space .youtube-space__thumb-wrap img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }
    #youtube-space .youtube-space__trigger:hover .youtube-space__thumb-wrap img {
        transform: scale(1.03);
    }
    #youtube-space .youtube-space__play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        transition: transform 0.2s ease, filter 0.2s ease;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.4));
    }
    #youtube-space .youtube-space__trigger:hover .youtube-space__play-btn {
        transform: translate(-50%, -50%) scale(1.1);
        filter: drop-shadow(0 4px 12px rgba(255, 0, 0, 0.5));
    }
    #youtube-space .youtube-space__play-bg {
        transition: fill 0.2s ease;
    }
    #youtube-space .youtube-space__trigger:hover .youtube-space__play-bg {
        fill: #cc0000;
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
    /* [04/11/2026] - Hiệu ứng trạng thái đang kiểm tra đăng nhập trên nút mở video */
    #youtube-space .youtube-space__trigger.is-loading {
        pointer-events: none;
        opacity: 0.65;
    }
</style>

<script>
    // [04/11/2026] - Kiểm tra đăng nhập thời gian thực qua AJAX trước khi mở Popup YouTube để tương thích với LiteSpeed Cache và Browser Back-Forward Cache (BFCache)
    jQuery(document).ready(function($) {
        var $modal       = $('#youtube-popup-modal');
        var $iframe      = $('#youtube-popup-iframe');
        var isRequesting = false;

        // [04/11/2026] - Mở Modal Popup và nạp link embed YouTube sau khi đã xác thực đăng nhập thành công
        function openYoutubePopup(embedUrl) {
            if (!embedUrl) return;
            $iframe.attr('src', embedUrl);
            $modal.addClass('is-visible').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }
        window.openYoutubePopup = openYoutubePopup;

        // [04/11/2026] - Đóng Modal Popup và ngắt luồng phát video YouTube
        function closeYoutubePopup() {
            $modal.removeClass('is-visible').attr('aria-hidden', 'true');
            $iframe.attr('src', '');
            $('body').css('overflow', '');
        }
        window.closeYoutubePopup = closeYoutubePopup;

        // [04/11/2026] - Đảm bảo đóng Popup video khi người dùng nhấn nút Back/Forward trên trình duyệt (khôi phục từ BFCache)
        $(window).on('pageshow', function(e) {
            if (e.originalEvent && e.originalEvent.persisted) {
                closeYoutubePopup();
                isRequesting = false;
                $('#youtube-space .youtube-space__trigger').removeClass('is-loading');
            }
        });

        // [04/11/2026] - Xử lý click xem video: Gửi AJAX xác thực phiên đăng nhập thực tế trên server trước khi bật Popup phát video
        $('#youtube-space').on('click', '.youtube-space__trigger', function(e) {
            e.preventDefault();
            if (isRequesting) return;

            var $trigger  = $(this);
            var embedUrl  = $trigger.attr('data-embed');
            var postId    = $trigger.attr('data-post-id');
            var postTitle = $trigger.attr('data-post-title');
            if (!embedUrl) return;

            var ajaxUrl   = (typeof MyAjax !== 'undefined' && MyAjax.ajax_url) ? MyAjax.ajax_url : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            var ajaxNonce = (typeof MyAjax !== 'undefined' && MyAjax.nonce) ? MyAjax.nonce : '<?php echo esc_js(wp_create_nonce('my_nonce')); ?>';

            isRequesting = true;
            $trigger.addClass('is-loading');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                cache: false,
                data: {
                    action: 'my_watch_youtube',
                    nonce: ajaxNonce,
                    post_id: postId,
                    post_title: postTitle
                },
                success: function(res) {
                    if (res && res.success) {
                        window.pendingVideoWatch = null;
                        // Chỉ mở Popup phát video khi server xác nhận còn phiên đăng nhập hợp lệ
                        openYoutubePopup(embedUrl);
                    } else {
                        // [04/11/2026] - Ghi nhận video đang chờ xem để tự động mở Popup phát ngay sau khi đăng nhập thành công
                        window.pendingVideoWatch = {
                            embedUrl: embedUrl,
                            postId: postId,
                            postTitle: postTitle
                        };
                        // Nếu đã đăng xuất hoặc chưa đăng nhập -> đóng video (nếu có) và bật ngay Popup đăng nhập
                        closeYoutubePopup();
                        if (typeof showLoginPopup === 'function') {
                            showLoginPopup();
                        } else {
                            $('#auth-popup-overlay').addClass('show');
                        }
                    }
                },
                error: function() {
                    // [04/11/2026] - Ghi nhận video đang chờ xem để tự động mở Popup phát ngay sau khi đăng nhập thành công
                    window.pendingVideoWatch = {
                        embedUrl: embedUrl,
                        postId: postId,
                        postTitle: postTitle
                    };
                    closeYoutubePopup();
                    if (typeof showLoginPopup === 'function') {
                        showLoginPopup();
                    } else {
                        $('#auth-popup-overlay').addClass('show');
                    }
                },
                complete: function() {
                    isRequesting = false;
                    $trigger.removeClass('is-loading');
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

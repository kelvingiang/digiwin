<?php
$data = null;
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}
?>
<div class="languages-container">
    <div class="login-box">
        <?php if ($data) : ?>
            <div class="link-login-welcome">
                <a href="<?php echo home_url('/member'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="15" height="15"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                        <path d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z" />
                    </svg>
                    <?php echo $data->username; ?>
                </a>
            </div>
        <?php else : ?>
            <a href="<?php echo home_url('/member'); ?>" class="link-login">
                <?php echo __('Login-Register', 'dgw'); ?>
            </a>
        <?php endif ?>

    </div>
    <div class="languages-box">
        <label class="link-languages" data-type="cn" onclick="changeLanguages(this)">
            中
        </label> |
        <label class="link-languages" data-type="vn" onclick="changeLanguages(this)">
            VN
        </label>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // 1. 初始化：從 PHP 取得目前的 Cookie 語系
        var currentLang = "<?php echo isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : 'vn'; ?>";

        // 找出對應的 label，移除原本 class 並加上 not-link
        jQuery('.link-languages[data-type="' + currentLang + '"]')
            .removeClass('link-languages')
            .addClass('not-link');
    });

    function changeLanguages(el) {
        var type = jQuery(el).attr('data-type');

        // 從 PHP 取得目前 Cookie 的值 (如果沒有則預設為空)
        var currentLang = "<?php echo isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : ''; ?>";

        // --- 新增判斷：如果點選的語言跟目前一樣，就直接結束不執行 AJAX ---
        if (type === currentLang) {
            console.log('語言已是 ' + type + '，不執行切換。');
            return;
        }

        jQuery.ajax({
            // 使用 WordPress 內建的 AJAX 窗口
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'change_languages', // 自定義的 Action 名稱
                type: type
            },
            success: function(res) {
                if (res.status === 'ok') {
                    window.location = '<?php echo home_url(); ?>';
                }
            },
            error: function(err) {
                console.error('AJAX Error:', err);
            }
        });

        // jQuery.ajax({
        //     url: '<?php echo get_template_directory_uri() . '/ajax/change_languages.php' ?>',
        //     dataType: 'json',
        //     type: 'post',
        //     data: {
        //         type: type
        //     },
        //     success: function(res) {
        //         if (res.status === 'ok') {
        //             window.location = '<?php echo home_url(); ?>';
        //         }
        //     }
        // });
    }
</script>
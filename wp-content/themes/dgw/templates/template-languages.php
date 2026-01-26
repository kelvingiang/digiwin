<div class="languages-box">
    <label class="link-languages" data-type="cn" onclick="changeLanguages(this)">
        中
    </label> |
    <label class="link-languages" data-type="vn" onclick="changeLanguages(this)">
        VN
    </label>
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
                    //window.location.reload(); // 或者跳轉回首頁：'<?php //echo home_url(); ?>'
                    window.location ='<?php echo home_url(); ?>'
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
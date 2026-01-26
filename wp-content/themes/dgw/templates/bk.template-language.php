<div>
    <a data-type="cn" onclick="changeLanguage(this)"> 中 文 1 </a> |
    <a data-type="vn" onclick="changeLanguage(this)"> Tiếng Việt 1</a>
</div>
&#160;&#160;
<script>
function changeLanguage(el) {
    alert('ss');
    var type = jQuery(el).attr('data-type');
    console.log(type);
    jQuery.ajax({
        url: '<?php echo get_template_directory_uri() . '/ajax/change_language.php' ?>',
        dataType: 'json',
        type: 'post',
        data: {
            type: type
        },
        success: function(res) {
            if (res.status === 'ok') {
                console.log('s');
                //  window.location = '/';
            }
        }
    });
}
</script>
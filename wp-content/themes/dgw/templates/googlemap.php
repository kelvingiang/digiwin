<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Sửa lỗi callback Google Maps (initMap trỏ đúng #google-map) và dùng wp_json_encode chống lỗi cú pháp JS / XSS
 */

if (!defined('ABSPATH')) {
    exit;
}

$lang        = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
$companyName = (string) get_post_meta(1, '_info_name_' . $lang, true);
?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAV4v2qSBuCA1Rn7NPd09exwP4smcjW_g&callback=initMap" type="text/javascript"></script>
<script type="text/javascript">
    var map;

    function initMap() {
        var container = document.getElementById("google-map");
        if (!container || typeof google === "undefined" || !google.maps) {
            return;
        }

        var myLatlng = new google.maps.LatLng(10.725911973551872, 106.72385302444266);
        var myOptions = {
            zoom: 16,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(container, myOptions);

        var companyTitle = <?php echo wp_json_encode($companyName); ?>;
        var text = "<b style='color:red;font-weight:bold;text-align:center;'>" + companyTitle + "</b>";
        var infowindow = new google.maps.InfoWindow({
            content: text,
            size: new google.maps.Size(100, 50),
            position: myLatlng
        });

        infowindow.open(map);

        new google.maps.Marker({
            position: myLatlng,
            map: map,
            title: companyTitle || "Digiwin Vietnam"
        });
    }
</script>
<div id="google-map" style="position: relative; z-index: 28; height: 500px; width: 100%; margin: 10px auto;"></div>

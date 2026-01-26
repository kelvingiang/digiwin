 <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAV4v2qSBuCA1Rn7NPd09exwP4smcjW_g&callback=initMap" type="text/javascript">
 </script>
 <!--<script type="text/javascript" 
        src="http://maps.googleapis.com/maps/api/js?sensor=false&language=en">
</script>-->
 <script type="text/javascript">
     var map;

     function initialize() {
         var myLatlng = new google.maps.LatLng(10.725911973551872, 106.72385302444266);
         var myOptions = {
             zoom: 16,
             center: myLatlng,
             mapTypeId: google.maps.MapTypeId.ROADMAP
         }
         map = new google.maps.Map(document.getElementById("google-map"), myOptions);
         // Biến text chứa nội dung sẽ được hiển thị
         var text;
         text = "<b style='color:red;font-weight: bold'" +
             "style='text-align:center;'><?php _e(get_post_meta(1, '_info_name_' . dgw_get_lang(), true)) ?></b>";
         var infowindow = new google.maps.InfoWindow({
             content: text,
             size: new google.maps.Size(100, 50),
             position: myLatlng
         });
         infowindow.open(map);
         var marker = new google.maps.Marker({
             position: myLatlng,
             map: map,
             title: "Mightway vn"
         });
     }
 </script>
 <div id="google-map" style="position: relative;  
 z-index: 28 ; 
 height: 500px;  
 width: 100%; 
 margin: 0 auto ; 
 margin: 10px 0 ;  ">

 </div>
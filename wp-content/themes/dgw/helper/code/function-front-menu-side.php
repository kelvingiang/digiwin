<?php
function menuSide($cate, $page)
{
?>
    <div class="menu-side">
        <?php
        global $wp;
        $param = $wp->query_vars;

        $data = getAllCategories($cate, 0, $page);

        foreach ($data as $key => $val) {
            $sub = getAllCategories($cate, $val['ID'], $page);
            echo "<div class='item has-sub'>";
            echo "<div class='menu-side-main'>";
            echo "<a id='" . $val['ID'] . "'  class='menu-side-main-link' href='" . home_url($val['page'] . '/cate/' .  $val['ID'] . '/tag/') . "'>" . $val['name'];
            if (!empty($sub)) {
                echo "<i style=' margin-left: 1rem' class='fas fa-angle-down'></i>";
            }
            echo "</a>";
            if (!empty($sub)) {
                echo "<div class='menu-side-sub'>";
                foreach ($sub as $skey => $sval) {
                    echo "<div >";
                    echo "<a id='" . $sval['ID'] . "' href='" . home_url($val['page'] . '/cate/' .  $val['ID'] . '/tag/' . $sval['ID']) . "'>" . $sval['name'] . "</a>";
                    echo "</div>";
                }
                echo "</div>";
            }
            echo '</div>';
            echo '</div>';
        }

        ?>
    </div>
    <script>
        jQuery(document).ready(function() {

            var cate = '<?php echo  $param['cate']; ?>';
            var tag = '<?php echo $param['tag']; ?>';

            if (cate !== '') {
                var cates = "#" + cate;
                jQuery(cates).addClass('linked');
                jQuery(cates).parent('.menu-side-main').addClass('active-item');
                jQuery(cates).parent().parent('.item').removeClass('has-sub');
                if (tag !== '') {
                    var tags = "#" + tag;
                    jQuery(tags).addClass('linked');
                    jQuery(tags).parent().parent('.menu-side-sub').css("display", "block");
                    jQuery(tags).parent().addClass('active-item-sub');
                }
            }


            jQuery('.item').mouseover(function() {
                if (jQuery(this).children().children('.menu-side-sub').css('display') === 'none') {
                    if (!jQuery(this).children().children().children().children().hasClass("linked")) {
                        jQuery(this).children().children('.menu-side-sub').slideDown('slow');
                    }
                }

            });

            jQuery('.item').mouseleave(function() {
                if (jQuery(this).children().children('.menu-side-sub').css('display') === 'block') {
                    if (!jQuery(this).children().children().children().children().hasClass("linked")) {
                        jQuery(this).children().children('.menu-side-sub').slideUp('slow');
                    }

                }

            });



        })
    </script>


<?php }

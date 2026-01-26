<div id="header-space">
    <a href="<?php echo home_url(); ?>">
        <img class="company-logo"  src="<?php echo PART_IMAGES . 'logo.png' ?>" 
        alt="digiwin viet nam" 
        width="150"
        height="42"
        />
    </a>
    <nav class="menu-main">
        <!-- MAIN MENU  -->
        <?php foreach (menu_main_list() as $key_main => $val_main) :
            $classData = $val_main['data'];
            $className = $val_main['name'];
        ?>
            <div class='<?php echo $val_main['class'] ?>' data-name='<?php echo $classData ?>'>
                <?php if (isset($val_main['sub'])) :
                ?>
                    <label class='menu-main-item-link has-sub'>
                        <?php _e($className); ?>
                    </label>
                <?php else : ?>
                    <a href='<?php echo home_url($key_main); ?>' class='menu-main-item-link'>
                        <?php _e($className, 'dgw'); ?>
                    </a>
                <?php endif ?>
                <div class='menu-main-item-bg'></div>

                <!--=========== PHAN MENU SUB =============== -->
                <?php if (isset($val_main['sub'])) : ?>
                    <div class=' <?php echo $val_main['subClass'] ?>'>
                        <?php foreach ($val_main['sub'] as $key_sub => $val_sub) : ?>
                            <div class=' <?php echo $val_sub['class'] ?> '>
                                <a href="<?php echo home_url($key_main) . '/cate/' . $val_sub['ID'] . '/tag/' ?>" class='menu-main-sub-1-item-link <?php echo $val_sub['sub'] != '' ? 'has-sub' : '' ?>'>
                                    <?php _e($val_sub['name']) ?></a>
                                <div class='menu-main-sub-1-item-bg'></div>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endforeach ?>
    </nav>
    <div>
        <?php get_template_part('templates/template', 'languages') ?>
    </div>
</div>
<!-- //======================================================================= -->
<div id="header-scroll">
    <a href="<?php echo home_url(); ?>">
        <img class="company-logo" src="<?php echo PART_IMAGES . 'logo.png' ?>" />
    </a>
    
    <nav class="menu-main">
        <!-- MAIN MENU  -->
        <?php foreach (menu_main_list() as $key_main => $val_main) {
            $classData = $val_main['data'];
            $className = $val_main['name'];
        ?>
            <div class='<?php echo $val_main['class'] ?>' data-name='<?php echo $classData ?>'>
                <?php if (isset($val_main['sub'])) : ?>
                    <label class='menu-main-item-link has-sub'>
                        <?php _e($className);  ?>
                    </label>
                <?php else : ?>
                    <a href='<?php echo home_url($key_main); ?>' class='menu-main-item-link'>
                        <?php _e($className);  ?>
                    </a>
                <?php endif ?>

                <div class='menu-main-item-bg'></div>

                <!--=========== PHAN MENU SUB =============== -->
                <?php if (isset($val_main['sub'])) : ?>
                    <div class=' <?php echo $val_main['subClass'] ?>'>
                        <?php foreach ($val_main['sub'] as $key_sub => $val_sub) : ?>
                            <div class=' <?php echo $val_sub['class'] ?> '>
                                <a href="<?php echo home_url($key_main) . '/cate/' . $val_sub['ID'] . '/tag/' ?>" class='menu-main-sub-1-item-link <?php echo $val_sub['sub'] != '' ? 'has-sub' : '' ?>'>
                                    <?php _e($val_sub['name']) ?></a>
                                <div class='menu-main-sub-1-item-bg'></div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <!-- END MENU SUB -->
                <?php endif ?>
            </div>
            <!-- AND MENU -->
        <?php } ?>
    </nav>
    <div>
        <?php get_template_part('templates/template', 'languages') ?>
    </div>

</div>

<script>
    const animationElements = document.querySelector("#header-space");
    // TAO HIEU UNG KHI CUON NOI DUNG TRAN WEB
    function myCheck(element) {
        // LAY VI TRI TOP VA BOTTOM CUA ELEMENT
        var rect = element.getClientRects()[0];
        // XAC DINH DO CAO CUA MAN HINH
        var heightScreen = window.innerHeight;

        if (rect.bottom < 0) {
            document.querySelector('#header-scroll').classList.add("start");
            document.querySelector('#header-scroll').classList.remove("close");
        } else {
            // kiểm tra class có tồn tại hay không bắng javascript =======
            if (document.querySelector('#header-scroll').classList.contains("start")) {
                document.querySelector('#header-scroll').classList.add("close");
            }
            document.querySelector('#header-scroll').classList.remove("start");

        }
    }

    function menuAnimation() {
        // LAY TAT CA CAC DOI TUONG CO CLASS LA .show-on-scroll
        //var animationElements = document.querySelectorAll('.show-on-scroll')
        // CHAY VONG LAP DE THEM CLASS
        //  animationElements.forEach((el) => {
        myCheck(animationElements);
        //  });
        // animationElements.myCheck();
    }
</script>
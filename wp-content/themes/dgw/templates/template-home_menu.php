<div class="menu-home">
        <?php foreach (menu_home_list() as $key => $val) { ?>
            <div class="menu-home-item lang-<?php echo dgw_get_lang() ?>">
                <a href="<?php echo home_url($key) ?>"> <?php _e($val); ?></a>
            </div>
        <?php } ?>
</div>
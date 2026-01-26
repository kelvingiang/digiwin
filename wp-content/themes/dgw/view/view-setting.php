<?php ?>
<form name="f-info" id="f-info" method="post">


    <div class="row-two-column">
        <div class="col">
            <div class="cell-title">
                <label><?php _e('首次加載') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" 
                       id="txt-first-load"
                       name="txt-first-load"
                       class="my-input"
                       value="<?php echo get_option('first_load') ?>"/>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php _e('更多加載') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" 
                       id="txt-more-load"
                       name="txt-more-load"
                       class="my-input"
                       value="<?php echo get_option('more_load')?>"
                       />
            </div>
        </div>

    </div>

 
    <div class="button-row">
        <input type="submit" name="btn-submit" id="btn-submit" class="button button-primary button-large" value="<?php echo _e('Submit') ?>"/>
    </div>
</form>


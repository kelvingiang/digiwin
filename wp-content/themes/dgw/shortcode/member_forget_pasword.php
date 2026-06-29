<?php
function member_forgot_password_form()
{
    ob_start(); ?>
    <div id="popup-forgot-password" class="dwf-wrapper" style="display:none;">
        <div class="dwf-container">
            <span class="dwf-close">&times;</span>
            <div class="dwf-header">
                <h3><?php _e('Forget Password') ?></h3>
                <p><?php _e('Enter your email address to reset your password') ?></p>
            </div>
            <form id="forgot_password_form_ajax" class="dwf-form">
                <div class="one-columns">
                    <div class="row-cell">
                        <input type="email" name="user_email" id="user_email" required placeholder="email@example.com">
                    </div>
                </div>

                <?php wp_nonce_field('ajax_forgot_nonce', 'forgot_nonce'); ?>
                <div class="btn-space">
                    <button type="submit" id="btn-submit-forgot" class="btn-my-style"><?php _e('Submit Email') ?></button>
                </div>
                <div id="forgot-password-msg" class="msg"></div>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('member_forgot_pw', 'member_forgot_password_form');

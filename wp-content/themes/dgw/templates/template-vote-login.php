<div class="vote-login">
    <div class="vote-login-title">
        <img class="company-logo" src="<?php echo PART_IMAGES . 'logo.png' ?>" style="margin: .5rem; " />
        <p>鼎捷軟件(越南)股份公司 - 股東大會投票  
            <br>
            Cty Cổ Phần Phần mềm DIGIWIN Việt Nam - Đại hội đồng cổ đông biểu quyết </p>
    </div>
    <div class="vote-login-box">
        <div>
            <label class="error-style error-login" id="login-failure"></label>
        </div>
        <div>
            <span><b>E-mail </b> <i class="error-style" id='error-email'></i></span>
            <span><input type="text" name="txt-email" id="txt-email" class="form-control"> </span>
        </div>

        <div>
            <span><b>Password </b> <i class="error-style" id='error-pass'></i></span>
            <span><input type="password" name="txt-pass" id="txt-pass" class="form-control"> </span>
        </div>
        <div class="btn-space">
            <button name="btn-login" id="btn-login" class="btn btn-primary"> Login</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#txt-email").focus();

        jQuery('#btn-login').click(function(e) { //     console.log(objInfo);
            jQuery('#error-pass').text('');
            jQuery('#error-email').text('');

            var email = jQuery('#txt-email').val();
            var pass = jQuery('#txt-pass').val();
            if (email !== '' && pass !== '') {
                jQuery.ajax({
                    url: '<?php echo get_template_directory_uri() . '/ajax/vote-login.php' ?>', // lay doi tuong chuyen sang dang array
                    type: 'post', //                data: $(this).serialize(),
                    data: {
                        email: email,
                        pass: pass
                    },
                    dataType: 'json',
                    success: function(data) { // set ket qua tra ve  data tra ve co thanh phan status va message
                        if (data.status === 'success') {
                            location.reload();
                        } else if (data.status === 'failure') {
                            document.getElementById("login-failure").innerHTML = data.message;
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.reponseText);

                    }
                });
                e.preventDefault();
            }

            if (email == '') {
                jQuery('#error-email').text('這欄目不能為空 - Không được rỗng');
            }

            if (pass == '') {
                jQuery('#error-pass').text('這欄目不能為空 - Không được rỗng');
            }

        });
    });
</script>
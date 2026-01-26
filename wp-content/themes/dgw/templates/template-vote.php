<?php
global $wpdb;
$table = $wpdb->prefix . 'vote';
$sql   = "SELECT ID, title, content FROM $table WHERE `status` = '1'";
$data  = $wpdb->get_row($sql, ARRAY_A);

$table_detail = $wpdb->prefix . 'vote_detail';
$sql2 = "SELECT  COUNT(ID) as count FROM $table_detail WHERE `vote_id` = '" . $data['ID'] . "' AND `person_id` ='" . $_SESSION['vote-login']['ID'] . "'";
$vote_result  = $wpdb->get_row($sql2, ARRAY_A);
// print_r($data);
// print_r($vote_result['count']);
?>

<div class="vote-content">
    <div class="vote-content-header">
        <div> <?php echo  $data['title']; ?></div>
        <div> <?php echo $data['content'] ?> </div>
    </div>

    <div class="vote-person">
        <div>
            <h4>
                <?php echo $_SESSION['vote-login']['name_cn'] .' - '. $_SESSION['vote-login']['name_en']  ?>
            </h4>
        </div>
        <div id="chang-pass" class="btn btn-primary">更改密碼 - Đổi Mật Mã</div>
        <div id="logout" class="btn btn-primary">登出 - Đăng Xuất</div>
    </div>
    <?php
    if ($vote_result['count'] < 1) {
    ?>
        <div class="vote-content-box">
            <input type="hidden" name="hid-id" id="hid-id" value="<?php echo $data['ID'] ?>" />
            <div>
                <input type="radio" id="1" name="rdo-vote" value="1" checked>
                <label>讚成 - Đồng Ý</label>
            </div>

            <div>
                <input type="radio" id="0" name="rdo-vote" value="0">
                <label>不讚成 - Không Đồng Ý</label>
            </div>
            <div>
                <button id="btn-submit" name="btn-submit" class="btn btn-primary">
                    提 交 - Gởi
                </button>
            </div>
        </div>
    <?php } else { ?>
        <div class="voted">
            <label>您已投票</label>
            <label>Bạn Đã Bỏ Phiếu</label>
        </div>
    <?php } ?>
</div>
<div id='vote-success-popup'>
    <label> 已提交成功，謝謝您！</label>
</div>

<div id='chang-password-popup'>
    <div class="chang-password-box">
        <div>
            <span>更改密碼 - Đổi Mật Mã</span>
            <span class="close-chang-pass-box">X</span>
        </div>
        <div>
            <label class="error-style" id="chang-pass-success"></label>
        </div>
        <div>
            <span><b>舊密碼 - Mật Mã Cũ </b> <i class="error-style" id='error-old-pass'></i></span>
            <span><input type="password" name="txt-old-pass" id="txt-old-pass" class="form-control"> </span>
        </div>
        <div>
            <span><b>新密碼 - Mật Mã Mới </b> <i class="error-style" id='error-new-pass'></i></span>
            <span><input type="password" name="txt-new-pass" id="txt-new-pass" class="form-control"> </span>
        </div>
        <div>
            <span><b>確認密碼 - Xác Nhận Mật Mã </b> <i class="error-style" id='error-confirm-pass'></i></span>
            <span><input type="password" name="txt-confirm-pass" id="txt-confirm-pass" class="form-control"> </span>
        </div>
        <div class="btn-space">
            <button name="btn-chang-pass" id="btn-chang-pass" class="btn btn-primary"> 提 交</button>
        </div>
    </div>
</div>




<script type="text/javascript">
    jQuery(document).ready(function() {

        setTimeout(function() {
            window.location.reload();
        }, 50000);

        jQuery('#btn-submit').click(function(e) { //     console.log(objInfo);
            var vote = jQuery('input:radio[name="rdo-vote"]:checked').val();
            var id = jQuery('#hid-id').val();
            var person = "<?php echo $_SESSION['vote-login']['ID'] ?>";
            jQuery.ajax({
                url: '<?php echo get_template_directory_uri() . '/ajax/vote.php' ?>', // lay doi tuong chuyen sang dang array
                type: 'post', //                data: $(this).serialize(),
                data: {
                    vote: vote,
                    id: id,
                    person: person
                },
                dataType: 'json',
                success: function(data) { // set ket qua tra ve  data tra ve co thanh phan status va message
                    if (data.status === 'success') {
                        jQuery('#vote-success-popup').css('visibility', 'visible');
                        setTimeout(function() {
                            //jQuery('#vote-success-popup').css('visibility', 'hidden');
                            location.reload();
                        }, 2000);
                    } else if (data.status === 'failure') {
                        document.getElementById("login-failure").innerHTML = data.message;
                    }
                },
                error: function(xhr) {
                    console.log(xhr.reponseText);

                }
            });
            e.preventDefault();
        });

        jQuery('#btn-chang-pass').click(function(e) { //     console.log(objInfo);
            var txt_old_pass = jQuery('#txt-old-pass').val();
            var txt_new_pass = jQuery('#txt-new-pass').val();
            var txt_confirm_pass = jQuery('#txt-confirm-pass').val();

            if (txt_old_pass == '') {
                jQuery('#error-old-pass').text('這欄目不能為空 - không được rỗng');
            } else {
                jQuery('#error-old-pass').text('');
            }

            if (txt_new_pass == '') {
                jQuery('#error-new-pass').text('這欄目不能為空 - không được rỗng');
            } else {
                jQuery('#error-new-pass').text('');
            }

            if (txt_confirm_pass == '') {
                jQuery('#error-confirm-pass').text('這欄目不能為空 - không được rỗng');
            } else {
                jQuery('#error-confirm-pass').text('');
            }

            if (txt_old_pass !== '' && txt_new_pass !== '' && txt_confirm_pass !== '') {
                var old_pass = txt_old_pass;
                var new_pass = txt_confirm_pass;
                var person = "<?php echo $_SESSION['vote-login']['ID'] ?>";
                jQuery.ajax({
                    url: '<?php echo get_template_directory_uri() . '/ajax/vote-chang-password.php' ?>', // lay doi tuong chuyen sang dang array
                    type: 'post', //                data: $(this).serialize(),
                    data: {
                        old_pass: old_pass, //,
                        new_pass: new_pass,
                        person: person
                    },
                    dataType: 'json',
                    success: function(data) { // set ket qua tra ve  data tra ve co thanh phan status va message
                        if (data.status === 'success') {
                            jQuery('#error-old-pass').text('');
                            jQuery('#chang-pass-success').text('密碼更新成功！')

                        } else if (data.status === 'failure') {
                            jQuery('#error-old-pass').text('密碼不正確！');

                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.reponseText);

                    }
                });
                e.preventDefault();
            }
        });

        jQuery('#chang-pass').click(function() {
            jQuery('#chang-password-popup').css('visibility', 'visible');
        });

        jQuery('.close-chang-pass-box').click(function() {
            jQuery('#chang-password-popup').css('visibility', 'hidden');
        })

        // kiem tra confirm pass ======
        jQuery('#txt-confirm-pass').blur(function() {
            var confirm_pass = jQuery('#txt-confirm-pass').val();
            var new_pass = jQuery('#txt-new-pass').val();
            console.log(new_pass);
            console.log(confirm_pass);
            if (confirm_pass != new_pass) {
                jQuery('#error-confirm-pass').text('確認密碼不正確！');
                jQuery('.btn-space').css('display', 'none');
            } else {
                jQuery('#error-confirm-pass').text('');
                jQuery('.btn-space').css('display', 'block');

            }
        });


        jQuery('#vote-success-popup').click(function() {
            jQuery(this).css('visibility', 'hidden');
        });


        jQuery('#logout').click(function(e) { //     console.log(objInfo);
            jQuery.ajax({
                url: '<?php echo get_template_directory_uri() . '/ajax/vote-logout.php' ?>', // lay doi tuong chuyen sang dang array
                type: 'post', //                data: $(this).serialize(),
                data: {},
                dataType: 'json',
                success: function(data) { // set ket qua tra ve  data tra ve co thanh phan status va message
                    if (data.status === 'success') {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.log(xhr.reponseText);
                }
            });
            e.preventDefault();
        });
    });
</script>
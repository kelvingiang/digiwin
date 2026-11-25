<?php
declare(strict_types=1);

/**
 * [20/11/2026] - Chuẩn MVC: View chỉ nhận dữ liệu thành viên từ MemberController, không gọi Model trực tiếp
 */
$id         = isset($id) ? (int) $id : 0;
$email      = (string) ($email ?? '');
$company    = (string) ($company ?? '');
$username   = (string) ($username ?? '');
$phone      = (string) ($phone ?? '');
$tax        = (string) ($tax ?? '');
$position   = (string) ($position ?? '');
$industry   = (string) ($industry ?? '');
$department = (string) ($department ?? '');
$details    = isset($details) && is_array($details) ? $details : [];
?>

<div style="margin-top: 3rem;">
    <div>
        <h2><?php echo esc_html__('Download Data', 'dgw'); ?></h2>
    </div>
    <div class="row-four-column">
        <div class="col">
            <div class="cell-title">
                <label>姓名 : <i><?php echo esc_html($username); ?></i></label>
            </div>
        </div>

        <div class="col">
            <div class="cell-title">
                <label>職位 : <i><?php echo esc_html($position); ?></i></label>
            </div>
        </div>

        <div class="col">
            <div class="cell-title">
                <label>部門 : <i><?php echo esc_html($department); ?></i></label>
            </div>
        </div>

        <div class="col">
            <div class="cell-title">
                <label>電話 : <i><?php echo esc_html($phone); ?></i></label>
            </div>
        </div>
    </div>

    <div class="row-four-column">
        <div class="col">
            <div class="cell-title">
                <label>公司名稱 : <i><?php echo esc_html($company); ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label>行業 : <i><?php echo esc_html($industry); ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label>E-mail : <i><?php echo esc_html($email); ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label>稅碼 : <i><?php echo esc_html($tax); ?></i></label>
            </div>
        </div>
    </div>

    <div class="admin-chang-password">
        <div class="admin-chang-password-title">
            <button id="btn-toggle-form" class="btn-outline" type="button">更新密碼</button>
        </div>

        <div id="password-form-container" class="admin-chang-password-form">
            <div class="form-group">
                <input type="password" id="new-password" placeholder="請輸入新密碼">
            </div>
            <div class="form-group">
                <button id="btn-chang-password"
                    type="button"
                    data-email="<?php echo esc_attr($email); ?>"
                    data-name="<?php echo esc_attr($username); ?>"
                    class="btn btn-primary">確認</button>
            </div>
        </div>
    </div>

    <div style="margin-top: 3rem;">
        <h3>已下載文章</h3>
        <?php
        $count = 1;
        foreach ($details as $item) :
        ?>
            <div class="downloaded-detail">
                <div><?php echo esc_html((string) $count++); ?></div>
                <div><?php echo esc_html((string) ($item->title ?? '')); ?></div>
                <div><?php echo esc_html((string) ($item->download_date ?? '')); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn         = document.getElementById('btn-toggle-form');
            const formContainer     = document.getElementById('password-form-container');
            const btnChangePassword = document.getElementById('btn-chang-password');
            const inputNewPassword  = document.getElementById('new-password');

            if (!toggleBtn || !formContainer || !btnChangePassword || !inputNewPassword) {
                return;
            }

            toggleBtn.addEventListener('click', function() {
                formContainer.classList.toggle('show');
                toggleBtn.textContent = formContainer.classList.contains('show') ? '取消更新' : '更新密碼';
            });

            btnChangePassword.addEventListener('click', function() {
                const newPassword = inputNewPassword.value;
                const clientEmail = this.getAttribute('data-email');
                const clientName  = this.getAttribute('data-name');

                if (newPassword.length < 6) {
                    alert('密碼長度必須在 6 個字元以上！');
                    return;
                }

                if (!confirm('確定要更新此客戶的密碼嗎？')) return;

                btnChangePassword.disabled    = true;
                btnChangePassword.textContent = '更新中...';

                const formData = new FormData();
                formData.append('action', 'admin_update_client_password');
                formData.append('nonce', '<?php echo esc_js(wp_create_nonce('dgw_admin_client_pw_nonce')); ?>');
                formData.append('email', clientEmail);
                formData.append('name', clientName);
                formData.append('password', newPassword);

                const ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

                fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.data.message);
                            inputNewPassword.value = '';
                            formContainer.classList.remove('show');
                            toggleBtn.textContent = '更新密碼';
                        } else {
                            alert('錯誤：' + (data.data && data.data.message ? data.data.message : '操作失敗'));
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        alert('系統連線錯誤。');
                    })
                    .finally(() => {
                        btnChangePassword.disabled    = false;
                        btnChangePassword.textContent = '確認';
                    });
            });
        });
    </script>
</div>

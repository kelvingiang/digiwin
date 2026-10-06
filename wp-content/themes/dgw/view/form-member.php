<?php
require_once(DIR_MODEL . 'model-download-function.php');
require_once(get_template_directory() . '/inc/code/code-member-dictionary.php');
if (!empty(getParams('id'))) {
    $model = new Model_Download_Function();
    $data = $model->get_user_by_id(getParams('id'));
    $id =  $data->ID ?? null;
    $email =  $data->email ?? null;
    $company =  $data->company ?? null;
    $username =  $data->username ?? null;
    $phone =  $data->phone ?? null;
    $tax = $data->tax ?? null;

    $position_key =  $data->position ?? '';
    $industry_key =  $data->industry ?? '';
    $department_key =  $data->department ?? '';

    $lang = $data->language ?? 'vi'; // 'vn' or 'cn'

    $position_dictionary = get_position_dictionary();
    $position = $position_dictionary[$position_key][$lang] ?? $position_key;
    $industry_dictionary = get_industry_dictionary();
    $industry = $industry_dictionary[$industry_key][$lang] ?? $industry_key;
    $department_dictionary = get_department_dictionary();
    $department = $department_dictionary[$department_key][$lang] ?? $department_key;

    $details = $model->get_downloaded_by_user($id);
}
?>

<div style="margin-top: 3rem;">
    <div class="row-four-column">

        <div class="col">
            <div class="cell-title">
                <label> 姓名 : <i><?php echo $username ?></i></label>
            </div>
        </div>


        <div class="col">
            <div class="cell-title">
                <label> 職位 : <i><?php echo $position ?></i></label>
            </div>
        </div>

        <div class="col">
            <div class="cell-title">
                <label> 部門 : <i><?php echo $department ?></i></label>
            </div>
        </div>

        <div class="col">
            <div class="cell-title">
                <label> 電話 : <i><?php echo $phone ?></i> </label>
            </div>
        </div>
    </div>

    <div class="row-four-column">
        <div class="col">
            <div class="cell-title">
                <label> 公司名稱 : <i><?php echo $company ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label> 行業 : <i><?php echo $industry ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label> E-mail : <i><?php echo $email ?></i></label>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label> 稅碼 : <i><?php echo $tax ?></i> </label>
            </div>
        </div>

    </div>

    <div class="admin-chang-password">
        <div class="admin-chang-password-title">
            <!-- 加上 ID 以便 JS 監聽點擊事件 -->
            <button id="btn-toggle-form" class="btn-outline">更新密碼</button>
        </div>

        <!-- 加上 ID 與預設的隱藏 class -->
        <div id="password-form-container" class="admin-chang-password-form">
            <div class="form-group">
                <input type="password" id='new-password' placeholder="請輸入新密碼">
            </div>
            <div class="form-group">
                <button id="btn-chang-password"
                    data-email='<?php echo $email ?>'
                    data-name='<?php echo $username ?>'
                    class="btn btn-primary">確認</button>
            </div>
        </div>
    </div>


    <div style="margin-top: 3rem;">
        <h3>已下載文章</h3>
        <?php
        $count = 1;
        foreach ($details as $item) :  ?>
            <div class="downloaded-detail">
                <div>
                    <?php echo $count++;  ?>
                </div>
                <div>
                    <?php echo $item->title ?>
                </div>
                <div>
                    <?php echo $item->download_date ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('btn-toggle-form');
            const formContainer = document.getElementById('password-form-container');
            const btnChangePassword = document.getElementById('btn-chang-password');
            const inputNewPassword = document.getElementById('new-password');

            // 展開/隱藏表單邏輯
            toggleBtn.addEventListener('click', function() {
                formContainer.classList.toggle('show');
                if (formContainer.classList.contains('show')) {
                    toggleBtn.textContent = '取消更新';
                } else {
                    toggleBtn.textContent = '更新密碼';
                }
            });

            // 執行更新
            btnChangePassword.addEventListener('click', function() {
                const newPassword = inputNewPassword.value;
                // 直接從 HTML 的 data-email 取得當前客戶的 Email
                const clientEmail = this.getAttribute('data-email');
                const clientName = this.getAttribute('data-name');

                if (newPassword.length < 6) {
                    alert('密碼長度必須在 6 個字元以上！');
                    return;
                }

                if (!confirm('確定要更新此客戶的密碼嗎？')) return;

                btnChangePassword.disabled = true;
                btnChangePassword.textContent = '更新中...';

                const formData = new FormData();
                formData.append('action', 'admin_update_client_password');
                formData.append('email', clientEmail);
                formData.append('name', clientName);
                formData.append('password', newPassword);

                // 使用 WordPress 內建的 AJAX URL
                const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';

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
                            alert('錯誤：' + data.data.message);
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        alert('系統連線錯誤。');
                    })
                    .finally(() => {
                        btnChangePassword.disabled = false;
                        btnChangePassword.textContent = '確認';
                    });
            });
        });
    </script>
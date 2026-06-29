<?php
class Model_Download
{
    private $_db;
    private $_table_registry;
    private $_table_details;

    // 建構子：初始化資料庫連線與資料表名稱
    public function __construct()
    {
        global $wpdb;
        $this->_db = $wpdb;
        $this->_table_registry = $wpdb->prefix . 'download_registrations';
        $this->_table_details = $wpdb->prefix . 'download_detail';
    }


    public function insert_download_detail($data)
    {
        $inserted = $this->_db->insert(
            $this->_table_details,
            [
                'user_id'       => $data['user_id'],
                'title'         => $data['title'],
                'resource'      => $data['resource'],
                'download_date' => current_time('mysql') // 1. 修正欄位名稱為 download_date
            ],
            [
                '%d', // user_id 是 int(11)，建議用 %d
                '%s', // title 是 varchar
                '%s', // resource 是 varchar
                '%s'  // download_date 是 datetime，使用 %s
            ] // 2. 確保這裡有 4 個佔位符對應上面的 4 個欄位
        );

        // 如果失敗，可以透過 $this->_db->last_error 查看報錯原因
        if (!$inserted) {
            error_log('Insert Error: ' . $this->_db->last_error);
        }

        return $inserted ? $this->_db->insert_id : false;
    }

    public function insert_registration_data($data)
    {
        $inserted = $this->_db->insert(
            $this->_table_registry,
            [
                'username'    => $data['username'],
                'email'       => $data['email'],
                'password'    => password_hash($data['password'], PASSWORD_BCRYPT),
                'company'     => $data['company'],
                'phone'       => $data['phone'],
                'position'    => $data['position'],
                'tax'         => $data['tax'],
                'industry'    => $data['industry'],
                'department'  => $data['department'],
                'active_code' => $data['active_code'],
                'create_date' => current_time('mysql') // 使用 WordPress 推薦的時間函數
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? $this->_db->insert_id : false;
    }

    // 1. 透過 Session Key 取得使用者
    public function get_user_by_session($session_key)
    {
        if (empty($session_key)) return false;
        return $this->_db->get_row(
            $this->_db->prepare("SELECT `ID`, `username`, `company`, `tax`, `industry`, `department`, `position`, `phone`, `email`  FROM {$this->_table_registry} WHERE session_key = %s", $session_key)
        );
    }

    // 2. 透過 Email 取得使用者 (用於登入)
    public function get_user_by_email($email)
    {
        return $this->_db->get_row(
            $this->_db->prepare("SELECT * FROM {$this->_table_registry} WHERE email = %s", $email)
        );
    }

    public function check_email_username_exists($email, $username)
    {
        return $this->_db->get_var($this->_db->prepare(
            "SELECT COUNT(*) FROM {$this->_table_registry} WHERE email = %s OR username = %s",
            $email['email'],
            $username['username']
        ));
    }

    public function active_member($email, $token)
    {
        // 1. Kiểm tra đầu vào
        if (empty($email) || empty($token)) {
            return false;
        }

        $data = [
            'status'      => '1',
            'active_code' => '',
            'update_date' => wp_date('Y-m-d H:i:s', null, new DateTimeZone('Asia/Ho_Chi_Minh')),
        ];

        $where = [
            'email'       => $email,
            'active_code' => $token // Hãy chắc chắn $token này đã được hash nếu trong DB lưu chuỗi hash
        ];

        $result = $this->_db->update(
            $this->_table_registry,
            $data,
            $where
        );

        // DEBUG: Nếu bạn đang chạy thử, hãy uncomment dòng dưới để xem lỗi thực sự là gì
        /*
    if ($result === false) {
        error_log("Update Error: " . $this->_db->last_error);
    }
    */

        // Trả về true nếu update thành công HOẶC nếu bản ghi vốn dĩ đã là status = 1 (không thay đổi)
        return ($result !== false);
    }

    public function update_token($email, $token, $expiry)
    {
        // Đảm bảo $expiry là số nguyên để tránh lỗi định dạng
        $expiry = (int) $expiry;

        return $this->_db->update(
            $this->_table_registry,
            [
                'token'       => $token,
                'expiry'      => $expiry, // Lưu số nguyên 10 chữ số (Vd: 1714821600)
                'update_date' => wp_date('Y-m-d H:i:s', null, new DateTimeZone('Asia/Ho_Chi_Minh')),
            ],
            ['email' => $email],
            [
                '%s', // token là string
                '%d', // expiry PHẢI là integer để so sánh nhanh và chính xác
                '%s'  // update_date là string (datetime)
            ],
            ['%s'] // email là string
        );
    }

    // 3. 更新使用者密碼
    public function update_login($id, $session_key, $ip_address)
    {
        return $this->_db->update(
            $this->_table_registry,
            [
                'session_key' => $session_key,
                'ip_address'  => $ip_address,
                'last_login'  => wp_date('Y-m-d H:i:s', null, new DateTimeZone('Asia/Ho_Chi_Minh')),
            ],
            ['ID' => $id]
        );
    }

    // 3. 更新使用者密碼
    public function update_password($session_key, $new_password_hash)
    {
        return $this->_db->update(
            $this->_table_registry,
            [
                'password'    => $new_password_hash,
                'update_date' => current_time('mysql')
            ],
            ['session_key' => $session_key]
        );
    }

    // 3. 更新使用者密碼
    public function reset_password($email, $new_password_hash)
    {
        return $this->_db->update(
            $this->_table_registry,
            [
                'password'    => $new_password_hash,
                'token'       => null, // 清除 token
                'expiry'      => null, // 清除 expiry
                'update_date' => current_time('mysql')
            ],
            ['email' => $email]
        );
    }

    // 4. 更新使用者一般資訊
    public function update_info($session_key, $data)
    {
        // 自動加上更新時間
        $data['update_date'] = current_time('mysql');

        return $this->_db->update(
            $this->_table_registry,
            $data,
            ['session_key' => $session_key]
        );
    }

    // 5. 清除 Session (用於登出)
    public function clear_session($session_key)
    {
        return $this->_db->update(
            $this->_table_registry,
            ['session_key' => null],
            ['session_key' => $session_key]
        );
    }
}

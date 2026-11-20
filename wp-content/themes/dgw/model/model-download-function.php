<?php
declare(strict_types=1);

use DGW\Models\BaseCustomTableModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model thao tác bảng thành viên & lịch sử tải tài liệu (kế thừa BaseCustomTableModel, whitelist update_info và xóa global $wpdb)
 * [2026-11-20] - @author: Kelvin - Loại bỏ thuộc tính $_db dư thừa, sanitize insert_registration_data, fix NULL expiry trong reset_password và dọn orphan download_detail khi xóa user
 */
class Model_Download_Function extends BaseCustomTableModel
{
    private string $_table_registry;
    private string $_table_details;

    /**
     * Cache tĩnh trong request cho thống kê download/view theo tiêu đề
     *
     * @var array<int, array>
     */
    private static array $statsCache = [];

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo kết nối DB qua BaseCustomTableModel và tên các bảng download
     */
    public function __construct()
    {
        parent::__construct();
        $this->_table_registry = $this->db->prefix . 'download_registrations';
        $this->_table_details  = $this->db->prefix . 'download_detail';
    }

    /**
     * [03/11/2026] - Bổ sung trường kid vào bảng download_detail (1: Download file, 2: Xem video YouTube)
     */
    public function insert_download_detail($data)
    {
        $kid      = isset($data['kid']) ? (int) $data['kid'] : 1;
        $inserted = $this->db->insert(
            $this->_table_details,
            [
                'user_id'       => absint($data['user_id'] ?? 0),
                'title'         => sanitize_text_field((string) ($data['title'] ?? '')),
                'resource'      => esc_url_raw((string) ($data['resource'] ?? '')),
                'kid'           => $kid,
                'download_date' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s']
        );

        if (!$inserted) {
            error_log('Insert Error: ' . $this->db->last_error);
        } else {
            unset(self::$statsCache[$kid]);
        }

        return $inserted ? $this->db->insert_id : false;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lưu thông tin đăng ký thành viên mới với sanitize đầy đủ ở tầng Model
     */
    public function insert_registration_data($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $email       = sanitize_email((string) ($data['email'] ?? ''));
        $rawPassword = (string) ($data['password'] ?? '');
        if ($email === '' || $rawPassword === '') {
            return false;
        }

        $inserted = $this->db->insert(
            $this->_table_registry,
            [
                'username'    => sanitize_text_field((string) ($data['username'] ?? '')),
                'email'       => $email,
                'password'    => password_hash($rawPassword, PASSWORD_BCRYPT),
                'company'     => sanitize_text_field((string) ($data['company'] ?? '')),
                'phone'       => sanitize_text_field((string) ($data['phone'] ?? '')),
                'position'    => sanitize_text_field((string) ($data['position'] ?? '')),
                'tax'         => sanitize_text_field((string) ($data['tax'] ?? '')),
                'industry'    => sanitize_text_field((string) ($data['industry'] ?? '')),
                'department'  => sanitize_text_field((string) ($data['department'] ?? '')),
                'language'    => sanitize_text_field((string) ($data['language'] ?? 'vn')),
                'active_code' => sanitize_text_field((string) ($data['active_code'] ?? '')),
                'create_date' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? $this->db->insert_id : false;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy thông tin thành viên theo session_key
     */
    public function get_user_by_session($session_key)
    {
        if (empty($session_key)) {
            return false;
        }

        return $this->db->get_row(
            $this->db->prepare(
                "SELECT `ID`, `username`, `password`, `company`, `tax`, `industry`, `department`, `position`, `phone`, `email` FROM {$this->_table_registry} WHERE session_key = %s",
                (string) $session_key
            )
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy thông tin thành viên theo email
     */
    public function get_user_by_email($email)
    {
        if (empty($email)) {
            return null;
        }

        return $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->_table_registry} WHERE email = %s", (string) $email)
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy thông tin thành viên theo ID
     */
    public function get_user_by_id($id)
    {
        $userId = absint($id);
        if ($userId <= 0) {
            return null;
        }

        return $this->db->get_row(
            $this->db->prepare("SELECT * FROM {$this->_table_registry} WHERE ID = %d", $userId)
        );
    }

    /**
     * [18/11/2026] - Chỉ kiểm tra trùng email trong toàn bảng đăng ký (không kiểm tra username và không lọc trash)
     */
    public function check_email_username_exists($email, $username = null)
    {
        $emailValue = is_array($email) ? ($email['email'] ?? '') : (string) $email;

        return (int) $this->db->get_var($this->db->prepare(
            "SELECT COUNT(*) FROM {$this->_table_registry} WHERE email = %s",
            $emailValue
        ));
    }

    /**
     * [2026-11-19] - @author: Kelvin - Kích hoạt tài khoản thành viên theo email và token
     */
    public function active_member($email, $token)
    {
        if (empty($email) || empty($token)) {
            return false;
        }

        $data = [
            'status'      => '1',
            'active_code' => '',
            'update_date' => current_time('mysql'),
        ];

        $where = [
            'email'       => (string) $email,
            'active_code' => (string) $token,
        ];

        $result = $this->db->update($this->_table_registry, $data, $where, ['%s', '%s', '%s'], ['%s', '%s']);

        return ($result !== false);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cập nhật token quên mật khẩu và thời hạn hết hạn
     */
    public function update_token($email, $token, $expiry)
    {
        return $this->db->update(
            $this->_table_registry,
            [
                'token'       => (string) $token,
                'expiry'      => (int) $expiry,
                'update_date' => current_time('mysql'),
            ],
            ['email' => (string) $email],
            ['%s', '%d', '%s'],
            ['%s']
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cập nhật session và IP đăng nhập
     */
    public function update_login($id, $session_key, $ip_address)
    {
        return $this->db->update(
            $this->_table_registry,
            [
                'session_key' => (string) $session_key,
                'ip_address'  => (string) $ip_address,
                'last_login'  => current_time('mysql'),
            ],
            ['ID' => absint($id)],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cập nhật mật khẩu người dùng theo session_key
     */
    public function update_password($session_key, $new_password_hash)
    {
        return $this->db->update(
            $this->_table_registry,
            [
                'password'    => (string) $new_password_hash,
                'update_date' => current_time('mysql'),
            ],
            ['session_key' => (string) $session_key],
            ['%s', '%s'],
            ['%s']
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đặt lại mật khẩu an toàn, set NULL thực sự cho token, expiry và session_key
     */
    public function reset_password($email, $new_password_hash, $hashed_token = null)
    {
        $nowMysql = current_time('mysql');

        if ($hashed_token !== null) {
            $sql = $this->db->prepare(
                "UPDATE {$this->_table_registry}
                SET password = %s, token = NULL, expiry = NULL, session_key = NULL, update_date = %s
                WHERE email = %s AND token = %s AND expiry >= %d",
                (string) $new_password_hash,
                $nowMysql,
                (string) $email,
                (string) $hashed_token,
                time()
            );

            return $this->db->query($sql);
        }

        $sql = $this->db->prepare(
            "UPDATE {$this->_table_registry}
            SET password = %s, token = NULL, expiry = NULL, session_key = NULL, update_date = %s
            WHERE email = %s",
            (string) $new_password_hash,
            $nowMysql,
            (string) $email
        );

        return $this->db->query($sql);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cập nhật thông tin hồ sơ thành viên với whitelist cột được phép sửa
     */
    public function update_info($session_key, $data)
    {
        if (empty($session_key) || !is_array($data)) {
            return false;
        }

        $allowedColumns = ['username', 'company', 'phone', 'position', 'tax', 'industry', 'department', 'language'];
        $cleanData      = [];
        $formats        = [];

        foreach ($allowedColumns as $column) {
            if (array_key_exists($column, $data)) {
                $cleanData[$column] = sanitize_text_field((string) $data[$column]);
                $formats[]          = '%s';
            }
        }

        if (empty($cleanData)) {
            return false;
        }

        $cleanData['update_date'] = current_time('mysql');
        $formats[]                = '%s';

        return $this->db->update(
            $this->_table_registry,
            $cleanData,
            ['session_key' => (string) $session_key],
            $formats,
            ['%s']
        );
    }

    /**
     * [2026-11-19] - @author: Kelvin - Xóa session_key khi đăng xuất
     */
    public function clear_session($session_key)
    {
        if (empty($session_key)) {
            return false;
        }

        $sql = $this->db->prepare(
            "UPDATE {$this->_table_registry} SET session_key = NULL WHERE session_key = %s",
            (string) $session_key
        );

        return $this->db->query($sql);
    }

    /**
     * [18/11/2026] - Chuyển bản ghi vào/ra thùng rác dùng prepared statement cho danh sách ID
     * [2026-11-19] - @author: Kelvin - Dùng helper bulkUpdateByIds() thay thế global $wpdb
     */
    public function toTrash($arrData = [], $options = [])
    {
        $trash  = ($options === 'trash') ? 1 : 0;
        $status = ($trash === 1) ? 0 : 1;

        $this->bulkUpdateByIds(
            $this->_table_registry,
            ['trash' => $trash, 'status' => $status],
            ['%d', '%d'],
            $arrData['id'] ?? null
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xóa vĩnh viễn thành viên và dọn sạch bản ghi liên quan trong bảng download_detail
     */
    public function toDelete($arrData = [], $options = [])
    {
        $ids = $this->normalizeIds($arrData['id'] ?? null);
        if (empty($ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sqlDetails   = $this->db->prepare(
            "DELETE FROM {$this->_table_details} WHERE user_id IN ({$placeholders})",
            ...$ids
        );
        $this->db->query($sqlDetails);

        $this->bulkDeleteByIds($this->_table_registry, $ids);
        self::$statsCache = [];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy lịch sử download theo user_id
     */
    public function get_downloaded_by_user($user_id)
    {
        $userId = absint($user_id);
        if ($userId <= 0) {
            return [];
        }

        return $this->db->get_results(
            $this->db->prepare("SELECT * FROM {$this->_table_details} WHERE user_id = %d ORDER BY download_date DESC", $userId)
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lấy danh sách lịch sử theo loại kid, hỗ trợ giới hạn $limit tùy chọn
     */
    public function get_all_downloads($kid = 1, int $limit = 0)
    {
        if ($limit > 0) {
            $sql = $this->db->prepare(
                "SELECT d.*, m.username, m.email, m.company, m.phone 
                FROM {$this->_table_details} AS d
                LEFT JOIN {$this->_table_registry} AS m ON d.user_id = m.ID 
                WHERE d.kid = %d
                ORDER BY d.download_date DESC
                LIMIT %d",
                (int) $kid,
                $limit
            );
        } else {
            $sql = $this->db->prepare(
                "SELECT d.*, m.username, m.email, m.company, m.phone 
                FROM {$this->_table_details} AS d
                LEFT JOIN {$this->_table_registry} AS m ON d.user_id = m.ID 
                WHERE d.kid = %d
                ORDER BY d.download_date DESC",
                (int) $kid
            );
        }

        return $this->db->get_results($sql);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Thống kê tổng số lượt theo tiêu đề tài liệu và phân loại kid (có memoization trong request)
     */
    public function get_download_stats_by_title($kid = 1)
    {
        $kidInt = (int) $kid;
        if (isset(self::$statsCache[$kidInt])) {
            return self::$statsCache[$kidInt];
        }

        $sql = $this->db->prepare(
            "SELECT title, COUNT(ID) AS download_count 
            FROM {$this->_table_details} 
            WHERE kid = %d
            GROUP BY title 
            ORDER BY download_count DESC",
            $kidInt
        );

        return self::$statsCache[$kidInt] = ($this->db->get_results($sql, ARRAY_A) ?: []);
    }
}

<?php
// Date: 2026-06-25
// Chức năng: Khởi tạo mảng từ điển ngành nghề (Industry) đa ngôn ngữ
function get_industry_dictionary() {
    return [
        'Metal Processing' => [
            'vn' => 'Gia công kim loại',
            'cn' => '金屬加工'
        ],
        'Automotive Parts' => [
            'vn' => 'Phụ tùng linh kiện xe',
            'cn' => '汽車零件'
        ],
        'Plastic Injection' => [
            'vn' => 'Ép nhựa',
            'cn' => '塑料注塑'
        ],
        'Rubber' => [
            'vn' => 'Cao su',
            'cn' => '橡膠'
        ],
        'Electronic Parts' => [
            'vn' => 'Linh kiện điện tử',
            'cn' => '電子零件'
        ],
        'Wooden Furniture' => [
            'vn' => 'Nội thất gỗ',
            'cn' => '木製家具'
        ],
        'F&B' => [
            'vn' => 'F&B (Thực phẩm & Đồ uống)',
            'cn' => 'F&B (食品與飲料)'
        ],
        'Textiles and Garments' => [
            'vn' => 'Dệt may',
            'cn' => '紡織與服裝'
        ],
        'Shoes and Leather' => [
            'vn' => 'Giày, da',
            'cn' => '鞋子與皮革'
        ],
        'Packaging' => [
            'vn' => 'Bao bì',
            'cn' => '包裝'
        ],
        'Wires and Fiber Optics' => [
            'vn' => 'Dây điện, cáp quang',
            'cn' => '線纜與光纖'
        ],
        'Pharmaceuticals' => [
            'vn' => 'Dược phẩm',
            'cn' => '製藥'
        ],
        'Chemicals' => [
            'vn' => 'Hóa chất',
            'cn' => '化學品'
        ],
        'Machinery Manufacturing' => [
            'vn' => 'Chế tạo máy',
            'cn' => '機械製造'
        ],
        'IoT and Automation' => [
            'vn' => 'IoT/Tự động hóa',
            'cn' => 'IoT與自動化'
        ],
        'IT' => [
            'vn' => 'IT (Công nghệ thông tin)',
            'cn' => 'IT (資訊技術)'
        ],
        'Associations' => [
            'vn' => 'Hiệp hội',
            'cn' => '協會'
        ],
        'Services' => [
            'vn' => 'Dịch vụ',
            'cn' => '服務'
        ],
        'Other Manufacturing' => [
            'vn' => 'Sản xuất khác',
            'cn' => '其他製造'
        ],
        'Other' => [
            'vn' => 'Khác',
            'cn' => '其他'
        ]
    ];
}

// Date: 2026-06-25
// Chức năng: Khởi tạo mảng từ điển chức vụ (Position) đa ngôn ngữ
function get_position_dictionary() {
    return [
        'President' => [
            'vn' => 'Chủ tịch',
            'cn' => '總裁'
        ],
        'CEO' => [
            'vn' => 'CEO',
            'cn' => 'CEO'
        ],
        'Vice President' => [
            'vn' => 'Phó Chủ tịch',
            'cn' => '副總裁'
        ],
        'General Director' => [
            'vn' => 'Tổng Giám đốc',
            'cn' => '總經理'
        ],
        'Deputy General Director' => [
            'vn' => 'Phó Tổng Giám đốc',
            'cn' => '副總經理'
        ],
        'Director' => [
            'vn' => 'Giám đốc',
            'cn' => '經理'
        ],
        'Deputy Director' => [
            'vn' => 'Phó Giám đốc',
            'cn' => '副經理'
        ],
        'Secretary' => [
            'vn' => 'Thư ký',
            'cn' => '秘書'
        ],
        'Assistant' => [
            'vn' => 'Trợ lý',
            'cn' => '助理'
        ],
        'Manager' => [
            'vn' => 'Trưởng phòng',
            'cn' => '主管'
        ],
        'Team Leader' => [
            'vn' => 'Tổ trưởng',  
            'cn' => '組長'
        ],
        'Consultant' => [
            'vn' => 'Chuyên viên (tư vấn)',
            'cn' => '顧問'
        ],
        'Staff' => [
            'vn' => 'Nhân viên',
            'cn' => '員工'
        ]
    ];
}

// Date: 2026-06-25
// Chức năng: Khởi tạo mảng từ điển phòng ban (Department) đa ngôn ngữ
function get_department_dictionary() {
    return [
        'Board of Directors' => [
            'vn' => 'Ban lãnh đạo',
            'cn' => '董事會'
        ],
        'R&D' => [
            'vn' => 'R&D (Nghiên cứu & Phát triển)',
            'cn' => 'R&D (研究與開發)'
        ],
        'Sales' => [
            'vn' => 'Kinh doanh',
            'cn' => '銷售'
        ],
        'Purchasing' => [
            'vn' => 'Thu mua',
            'cn' => '採購'
        ],
        'Inventory' => [
            'vn' => 'Tồn kho',
            'cn' => '庫存'
        ],
        'Production' => [
            'vn' => 'Sản xuất',
            'cn' => '生產'
        ],
        'Quality Control' => [
            'vn' => 'Kiểm soát chất lượng (QC)',
            'cn' => '品質控制 (QC)'
        ],
        'Finance and Accounting' => [
            'vn' => 'Tài chính Kế toán',
            'cn' => '財務會計'
        ],
        'Marketing' => [
            'vn' => 'Marketing',
            'cn' => '行銷'
        ],
        'IT' => [
            'vn' => 'IT (Công nghệ thông tin)',
            'cn' => 'IT (資訊技術)'
        ],
        'HR' => [
            'vn' => 'HR (Nhân sự)',
            'cn' => 'HR (人力資源)'
        ],
        'Import and Export' => [
            'vn' => 'XNK (Xuất nhập khẩu)',
            'cn' => '進出口'
        ],
        'Other' => [
            'vn' => 'Khác',
            'cn' => '其他'
        ]
    ];
}
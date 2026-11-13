---
name: wp-mvc-standards
description: Triggers when architecting, writing, or refactoring WordPress features using the MVC (Model-View-Controller) design pattern. Enforces strict separation of concerns, PSR namespaces, and secure rendering.
---

# WordPress MVC Development Standards

## When to use this skill
- Trigger when building custom plugins, theme modules, or refactoring legacy procedural WordPress code into an MVC structure.

## CRITICAL ARCHITECTURAL CONSTRAINTS (STRICTLY ENFORCED)
- **STRICT SEPARATION OF CONCERNS:**
  - **Models:** Exclusively handle data fetching, database interactions (`$wpdb`, `WP_Query`), cache management, and business logic.
  - **Controllers:** Handle HTTP requests, hooks/filters (`add_action`, `add_filter`), sanitize user inputs, invoke Models, and pass prepared data to Views.
  - **Views:** Strictly restricted to HTML and display logic (simple loops, conditionals).
- **NO RAW QUERIES IN VIEWS:** Absolutely NO direct database queries (`$wpdb`, `WP_Query`, `get_posts()`, `get_post_meta()`) within View/template files.
- **LATE ESCAPING IN VIEWS:** All variables echoed in Views must be escaped using WordPress escaping functions (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- **PSR-4 NAMESPACING:** Every class must declare an appropriate `namespace` and adhere to PSR-4 autoloading standards.
- **NO TERMINAL CALLS:** Derive the current system date from context; do not execute bash commands to fetch the date.

## Output Requirements
- Provide production-ready, object-oriented PHP code directly. Skip basic conceptual explanations.
- Follow the mandatory standard header comment:
  `// [YYYY-MM-DD] - @author: Kelvin - [Mô tả module/chức năng]`

## Expected Output Example

```text
// 2026-09-30 - Developer: Kelvin - MVC Refactor Report

**Components Created:**
1. [Model]: `app/Models/Product.php` - Chứa logic lấy dữ liệu `WP_Query`.
2. [Controller]: `app/Controllers/ProductController.php` - Điều phối dữ liệu từ Model sang View.
3. [View]: `app/Views/product-list.php` - Template hiển thị sản phẩm.

**Implementation:**
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

// 2026-09-30 - @author: Kelvin - Controller điều phối dữ liệu sản phẩm sang view an toàn
use App\Models\Product;

class ProductController {
    public function render(): void {
        $model = new Product();
        $products = $model->getAll();

        require_once dirname(__DIR__) . '/Views/product-list.php';
    }
}
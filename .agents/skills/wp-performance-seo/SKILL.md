---
name: wp-performance-seo
description: Triggers when writing WordPress database queries, loop templates, or handling heavy data operations. Focuses on TTFB reduction, Technical SEO, and LCP optimization.
---

# WordPress Performance & SEO Standards

## When to use this skill
- Trigger when creating, editing, or optimizing WordPress database queries (`WP_Query`, `get_posts`, custom SQL), loop templates, or archive/single templates.

## CRITICAL PERFORMANCE CONSTRAINTS (STRICTLY ENFORCED)
- **CACHE-FIRST STRATEGY:** Always check the Transient API or Object Cache (`wp_cache_*`) before querying the database for post/product lists.
- **FORBID UNBOUNDED QUERIES:** Never use `'posts_per_page' => -1` or `'nopaging' => true`. Always define a strict numeric limit.
- **WP_QUERY OPTIMIZATION FLAGS:**
  - Set `'no_found_rows' => true` whenever pagination is not required (skips `SQL_CALC_FOUND_ROWS`).
  - Disable meta/term caches (`'update_post_meta_cache' => false`, `'update_post_term_cache' => false`) if the template does not read post meta or taxonomies.
  - Use `'fields' => 'ids'` when full `WP_Post` objects are not needed.
- **LCP & IMAGE RENDERING:**
  - The first viewport image (hero/featured image) MUST include `fetchpriority="high"` and MUST omit `loading="lazy"`.
  - All subsequent below-the-fold images must retain native `loading="lazy"` and `decoding="async"`.
- **NO TERMINAL CALLS:** Extract the current system date from context; do not execute terminal/bash commands to fetch the date.

## Instructions
1. **Response Structure:** Deliver clean, production-ready PHP/WordPress code directly. Omit generic explanations and conceptual fluff.
2. **Comment Format:** Prepend every function/block with the mandatory standard header:
   `// [YYYY-MM-DD] - @author: Kelvin - [Giải thích cách query này tối ưu TTFB/LCP]`

## Expected Output Example

```php
<?php
declare(strict_types=1);

// 2026-06-17 - @author: Kelvin - Lấy danh sách bài viết nổi bật, sử dụng Transient API để giảm TTFB xuống dưới 200ms
function get_featured_posts_optimized(): array {
    $transient_key = 'featured_posts_data';
    $posts = get_transient($transient_key);

    if (false === $posts) {$query = new WP_Query([
            'post_type'              => 'post',
            'posts_per_page'         => 5,
            'no_found_rows'          => true, // Bỏ qua đếm tổng số trang để query nhanh hơn
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'fields'                 => 'ids',
        ]);

        $posts =$query->posts;
        set_transient($transient_key,$posts, 12 * HOUR_IN_SECONDS);
    }

    return $posts;
}
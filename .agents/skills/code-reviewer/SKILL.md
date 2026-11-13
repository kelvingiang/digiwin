---
name: code-reviewer
description: Triggers when the user asks to review, audit, or check code before committing. Scans for performance bottlenecks, Technical SEO issues, and security vulnerabilities.
---

# Code Review & Performance Audit Standards

## When to use this skill
- Trigger when the user requests code review or audit, including prompts like: "Review file này", "Kiểm tra code", "Audit trước khi commit", "Check this code", "Pre-commit review".

## CRITICAL REVIEW CHECKLIST

### 1. WordPress / PHP Performance & TTFB:
- **N+1 Query Detection:** Flag any database query or meta function (e.g., `get_post_meta()`, `get_term_meta()`, custom queries) executed inside loops (`foreach`, `while`). Enforce bulk fetching or `update_post_meta_cache()`.
- **Caching Mechanism:** Verify whether expensive operations/queries utilize the Transients API or Object Cache (`wp_cache_*`).

### 2. Frontend, LCP & Rendering:
- **Core Web Vitals (LCP):** Ensure the primary viewport image (hero image) has `fetchpriority="high"` and does not use `loading="lazy"`.
- **CSSOM Optimization:** Disallow SCSS/CSS nesting deeper than 3 levels to maintain fast CSSOM parsing.
- **React Performance:** Flag missing `useMemo`, `useCallback`, or improperly scoped dependencies causing redundant re-renders.

### 3. WordPress Security:
- **Late Escaping:** 100% of dynamic data output must be properly escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- **SQL Injection Prevention:** Enforce `$wpdb->prepare()` for all custom SQL queries. Never concatenate variables into raw SQL.

## Output Requirements
- **No Fluff:** Be concise and direct. Do not include theoretical explanations or basic concepts.
- **Date Handling:** Extract the current system date from the environment/context (Do NOT run bash commands just to fetch the date).
- **Mandatory Deliverables:**
  1. Concise list of detected issues categorized by [Performance/TTFB], [LCP/Rendering], [Security], or [SEO].
  2. Fixed, production-ready code with standardized comment format:
     `// [YYYY-MM-DD] - @author: Kelvin - [Giải pháp fix bug]`

## Expected Output Example

```text
// 2026-09-30 - Reviewer: Kelvin - Code Audit Report

**Issues Detected:**
1. [TTFB/Performance]: `get_post_meta()` is called inside a `foreach` loop, resulting in N+1 database queries and increased server response time.
2. [Security]: Unescaped output `$user_input` poses an XSS risk.

**Fixed Code:**
```php
// 2026-09-30 - @author: Kelvin - Warm post meta cache upfront and escape output
update_post_meta_cache($post_ids);
foreach ($post_ids as$id) {
    $meta = get_post_meta($id, 'custom_key', true);
    echo esc_html($meta);
}
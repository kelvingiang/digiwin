<?php

// ===== CORE =====
foreach (glob(__DIR__ . '/code/*.php') as $file) {
    require_once $file;
}

// ===== FRONT =====
foreach (glob(__DIR__ . '/front/*.php') as $file) {
    require_once $file;
}

// ===== ADMIN (admin + ajax) =====
if (is_admin() || wp_doing_ajax()) {
    foreach (glob(__DIR__ . '/admin/*.php') as $file) {
        require_once $file;
    }
}

// ===== AJAX =====
if (wp_doing_ajax()) {
    foreach (glob(__DIR__ . '/ajax/*.php') as $file) {
        require_once $file;
    }
}

// ===== SHORTCODE =====
foreach (glob(__DIR__ . '/shortcode/*.php') as $file) {
    require_once $file;
}
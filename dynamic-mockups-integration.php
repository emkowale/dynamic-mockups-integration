<?php
/*
 * Plugin Name: Dynamic Mockups Integration
 * Description: Integrates Dynamic Mockups API with WooCommerce for live previews and featured image setting.
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Last Updated: May 10, 2025 11:59 PM EDT
 */

if (!defined('ABSPATH')) exit;

// === Admin Settings Panel ===
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// === Product Meta Box with Thumbnails and Smart Objects ===
require_once plugin_dir_path(__FILE__) . 'includes/product-meta-box.php';

// === Frontend Upload and Render UI ===
add_action('woocommerce_after_add_to_cart_button', function () {
    if (is_product()) {
        include plugin_dir_path(__FILE__) . 'includes/frontend.php';
    }
});

// === AJAX Upload Handler ===
require_once plugin_dir_path(__FILE__) . 'includes/upload-handler.php';

// === AJAX Render Handler ===
require_once plugin_dir_path(__FILE__) . 'includes/ajax-render.php';

// === Enqueue Frontend Assets (JS + CSS + Localized Vars) ===
add_action('wp_enqueue_scripts', function () {
    if (is_product()) {
        wp_enqueue_style(
            'dmi-frontend-style',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            '1.9.6'
        );

        wp_enqueue_script(
            'dmi-frontend',
            plugin_dir_url(__FILE__) . 'assets/js/frontend.js',
            array('jquery'),
            '1.9.6',
            true
        );

        wp_localize_script('dmi-frontend', 'dmi_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dmi_nonce'),
            'api_key'  => get_option('dmi_api_key') ?: ''
        ));
    }
});

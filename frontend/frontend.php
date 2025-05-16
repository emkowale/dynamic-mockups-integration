<?php
/*
 * File: frontend.php
 * Plugin Name: Dynamic Mockups Integration
 * Description: Front-end UI for image upload and rendering with Dynamic Mockups API
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Last Updated: May 10, 2025 10:07 PM EDT
 */

if (!defined('ABSPATH')) exit;

// Exit if not on product page frontend
if (!is_product() || is_admin()) return;

// Prevent fatal error if $product is not initialized
global $product;
if (!$product || !is_a($product, 'WC_Product')) return;

add_action('wp_enqueue_scripts', function() {
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
        'nonce'    => wp_create_nonce('dmi_upload'),
        'api_key'  => get_option('dmi_api_key') ?: ''
    ));
});

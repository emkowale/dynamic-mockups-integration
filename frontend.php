<?php
/*
 * File: frontend.php
 * Plugin Name: Dynamic Mockups Integration
 * Description: Integration for WooCommerce with Dynamic Mockups API
 * Author: Eric Kowalewski
 * Version: 1.9.5
 * Last Updated: April 29, 2025 5:45 PM EDT
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_after_add_to_cart_button', 'dmi_render_upload_ui');

add_action('wp_enqueue_scripts', 'dmi_enqueue_frontend_scripts');
function dmi_enqueue_frontend_scripts() {
    if (is_product()) {
        wp_enqueue_script(
            'dmi-frontend',
            plugin_dir_url(__FILE__) . 'frontend.js',
            array('jquery'),
            '1.9.5',
            true
        );

        $api_key = get_option('dmi_api_key');

        wp_localize_script('dmi-frontend', 'dmi_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_key'  => $api_key
        ));

        echo "<script>console.log('✅ DMI Debug: frontend.js enqueued.');</script>";
    }
}

function dmi_render_upload_ui() {
    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        echo "<script>console.warn('⚠️ DMI Debug: Product not available or invalid.');</script>";
        return;
    }

    $product_id = $product->get_id();
    $mockup_uuid = get_post_meta($product_id, '_dmi_mockup_uuid', true);
    $smartobject_uuid = get_post_meta($product_id, '_dmi_smartobject_uuid', true);

    echo "<script>console.log('✅ DMI Debug: Product ID: $product_id');</script>";
    echo "<script>console.log('✅ DMI Debug: Mockup UUID: $mockup_uuid');</script>";
    echo "<script>console.log('✅ DMI Debug: SmartObject UUID: $smartobject_uuid');</script>";

    if (empty($mockup_uuid) || empty($smartobject_uuid)) {
        echo "<script>console.warn('⚠️ DMI Debug: Missing UUIDs. Product ID: $product_id');</script>";
        echo '<p><em>Dynamic Mockups: Product not fully configured.</em></p>';
        return;
    }

    echo '<div id="dmi-upload-container" style="display:block !important; margin-top:20px; padding:10px; background:#f9f9f9; border-radius:8px; position:relative; z-index:9999;">';
    echo '  <label for="dmi-upload" style="display:block; font-weight:600; margin-bottom:8px;">Upload your image (PNG or JPG only):</label>';
    echo '  <input type="file" id="dmi-upload" accept="image/png,image/jpeg" style="display:block; width:100%; max-width:100%; font-size:16px; margin-bottom:10px;" />';
    echo '  <button type="button" id="dmi-submit" style="display:block; width:100%; font-size:16px; padding:10px; background:#0073aa; color:white; border:none; border-radius:5px;">Render My Image</button>';
    echo '  <input type="hidden" id="dmi-product-id" value="' . esc_attr($product_id) . '" />';
    echo '  <input type="hidden" id="dmi-mockup-uuid" value="' . esc_attr($mockup_uuid) . '" />';
    echo '  <input type="hidden" id="dmi-smartobject-uuid" value="' . esc_attr($smartobject_uuid) . '" />';
    echo '</div>';
}

// ✅ AJAX handler for image upload
add_action('wp_ajax_upload_image', 'dmi_handle_upload');
add_action('wp_ajax_nopriv_upload_image', 'dmi_handle_upload');

function dmi_handle_upload() {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['file'];
    $upload_overrides = array('test_form' => false);

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        wp_send_json_success(array('url' => $movefile['url']));
    } else {
        wp_send_json_error(array('error' => $movefile['error']));
    }
}


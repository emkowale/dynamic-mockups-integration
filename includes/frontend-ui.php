<?php
/*
 * File: includes/frontend-ui.php
 * Description: Frontend UI and script handling for image upload and Dynamic Mockups rendering
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: 2025-06-06 18:45 EDT
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    if (!is_singular('product')) return;

    wp_enqueue_style(
        'dmi-frontend-style',
        plugin_dir_url(__FILE__) . '../assets/css/frontend.css',
        array(),
        '1.9.6'
    );

    wp_localize_script('dmi-upload-handler', 'dmi_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('dmi_nonce'),
        'api_key'  => get_option('dmi_api_key') ?: ''
    ));
});

add_action('woocommerce_before_add_to_cart_button', function () {
    global $product;

    if (!is_singular('product') || !$product || !is_a($product, 'WC_Product')) return;

    $mockup_uuid = get_post_meta($product->get_id(), '_dmi_mockup_uuid', true);
    $smartobject_uuid = get_post_meta($product->get_id(), '_dmi_smartobject_uuid', true);

    if (!$mockup_uuid || !$smartobject_uuid) {
        echo '<p class="dmi-warning">⚠️ This product is missing Dynamic Mockup settings.</p>';
        return;
    }

    // Add dynamic class for layout control
    $type_class = $product->is_type('variable') ? 'dmi-variable' : 'dmi-simple';

    echo '<div class="dmi-cart-block ' . esc_attr($type_class) . '">';
    
    echo '<div id="dmi-upload-container">';
    echo '<input type="file" id="dmi-upload" accept="image/png, image/jpeg" style="display:none;">';
    echo '<button type="button" id="dmi-upload-button" class="button alt dmi-upload-button">Upload your own image</button>';
    echo '<div id="dmi-upload-preview"></div>';
    echo '</div>';

    echo '<div id="dmi-spinner-overlay" style="display:none;"><div class="dmi-spinner"></div></div>';

    echo '<input type="hidden" id="dmi-mockup-uuid" value="' . esc_attr($mockup_uuid) . '">';
    echo '<input type="hidden" id="dmi-smartobject-uuid" value="' . esc_attr($smartobject_uuid) . '">';
    echo '<input type="hidden" id="dmi_color_count" name="dmi_color_count" value="">';
    echo '<input type="hidden" id="dmi_color_hexes" name="dmi_color_hexes" value="">';

    echo '</div>'; // .dmi-cart-block
});

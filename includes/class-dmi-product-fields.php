<?php
/*
 * Filename: class-dmi-product-fields.php
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Version: 1.9.6
 * Date: 2025-04-29
 * Time: 12:58 PM EST
 */

function dmi_product_fields_init() {
    add_action('woocommerce_product_options_general_product_data', 'dmi_render_mockup_meta_box');
    add_action('save_post_product', 'dmi_save_mockup_fields');
    add_action('admin_enqueue_scripts', 'dmi_enqueue_admin_assets');
}

function dmi_render_mockup_meta_box() {
    global $post;
    $mockup_uuid = get_post_meta($post->ID, '_dmi_mockup_uuid', true);
    $smart_object_uuid = get_post_meta($post->ID, '_dmi_smart_object_uuid', true);

    echo '<div id="dmi_mockup_selector" style="margin-bottom:10px;"></div>';
    echo '<input type="hidden" id="dmi_mockup_uuid_field" name="_dmi_mockup_uuid" value="' . esc_attr($mockup_uuid) . '" />';
    echo '<select id="dmi_smart_object_selector" name="_dmi_smart_object_uuid" style="width:100%;"></select>';
}

function dmi_save_mockup_fields($post_id) {
    if (isset($_POST['_dmi_mockup_uuid'])) {
        update_post_meta($post_id, '_dmi_mockup_uuid', sanitize_text_field($_POST['_dmi_mockup_uuid']));
    }
    if (isset($_POST['_dmi_smart_object_uuid'])) {
        update_post_meta($post_id, '_dmi_smart_object_uuid', sanitize_text_field($_POST['_dmi_smart_object_uuid']));
    }
}

function dmi_enqueue_admin_assets($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    if (get_post_type() !== 'product') {
        return;
    }

    wp_enqueue_script('dmi-admin-js', plugin_dir_url(__FILE__) . '../admin.js', array('jquery'), null, true);

    // Load mockups
    $api_key = get_option('dmi_api_key');
    $mockups = array();

    if (!empty($api_key)) {
        $response = wp_remote_get('https://app.dynamicmockups.com/api/v1/mockups', array(
            'headers' => array(
                'x-api-key' => $api_key
            )
        ));

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (!empty($data['data'])) {
                $mockups = $data['data'];
            }
        }
    }

    // Pass mockups into window scope
    wp_add_inline_script('dmi-admin-js', 'window.dmi_mockups = ' . json_encode($mockups) . ';', 'before');
}

// Enqueue admin.css for WooCommerce product editor
add_action('admin_enqueue_scripts', function($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        if (get_post_type() === 'product') {
            wp_enqueue_style('dmi-admin-css', plugin_dir_url(__FILE__) . '../admin.css', array(), null);
        }
    }
});
?>


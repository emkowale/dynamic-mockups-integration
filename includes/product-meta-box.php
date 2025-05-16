<?php
/*
 * File: product-meta-box.php
 * Description: Adds mockup UUID and smart object UUID fields with dynamic dropdown filtering and sets product image
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 7, 2025 1:34 PM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_product_options_general_product_data', function() {
    global $post;

    $mockups = get_option('dmi_mockup_data', []);
    $smart_objects_grouped = get_option('dmi_smart_object_data', []);

    $selected_mockup = get_post_meta($post->ID, '_dmi_mockup_uuid', true);
    $selected_smart_object = get_post_meta($post->ID, '_dmi_smartobject_uuid', true);

    echo '<div class="options_group">';

    echo '<h4 style="padding-left: 15px; font-weight: bold; margin-bottom: 0;">Select Mockup</h4>';
    echo '<label style="padding-left: 15px !important; font-weight: bold;">Mockup UUID</label><br />';
    echo '<div id="dmi-mockup-list" style="overflow-x: auto; white-space: nowrap; padding: 10px 0;">';
    foreach ($mockups as $uuid => $mockup) {
        $is_selected = $uuid === $selected_mockup ? 'border: 2px solid blue;' : 'border: 1px solid gray;';
        echo '<img src="' . esc_url($mockup['thumbnail']) . '" data-uuid="' . esc_attr($uuid) . '" class="dmi-mockup-thumb" style="cursor:pointer; width:100px; height:auto; margin-right:10px; ' . $is_selected . '" />';
    }
    echo '<input type="hidden" name="_dmi_mockup_uuid" id="_dmi_mockup_uuid" value="' . esc_attr($selected_mockup) . '" />';
    echo '</div>';

    echo '<h4 style="padding-left: 15px; font-weight: bold; margin-bottom: 0;">Select Smart Object</h4>';
    echo '<label style="padding-left: 15px !important; font-weight: bold;">Smart Object UUID</label><br />';
    echo '<select name="_dmi_smartobject_uuid" id="_dmi_smartobject_uuid" style="min-width: 300px; margin-top: 10px;">';

    if (!empty($selected_mockup) && isset($smart_objects_grouped[$selected_mockup])) {
        foreach ($smart_objects_grouped[$selected_mockup] as $so) {
            $so_uuid = esc_attr($so['uuid']);
            $so_name = esc_html($so['name']);
            $selected_attr = $so_uuid === $selected_smart_object ? 'selected' : '';
            echo "<option value='$so_uuid' $selected_attr>$so_name</option>";
        }
    }

    echo '</select>';
    echo '</div>';
});

add_action('woocommerce_process_product_meta', function($post_id) {
    if (isset($_POST['_dmi_mockup_uuid'])) {
        $mockup_uuid = sanitize_text_field($_POST['_dmi_mockup_uuid']);
        update_post_meta($post_id, '_dmi_mockup_uuid', $mockup_uuid);

        $mockups = get_option('dmi_mockup_data', []);
        if (isset($mockups[$mockup_uuid]['thumbnail'])) {
            $thumbnail_url = esc_url_raw($mockups[$mockup_uuid]['thumbnail']);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            // Download and sideload the image
            $tmp = download_url($thumbnail_url);
            if (!is_wp_error($tmp)) {
                $file_array = [
                    'name'     => basename($thumbnail_url),
                    'tmp_name' => $tmp
                ];

                $attachment_id = media_handle_sideload($file_array, $post_id);

                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                } else {
                    error_log('❌ DMI: Failed to sideload image: ' . $attachment_id->get_error_message());
                }
            } else {
                error_log('❌ DMI: Failed to download thumbnail: ' . $tmp->get_error_message());
            }
        }
    }

    if (isset($_POST['_dmi_smartobject_uuid'])) {
        update_post_meta($post_id, '_dmi_smartobject_uuid', sanitize_text_field($_POST['_dmi_smartobject_uuid']));
    }
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_script('dmi-admin-product', plugin_dir_url(__FILE__) . '../assets/js/admin-product.js', array('jquery'), '1.0', true);
        wp_localize_script('dmi-admin-product', 'dmi_data', [
            'smart_objects' => get_option('dmi_smart_object_data', [])
        ]);
    }
});

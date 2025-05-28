<?php
/*
 * File: includes/email-render-handler.php
 * Description: Adds rendered image to all WooCommerce emails and strips uploaded image
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 19:45 EDT
 */

add_filter('woocommerce_order_item_name', function ($item_name, $item, $is_visible) {
    if (is_admin()) return $item_name;

    $rendered_url = wc_get_order_item_meta($item->get_id(), '_dmi_rendered_image_url', true);
    if ($rendered_url && strpos($item_name, $rendered_url) === false) {
        $item_name .= '<br><img src="' . esc_url($rendered_url) . '" style="max-width:150px; margin-top:6px;">';
    }

    return $item_name;
}, 10, 3);

add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta, $item) {
    foreach ($formatted_meta as $key => $meta) {
        if (in_array($meta->key, ['_dmi_uploaded_image_url', 'dmi_uploaded_image_url', '_dmi_rendered_image_url'])) {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}, 10, 2);

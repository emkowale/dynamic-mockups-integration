<?php
/*
 * File: includes/save-order-meta.php
 * Description: Saves image URLs from cart item to WooCommerce order item meta
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 22:23 EDT
 */

add_filter('woocommerce_add_cart_item_data', 'dmi_store_custom_image_urls', 10, 2);
function dmi_store_custom_image_urls($cart_item_data, $product_id) {
    if (!empty($_POST['dmi_uploaded_image_url'])) {
        $cart_item_data['_dmi_uploaded_image_url'] = esc_url_raw($_POST['dmi_uploaded_image_url']);
    }

    if (!empty($_POST['dmi_rendered_image_url'])) {
        $cart_item_data['_dmi_rendered_image_url'] = esc_url_raw($_POST['dmi_rendered_image_url']);
    }

    return $cart_item_data;
}

add_action('woocommerce_checkout_create_order_line_item', 'dmi_save_image_urls_to_order_item', 10, 4);
function dmi_save_image_urls_to_order_item($item, $cart_item_key, $values, $order) {
    $rendered_url = $values['_dmi_rendered_image_url'] ?? $values['dmi_rendered_image'] ?? '';
    $uploaded_url = $values['_dmi_uploaded_image_url'] ?? $values['dmi_uploaded_image'] ?? '';

    if (!empty($rendered_url)) {
        $item->add_meta_data('_dmi_rendered_image_url', esc_url_raw($rendered_url), true);
    }

    if (!empty($uploaded_url)) {
        $item->add_meta_data('_dmi_uploaded_image_url', esc_url_raw($uploaded_url), true);
    }
}

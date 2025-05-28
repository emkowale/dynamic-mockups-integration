<?php
/*
 * File: includes/save-order-meta.php
 * Description: Saves image URLs from cart item to WooCommerce order item meta
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 21:46 EDT
 */

add_action('woocommerce_checkout_create_order_line_item', 'dmi_save_image_urls_to_order_item', 10, 4);

function dmi_save_image_urls_to_order_item($item, $cart_item_key, $values, $order) {
    // Log cart item for debugging
    error_log('🛒 Saving meta for cart item ' . $cart_item_key);
    error_log('📦 Cart item values: ' . print_r($values, true));

    // Pull image URLs from cart item (supporting both key formats)
    $rendered_url = $values['_dmi_rendered_image_url'] ?? $values['dmi_rendered_image'] ?? '';
    $uploaded_url = $values['_dmi_uploaded_image_url'] ?? $values['dmi_uploaded_image'] ?? '';

    if (!empty($rendered_url)) {
        $item->add_meta_data('_dmi_rendered_image_url', esc_url_raw($rendered_url), true);
        error_log('✅ Saved _dmi_rendered_image_url to order item: ' . $rendered_url);
    } else {
        error_log('❌ _dmi_rendered_image_url not found');
    }

    if (!empty($uploaded_url)) {
        $item->add_meta_data('_dmi_uploaded_image_url', esc_url_raw($uploaded_url), true);
        error_log('✅ Saved _dmi_uploaded_image_url to order item: ' . $uploaded_url);
    } else {
        error_log('ℹ️ _dmi_uploaded_image_url not set');
    }
}

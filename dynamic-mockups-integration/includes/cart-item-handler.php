<?php
/*
 * File: includes/cart-item-handler.php
 * Description: Attaches rendered and uploaded image URLs to cart item data
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 21:30 EDT
 */

add_filter('woocommerce_add_cart_item_data', 'dmi_attach_image_urls_to_cart_item', 10, 3);

function dmi_attach_image_urls_to_cart_item($cart_item_data, $product_id, $variation_id) {
    // Manually check both values and choose the non-empty one
    $rendered_url = '';
    if (!empty($_POST['dmi_rendered_image_url'])) {
        $rendered_url = $_POST['dmi_rendered_image_url'];
        error_log('✅ Fallback not needed: got dmi_rendered_image_url');
    } elseif (!empty($_POST['dmi_rendered_image'])) {
        $rendered_url = $_POST['dmi_rendered_image'];
        error_log('✅ Used fallback: dmi_rendered_image');
    }

    if (!empty($rendered_url)) {
        $cart_item_data['_dmi_rendered_image_url'] = sanitize_text_field($rendered_url);
        error_log('✅ 🛒 _dmi_rendered_image_url added to cart item: ' . $rendered_url);
    } else {
        error_log('❌ _dmi_rendered_image_url missing from cart item');
    }

    if (!empty($_POST['dmi_uploaded_image_url'])) {
        $cart_item_data['_dmi_uploaded_image_url'] = sanitize_text_field($_POST['dmi_uploaded_image_url']);
        error_log('✅ 🛒 _dmi_uploaded_image_url added to cart item: ' . $_POST['dmi_uploaded_image_url']);
    } else {
        error_log('ℹ️ _dmi_uploaded_image_url not set');
    }

    return $cart_item_data;
}

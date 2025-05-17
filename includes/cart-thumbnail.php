<?php
/*
 * File: includes/cart-thumbnail.php
 * Description: Overrides WooCommerce cart item thumbnail with rendered image if present
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 18, 2025 11:45 EDT
 */

if (!defined('ABSPATH')) exit;

// ✅ Save rendered image to cart item when product is added to cart
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    if (!empty($_POST['dmi_rendered_image'])) {
        error_log('📦 DMI: Saving rendered image to cart item data: ' . $_POST['dmi_rendered_image']);
        $cart_item_data['dmi_rendered_image'] = esc_url_raw($_POST['dmi_rendered_image']);
    }
    return $cart_item_data;
}, 10, 2);

// ✅ Display custom thumbnail in the cart if available
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item, $cart_item_key) {
    if (!empty($cart_item['dmi_rendered_image'])) {
        error_log('🟢 DMI: Overriding cart thumbnail with rendered image: ' . $cart_item['dmi_rendered_image']);
        return '<img src="' . esc_url($cart_item['dmi_rendered_image']) . '" alt="Custom Rendered Image" style="max-width: 100px; height: auto;" />';
    } else {
        error_log('⚠️ DMI: No rendered image found in cart item.');
    }
    return $thumbnail;
}, 10, 3);

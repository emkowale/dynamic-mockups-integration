/*
 * File: frontend/cart-hooks.php
 * Description: Handles storing image URLs in cart + displaying rendered thumbnail in WooCommerce cart
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 3:10 AM EDT
 */

if (!defined('ABSPATH')) exit;

// Store uploaded + rendered image URLs in cart item
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    if (isset($_POST['dmi_uploaded_image_url'])) {
        $cart_item_data['dmi_uploaded_image_url'] = esc_url_raw($_POST['dmi_uploaded_image_url']);
    }
    if (isset($_POST['dmi_rendered_image_url'])) {
        $cart_item_data['dmi_rendered_image_url'] = esc_url_raw($_POST['dmi_rendered_image_url']);
    }
    return $cart_item_data;
}, 10, 2);

// Preserve image data across sessions
add_filter('woocommerce_get_cart_item_from_session', function ($item, $values) {
    if (isset($values['dmi_uploaded_image_url'])) {
        $item['dmi_uploaded_image_url'] = $values['dmi_uploaded_image_url'];
    }
    if (isset($values['dmi_rendered_image_url'])) {
        $item['dmi_rendered_image_url'] = $values['dmi_rendered_image_url'];
    }
    return $item;
}, 10, 2);

// Display rendered image in cart
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item) {
    if (!empty($cart_item['dmi_rendered_image_url'])) {
        $thumbnail = '<img src="' . esc_url($cart_item['dmi_rendered_image_url']) . '" style="max-height:80px; border-radius:8px;">';
    }
    return $thumbnail;
}, 10, 2);

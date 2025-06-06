/*
 * File: frontend/cart-thumbnails.php
 * Description: Displays rendered mockup image as product thumbnail in the WooCommerce cart
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 3:55 AM EDT
 */

if (!defined('ABSPATH')) exit;

// Filter cart item thumbnail
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item, $cart_item_key) {
    if (!empty($cart_item['dmi_rendered_image_url'])) {
        return '<img src="' . esc_url($cart_item['dmi_rendered_image_url']) . '" alt="Mockup" style="max-height:60px;">';
    }
    return $thumbnail;
}, 10, 3);

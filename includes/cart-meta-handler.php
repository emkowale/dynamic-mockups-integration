<?php
/*
 * File: includes/cart-meta-handler.php
 * Description: Saves uploaded image color data to WooCommerce order metadata
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: June 03, 2025 14:15 EDT
 */

if (!defined('ABSPATH')) exit;

// Add custom cart item data from POST
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id) {
    if (!empty($_POST['dmi_color_count'])) {
        $cart_item_data['dmi_color_count'] = sanitize_text_field($_POST['dmi_color_count']);
    }

    if (!empty($_POST['dmi_color_hexes'])) {
        $cart_item_data['dmi_color_hexes'] = sanitize_text_field($_POST['dmi_color_hexes']);
    }

    return $cart_item_data;
}, 10, 3);

// Save color data to order item meta
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    if (isset($values['dmi_color_count'])) {
        $item->add_meta_data('Color Count', $values['dmi_color_count']);
    }

    if (isset($values['dmi_color_hexes'])) {
        $item->add_meta_data('Hex Colors', $values['dmi_color_hexes']);
    }
}, 10, 4);

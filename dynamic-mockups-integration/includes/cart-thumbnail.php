<?php
/*
 * File: includes/cart-thumbnail.php
 * Description: Overrides WooCommerce cart item thumbnail with rendered image if present
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 19, 2025 00:07 EDT
 */

if (!defined('ABSPATH')) exit;

// STEP 1: Inject debug to console during Add to Cart (early POST capture)
add_action('woocommerce_before_single_product', function () {
    if (!is_product()) return;

    $val = isset($_REQUEST['dmi_rendered_image']) ? esc_url_raw($_REQUEST['dmi_rendered_image']) : 'null';
    echo "<script>console.log('🏰 STEP 1: dmi_rendered_image from REQUEST = \"$val\"');</script>";
});

// STEP 2: Add rendered image to cart item
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    if (!empty($_REQUEST['dmi_rendered_image'])) {
        $rendered = esc_url_raw($_REQUEST['dmi_rendered_image']);
        $cart_item_data['dmi_rendered_image'] = $rendered;
        echo "<script>console.log('🏰 STEP 2: Added rendered image to cart item: \"$rendered\"');</script>";
    } else {
        echo "<script>console.warn('🏰 STEP 2: No rendered image found in REQUEST');</script>";
    }
    return $cart_item_data;
}, 10, 2);

// STEP 3: Log cart contents before totals
add_action('woocommerce_before_calculate_totals', function ($cart) {
    foreach ($cart->get_cart() as $key => $item) {
        $meta_keys = implode(', ', array_keys($item));
        $has_rendered = !empty($item['dmi_rendered_image']) ? '✅ YES' : '❌ NO';
        $img = !empty($item['dmi_rendered_image']) ? $item['dmi_rendered_image'] : 'N/A';
        echo "<script>console.log('🏰 STEP 3: Cart item [$key] keys: $meta_keys | Rendered: $has_rendered | URL: \"$img\"');</script>";
    }
});

// STEP 4: Override cart thumbnail if available
add_filter('woocommerce_cart_item_thumbnail', function ($thumbnail, $cart_item, $cart_item_key) {
    if (!empty($cart_item['dmi_rendered_image'])) {
        $url = esc_url($cart_item['dmi_rendered_image']);
        echo "<script>console.log('🏰 STEP 4: Overriding thumbnail for [$cart_item_key] with: \"$url\"');</script>";
        return '<img src="' . $url . '" alt="Custom Rendered Image" style="max-width: 100px; height: auto;" />';
    } else {
        echo "<script>console.warn('🏰 STEP 4: No rendered image for [$cart_item_key], using default thumbnail');</script>";
    }
    return $thumbnail;
}, 10, 3);

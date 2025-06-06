/*
 * File: frontend/cleanup.php
 * Description: Deletes uploaded and rendered temp images when WooCommerce order is marked complete
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 4:02 AM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_order_status_completed', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    foreach ($order->get_items() as $item) {
        $urls = [
            $item->get_meta('Uploaded Image'),
            $item->get_meta('Rendered Image')
        ];

        foreach ($urls as $url) {
            if (!$url) continue;

            $parsed = wp_parse_url($url);
            $path = $parsed['path'] ?? '';
            if (!$path) continue;

            $file = ABSPATH . ltrim($path, '/');
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
});

// ✅ Also add image URLs to cart items on add-to-cart
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['dmi_uploaded_image_url'])) {
        $cart_item_data['dmi_uploaded_image_url'] = esc_url_raw($_POST['dmi_uploaded_image_url']);
    }
    if (isset($_POST['dmi_rendered_image_url'])) {
        $cart_item_data['dmi_rendered_image_url'] = esc_url_raw($_POST['dmi_rendered_image_url']);
    }
    return $cart_item_data;
}, 10, 3);

add_filter('woocommerce_get_cart_item_from_session', function ($item, $values) {
    if (isset($values['dmi_uploaded_image_url'])) {
        $item['dmi_uploaded_image_url'] = $values['dmi_uploaded_image_url'];
    }
    if (isset($values['dmi_rendered_image_url'])) {
        $item['dmi_rendered_image_url'] = $values['dmi_rendered_image_url'];
    }
    return $item;
});

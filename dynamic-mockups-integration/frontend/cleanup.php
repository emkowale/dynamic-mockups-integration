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

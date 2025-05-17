/*
 * File: frontend/account-thumbnails.php
 * Description: Shows uploaded and rendered image previews in My Account → Orders view
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 4:00 AM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_order_item_meta_end', function ($item_id, $item, $order, $plain_text) {
    if (!is_account_page()) return;

    $uploaded_url = $item->get_meta('Uploaded Image');
    $rendered_url = $item->get_meta('Rendered Image');

    if ($uploaded_url || $rendered_url) {
        echo '<div style="margin-top:8px;">';
        if ($uploaded_url) {
            echo '<div><strong>Uploaded:</strong><br><img src="' . esc_url($uploaded_url) . '" style="max-width:120px; margin-top:4px; border-radius:4px;"></div>';
        }
        if ($rendered_url) {
            echo '<div style="margin-top:8px;"><strong>Rendered:</strong><br><img src="' . esc_url($rendered_url) . '" style="max-width:120px; margin-top:4px; border-radius:4px;"></div>';
        }
        echo '</div>';
    }
}, 10, 4);

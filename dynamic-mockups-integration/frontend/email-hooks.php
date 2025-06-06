/*
 * File: frontend/email-hooks.php
 * Description: Adds uploaded and rendered image previews to WooCommerce emails (customer + admin)
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 3:50 AM EDT
 */

if (!defined('ABSPATH')) exit;

// Add metadata to order line item
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    if (!empty($values['dmi_uploaded_image_url'])) {
        $item->add_meta_data('Uploaded Image', esc_url_raw($values['dmi_uploaded_image_url']));
    }
    if (!empty($values['dmi_rendered_image_url'])) {
        $item->add_meta_data('Rendered Image', esc_url_raw($values['dmi_rendered_image_url']));
    }
}, 10, 4);

// Display preview images in emails
add_filter('woocommerce_order_item_meta_end', function ($item_id, $item, $order, $plain_text) {
    $uploaded_url = $item->get_meta('Uploaded Image');
    $rendered_url = $item->get_meta('Rendered Image');

    if ($uploaded_url || $rendered_url) {
        echo '<div style="margin-top:10px;">';
        if ($uploaded_url) {
            echo '<div><strong>Uploaded:</strong><br><img src="' . esc_url($uploaded_url) . '" style="max-width:150px; margin-top:5px; border-radius:4px;"></div>';
        }
        if ($rendered_url) {
            echo '<div style="margin-top:10px;"><strong>Rendered:</strong><br><img src="' . esc_url($rendered_url) . '" style="max-width:150px; margin-top:5px; border-radius:4px;"></div>';
        }
        echo '</div>';
    }
}, 10, 4);

<?php
/*
 * File: includes/test-email-preview.php
 * Description: Renders a fake order email preview in the browser for testing email output
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 17:13 EDT
 */

add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        'DMI Email Preview',
        'DMI Email Preview',
        'manage_options',
        'dmi-email-preview',
        'dmi_render_email_preview'
    );
});

function dmi_render_email_preview() {
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if (!$order_id) {
        echo '<div class="notice notice-error"><p>⚠️ Please provide a valid <code>order_id</code> in the URL.</p></div>';
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        echo '<div class="notice notice-error"><p>❌ Order not found.</p></div>';
        return;
    }

    echo '<div class="wrap"><h1>DMI Email Preview for Order #' . esc_html($order->get_id()) . '</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Product</th><th>Rendered Image & Metadata</th></tr></thead><tbody>';

    foreach ($order->get_items() as $item_id => $item) {
        $product_name = $item->get_name();
        $all_meta = wc_get_order_item_meta($item_id, '', false);

        $rendered_url_raw = isset($all_meta['_dmi_rendered_image_url']) ? $all_meta['_dmi_rendered_image_url'] : null;
        $rendered_url = is_array($rendered_url_raw) ? reset($rendered_url_raw) : $rendered_url_raw;

        echo '<tr>';
        echo '<td>' . esc_html($product_name) . '</td>';
        echo '<td>';

        if ($rendered_url) {
            echo '<img src="' . esc_url($rendered_url) . '" style="max-width: 150px;"><br>';
            echo "<script>console.log('✅ Rendered image for item {$item_id}: " . esc_js($rendered_url) . "');</script>";
        } else {
            echo '<em>No rendered image found</em><br>';
            echo "<script>console.warn('❌ No rendered image for item {$item_id}');</script>";
        }

        echo "<script>console.groupCollapsed('🧾 Meta for Item {$item_id}');</script>";
        foreach ($all_meta as $key => $value) {
            $val = is_array($value) ? json_encode($value) : $value;
            echo "<script>console.log('" . esc_js($key) . ": " . esc_js($val) . "');</script>";
        }
        echo "<script>console.groupEnd();</script>";

        echo '</td></tr>';
    }

    echo '</tbody></table></div>';
}

<?php
/*
 * File: includes/admin-order-meta.php
 * Description: Shows rendered/uploaded image previews with download links per order line item, gracefully handling missing files
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 23:15 EDT
 */

add_action('woocommerce_after_order_itemmeta', 'dmi_render_images_inline_per_item', 10, 3);

function dmi_render_images_inline_per_item($item_id, $item, $order) {
    if ($item->get_type() !== 'line_item') return;

    $rendered_url = $item->get_meta('_dmi_rendered_image_url');
    $uploaded_url = $item->get_meta('_dmi_uploaded_image_url');

    if (!$rendered_url && !$uploaded_url) return;

    echo '<div class="dmi-inline-images" style="margin-top: 10px;">';
    echo '<table style="border-collapse: collapse; margin-top: 5px;">';
    echo '<thead><tr><th style="text-align:left;">Image</th><th>Preview</th><th>Download</th></tr></thead>';
    echo '<tbody>';

    if ($uploaded_url) {
        $exists = dmi_file_exists_from_url($uploaded_url);
        echo '<tr>';
        echo '<td style="padding: 4px;">Uploaded</td>';
        echo '<td style="padding: 4px;">' . ($exists
            ? '<img src="' . esc_url($uploaded_url) . '" style="max-width:70px; border:1px solid #ccc;">'
            : '<span style="color: #999;">File not available</span>') . '</td>';
        echo '<td style="padding: 4px;">' . ($exists
            ? '<a class="button" href="' . esc_url($uploaded_url) . '" download target="_blank">Download</a>'
            : '<span style="color: #999;">Unavailable</span>') . '</td>';
        echo '</tr>';
    }

    if ($rendered_url) {
        $exists = dmi_file_exists_from_url($rendered_url);
        echo '<tr>';
        echo '<td style="padding: 4px;">Rendered</td>';
        echo '<td style="padding: 4px;">' . ($exists
            ? '<img src="' . esc_url($rendered_url) . '" style="max-width:70px; border:1px solid #ccc;">'
            : '<span style="color: #999;">File not available</span>') . '</td>';
        echo '<td style="padding: 4px;">' . ($exists
            ? '<a class="button" href="' . esc_url($rendered_url) . '" download target="_blank">Download</a>'
            : '<span style="color: #999;">Unavailable</span>') . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}

function dmi_file_exists_from_url($url) {
    $upload_dir = wp_upload_dir();
    $baseurl = $upload_dir['baseurl'];
    $basedir = $upload_dir['basedir'];

    // ✅ External URL: assume it exists (no file_exists() check)
    if (strpos($url, $baseurl) === false) {
        return true;
    }

    $relative_path = str_replace($baseurl, '', $url);
    $full_path = $basedir . $relative_path;
    return file_exists($full_path);
}

<?php
/*
 * File: upload-handler.php
 * Description: Handles AJAX image uploads from frontend.js
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 10, 2025 9:56 PM EDT
 */

if (!defined('ABSPATH')) exit;

// Frontend AJAX handler for logged-in and guest users
add_action('wp_ajax_dmi_upload_image', 'dmi_upload_image_callback');
add_action('wp_ajax_nopriv_dmi_upload_image', 'dmi_upload_image_callback');

function dmi_upload_image_callback() {
    check_ajax_referer('dmi_upload');

    if (!isset($_FILES['file'])) {
        wp_send_json_error(['message' => 'No file uploaded.']);
    }

    $file = $_FILES['file'];

    // Validate file type
    $allowed_types = ['image/png', 'image/jpeg'];
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error(['message' => 'Only PNG and JPG files are allowed.']);
    }

    // Upload the file to the WordPress Media Library
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $upload_overrides = ['test_form' => false];
    $movefile = wp_handle_upload($file, $upload_overrides);

    if (!$movefile || isset($movefile['error'])) {
        wp_send_json_error(['message' => 'Upload failed.', 'details' => $movefile['error'] ?? 'Unknown error']);
    }

    // Optionally insert into Media Library
    $attachment_id = wp_insert_attachment([
        'guid'           => $movefile['url'],
        'post_mime_type' => $movefile['type'],
        'post_title'     => sanitize_file_name($file['name']),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ], $movefile['file']);

    if (!is_wp_error($attachment_id)) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $movefile['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
    }

    // Return the image URL
    wp_send_json_success([
        'url' => $movefile['url'],
        'attachment_id' => $attachment_id
    ]);
}

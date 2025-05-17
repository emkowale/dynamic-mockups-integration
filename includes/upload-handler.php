<?php
/*
 * File: includes/upload-handler.php
 * Description: Handles AJAX image uploads for Dynamic Mockups with extensive console debugging
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 3:40 AM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_dmi_upload_image', 'dmi_handle_image_upload');
add_action('wp_ajax_nopriv_dmi_upload_image', 'dmi_handle_image_upload');

function dmi_handle_image_upload() {
    $response_debug = [];

    // ✅ Step 1: Nonce check
    $response_debug['received_nonce'] = $_POST['_ajax_nonce'] ?? null;
    $response_debug['expected_nonce_context'] = 'dmi_nonce';
    $nonce_verified = check_ajax_referer('dmi_nonce', '_ajax_nonce', false);

    if (!$nonce_verified) {
        wp_send_json_error([
            'message' => 'Security check failed.',
            'debug' => $response_debug
        ]);
    }

    // ✅ Step 2: File presence
    if (!isset($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
        $response_debug['file_received'] = false;
        wp_send_json_error([
            'message' => 'No file received.',
            'debug' => $response_debug
        ]);
    }

    $file = $_FILES['file'];
    $response_debug['file_received'] = true;
    $response_debug['original_name'] = $file['name'] ?? null;
    $response_debug['type'] = $file['type'] ?? null;
    $response_debug['size'] = $file['size'] ?? null;

    // ✅ Step 3: File type check
    $allowed_types = ['image/png', 'image/jpeg'];
    if (!in_array($file['type'], $allowed_types)) {
        $response_debug['file_allowed'] = false;
        wp_send_json_error([
            'message' => 'Invalid file type. Only PNG and JPG are allowed.',
            'debug' => $response_debug
        ]);
    }

    $response_debug['file_allowed'] = true;

    // ✅ Step 4: Upload to /dmi-temp/
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['basedir']) . 'dmi-temp/';
    $target_url = trailingslashit($upload_dir['baseurl']) . 'dmi-temp/';

    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    $filename = wp_unique_filename($target_dir, $file['name']);
    $destination = $target_dir . $filename;
    $public_url = $target_url . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $response_debug['upload_success'] = true;
        $response_debug['uploaded_file_path'] = $destination;
        $response_debug['public_url'] = $public_url;

        wp_send_json_success([
            'url' => esc_url_raw($public_url),
            'debug' => $response_debug
        ]);
    } else {
        $response_debug['upload_success'] = false;
        $response_debug['error'] = 'move_uploaded_file failed';

        wp_send_json_error([
            'message' => 'Upload failed.',
            'debug' => $response_debug
        ]);
    }
}

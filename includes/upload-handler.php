<?php
/*
 * File: upload-handler.php
 * Description: Handles AJAX image uploads for Dynamic Mockups with extensive console debugging
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 16, 2025 5:40 PM EDT
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

    // ✅ Step 4: Upload to WP
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $upload_overrides = [
        'test_form' => false,
        'mimes' => [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png'
        ]
    ];

    $movefile = wp_handle_upload($file, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $response_debug['upload_success'] = true;
        $response_debug['uploaded_file_path'] = $movefile['file'] ?? null;
        $response_debug['public_url'] = $movefile['url'] ?? null;

        wp_send_json_success([
            'url' => esc_url_raw($movefile['url']),
            'debug' => $response_debug
        ]);
    } else {
        $response_debug['upload_success'] = false;
        $response_debug['error'] = $movefile['error'] ?? 'Unknown error';

        wp_send_json_error([
            'message' => 'Upload failed.',
            'debug' => $response_debug
        ]);
    }
}

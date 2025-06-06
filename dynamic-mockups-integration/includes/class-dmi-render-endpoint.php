<?php
/*
 * Filename: class-dmi-render-endpoint.php
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Version: 1.9.7
 * Date: 2025-04-27
 * Time: 09:28 EST
 */

function dmi_render_endpoint_init() {
    add_action('wp_ajax_dmi_render_mockup', 'dmi_handle_render_request');
    add_action('wp_ajax_nopriv_dmi_render_mockup', 'dmi_handle_render_request');
}

function dmi_handle_render_request() {
    check_ajax_referer('dmi_ajax_nonce', 'security');

    $mockup_uuid = sanitize_text_field($_POST['mockup_uuid'] ?? '');
    $smart_object_uuid = sanitize_text_field($_POST['smart_object_uuid'] ?? '');
    $image_url = esc_url_raw($_POST['image_url'] ?? '');

    if (empty($mockup_uuid) || empty($smart_object_uuid) || empty($image_url)) {
        wp_send_json_error('Missing required fields');
    }

    $result = dmi_render_native_curl($mockup_uuid, $smart_object_uuid, $image_url);

    if ($result && isset($result['success']) && $result['success'] === true) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result ?? 'Unknown error');
    }
}

function dmi_render_native_curl($mockup_uuid, $smart_object_uuid, $image_url) {

    $api_key = get_option('dmi_api_key');

    if (!$api_key) {
        return null;
    }

    $url = 'https://app.dynamicmockups.com/api/v1/render';

    $payload = json_encode([
        'mockup_uuid' => $mockup_uuid,
        'smart_object_uuid' => $smart_object_uuid,
        'image_url' => $image_url,
        'output_format' => 'jpeg'
    ]);

    $headers = [
        'x-api-key: ' . $api_key,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    //echo "<script>console.log('DMI Native cURL Render Response:', " . json_encode($response) . ");</script>";

    $result = json_decode($response, true);

    return $result;
}


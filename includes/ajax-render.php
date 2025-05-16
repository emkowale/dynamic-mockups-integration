<?php
/*
 * File: ajax-render.php
 * Description: Handles AJAX rendering requests to the Dynamic Mockups Render API using cURL
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 10, 2025 11:59 PM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_dmi_render_image', 'dmi_render_image');
add_action('wp_ajax_nopriv_dmi_render_image', 'dmi_render_image');

function dmi_render_image() {
    // ✅ Nonce verification
    if (!check_ajax_referer('dmi_nonce', false, false)) {
        wp_send_json_error(['message' => 'Security check failed (invalid nonce).']);
    }

    $mockup_uuid = sanitize_text_field($_POST['mockup_uuid'] ?? '');
    $smartobject_uuid = sanitize_text_field($_POST['smartobject_uuid'] ?? '');
    $image_url = esc_url_raw($_POST['image_url'] ?? '');

    if (empty($mockup_uuid) || empty($smartobject_uuid) || empty($image_url)) {
        wp_send_json_error(['message' => 'Missing required parameters.']);
    }

    $api_key = get_option('dmi_api_key');
    if (empty($api_key)) {
        wp_send_json_error(['message' => 'API key not set.']);
    }

    $payload = json_encode([
        'mockup_uuid' => $mockup_uuid,
        'smart_objects' => [[
            'uuid' => $smartobject_uuid,
            'asset' => ['url' => $image_url],
            'color' => '#ffffff'
        ]]
    ]);

    $ch = curl_init('https://app.dynamicmockups.com/api/v1/renders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-KEY: ' . $api_key
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 200 || empty($response)) {
        error_log("DMI Render Debug: HTTP Code = $http_code");
        error_log("DMI Render Debug: cURL Error = $curl_error");
        error_log("DMI Render Debug: Raw Response = $response");
        wp_send_json_error([
            'message' => 'Rendering failed.',
            'http_code' => $http_code,
            'error' => $curl_error,
            'raw' => $response,
        ]);
    }

    $data = json_decode($response, true);
    if (empty($data['data']['export_path'])) {
        wp_send_json_error([
            'message' => 'No export_path returned in API response.',
            'raw' => $response,
        ]);
    }

    wp_send_json_success([
        'rendered_url' => esc_url_raw($data['data']['export_path']),
    ]);
}

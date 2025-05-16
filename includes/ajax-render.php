<?php
/*
 * File: ajax-render.php
 * Description: Uses smart_objects array from frontend and renders via Dynamic Mockups API using asset.url format
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 16, 2025 9:50 PM EDT
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_dmi_render_image', 'dmi_handle_render_request');
add_action('wp_ajax_nopriv_dmi_render_image', 'dmi_handle_render_request');

function dmi_handle_render_request() {
    $debug = [];

    $nonce = $_POST['_ajax_nonce'] ?? '';
    $debug['received_nonce'] = $nonce;
    if (!wp_verify_nonce($nonce, 'dmi_nonce')) {
        wp_send_json_error([
            'message' => 'Security check failed',
            'debug' => $debug
        ]);
    }

    $mockup_uuid = sanitize_text_field($_POST['mockup_uuid'] ?? '');
    $smart_objects = $_POST['smart_objects'] ?? [];
    $debug['mockup_uuid'] = $mockup_uuid;
    $debug['smart_objects'] = $smart_objects;

    if (!$mockup_uuid || !is_array($smart_objects) || count($smart_objects) === 0) {
        wp_send_json_error([
            'message' => 'Missing parameters',
            'debug' => $debug
        ]);
    }

    $smart_object = $smart_objects[0];
    $uuid = sanitize_text_field($smart_object['uuid'] ?? '');
    $image_url_raw = esc_url_raw($smart_object['image_url'] ?? '');
    $image_url = $image_url_raw . '?nocache=' . time();

    $debug['smart_object_uuid'] = $uuid;
    $debug['image_url'] = $image_url;

    if (!$uuid || !$image_url_raw) {
        wp_send_json_error([
            'message' => 'Invalid smart object payload',
            'debug' => $debug
        ]);
    }

    global $post;
    $product_id = $post ? $post->ID : 0;
    $debug['product_id'] = $product_id;

    $top    = get_post_meta($product_id, '_dmi_smartobject_position_top', true);
    $left   = get_post_meta($product_id, '_dmi_smartobject_position_left', true);
    $width  = get_post_meta($product_id, '_dmi_smartobject_size_width', true);
    $height = get_post_meta($product_id, '_dmi_smartobject_size_height', true);

    $has_all_dimensions = ($top !== '' && $left !== '' && $width !== '' && $height !== '');
    $debug['position_meta_found'] = $has_all_dimensions;
    if ($has_all_dimensions) {
        $debug['position'] = ['top' => (int)$top, 'left' => (int)$left];
        $debug['size']     = ['width' => (int)$width, 'height' => (int)$height];
    }

    $api_key = get_option('dmi_api_key');
    $debug['api_key_present'] = $api_key ? true : false;
    if (!$api_key) {
        wp_send_json_error([
            'message' => 'Missing API key',
            'debug' => $debug
        ]);
    }

    $smart_obj_payload = [
        'uuid' => $uuid,
        'asset' => [ 'url' => $image_url ]
    ];

    if ($has_all_dimensions) {
        $smart_obj_payload['position'] = [
            'top' => (int)$top,
            'left' => (int)$left
        ];
        $smart_obj_payload['size'] = [
            'width' => (int)$width,
            'height' => (int)$height
        ];
    }

    $url = 'https://app.dynamicmockups.com/api/v1/renders';
    $payload_array = [
        'mockup_uuid' => $mockup_uuid,
        'smart_objects' => [ $smart_obj_payload ],
        'output_format' => 'jpeg',
        'flatten_layers' => true
    ];
    $payload = json_encode($payload_array);

    $headers = [
        'x-api-key: ' . $api_key,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $debug['request_url'] = $url;
    $debug['request_payload'] = $payload_array;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    file_put_contents(WP_CONTENT_DIR . '/dmi_render_debug.json', json_encode($decoded, JSON_PRETTY_PRINT));

    $debug['http_code'] = $http_code;
    $debug['curl_error'] = $curl_error;
    $debug['raw_response'] = $response;
    $debug['decoded_response'] = $decoded;

    if (
        !$decoded ||
        !isset($decoded['success']) ||
        $decoded['success'] !== true ||
        !isset($decoded['data']['export_path'])
    ) {
        wp_send_json_error([
            'message' => 'Render failed',
            'debug' => $debug
        ]);
    }

    if (isset($decoded['data']['warnings'])) {
        $debug['warnings'] = $decoded['data']['warnings'];
    }

    wp_send_json_success([
        'rendered_url' => esc_url_raw($decoded['data']['export_path']),
        'debug' => $debug
    ]);
}

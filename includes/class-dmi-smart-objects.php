<?php
/*
 * Filename: class-dmi-smart-objects.php
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Version: 1.9.6
 * Date: 2025-04-28
 * Time: 07:43 EST
 */

if (!defined('ABSPATH')) exit;

function dmi_smart_objects_init() {
    add_action('wp_ajax_dmi_fetch_smart_objects', 'dmi_fetch_smart_objects_handler');
}

function dmi_fetch_smart_objects_handler() {
    check_ajax_referer('dmi_admin_nonce', 'nonce');

    $mockup_uuid = sanitize_text_field($_POST['mockup_uuid'] ?? '');

    if (empty($mockup_uuid)) {
        wp_send_json_error('Missing mockup UUID');
    }

    $api_key = get_option('dmi_api_key');
    if (empty($api_key)) {
        wp_send_json_error('Missing API key');
    }

    $url = 'https://api.dynamicmockups.com/api/v1/mockups/' . $mockup_uuid;
    $response = wp_remote_get($url, [
        'headers' => [
            'x-api-key' => $api_key
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('API error: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['data']['smart_objects']) && is_array($data['data']['smart_objects'])) {
        $smart_objects = array_map(function($so) {
            return [
                'uuid' => $so['uuid'],
                'name' => $so['name']
            ];
        }, $data['data']['smart_objects']);

        wp_send_json_success($smart_objects);
    } else {
        wp_send_json_error('No smart objects found.');
    }
}


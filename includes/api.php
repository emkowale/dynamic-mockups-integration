<?php
/*
 * File: api.php
 * Description: Retrieves mockups and smart objects from the Dynamic Mockups API and stores them
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 7, 2025 1:20 PM EDT
 */

if (!defined('ABSPATH')) exit;

function dmi_fetch_and_store_mockup_data() {
    $api_key = get_option('dmi_api_key');
    if (!$api_key) return;

    $headers = [
        'x-api-key' => $api_key,
        'Accept' => 'application/json'
    ];

    $response = wp_remote_get('https://app.dynamicmockups.com/api/v1/mockups', [
        'headers' => $headers,
        'timeout' => 10,
        'sslverify' => true,
    ]);

    if (is_wp_error($response)) {
        echo "<script>console.error('❌ DMI Fetch: mockups failed - " . esc_js($response->get_error_message()) . "');</script>";
        return;
    }

    $raw_body = wp_remote_retrieve_body($response);
    echo "<script>console.log('📦 DMI Raw API Response:', " . json_encode($raw_body) . ");</script>";

    $json = json_decode($raw_body, true);
    if (!is_array($json) || !isset($json['data']) || !is_array($json['data'])) {
        echo "<script>console.warn('⚠️ DMI API response missing data array');</script>";
        return;
    }

    $data = $json['data'];
    $mockups = [];
    $smart_objects_by_mockup = [];

    foreach ($data as $item) {
        if (!empty($item['uuid']) && !empty($item['thumbnail'])) {
            $mockups[$item['uuid']] = [
                'thumbnail' => $item['thumbnail'],
                'name' => $item['name'] ?? ''
            ];
        }

        if (!empty($item['uuid']) && !empty($item['smart_objects']) && is_array($item['smart_objects'])) {
            foreach ($item['smart_objects'] as $so) {
                if (!empty($so['uuid'])) {
                    $smart_objects_by_mockup[$item['uuid']][] = [
                        'uuid' => $so['uuid'],
                        'name' => $so['name'] ?? '',
                        'thumbnail' => $so['thumbnail'] ?? ''
                    ];
                }
            }
        }
    }

    update_option('dmi_mockup_data', $mockups);
    update_option('dmi_smart_object_data', $smart_objects_by_mockup);
    echo "<script>console.log('✅ DMI Fetch Complete: ' + " . json_encode(count($mockups)) . " + ' mockups and ' + " . json_encode(array_sum(array_map('count', $smart_objects_by_mockup))) . " + ' smart objects loaded.');</script>";
}

add_action('admin_init', 'dmi_fetch_and_store_mockup_data');

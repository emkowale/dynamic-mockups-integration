<?php
/*
 * File: admin-settings.php
 * Description: Admin settings page for API key and connection status
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 10, 2025 8:55 PM EDT
 */

if (!defined('ABSPATH')) exit;

// Add top-level menu
add_action('admin_menu', function() {
    add_menu_page(
        'Dynamic Mockups Integration',
        'Dynamic Mockups',
        'manage_options',
        'dynamic-mockups-integration',
        'dmi_admin_page',
        'dashicons-format-image'
    );
});

// Register API key setting
add_action('admin_init', function() {
    register_setting('dmi_settings_group', 'dmi_api_key');
});

// Render settings page
function dmi_admin_page() {
    $api_key = get_option('dmi_api_key');
    $is_connected = dmi_test_api_connection($api_key);
    ?>
    <div class="wrap">
        <h1>Dynamic Mockups Integration</h1>

        <div style="margin-top: 15px;">
            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $is_connected ? 'green' : 'red'; ?>;"></span>
            <strong style="margin-left: 8px;">
                <?php echo $is_connected ? 'Connected to API' : 'Not Connected'; ?>
            </strong>
        </div>

        <form method="post" action="options.php" style="margin-top: 20px;">
            <?php settings_fields('dmi_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="dmi_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button('Save API Key'); ?>
        </form>
    </div>
    <?php
}

// Test API connection with cURL
function dmi_test_api_connection($api_key) {
    if (!$api_key) return false;

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://app.dynamicmockups.com/api/v1/mockups',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $api_key,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        error_log('❌ DMI API cURL Error: ' . curl_error($curl));
        curl_close($curl);
        return false;
    }

    curl_close($curl);

    error_log('✅ DMI API test response code: ' . $http_code);
    return $http_code === 200;
}
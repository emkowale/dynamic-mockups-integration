/*
 * File: dynamic-mockups-integration.php
 * Plugin Name: Dynamic Mockups Integration
 * Description: Integrates Dynamic Mockups API with WooCommerce for live previews and featured image setting.
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Date: 2025-04-27
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_menu_page('Dynamic Mockups Integration', 'Dynamic Mockups', 'manage_options', 'dynamic-mockups-integration', 'dmi_admin_page', 'dashicons-format-image');
});

function dmi_admin_page() {
    $api_key = get_option('dmi_api_key');
    $connection_status = dmi_check_api_connection($api_key);
    ?>
    <div class="wrap">
        <h1>Dynamic Mockups Integration</h1>
        <form method="post" action="options.php">
            <?php settings_fields('dmi_settings_group'); ?>
            <?php do_settings_sections('dmi_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="dmi_api_key" value="<?php echo esc_attr($api_key); ?>" style="width:400px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Connection</th>
                    <td>
                        <?php if ($connection_status): ?>
                            <span style="color: green; font-weight: bold;">● Connected</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">● Not Connected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function dmi_check_api_connection($api_key) {
    if (empty($api_key)) return false;

    $response = wp_remote_get('https://app.dynamicmockups.com/api/v1/mockups', [
        'headers' => [
            'x-api-key' => $api_key,
            'Accept' => 'application/json',
        ],
        'timeout' => 10
    ]);

    if (is_wp_error($response)) return false;
    return wp_remote_retrieve_response_code($response) === 200;
}

add_action('admin_init', function() {
    register_setting('dmi_settings_group', 'dmi_api_key');
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_dynamic-mockups-integration') {
        wp_enqueue_script('dmi-admin-settings', plugin_dir_url(__FILE__) . 'assets/js/admin-settings.js', ['jquery'], null, true);
    }

    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_script('dmi-admin-product', plugin_dir_url(__FILE__) . 'assets/js/admin-product.js', ['jquery'], null, true);
        $mockups = [];
        $api_key = get_option('dmi_api_key');
        if (!empty($api_key)) {
            $response = wp_remote_get('https://app.dynamicmockups.com/api/v1/mockups', [
                'headers' => [
                    'x-api-key' => $api_key,
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
            ]);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($data['success']) && !empty($data['data'])) {
                    $mockups = $data['data'];
                }
            }
        }
        wp_localize_script('dmi-admin-product', 'dmi_admin_mockups', $mockups);
    }
});

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('dmi-frontend', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', ['jquery'], null, true);
    wp_enqueue_style('dmi-frontend-style', plugin_dir_url(__FILE__) . 'assets/css/frontend.css', [], null);
    wp_localize_script('dmi-frontend', 'dmi_ajax_data', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dmi_upload_nonce')
    ]);
});

add_action('wp_ajax_upload_user_image', 'dmi_handle_user_image_upload');
add_action('wp_ajax_nopriv_upload_user_image', 'dmi_handle_user_image_upload');

function dmi_handle_user_image_upload() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dmi_upload_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token.'], 403);
    }
    if (empty($_FILES['file']) || empty($_POST['product_id']) || empty($_POST['mockup_uuid']) || empty($_POST['smartobject_uuid'])) {
        wp_send_json_error(['message' => 'Missing required fields.'], 400);
    }
    $file = $_FILES['file'];
    $product_id = intval($_POST['product_id']);

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload = wp_handle_upload($file, ['test_form' => false]);
    if (isset($upload['error'])) {
        wp_send_json_error(['message' => $upload['error']], 400);
    }

    $attachment = [
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($file['name']),
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    $attach_id = wp_insert_attachment($attachment, $upload['file'], $product_id);
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    wp_send_json_success(['url' => wp_get_attachment_url($attach_id)]);
}

// [REMAINDER OF THE FILE REMAINS UNCHANGED -- MOCKUP INTERFACE, SAVE POST, IMAGE DOWNLOAD, FRONTEND LOADER] (unchanged)


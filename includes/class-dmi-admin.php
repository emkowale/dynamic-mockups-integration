<?php
function dmi_admin_init() {
    add_action('admin_menu', function() {
        add_menu_page('Dynamic Mockups', 'Dynamic Mockups', 'manage_options', 'dynamic-mockups', 'dmi_render_settings_page');
    });

    add_action('admin_init', function() {
        register_setting('dmi_settings_group', 'dmi_api_key');
    });
}

function dmi_render_settings_page() {
    $api_key = get_option('dmi_api_key');
    $status = 'Disconnected';
    $color = 'red';
    $debug_data = [];

    if (!empty($api_key)) {
        $response = wp_remote_get('https://app.dynamicmockups.com/api/v1/mockups', [
            'headers' => ['x-api-key' => $api_key]
        ]);

        if (is_wp_error($response)) {
            $debug_data['error'] = $response->get_error_message();
        } else {
            $debug_data['status_code'] = wp_remote_retrieve_response_code($response);
            $debug_data['headers'] = wp_remote_retrieve_headers($response);
            $debug_data['body'] = wp_remote_retrieve_body($response);

            if ($debug_data['status_code'] == 200) {
                $status = 'Connected';
                $color = 'green';
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>Dynamic Mockups Settings</h1>
        <div style="margin: 10px 0;">
            <strong>Status:</strong> <span style="background-color: <?php echo $color; ?>; color: white; padding: 5px 10px; border-radius: 5px;"><?php echo $status; ?></span>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields('dmi_settings_group'); ?>
            <?php do_settings_sections('dmi_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="dmi_api_key" value="<?php echo esc_attr($api_key); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        console.log("Dynamic Mockups API Debug:", <?php echo json_encode($debug_data); ?>);
    </script>
    <?php
}


function dmi_admin_enqueue_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) return;

    wp_enqueue_script('dmi-admin-js', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'dmi_admin_enqueue_scripts');

?>


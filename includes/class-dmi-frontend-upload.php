<?php
/*
 * Filename: class-dmi-frontend-upload.php
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Version: 1.9.9
 * Date: 2025-04-26
 * Time: 16:29 EST
 */

function dmi_frontend_upload_init() {
    add_action('woocommerce_before_add_to_cart_form', 'dmi_display_upload_form');
    add_action('init', 'dmi_handle_frontend_upload');
}

function dmi_display_upload_form() {
    global $post;

    $mockup_uuid = get_post_meta($post->ID, '_dmi_mockup_uuid', true);
    $smart_object_uuid = get_post_meta($post->ID, '_dmi_smart_object_uuid', true);

    if (!$mockup_uuid || !$smart_object_uuid) {
        return; // Don't show uploader if UUIDs not selected
    }

    echo '<div class="dmi-frontend-upload">';
    echo '<h3>Upload Your Image</h3>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="dmi_mockup_uuid" value="' . esc_attr($mockup_uuid) . '">';
    echo '<input type="hidden" name="dmi_smart_object_uuid" value="' . esc_attr($smart_object_uuid) . '">';
    echo '<input type="file" name="dmi_customer_image" accept="image/jpeg,image/png" required>';
    echo '<button type="submit" name="dmi_submit_upload">Upload & Render</button>';
    echo '</form>';
    if (!empty($_SESSION['dmi_rendered_image_url'])) {
        echo '<div class="dmi-render-preview">';
        echo '<h4>Rendered Preview:</h4>';
        echo '<img src="' . esc_url($_SESSION['dmi_rendered_image_url']) . '" style="max-width:100%;margin-top:10px;">';
        echo '</div>';
    }
    echo '</div>';
}

function dmi_handle_frontend_upload() {
    if (isset($_POST['dmi_submit_upload']) && !empty($_FILES['dmi_customer_image'])) {

        $file = $_FILES['dmi_customer_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wc_add_notice('Upload failed. Please try again.', 'error');
            return;
        }

        $mockup_uuid = sanitize_text_field($_POST['dmi_mockup_uuid']);
        $smart_object_uuid = sanitize_text_field($_POST['dmi_smart_object_uuid']);
        $api_key = get_option('dmi_api_key');
        $render_url = 'https://app.dynamicmockups.com/api/v1/render';

        // Build cURL file upload
        $cfile = new CURLFile($file['tmp_name'], $file['type'], $file['name']);

        $post_fields = [
            'mockup_uuid' => $mockup_uuid,
            'smart_object_uuid' => $smart_object_uuid,
            'output_format' => 'jpeg',
            'text_layers' => '[]', // Must be a string here for multipart
            'image_file' => $cfile
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $render_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $api_key,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        echo "<script>console.log('DMI Raw cURL Response:', " . json_encode($response) . ");</script>";
        $result = json_decode($response, true);


        // Debugging to browser console
        echo "<script>console.log('DMI Native cURL Request: mockup_uuid = " . esc_js($mockup_uuid) . ", smart_object_uuid = " . esc_js($smart_object_uuid) . ", output_format = jpeg');</script>";
        echo "<script>console.log('DMI Native cURL Response:', " . json_encode($result) . ");</script>";

        if (!empty($error)) {
            wc_add_notice('cURL error: ' . esc_html($error), 'error');
        } elseif (!empty($result['success']) && !empty($result['data']['render_url'])) {
            $_SESSION['dmi_rendered_image_url'] = $result['data']['render_url'];
            wc_add_notice('Image rendered successfully!', 'success');
        } else {
            wc_add_notice('Rendering failed: ' . esc_html($result['message'] ?? 'Unknown error'), 'error');
        }
    }
}

dmi_frontend_upload_init();


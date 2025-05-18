<?php
/*
 * File: frontend-ui.php
 * Description: Frontend UI and script handling for image upload and Dynamic Mockups rendering
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 23:36 EDT
 */

if (!defined('ABSPATH')) exit;

// Load styles/scripts only on product pages
add_action('wp_enqueue_scripts', function () {
    if (!is_singular('product')) return;

    wp_enqueue_style(
        'dmi-frontend-style',
        plugin_dir_url(__FILE__) . '../assets/css/frontend.css',
        array(),
        '1.9.6'
    );

    wp_localize_script('dmi-upload-handler', 'dmi_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('dmi_nonce'),
        'api_key'  => get_option('dmi_api_key') ?: ''
    ));
});

// Inject upload UI below Add to Cart button
add_action('woocommerce_after_add_to_cart_button', function () {
    if (!is_singular('product')) return;

    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        echo "<script>console.warn('⚠️ DMI: No valid WC_Product');</script>";
        return;
    }

    $mockup_uuid = get_post_meta($product->get_id(), '_dmi_mockup_uuid', true);
    $smartobject_uuid = get_post_meta($product->get_id(), '_dmi_smartobject_uuid', true);

    if (!$mockup_uuid || !$smartobject_uuid) {
        echo '<p class="dmi-warning">⚠️ This product is missing Dynamic Mockup settings.</p>';
        return;
    }

    echo "<script>console.log('✅ DMI: Injecting upload UI under Add to Cart');</script>";
    ?>

    <div id="dmi-upload-container" style="background: none; border: none; padding: 0;">
        <input type="file" id="dmi-upload" accept="image/png, image/jpeg" style="display:none;">
        <button type="button" id="dmi-upload-button">Upload your own image</button>
        <div id="dmi-upload-preview"></div>
    </div>

    <div id="dmi-spinner-overlay" style="display:none;">
        <div class="dmi-spinner"></div>
    </div>

    <input type="hidden" id="dmi-mockup-uuid" value="<?php echo esc_attr($mockup_uuid); ?>">
    <input type="hidden" id="dmi-smartobject-uuid" value="<?php echo esc_attr($smartobject_uuid); ?>">

    <?php
});

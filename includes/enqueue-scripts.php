<?php
/*
 * File: includes/enqueue-scripts.php
 * Description: Enqueues modular frontend JS files for Dynamic Mockups Integration
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 19, 2025 00:33 EDT
 */

function dmi_enqueue_modular_scripts() {
    $plugin_url = plugin_dir_url(__DIR__);

    // ✅ Always load the CSS on product and cart pages
    if (is_product() || is_cart()) {
        wp_enqueue_style('dmi-frontend-style', $plugin_url . 'assets/css/frontend.css', [], '1.9.6');
    }

    // ✅ Only enqueue JS where needed (product page)
    if (is_product()) {
        wp_enqueue_script('dmi-ui-init', $plugin_url . 'assets/js/ui-init.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-upload-handler', $plugin_url . 'assets/js/upload-handler.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-render-handler', $plugin_url . 'assets/js/render-handler.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-zoom', $plugin_url . 'assets/js/zoom.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-confirm-overlay', $plugin_url . 'assets/js/confirm-overlay.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-form-guard', $plugin_url . 'assets/js/form-guard.js', ['jquery'], '1.0', true);

        $localized_data = [
            'ajax_url'   => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('dmi_nonce'),
            'product_id' => get_the_ID()
        ];

        wp_localize_script('dmi-ui-init', 'dmi_ajax', $localized_data);
        wp_localize_script('dmi-upload-handler', 'dmi_ajax', $localized_data);
    }
}
add_action('wp_enqueue_scripts', 'dmi_enqueue_modular_scripts');

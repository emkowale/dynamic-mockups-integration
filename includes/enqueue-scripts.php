<?php
/*
 * File: includes/enqueue-scripts.php
 * Description: Enqueues modular frontend JS files for Dynamic Mockups Integration
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 18, 2025 00:19 EDT
 */

function dmi_enqueue_modular_scripts() {
    if (is_product()) {
        $plugin_url = plugin_dir_url(__DIR__);

        wp_enqueue_style('dmi-frontend-style', $plugin_url . 'assets/css/frontend.css', [], '1.9.6');

        wp_enqueue_script('dmi-ui-init', $plugin_url . 'assets/js/ui-init.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-upload-handler', $plugin_url . 'assets/js/upload-handler.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-render-handler', $plugin_url . 'assets/js/render-handler.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-zoom', $plugin_url . 'assets/js/zoom.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-confirm-overlay', $plugin_url . 'assets/js/confirm-overlay.js', ['jquery'], '1.0', true);
        wp_enqueue_script('dmi-form-guard', $plugin_url . 'assets/js/form-guard.js', ['jquery'], '1.0', true);

        wp_localize_script('dmi-ui-init', 'dmi_ajax', [
            'ajax_url'   => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('dmi_nonce'),
            'product_id' => get_the_ID()
        ]);
    }
}
add_action('wp_enqueue_scripts', 'dmi_enqueue_modular_scripts');

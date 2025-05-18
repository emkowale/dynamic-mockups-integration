<?php
/*
 * File: includes/validate-product-price.php
 * Description: Enforces price requirement for Simple products with mockups in WooCommerce admin
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 18, 2025 02:04 EDT
 */

if (!defined('ABSPATH')) exit;

add_action('woocommerce_admin_process_product_object', function($product) {
    if (!$product || !$product->is_type('simple')) return;

    $mockup_uuid = get_post_meta($product->get_id(), '_dmi_mockup_uuid', true);

    if (!empty($mockup_uuid) && !$product->get_price()) {
        if (is_admin() && !defined('DOING_AJAX')) {
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('dmi_price_error', '1', $location);
            });
        }
    }
}, 10, 1);

add_action('admin_notices', function() {
    if (!empty($_GET['dmi_price_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>Error: This product uses a Dynamic Mockup. You must enter a price.</p></div>';
    }
});

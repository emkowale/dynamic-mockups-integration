<?php
/*
 * File: remove-default-product-image.php
 * Description: Suppresses default WooCommerce product image in order emails
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 16:34 EDT
 */

add_filter('woocommerce_order_item_thumbnail', 'dmi_suppress_email_product_image', 10, 2);

function dmi_suppress_email_product_image($image, $item) {
    // Debug to confirm this runs
    error_log('✅ DMI: Suppressing default email product image');

    // Suppress default product image by returning an empty string
    return '';
}

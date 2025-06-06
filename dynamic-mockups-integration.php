<?php
/*
 * Plugin Name: Dynamic Mockups Integration
 * Description: Integrates Dynamic Mockups API with WooCommerce for live previews and featured image setting.
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Last Updated: May 19, 2025 00:18 EDT
 */

if (!defined('ABSPATH')) exit;

// === Admin Settings Panel ===
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// === Product Meta Box with Thumbnails and Smart Objects ===
require_once plugin_dir_path(__FILE__) . 'includes/product-meta-box.php';

// === AJAX Upload Handler ===
require_once plugin_dir_path(__FILE__) . 'includes/upload-handler.php';

// === AJAX Render Handler ===
require_once plugin_dir_path(__FILE__) . 'includes/ajax-render.php';

// === Frontend Upload UI ===
require_once plugin_dir_path(__FILE__) . 'includes/frontend-ui.php';

// ✅ Modular JS and CSS enqueuing (must be after UI injection)
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';

// ✅ Enforce price requirement for Simple products
require_once plugin_dir_path(__FILE__) . 'includes/validate-product-price.php';

// ✅ Cart image override logic (rendered thumbnail support)
require_once plugin_dir_path(__FILE__) . 'includes/cart-thumbnail.php';

//require_once plugin_dir_path(__FILE__) . 'includes/email-hooks.php';

require_once plugin_dir_path(__FILE__) . 'includes/test-email-preview.php';

require_once plugin_dir_path(__FILE__) . 'includes/save-order-meta.php';

require_once plugin_dir_path(__FILE__) . 'includes/email-render-handler.php';

require_once plugin_dir_path(__FILE__) . 'includes/remove-email-product-image.php';

require_once plugin_dir_path(__FILE__) . 'includes/admin-order-meta.php';

require_once plugin_dir_path(__FILE__) . 'includes/cart-meta-handler.php';

<?php
/*
 * File: frontend.php
 * Plugin Name: Dynamic Mockups Integration
 * Description: Front-end UI for image upload and rendering with Dynamic Mockups API
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Last Updated: May 10, 2025 7:43 PM EDT
 */

if (!defined('ABSPATH')) exit;

global $product;

$mockup_uuid = get_post_meta($product->get_id(), '_dmi_mockup_uuid', true);
$smartobject_uuid = get_post_meta($product->get_id(), '_dmi_smartobject_uuid', true);

if (!$mockup_uuid || !$smartobject_uuid) {
    echo '<p class="dmi-warning">⚠️ This product is missing Dynamic Mockup settings.</p>';
    return;
}
?>

<div id="dmi-upload-container">
    <label for="dmi-upload" class="dmi-label">Upload your image:</label>
    <input type="file" id="dmi-upload" accept="image/png, image/jpeg" style="display:none;">
    <button type="button" id="dmi-upload-button">Choose Image</button>
    <div id="dmi-upload-preview"></div>
</div>

<div id="dmi-spinner-overlay" style="display:none;">
    <div class="dmi-spinner"></div>
</div>

<input type="hidden" id="dmi-mockup-uuid" value="<?php echo esc_attr($mockup_uuid); ?>">
<input type="hidden" id="dmi-smartobject-uuid" value="<?php echo esc_attr($smartobject_uuid); ?>">

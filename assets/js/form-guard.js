/*
 * File: assets/js/form-guard.js
 * Description: Prevents Add to Cart submission if no rendered image is present
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 18, 2025 00:18 EDT
 */

jQuery(document).ready(function ($) {
  $('form.cart').on('submit', function (e) {
    const $field = $('#dmi-rendered-image-field');
    const val = $field.val();

    if ($field.length && val.trim() === '') {
      console.warn('🛑 No rendered image in field. Cart submission paused.');
      alert('Please wait for your custom image to finish rendering before adding to cart.');
      e.preventDefault();
    }
  });
});

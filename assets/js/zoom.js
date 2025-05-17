/*
 * File: assets/js/zoom.js
 * Description: Rebinds zoom effect after rendered image is displayed
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 23:24 EDT
 */

jQuery(document).on('dmi:imageRendered', function (e, renderedImageUrl) {
  console.log('🔍 DMI Zoom: Reinitializing zoom for rendered image');

  const $zoomTarget = jQuery('.woocommerce-product-gallery .woocommerce-product-gallery__image a');
  if (jQuery.fn.zoom && $zoomTarget.length) {
    $zoomTarget.trigger('zoom.destroy');
    $zoomTarget.zoom();
    console.log('✅ DMI Zoom: Zoom reinitialized');
  } else {
    console.warn('⚠️ DMI Zoom: .zoom() not available or zoom target missing');
  }
});

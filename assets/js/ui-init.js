/*
 * File: assets/js/ui-init.js
 * Description: Handles DOM readiness and upload button binding
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 23:50 EDT
 */

jQuery(document).ready(function ($) {
  console.log('✅ DMI UI Init: Waiting for upload button/input...');

  const waitForElements = setInterval(() => {
    const $uploadButton = $('#dmi-upload-button');
    const $fileInput = $('#dmi-upload');

    if ($uploadButton.length && $fileInput.length) {
      clearInterval(waitForElements);
      console.log('✅ DMI UI Init: Found #dmi-upload-button and #dmi-upload');

      $uploadButton.addClass('button alt single_add_to_cart_button');
      $uploadButton.on('click', function (e) {
        e.preventDefault();
        console.log('🖱️ DMI UI: Upload button clicked');
        $fileInput.trigger('click');
      });
    } else {
      console.log('⏳ DMI UI Init: Waiting for DOM injection...');
    }
  }, 250);
});

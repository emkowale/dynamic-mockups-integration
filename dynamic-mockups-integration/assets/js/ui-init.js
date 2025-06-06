/*
 * File: assets/js/ui-init.js
 * Description: Handles DOM readiness and upload button binding
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 17:40 EDT
 */

jQuery(document).ready(function ($) {
  console.log('✅ DMI UI Init: Waiting for upload button/input...');

  const waitForElements = setInterval(() => {
    const $uploadButton = $('#dmi-upload-button');
    const $fileInput = $('#dmi-upload');
    const $form = $('form.cart');

    if ($uploadButton.length && $fileInput.length && $form.length) {
      clearInterval(waitForElements);
      console.log('✅ DMI UI Init: Found #dmi-upload-button, #dmi-upload, and form.cart');

      // 🔁 Inject hidden fields if missing
      const hasRenderField = $('#dmi_rendered_image_url_field').length;
      const hasUploadField = $('#dmi_uploaded_image_url_field').length;

      if (!hasRenderField || !hasUploadField) {
        if (!hasRenderField) {
          $form.append('<input type="hidden" name="dmi_rendered_image_url" id="dmi_rendered_image_url_field" value="">');
          console.log('✅ DMI UI Init: Injected dmi_rendered_image_url_field');
        }

        if (!hasUploadField) {
          $form.append('<input type="hidden" name="dmi_uploaded_image_url" id="dmi_uploaded_image_url_field" value="">');
          console.log('✅ DMI UI Init: Injected dmi_uploaded_image_url_field');
        }
      } else {
        console.log('ℹ️ DMI UI Init: Hidden inputs already exist');
      }

      // Hook upload button to input trigger
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

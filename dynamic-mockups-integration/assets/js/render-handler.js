/*
 * File: assets/js/render-handler.js
 * Description: Sends uploaded image to Dynamic Mockups render endpoint
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 28, 2025 17:27 EDT
 */

jQuery(document).on('dmi:imageUploaded', function (e, uploadedImageUrl) {
  console.log('📤 Rendering With Image:', uploadedImageUrl);

  // ✅ Temporarily disable jQuery.blockUI to prevent white overlay
  const originalBlockUI = jQuery.blockUI;
  jQuery.blockUI = function () {
    console.log('🚫 jQuery.blockUI was called and blocked.');
  };

  // Ensure spinner is visible
  jQuery('#dmi-spinner-overlay').fadeIn();

  const formData = new FormData();
  formData.append('action', 'dmi_render_image');
  formData.append('_ajax_nonce', dmi_ajax.nonce);
  formData.append('product_id', dmi_ajax.product_id);
  formData.append('mockup_uuid', jQuery('#dmi-mockup-uuid').val());
  formData.append('smart_objects[0][uuid]', jQuery('#dmi-smartobject-uuid').val());
  formData.append('smart_objects[0][image_url]', uploadedImageUrl);

  jQuery.ajax({
    url: dmi_ajax.ajax_url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.success && response.data.rendered_url) {
        const renderedImageUrl = response.data.rendered_url;
        console.log('🎯 Rendered Image URL:', renderedImageUrl);

        const image = new Image();
        image.onload = function () {
          if (jQuery('#dmi-upload-preview').length) {
            jQuery('#dmi-upload-preview').html(image);
          }

          const $mainImg = jQuery('.woocommerce-product-gallery img.wp-post-image');
          if ($mainImg.length) {
            $mainImg.attr('src', renderedImageUrl)
              .attr('data-src', renderedImageUrl)
              .attr('data-large_image', renderedImageUrl)
              .attr('data-large_image_width', 800)
              .attr('data-large_image_height', 800)
              .removeAttr('srcset')
              .removeAttr('sizes');
          }

          jQuery('.woocommerce-product-gallery .woocommerce-product-gallery__image').css({
            'background-image': `url(${renderedImageUrl})`
          });

          jQuery('img.zoomImg').attr('src', renderedImageUrl);
          jQuery('#dmi-upload-container').slideUp();

          // Save to global + hidden input
          window.dmi_renderedImageUrl = renderedImageUrl;

          // ✅ Set hidden fields for cart submission
          jQuery('#dmi_rendered_image_url_field').val(renderedImageUrl);
          jQuery('#dmi_uploaded_image_url_field').val(uploadedImageUrl);
          console.log('✅ DMI: Updated hidden fields for cart submission');

          // Also set fallback hidden input (legacy support)
          const $form = jQuery('form.cart');
          if ($form.length) {
            let $existing = $form.find('input[name="dmi_rendered_image"]');
            if ($existing.length === 0) {
              const $hiddenInput = jQuery('<input>')
                .attr('type', 'hidden')
                .attr('name', 'dmi_rendered_image')
                .val(renderedImageUrl);
              $form.append($hiddenInput);
              console.log('✅ DMI: Injected fallback hidden input:', renderedImageUrl);
            } else {
              $existing.val(renderedImageUrl);
              console.log('🔁 DMI: Updated fallback input with new rendered image:', renderedImageUrl);
            }
          }

          jQuery(document).trigger('dmi:imageRendered', [renderedImageUrl]);
          jQuery('#dmi-spinner-overlay').fadeOut();

          // 🔓 Restore blockUI behavior
          jQuery.blockUI = originalBlockUI;
          console.log('🔓 jQuery.blockUI restored.');
        };

        image.src = renderedImageUrl;
      } else {
        console.error('❌ Render failed:', response);
        jQuery('#dmi-spinner-overlay').fadeOut();

        // 🔓 Restore blockUI behavior
        jQuery.blockUI = originalBlockUI;
        console.log('🔓 jQuery.blockUI restored.');
      }
    },
    error: function (xhr, status, error) {
      jQuery('#dmi-spinner-overlay').fadeOut();
      console.error('❌ AJAX error during render:', error);

      // 🔓 Restore blockUI behavior
      jQuery.blockUI = originalBlockUI;
      console.log('🔓 jQuery.blockUI restored.');
    }
  });
});

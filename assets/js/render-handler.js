/*
 * File: assets/js/render-handler.js
 * Description: Sends uploaded image to Dynamic Mockups render endpoint
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 18, 2025 00:16 EDT
 */

jQuery(document).on('dmi:imageUploaded', function (e, uploadedImageUrl) {
  console.log('📤 Rendering With Image:', uploadedImageUrl);

  // Ensure spinner is visible from start of upload through end of render
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

          window.dmi_renderedImageUrl = renderedImageUrl;
          jQuery('#dmi-rendered-image-field').val(renderedImageUrl);

          jQuery(document).trigger('dmi:imageRendered', [renderedImageUrl]);

          jQuery('#dmi-spinner-overlay').fadeOut();
        };

        image.src = renderedImageUrl;
      } else {
        console.error('❌ Render failed:', response);
        jQuery('#dmi-spinner-overlay').fadeOut();
      }
    },
    error: function (xhr, status, error) {
      jQuery('#dmi-spinner-overlay').fadeOut();
      console.error('❌ AJAX error during render:', error);
    }
  });
});
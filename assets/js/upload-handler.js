/*
 * File: assets/js/upload-handler.js
 * Description: Handles image upload and sends to backend
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 23:58 EDT
 */

jQuery(document).on('change', '#dmi-upload', function (e) {
  const file = this.files[0];
  if (!file) {
    console.warn('⚠️ No file selected.');
    return;
  }

  console.log('📁 File selected:', file.name);

  const formData = new FormData();
  formData.append('file', file);
  formData.append('action', 'dmi_upload_image');
  formData.append('_ajax_nonce', dmi_ajax.nonce);

  console.log('🔐 Upload Nonce:', dmi_ajax.nonce);
  jQuery('#dmi-spinner').addClass('active');
  console.log('📤 Uploading image...');

  jQuery.ajax({
    url: dmi_ajax.ajax_url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      jQuery('#dmi-spinner').removeClass('active');

      if (!response?.data?.url) {
        console.error('❌ Upload failed:', response);
        return;
      }

      const uploadedImageUrl = response.data.url;
      console.log('🔗 Uploaded Image:', uploadedImageUrl);

      if (jQuery('#dmi-upload-preview').length) {
        jQuery('#dmi-upload-preview').html(`<img src="${uploadedImageUrl}" style="max-width: 100%; margin-top: 10px;">`);
      }

      // Store globally for debugging/testing
      window.dmi_uploadedImageUrl = uploadedImageUrl;

      // 🔥 Trigger render
      jQuery(document).trigger('dmi:imageUploaded', [uploadedImageUrl]);
    },
    error: function (xhr, status, error) {
      jQuery('#dmi-spinner').removeClass('active');
      console.error('❌ AJAX error during upload:', error);
    }
  });
});

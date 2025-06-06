/*
 * File: assets/js/upload-handler.js
 * Description: Handles image upload, reduces color complexity, and uploads simplified image
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: June 03, 2025 14:30 EDT
 */

function rgbToHex(r, g, b) {
  return "#" + [r, g, b].map(x => {
    const hex = x.toString(16);
    return hex.length === 1 ? "0" + hex : hex;
  }).join('');
}

function bucketChannel(value, bucketSize) {
  return Math.floor(value / bucketSize) * bucketSize;
}

function quantizeColor(r, g, b, bucketSize = 32) {
  const rQ = bucketChannel(r, bucketSize);
  const gQ = bucketChannel(g, bucketSize);
  const bQ = bucketChannel(b, bucketSize);
  return { r: rQ, g: gQ, b: bQ, hex: rgbToHex(rQ, gQ, bQ) };
}

function processAndUploadImage(file) {
  const img = new Image();
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const reader = new FileReader();

  reader.onload = function (e) {
    img.onload = function () {
      canvas.width = img.width;
      canvas.height = img.height;
      ctx.drawImage(img, 0, 0);

      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      const data = imageData.data;
      const hexSet = new Set();

      // Quantize pixel data
      for (let i = 0; i < data.length; i += 4) {
        const [r, g, b, a] = data.slice(i, i + 4);
        if (a < 128) continue; // skip transparent
        const q = quantizeColor(r, g, b);
        hexSet.add(q.hex);
        data[i] = q.r;
        data[i + 1] = q.g;
        data[i + 2] = q.b;
      }

      ctx.putImageData(imageData, 0, 0);

      canvas.toBlob(function (blob) {
        const simplifiedFile = new File([blob], "simplified-" + file.name, {
          type: "image/png"
        });

        uploadQuantizedFile(simplifiedFile, Array.from(hexSet));
      }, 'image/png', 1.0);
    };

    img.src = e.target.result;
  };

  reader.readAsDataURL(file);
}

function uploadQuantizedFile(file, hexList) {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('action', 'dmi_upload_image');
  formData.append('_ajax_nonce', dmi_ajax.nonce);

  console.log('🔐 Upload Nonce:', dmi_ajax.nonce);
  jQuery('#dmi-spinner').addClass('active');
  console.log('📤 Uploading simplified image...');

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

      // Store globally
      window.dmi_uploadedImageUrl = uploadedImageUrl;

      // Inject into hidden fields
      jQuery('#dmi_color_count').val(hexList.length);
      jQuery('#dmi_color_hexes').val(JSON.stringify(hexList));

      // Show styled modal alert
      const alertBox = jQuery('<div class="dmi-alert-modal">')
        .append(`<div class="dmi-alert-box"><h3>🎨 Image Optimized</h3>
        <p>We detected <strong>${hexList.length}</strong> unique print colors in your image.</p>
        <p>The image was simplified to reduce printing cost and ensure quality output.</p>
        <button id="dmi-alert-ok">OK</button></div>`);

      jQuery('body').append(alertBox);
      jQuery('#dmi-alert-ok').on('click', function () {
        alertBox.remove();
      });

      // 🔥 Trigger render using simplified image
      jQuery(document).trigger('dmi:imageUploaded', [uploadedImageUrl]);
    },
    error: function (xhr, status, error) {
      jQuery('#dmi-spinner').removeClass('active');
      console.error('❌ AJAX error during upload:', error);
    }
  });
}

jQuery(document).on('change', '#dmi-upload', function (e) {
  const file = this.files[0];
  if (!file) {
    console.warn('⚠️ No file selected.');
    return;
  }

  console.log('📁 File selected:', file.name);
  processAndUploadImage(file);
});

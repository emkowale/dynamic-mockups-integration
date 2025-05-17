/*
 * File: assets/js/confirm-overlay.js
 * Description: Shows styled overlay if Add to Cart is clicked with no rendered image
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 23:26 EDT
 */

jQuery(document).ready(function ($) {
  if ($('#dmi-confirm-overlay').length === 0) {
    $('body').append(`
      <div id="dmi-confirm-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.4); z-index:10000;">
        <div style="background:#fff; padding:20px 30px; border-radius:10px; max-width:400px; margin:100px auto; text-align:center; box-shadow:0 0 20px rgba(0,0,0,0.3);">
          <p style="font-size:16px; margin-bottom:20px;">⚠️ You didn’t upload an image. Do you want to continue and order it blank?</p>
          <button id="dmi-confirm-yes" class="button" style="margin-right:10px;">Yes</button>
          <button id="dmi-confirm-no" class="button">No</button>
        </div>
      </div>
    `);
  }

  $('form.cart').on('submit', function (e) {
    if (!window.dmi_renderedImageUrl) {
      e.preventDefault();
      $('#dmi-confirm-overlay').fadeIn();

      $('#dmi-confirm-yes').off('click').on('click', function () {
        $('#dmi-confirm-overlay').fadeOut();
        $('form.cart')[0].submit();
      });

      $('#dmi-confirm-no').off('click').on('click', function () {
        $('#dmi-confirm-overlay').fadeOut();
      });
    }
  });
});

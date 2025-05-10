/*
 * Filename: product-fields.js
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Date: 2025-04-25
 * Time: 19:10 EST
 */

jQuery(document).ready(function($) {
    function highlightSelectedThumbnail() {
        $('.dmi-thumbnail-scroll label').removeClass('selected');
        $('.dmi-thumbnail-scroll label input[type="radio"]:checked').each(function() {
            $(this).closest('label').addClass('selected');
        });
    }

    $(document).on('change', '.dmi-thumbnail-scroll label input[type="radio"]', function() {
        highlightSelectedThumbnail();
    });

    // Run immediately after page loads
    setTimeout(function() {
        highlightSelectedThumbnail();
    }, 500);
});


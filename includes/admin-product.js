/*
 * Filename: admin-product.js
 * Author: Eric Kowalewski
 * Plugin Name: Dynamic Mockups Integration
 * Version: 1.9.6
 * Date: 2025-04-28
 * Time: 01:27 EST
 */

jQuery(document).ready(function($) {
    // Handle thumbnail clicks
    $('.dmi-mockup-thumbnail').on('click', function() {
        $('.dmi-mockup-thumbnail').css('border', '1px solid #ccc'); // Reset all
        $(this).css('border', '3px solid blue'); // Highlight selected

        var selectedUuid = $(this).data('uuid');
        $('#_dmi_mockup_uuid').val(selectedUuid);

        // Clear and reset smart object dropdown
        var $dropdown = $('#_dmi_smart_object_uuid');
        $dropdown.empty();
        $dropdown.append($('<option>', {
            value: '',
            text: 'Loading...'
        }));

        // Fetch smart objects dynamically via AJAX
        $.ajax({
            url: dmi_admin_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'dmi_fetch_smart_objects',
                nonce: dmi_admin_ajax_object.nonce,
                mockup_uuid: selectedUuid
            },
            success: function(response) {
                $dropdown.empty();
                if (response.success && response.data.length) {
                    $dropdown.append($('<option>', { value: '', text: 'Select Smart Object' }));
                    response.data.forEach(function(so) {
                        $dropdown.append($('<option>', {
                            value: so.uuid,
                            text: so.name
                        }));
                    });
                } else {
                    $dropdown.append($('<option>', { value: '', text: 'No smart objects found' }));
                }
            },
            error: function() {
                $dropdown.empty().append($('<option>', { value: '', text: 'Error loading smart objects' }));
            }
        });
    });
});


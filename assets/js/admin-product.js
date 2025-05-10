/**
 * File: admin-product.js
 * Description: Handles WooCommerce Product Page for selecting Mockup UUIDs, SmartObject UUIDs (in a dropdown), updating the product image live.
 * Author: Eric Kowalewski
 * Version: 1.9.6
 * Plugin Name: Dynamic Mockups Integration
 */

jQuery(document).ready(function($) {
    console.log('✅ DMI Admin Product: admin-product.js loaded');

    // Make sure mockups are localized
    if (typeof dmi_admin_mockups !== 'undefined' && Array.isArray(dmi_admin_mockups)) {
        console.log('✅ DMI Admin Product: Mockups loaded', dmi_admin_mockups);

        // Populate the Mockup Thumbnails Section
        const $mockupContainer = $('.dmi-mockup-thumbnails');
        $mockupContainer.empty();

        dmi_admin_mockups.forEach(function(mockup) {
            if (mockup.thumbnail) {
                const thumbHtml = `
                    <div style="margin:5px;">
                        <img src="${mockup.thumbnail}" 
                             data-mockup-uuid="${mockup.uuid}" 
                             data-thumbnail-url="${mockup.thumbnail}" 
                             data-smart-objects='${JSON.stringify(mockup.smart_objects || [])}'
                             class="dmi-mockup-thumbnail" 
                             style="width:80px;height:auto;cursor:pointer;">
                    </div>
                `;
                $mockupContainer.append(thumbHtml);
            }
        });

        // Insert Smart Object Dropdown if missing
        if ($('#_dmi_smartobject_uuid_dropdown').length === 0) {
            $('.dmi-smartobject-thumbnails').html(`
                <select id="_dmi_smartobject_uuid_dropdown" style="min-width: 200px;">
                    <option value="">Select Smart Object</option>
                </select>
            `);
        }
    } else {
        console.error('❌ DMI Admin Product: Mockups data missing.');
    }

    // Handle clicking a mockup thumbnail
    $(document).on('click', '.dmi-mockup-thumbnail', function() {
        console.log('✅ DMI Admin Product: Mockup thumbnail clicked');

        $('.dmi-mockup-thumbnail').removeClass('selected');
        $(this).addClass('selected');

        const mockupUUID = $(this).data('mockup-uuid');
        const thumbnailURL = $(this).data('thumbnail-url');
        const smartObjects = $(this).data('smart-objects') || [];

        console.log('✅ DMI Admin Product: Selected UUID:', mockupUUID);
        console.log('✅ DMI Admin Product: Selected Thumbnail URL:', thumbnailURL);
        console.log('✅ DMI Admin Product: Smart Objects:', smartObjects);

        // Update Hidden Inputs
        $('#_dmi_mockup_uuid').val(mockupUUID);
        $('#_dmi_mockup_thumbnail_url').val(thumbnailURL);
        $('#_dmi_smartobject_uuid').val(''); // Reset smartobject field when new mockup clicked

        // Update Product Image live
        const previewImage = document.querySelector('.woocommerce-product-gallery__image img');
        if (previewImage) {
            previewImage.src = thumbnailURL;
            previewImage.srcset = ''; // Clear srcset to avoid WordPress overriding
        }

        // Update Smart Object Dropdown
        const $dropdown = $('#_dmi_smartobject_uuid_dropdown');
        $dropdown.empty();
        $dropdown.append('<option value="">Select Smart Object</option>');

        smartObjects.forEach(function(smart) {
            $dropdown.append(`<option value="${smart.uuid}">${smart.name || smart.uuid}</option>`);
        });
    });

    // Handle selecting a Smart Object from Dropdown
    $(document).on('change', '#_dmi_smartobject_uuid_dropdown', function() {
        const selectedUUID = $(this).val();
        console.log('✅ DMI Admin Product: Selected Smart Object from dropdown:', selectedUUID);

        $('#_dmi_smartobject_uuid').val(selectedUUID); // Update hidden field for saving
    });
});


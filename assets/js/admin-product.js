/*
 * File: admin-product.js
 * Description: Enables clickable thumbnail selection and dynamic smart object dropdown filtering
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 7, 2025 1:31 PM EDT
 */

jQuery(document).ready(function($) {
    console.log('✅ DMI Debug: admin-product.js loaded');

    function renderSmartObjectDropdown(mockupUUID) {
        const smartObjects = dmi_data.smart_objects[mockupUUID] || [];
        const $dropdown = $('#_dmi_smartobject_uuid');
        const current = $dropdown.val();

        $dropdown.empty();
        smartObjects.forEach(obj => {
            const option = $('<option>', {
                value: obj.uuid,
                text: obj.name
            });
            if (obj.uuid === current) {
                option.prop('selected', true);
            }
            $dropdown.append(option);
        });
    }

    $(document).on('click', '.dmi-mockup-thumb', function() {
        const uuid = $(this).data('uuid');
        $('#_dmi_mockup_uuid').val(uuid);
        $('.dmi-mockup-thumb').css('border', '1px solid gray');
        $(this).css('border', '2px solid blue');
        console.log('🎯 Selected Mockup UUID:', uuid);
        renderSmartObjectDropdown(uuid);
    });

    const initialMockup = $('#_dmi_mockup_uuid').val();
    if (initialMockup) {
        renderSmartObjectDropdown(initialMockup);
    }
});

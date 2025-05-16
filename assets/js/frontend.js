/*
 * File: frontend.js
 * Description: Automatically uploads and renders image using Dynamic Mockups after file selection
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 10, 2025 11:59 PM EDT
 */

jQuery(document).ready(function ($) {
    console.log('✅ DMI Debug: frontend.js initialized');

    let uploadedImageUrl = null;

    // Remove the "Upload your image:" label if present
    $('.dmi-label[for="dmi-upload"]').remove();

    // Ensure upload button is styled
    const $uploadButton = $('#dmi-upload-button');
    if ($uploadButton.length) {
        $uploadButton.addClass('button');
        console.log('✅ .button class added to #dmi-upload-button');
    }

    // Add spinner overlay if not already present
    if ($('#dmi-spinner').length === 0) {
        $('body').append('<div id="dmi-spinner"><div></div></div>');
    }

    function showSpinner() {
        $('#dmi-spinner').addClass('active');
    }

    function hideSpinner() {
        $('#dmi-spinner').removeClass('active');
    }

    // Open file dialog
    $(document).on('click', '#dmi-upload-button', function (e) {
        e.preventDefault();
        $('#dmi-upload').trigger('click');
    });

    // Handle file selection + auto render
    $(document).on('change', '#dmi-upload', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'dmi_upload_image');
        formData.append('_ajax_nonce', dmi_ajax.nonce);

        showSpinner();
        console.log('📤 Uploading image...');

        $.ajax({
            url: dmi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                hideSpinner();

                if (!response?.data?.url) {
                    console.error('❌ Upload failed:', response);
                    return;
                }

                uploadedImageUrl = response.data.url;
                console.log('✅ Uploaded image URL:', uploadedImageUrl);

                if ($('#dmi-upload-preview').length) {
                    $('#dmi-upload-preview').html(`<img src="${uploadedImageUrl}" style="max-width: 100%; margin-top: 10px;">`);
                }

                // 🔁 Auto trigger rendering
                const mockupUuid = $('#dmi-mockup-uuid').val();
                const smartobjectUuid = $('#dmi-smartobject-uuid').val();

                if (!mockupUuid || !smartobjectUuid) {
                    console.warn('⚠️ Missing mockup or smartobject UUIDs');
                    return;
                }

                console.log('🎨 Sending render request...');

                showSpinner();

                $.ajax({
                    url: dmi_ajax.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'dmi_render_image',
                        mockup_uuid: mockupUuid,
                        smartobject_uuid: smartobjectUuid,
                        image_url: uploadedImageUrl,
                        _ajax_nonce: dmi_ajax.nonce
                    },
                    success: function (renderResponse) {
                        hideSpinner();

                        if (renderResponse.success && renderResponse.data.rendered_url) {
                            const renderedUrl = renderResponse.data.rendered_url;
                            console.log('✅ Rendered URL:', renderedUrl);

                            $('.woocommerce-product-gallery img.wp-post-image')
                                .attr('src', renderedUrl)
                                .attr('data-src', renderedUrl)
                                .attr('srcset', '');

                            if ($('#dmi-upload-preview').length) {
                                $('#dmi-upload-preview').html(`<img src="${renderedUrl}" style="max-width: 100%; margin-top: 10px;">`);
                            }
                        } else {
                            console.error('❌ Render failed:', renderResponse);
                        }
                    },
                    error: function (xhr, status, error) {
                        hideSpinner();
                        console.error('❌ AJAX error during render:', error);
                    }
                });
            },
            error: function (err) {
                hideSpinner();
                console.error('❌ Upload error:', err);
            }
        });
    });
});

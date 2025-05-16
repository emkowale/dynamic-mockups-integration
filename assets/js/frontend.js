/*
 * File: frontend.js
 * Description: Uploads and renders image with Dynamic Mockups API using smart_objects array, full console debugging
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 16, 2025 8:50 PM EDT
 */

jQuery(document).ready(function ($) {
    console.log('✅ DMI Debug: frontend.js initialized');

    let uploadedImageUrl = null;

    $('.dmi-label[for="dmi-upload"]').remove();

    const $uploadButton = $('#dmi-upload-button');
    if ($uploadButton.length) {
        $uploadButton.addClass('button');
        console.log('✅ .button class added to #dmi-upload-button');
    }

    if ($('#dmi-spinner').length === 0) {
        $('body').append('<div id="dmi-spinner"><div></div></div>');
    }

    function showSpinner() {
        $('#dmi-spinner').addClass('active');
    }

    function hideSpinner() {
        $('#dmi-spinner').removeClass('active');
    }

    $(document).on('click', '#dmi-upload-button', function (e) {
        e.preventDefault();
        $('#dmi-upload').trigger('click');
    });

    $(document).on('change', '#dmi-upload', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'dmi_upload_image');

        console.log('🔐 Upload Nonce:', dmi_ajax.nonce);
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
                    if (response?.data?.message) {
                        console.warn('📛 Server message:', response.data.message);
                    }
                    if (response?.data?.error) {
                        console.warn('📛 Upload error detail:', response.data.error);
                    }
                    if (response?.data?.debug) {
                        console.warn('🐞 DMI Upload Failure Debug:', response.data.debug);
                    }
                    return;
                }

                uploadedImageUrl = response.data.url;
                console.log('🔗 Uploaded Image:', uploadedImageUrl);

                if (response.data.debug) {
                    console.log('🧪 DMI Upload Debug:', response.data.debug);
                }

                if ($('#dmi-upload-preview').length) {
                    $('#dmi-upload-preview').html(`<img src="${uploadedImageUrl}" style="max-width: 100%; margin-top: 10px;">`);
                }

                const mockupUuid = $('#dmi-mockup-uuid').val();
                const smartobjectUuid = $('#dmi-smartobject-uuid').val();

                if (!mockupUuid || !smartobjectUuid) {
                    console.warn('⚠️ Missing mockup or smartobject UUIDs');
                    return;
                }

                const renderPayload = {
                    action: 'dmi_render_image',
                    mockup_uuid: mockupUuid,
                    smart_objects: [
                        {
                            uuid: smartobjectUuid,
                            image_url: uploadedImageUrl
                        }
                    ],
                    _ajax_nonce: dmi_ajax.nonce
                };

                console.log('📤 Rendering With Image:', uploadedImageUrl);
                console.log('📦 DMI Render Request Payload:', JSON.stringify(renderPayload, null, 2));

                showSpinner();

                $.ajax({
                    url: dmi_ajax.ajax_url,
                    method: 'POST',
                    data: renderPayload,
                    success: function (renderResponse) {
                        hideSpinner();

                        if (renderResponse.success && renderResponse.data.rendered_url) {
                            const renderedUrl = renderResponse.data.rendered_url;
                            console.log('🎯 Rendered Image URL:', renderedUrl);
                            console.log('🧪 DMI Render Debug:', renderResponse.data.debug); // ✅ FIXED

                            const uploadedName = uploadedImageUrl.split('/').pop();
                            if (!renderedUrl.includes(uploadedName)) {
                                console.warn(`⚠️ Mismatch: Rendered image does not visibly contain uploaded image filename (${uploadedName})`);
                            }

                            if ($('#dmi-upload-preview').length) {
                                $('#dmi-upload-preview').html(`<img src="${renderedUrl}" style="max-width: 100%; margin-top: 10px;">`);
                            }

                            const $gallery = $('.woocommerce-product-gallery');
                            const $mainImg = $gallery.find('img.wp-post-image');

                            if ($mainImg.length) {
                                $mainImg.attr('src', renderedUrl)
                                        .attr('data-src', renderedUrl)
                                        .attr('data-large_image', renderedUrl)
                                        .attr('data-large_image_width', 800)
                                        .attr('data-large_image_height', 800)
                                        .removeAttr('srcset')
                                        .removeAttr('sizes');

                                console.log('🖼️ WooCommerce image src + metadata updated');
                            }

                            if ($gallery.hasClass('woocommerce-product-gallery--with-images')) {
                                if (typeof $.fn.wc_product_gallery !== 'undefined') {
                                    $gallery.each(function () {
                                        $(this).data('wc_product_gallery')?.init();
                                    });
                                    console.log('🔁 WooCommerce gallery forcibly reinitialized');
                                }
                            }
                        } else {
                            console.error('❌ Render failed:', renderResponse);
                            if (renderResponse.data) {
                                console.log('🧩 Full render error response:', renderResponse.data);
                            }
                            if (renderResponse.data?.debug) {
                                console.log('🧪 DMI Render Debug:', renderResponse.data.debug); // ✅ FIXED
                            }
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

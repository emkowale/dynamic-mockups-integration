/*
 * File: frontend.js
 * Description: Uploads, renders, and restores WooCommerce zoom after image replacement
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: May 17, 2025 01:20 AM EDT
 */

jQuery(document).ready(function ($) {
    console.log('✅ DMI Debug: frontend.js initialized');

    let uploadedImageUrl = null;
    let renderedImageUrl = null;
    let originalImageSrc = null;
    let originalLargeImage = null;

    const $uploadButton = $('#dmi-upload-button');
    if ($uploadButton.length) {
        $uploadButton.addClass('button');
        console.log('✅ .button class added to #dmi-upload-button');
    }

    if ($('#dmi-spinner').length === 0) {
        $('body').append('<div id="dmi-spinner"><div></div></div>');
    }

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
        formData.append('_ajax_nonce', dmi_ajax.nonce);

        console.log('🔐 Upload Nonce:', dmi_ajax.nonce);
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
                console.log('🔗 Uploaded Image:', uploadedImageUrl);

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
                showSpinner();

                $.ajax({
                    url: dmi_ajax.ajax_url,
                    method: 'POST',
                    data: renderPayload,
                    success: function (renderResponse) {
                        hideSpinner();

                        if (renderResponse.success && renderResponse.data.rendered_url) {
                            renderedImageUrl = renderResponse.data.rendered_url;
                            console.log('🎯 Rendered Image URL:', renderedImageUrl);

                            $('#dmi-upload-preview').html(`<img src="${renderedImageUrl}" style="max-width: 100%; margin-top: 10px;">`);

                            const $mainImg = $('.woocommerce-product-gallery img.wp-post-image');
                            const $gallery = $('.woocommerce-product-gallery');

                            if ($mainImg.length) {
                                originalImageSrc = $mainImg.attr('src');
                                originalLargeImage = $mainImg.attr('data-large_image');

                                $mainImg.attr('src', renderedImageUrl)
                                    .attr('data-src', renderedImageUrl)
                                    .attr('data-large_image', renderedImageUrl)
                                    .attr('data-large_image_width', 800)
                                    .attr('data-large_image_height', 800)
                                    .removeAttr('srcset')
                                    .removeAttr('sizes');
                            }

                            $('.woocommerce-product-gallery .woocommerce-product-gallery__image').css({
                                'background-image': `url(${renderedImageUrl})`
                            });

                            $('img.zoomImg').attr('src', renderedImageUrl);

                            // 🔁 Manual zoom rebind for The7
                            setTimeout(() => {
                                if ($.fn.zoom) {
                                    $('.woocommerce-product-gallery .woocommerce-product-gallery__image a').trigger('zoom.destroy');
                                    $('.woocommerce-product-gallery .woocommerce-product-gallery__image a').zoom();
                                    console.log('🔁 Manual zoom reinitialized via .zoom()');
                                }
                            }, 200);

                            $('#dmi-upload-container').slideUp();
                            console.log('🙈 Upload UI hidden after render');
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

    $('form.cart').on('submit', function (e) {
        if (!renderedImageUrl) {
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

/*
 * File: frontend.js
 * Description: Handles customer uploads and injects rendered image into WooCommerce product box
 * Plugin: Dynamic Mockups Integration
 * Author: Eric Kowalewski
 * Last Updated: April 29, 2025 6:31 PM EDT
 */

jQuery(document).ready(function ($) {
    let uploadedImageUrl = null;

    // Inject WooCommerce theme-matching styles
    const style = document.createElement('style');
    style.innerHTML = `
        #dmi-upload-container {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            max-width: 100%;
            box-sizing: border-box;
        }
        #dmi-upload {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 8px;
            font-size: 14px;
        }
        #dmi-submit {
            display: inline-block;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            color: #fff;
            background-color: #515151;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #dmi-submit:hover {
            background-color: #3a3a3a;
        }
        #dmi-spinner {
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s linear 300ms, opacity 300ms;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #dmi-spinner.active {
            visibility: visible;
            opacity: 1;
            transition-delay: 0s;
        }
        #dmi-spinner div {
            width: 60px;
            height: 60px;
            border: 6px solid #fff;
            border-top: 6px solid #515151;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    const spinnerHTML = '<div id="dmi-spinner"><div></div></div>';
    $('body').append(spinnerHTML);

    function showSpinner() {
        $('#dmi-spinner').addClass('active');
    }

    function hideSpinner() {
        $('#dmi-spinner').removeClass('active');
    }

    $(document).on('change', '#dmi-upload', function (e) {
        const file = e.target.files[0];
        if (!file) {
            console.warn('⚠️ DMI Debug: No file selected.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload_image');

        console.log('📤 DMI Debug: Image Uploading...');
        showSpinner();

        $.ajax({
            url: dmi_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                hideSpinner();
                console.log('✅ DMI Debug: Image Done Uploading.');

                if (!response?.data?.url) {
                    console.error('❌ DMI Upload Failed:', response);
                    return;
                }

                uploadedImageUrl = response.data.url;
                console.log('✅ Uploaded image URL:', uploadedImageUrl);
            },
            error: function (err) {
                hideSpinner();
                console.error('❌ Upload error:', err);
            }
        });
    });

    $(document).on('click', '#dmi-submit', function () {
        const productId = $('#dmi-product-id').val();
        const mockupUuid = $('#dmi-mockup-uuid').val();
        const smartobjectUuid = $('#dmi-smartobject-uuid').val();

        if (!uploadedImageUrl) {
            console.warn('⚠️ DMI Debug: No uploaded image URL to render.');
            return;
        }

        const apiEndpoint = 'https://app.dynamicmockups.com/api/v1/renders';
        const apiKey = dmi_ajax.api_key || '';

        const renderPayload = {
            mockup_uuid: mockupUuid,
            smart_objects: [
                {
                    uuid: smartobjectUuid,
                    asset: {
                        url: uploadedImageUrl
                    },
                    color: '#ffffff'
                }
            ]
        };

        console.log('🎨 DMI Debug: Image Rendering...');
        showSpinner();

        fetch(apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': apiKey
            },
            body: JSON.stringify(renderPayload)
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('HTTP Error ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            hideSpinner();
            console.log('✅ DMI Debug: Image Done Rendering.');

            const renderedUrl = data?.data?.export_path;
            if (renderedUrl) {
                const productImage = $('.woocommerce-product-gallery img.wp-post-image');
                if (productImage.length) {
                    productImage.attr('src', renderedUrl);
                    productImage.attr('srcset', '');
                    productImage.attr('data-src', renderedUrl);
                    console.log('✅ Rendered image injected:', renderedUrl);
                } else {
                    console.warn('⚠️ Product image element not found.');
                }
            } else {
                console.error('❌ No rendered_image_url (export_path) in response:', data);
            }
        })
        .catch(err => {
            hideSpinner();
            console.error('❌ Render request failed:', err);
        });
    });

    const productId = $('form.cart').closest('[data-product_id]').data('product_id') || '';
    const mockupUuid = window.dmi_mockup_uuid || '';
    const smartobjectUuid = window.dmi_smartobject_uuid || '';

    const uploadHTML = `
        <div id="dmi-upload-container">
            <label for="dmi-upload">Upload your image (PNG or JPG only):</label>
            <input type="file" id="dmi-upload" accept="image/png,image/jpeg" />
            <button type="button" id="dmi-submit">Render My Image</button>
            <input type="hidden" id="dmi-product-id" value="${productId}" />
            <input type="hidden" id="dmi-mockup-uuid" value="${mockupUuid}" />
            <input type="hidden" id="dmi-smartobject-uuid" value="${smartobjectUuid}" />
        </div>
    `;
    if ($('#dmi-upload-container').length === 0) {
        $('.single-product .cart').after(uploadHTML);
    }
});


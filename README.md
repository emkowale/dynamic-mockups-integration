# 🎨 Dynamic Mockups Integration for WooCommerce

**Version:** 1.9.6  
**Author:** Eric Kowalewski  
**License:** GPLv2 or later  
**Last Updated:** May 28, 2025

---

## 🛠️ Overview

The **Dynamic Mockups Integration** plugin connects your WooCommerce store with the [Dynamic Mockups API](https://docs.dynamicmockups.com), enabling personalized product previews with customer-uploaded images. It automates image rendering, live previews, and ensures the final mockup appears throughout the purchase experience.

---

## ⚙️ Features

- ✅ Live API connection status with red/green indicator
- 🖼 Assign specific mockup and smart object UUIDs per product
- 📤 Customers upload their own images (JPG/PNG)
- 🧠 Auto-rendering via Dynamic Mockups API
- 🖼 Preview updates on product image with zoom support
- 🛒 Rendered image shows in:
  - Add to Cart thumbnail
  - WooCommerce order emails
  - Customer and admin order history
- 🧼 Automatic cleanup after order completion

---

## 🚀 Getting Started

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate it via **Plugins → Dynamic Mockups Integration**
3. Visit **Dynamic Mockups** in your admin menu
4. Enter your API key
5. Assign mockup + smart object UUIDs per product
6. You're ready to start customizing products

---

## 📁 File Structure

dynamic-mockups-integration/
├── admin/
│ └── admin.css
├── assets/
│ ├── css/
│ │ └── frontend.css
│ ├── img/
│ │ └── spinner.svg
│ └── js/
│ ├── admin-product.js
│ ├── admin-settings.js
│ ├── confirm-overlay.js
│ ├── form-guard.js
│ ├── render-handler.js
│ ├── ui-init.js
│ ├── upload-handler.js
│ └── zoom.js
├── frontend/
│ ├── account-thumbnails.php
│ ├── cart-hooks.php
│ ├── cart-meta-injection.php
│ ├── cart-thumbnail.php
│ ├── cart-thumbnails.php
│ ├── cleanup.php
│ ├── email-hooks.php
│ └── render-handler.php
├── includes/
│ ├── admin-settings.php
│ ├── ajax-render.php
│ ├── api.php
│ ├── cart-thumbnail.php
│ ├── class-dmi-render-endpoint.php
│ ├── enqueue-scripts.php
│ ├── frontend-ui.php
│ ├── product-meta-box.php
│ ├── upload-handler.php
│ ├── utils.php
│ └── validate-product-price.php
├── dynamic-mockups-integration.php
├── list-files.php
├── plugin-filetree.txt


---

## 🧪 Developer Notes

- `render-handler.js` injects rendered image URL into form on upload
- `cart-thumbnail.php` overrides the cart image display using stored meta
- `ajax-render.php` handles server-side mockup generation
- The plugin is fully modular for safe, isolated feature updates

---

## 👨‍💻 Author

Eric Kowalewski  
[https://erickowalewski.com](https://erickowalewski.com)

---

## 📄 License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).

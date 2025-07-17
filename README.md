# SAS Find Member Plugin for WordPress

A custom feature allows you to search post types using custom fields and categories.

---
## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Features](#features)
- [Compatibility](#compatibility)
- [Changelog](#changelog)
- [License](#license)

---

## Installation

### Standard WordPress Installation

1.  **Manual Upload (for .zip file):**
    * Download the plugin's `.zip` file from the [GitHub Releases page](https://github.com/your-username/your-repo/releases).
    * In your WordPress admin, go to **Plugins > Add New**.
    * Click the "Upload Plugin" button at the top.
    * Choose the downloaded `.zip` file and click "Install Now."
    * Click "Activate Plugin" once installed.

2.  **Manual FTP Upload:**
    * Unzip the plugin file on your computer.
    * Connect to your WordPress site via FTP/SFTP client.
    * Navigate to the `/wp-content/plugins/` directory.
    * Upload the entire `my-custom-search-plugin` (or your plugin folder name) folder into this directory.
    * Log in to your WordPress admin, go to **Plugins**, and activate "My Custom Search Plugin."

---

## Usage & Configuration

**Basic Usage:**

1. Install CPT UI and ACF plugins.

2. Create a Custom post type make sure the slug is **'members'**

Once activated, the plugin provides a shortcode. You can find the shortcode below or go to **Dashboard > SAS Find Member**

### Shortcode

You can embed a custom search form anywhere on your posts, pages, or custom post types using the `[display_find_member_form]` shortcode.

Paste this on your pages, posts or custom post types.

---

## Features

* Search any Members post type.
* Filter search results by custom field values (supports text, number, and select fields).
* Refine searches by existing WordPress categories.
* Easy integration via a user-friendly shortcode.
* Lightweight and optimized for performance.

---

## Compatibility

* **WordPress Version:** 5.0 or higher
* **PHP Version:** 7.4 or higher
* **Tested Up To:** WordPress 6.5.3

---

## Changelog

**1.0.0 - 2025-07-01**
* Initial Release
* Introduced custom field and category search functionality.
* Added `[display_find_member_form]` shortcode and Custom Search Widget.

---

## License

© [Year] SAS Server Engineer. All rights reserved.
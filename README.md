# SAS Find Member Plugin for WordPress

A custom feature allows you to search post types using custom fields and categories.

---

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Maintenance](#maintenance)
- [Compatibility](#compatibility)
- [Changelog](#changelog)
- [License](#license)

---

## Installation
 
### Standard WordPress Installation

1.  **Manual Upload (for .zip file):**

    - Download the plugin's `.zip` file from the [GitHub Releases page](https://github.com/sitesatscale/sas_custom_find_member/releases).
    - In your WordPress admin, go to **Plugins > Add New**.
    - Click the "Upload Plugin" button at the top.
    - Choose the downloaded `.zip` file and click "Install Now."
    - Click "Activate Plugin" once installed.

2.  **Manual FTP Upload:**
    - Unzip the plugin file on your computer.
    - Connect to your WordPress site via FTP/SFTP client.
    - Navigate to the `/wp-content/plugins/` directory.
    - Upload the entire `sas_custom_find_member` folder into this directory.
    - Log in to your WordPress admin, go to **Plugins**, and activate "My Custom Search Plugin."

---

## Usage

**Basic Usage:**

1. Install **CPT UI** and **ACF plugins**.

2. Create a Custom post type make sure the slug is **'members'**

Once activated, the plugin provides a shortcode. You can find the shortcode below or go to **Dashboard > SAS Find Member**

### Shortcode

You can embed a custom search form anywhere on your posts, pages, or custom post types using the `[display_find_member_form]` shortcode.

Paste this on your pages, posts or custom post types.

---

## Features

- Search any Members post type.
- Filter search results by custom field values (supports text, number, and select fields).
- Refine searches by existing WordPress categories.
- Easy integration via a user-friendly shortcode.
- Lightweight and optimized for performance.

---

## Maintenance

### Contributions

To contribute to this plugin, follow these steps:

1. **Repository Access:**

- This is a private repository located at [GitHub](https://github.com/Scale-it-Sas/sas_custom_find_member).
- To gain access, you must have an active GitHub account.
- Please request an invitation to the `sitesatscale` GitHub Organization from the Server Engineers. Provide them with your GitHub account email address.

2. **Contribution Workflow (Pull Requests):**

- Once you have access, clone the repository to your local machine.
  - `git clone [repo_link]`
- Create a new branch for your changes (e.g., `feature/your-new-feature or bugfix/issue-description`).
- Make your desired code modifications.
- Push your branch to the repository.
  - `git push origin main:your_branch`
- Open a **Pull Request (PR)** from your new branch targeting the `main` branch.
- Your **PR** will require approval from a Server Engineer before it can be merged into `main`
- Once approved, please document your changes in the [changelog](#changelog) below.

---

## Compatibility

- **WordPress Version:** 5.0 or higher
- **PHP Version:** 7.4 or higher
- **Tested Up To:** WordPress 6.5.3

---

## Changelog

**1.0.2 - 2025-08-19**

- Modified search functionality to display all members when no search criteria are selected
- Removed validation requirement for at least one search criterion
- Improved user experience by allowing empty searches to show complete member directory

**1.0.0 - 2025-07-18**

- Added additional info in plugin

**1.0.0 - 2025-07-17**

- Initial Release
- Introduced custom field and category search functionality.
- Added `[display_find_member_form]` shortcode and Custom Search Widget.


---

## License

© 2025 SAS Server Engineer. All rights reserved.

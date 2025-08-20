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

### Initial Setup

1. Install **CPT UI** and **ACF plugins**.
2. Create a Custom post type with slug **'members'**
3. Activate the SAS Find Member plugin.

### Plugin Settings

Navigate to **Dashboard > SAS Find Member** to configure:

#### ACF Field Mapping Tab
- **Enable/disable** ACF fields for search and display
- **Customize labels** for field display names
- **Set searchability** - make fields searchable in frontend
- **Choose position** - display fields in left or right column
- **Configure visibility** - show/hide fields in search results

#### Consulting Services Management Tab
- **Add new services** using the input field and "Add Service" button
- **Edit existing services** by modifying text directly in the list
- **Remove services** using the red "Remove" button
- **Manage dropdown options** that appear in the search form

### Shortcode Usage

Embed the search form using: `[display_find_member_form]`

**Where to use:**
- Pages, posts, or custom post types
- Copy shortcode from settings page for convenience

### Search Features

**Frontend users can search by:**
- Company name, first name, surname (default fields)
- Any ACF fields marked as "searchable" in settings
- Consulting services (categories) via multi-select dropdown
- Partial text matching (e.g., "John" finds "Johnson")

**Search behavior:**
- Empty search shows all members
- Multiple criteria use AND logic (all must match)
- Results display configured fields in chosen positions

---

## Features

### Core Functionality
- **Dynamic search** across Members post type
- **ACF field integration** with configurable search and display
- **Custom consulting services** management (independent from categories)
- **Multi-field search** with partial text matching
- **Responsive design** with mobile-friendly interface

### Admin Features
- **Tabbed settings interface** for easy navigation
- **Real-time ACF field detection** and configuration
- **Visual field mapping** with drag-and-drop positioning
- **Service management** with add/edit/remove capabilities
- **Settings persistence** with automatic saving

### Search Capabilities
- **Flexible field searching** - any ACF field can be made searchable
- **Multiple value support** - handles repeater fields and arrays
- **Smart filtering** - combines multiple search criteria intelligently
- **Clean results display** - customizable field positioning and labels
- **Performance optimized** - efficient database queries

### User Experience
- **No-code configuration** - admin-friendly settings interface
- **Instant feedback** - real-time form validation and responses
- **Accessible design** - keyboard navigation and screen reader support
- **Clean URLs** - SEO-friendly search functionality

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

**2.0.0 - 2025-08-20**

**🎯 Major Release - Dynamic Field Management System**

- **NEW:** Tabbed admin interface with ACF Field Mapping and Consulting Services Management
- **NEW:** Dynamic ACF field detection and configuration system
- **NEW:** Custom consulting services management (independent from WordPress categories)
- **NEW:** Real-time field mapping with searchable/display toggles and positioning controls
- **NEW:** Multi-value field support (repeater fields, arrays) for search and display
- **ENHANCED:** Improved search logic with proper AND/OR handling for multiple criteria
- **ENHANCED:** Better frontend form with dynamic field generation based on admin settings
- **ENHANCED:** Professional WordPress-style admin interface with persistent tab states
- **FIXED:** Resolved consulting services dropdown display issues (no more merged text)
- **FIXED:** Proper search functionality for individual field criteria
- **IMPROVED:** Database efficiency using WordPress options API (no custom tables)
- **IMPROVED:** Category filtering - hidden "Uncategorized" and unused services from results

**1.0.2 - 2025-08-19**

- Modified search functionality to display all members when no search criteria are selected
- Removed validation requirement for at least one search criterion
- Improved user experience by allowing empty searches to show complete member directory

**1.0.1 - 2025-07-18**

- Added additional info in plugin

**1.0.0 - 2025-07-17**

- Initial Release
- Introduced custom field and category search functionality
- Added `[display_find_member_form]` shortcode and Custom Search Widget


---

## License

© 2025 SAS Server Engineer. All rights reserved.

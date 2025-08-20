<?php
// Handle updating consulting service
if (isset($_POST['update_service']) && isset($_POST['new_service_value'])) {
    $old_service = sanitize_text_field($_POST['update_service']);
    $new_service = sanitize_text_field($_POST['new_service_value']);
    $updated_services = [];
    
    // Get updated services from form
    if (isset($_POST['consulting_services']) && is_array($_POST['consulting_services'])) {
        $updated_services = array_map('sanitize_text_field', $_POST['consulting_services']);
        $updated_services = array_filter($updated_services, function($service) {
            return !empty(trim($service));
        });
    }
    
    update_option('sas_consulting_services', array_values($updated_services));
    echo '<div class="notice notice-success"><p>Consulting service "' . esc_html($old_service) . '" updated to "' . esc_html($new_service) . '" successfully!</p></div>';
    echo '<script>localStorage.setItem("sas_active_tab", "consulting-services");</script>';
}

// Handle removing consulting service
if (isset($_POST['remove_service'])) {
    $service_to_remove = sanitize_text_field($_POST['remove_service']);
    $updated_services = [];
    
    // Get updated services from form (excluding the removed one)
    if (isset($_POST['consulting_services']) && is_array($_POST['consulting_services'])) {
        $updated_services = array_map('sanitize_text_field', $_POST['consulting_services']);
        $updated_services = array_filter($updated_services, function($service) {
            return !empty(trim($service));
        });
    }
    
    update_option('sas_consulting_services', array_values($updated_services));
    echo '<div class="notice notice-success"><p>Consulting service "' . esc_html($service_to_remove) . '" removed successfully!</p></div>';
    echo '<script>localStorage.setItem("sas_active_tab", "consulting-services");</script>';
}

// Handle adding new consulting service (separate from main form)
if (isset($_POST['add_new_service']) && isset($_POST['new_consulting_service']) && !empty(trim($_POST['new_consulting_service']))) {
    $current_services = get_option('sas_consulting_services', []);
    $new_service = sanitize_text_field(trim($_POST['new_consulting_service']));
    if (!in_array($new_service, $current_services)) {
        $current_services[] = $new_service;
        update_option('sas_consulting_services', $current_services);
        echo '<div class="notice notice-success"><p>Consulting service "' . esc_html($new_service) . '" added successfully!</p></div>';
        echo '<script>localStorage.setItem("sas_active_tab", "consulting-services");</script>';
    } else {
        echo '<div class="notice notice-warning"><p>Consulting service "' . esc_html($new_service) . '" already exists!</p></div>';
        echo '<script>localStorage.setItem("sas_active_tab", "consulting-services");</script>';
    }
}

// Handle main form submission
if (isset($_POST['sas_save_settings'])) {
    // Handle ACF field mappings
    $field_mappings = [];
    if (isset($_POST['acf_fields']) && is_array($_POST['acf_fields'])) {
        foreach ($_POST['acf_fields'] as $acf_field => $display_data) {
            if (!empty($display_data['enabled'])) {
                $field_mappings[$acf_field] = [
                    'label' => sanitize_text_field($display_data['label']),
                    'show_in_results' => !empty($display_data['show_in_results']),
                    'searchable' => !empty($display_data['searchable']),
                    'position' => sanitize_text_field($display_data['position'] ?? 'left')
                ];
            }
        }
    }
    update_option('sas_acf_field_mappings', $field_mappings);
    
    // Handle consulting services management
    if (isset($_POST['consulting_services']) && is_array($_POST['consulting_services'])) {
        $consulting_services = array_map('sanitize_text_field', $_POST['consulting_services']);
        // Remove empty entries
        $consulting_services = array_filter($consulting_services, function($service) {
            return !empty(trim($service));
        });
        update_option('sas_consulting_services', array_values($consulting_services));
    }
    
    echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
}

// Get current settings
$current_mappings = get_option('sas_acf_field_mappings', []);

// Get ACF fields for members post type
$acf_fields = [];
if (function_exists('acf_get_field_groups')) {
    $field_groups = acf_get_field_groups(['post_type' => 'members']);
    foreach ($field_groups as $group) {
        $fields = acf_get_fields($group['key']);
        if ($fields) {
            foreach ($fields as $field) {
                $acf_fields[$field['name']] = $field['label'];
            }
        }
    }
}

// Get custom consulting services
$consulting_services = get_option('sas_consulting_services', [
    'Accounting & Controls',
    'Architectural Design', 
    'Business Strategy',
    'Concept Development',
    'Design of Kitchens/Food Production Facilities',
    'Dietary',
    'Distribution & Procurement',
    'Energy & Environment',
    'Finance Raising/Corporate Finance',
    'Food Safety & Hygiene',
    'Franchising',
    'Human Resources',
    'Interior Design',
    'IT Systems',
    'Laundry Design',
    'Legal Advice & Litigation Support',
    'Management',
    'Recruitment & Development',
    'Market & Financial Feasibility Studies',
    'Marketing & Promotion',
    'Menu & Recipe Development',
    'Operating Procedures & Systems',
    'Operations Review & Re-Engineering',
    'Operator RFPs',
    'Appointment & Monitoring',
    'Quality Management',
    'Training',
    'Other'
]);
?>

<div class="wrap">
    <h1><strong>SAS Find Member Settings</strong></h1>
    
    <!-- Shortcode Section (Always visible) -->
    <div class="sas-admin-container">
        <h4>Copy this shortcode</h4>
        <div class="shortcode-container">
            <input
                class="sas-copy-shortcode"
                type="text"
                readonly
                value="[display_find_member_form]"
                id="shortcodeToCopy"
            >
            <button
                class="copy-button"
                onclick="copyShortcode()"
                id="copyButton"
            >
                Copy
            </button>
        </div>
        <p class="documentation-link-container">
            For more detailed instructions and advanced usage, refer to our <a href="https://github.com/your-repo-link" target="_blank" class="documentation-link">GitHub Documentation</a>.
        </p>
    </div>

    <!-- Tab Navigation -->
    <nav class="nav-tab-wrapper">
        <a href="#acf-fields" class="nav-tab nav-tab-active" onclick="switchTab(event, 'acf-fields')">ACF Field Mapping</a>
        <a href="#consulting-services" class="nav-tab" onclick="switchTab(event, 'consulting-services')">Consulting Services Management</a>
    </nav>

    <!-- Tab Content -->
    <div id="acf-fields" class="tab-content active">
        <?php if (!empty($acf_fields)): ?>
        <div class="sas-admin-container">
            <h2>ACF Field Mapping</h2>
            <p>Configure which ACF fields to display in search results and make them searchable.</p>
            
            <div class="bulk-actions-section">
                <h3>Bulk Actions</h3>
                <div class="bulk-actions-controls">
                    <button type="button" class="button button-secondary" onclick="selectAll('enable')">Enable All</button>
                    <button type="button" class="button button-secondary" onclick="selectAll('show_in_results')">Show All in Results</button>
                    <button type="button" class="button button-secondary" onclick="selectAll('searchable')">Make All Searchable</button>
                    <button type="button" class="button button-secondary" onclick="selectNone('enable')">Disable All</button>
                    <button type="button" class="button button-secondary" onclick="selectNone('show_in_results')">Hide All from Results</button>
                    <button type="button" class="button button-secondary" onclick="selectNone('searchable')">Make None Searchable</button>
                </div>
            </div>

            <form method="post" action="">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>
                                Enable
                                <br><small>
                                    <a href="#" onclick="selectAll('enable'); return false;">All</a> | 
                                    <a href="#" onclick="selectNone('enable'); return false;">None</a>
                                </small>
                            </th>
                            <th>ACF Field</th>
                            <th>Display Label</th>
                            <th>
                                Show in Results
                                <br><small>
                                    <a href="#" onclick="selectAll('show_in_results'); return false;">All</a> | 
                                    <a href="#" onclick="selectNone('show_in_results'); return false;">None</a>
                                </small>
                            </th>
                            <th>
                                Searchable
                                <br><small>
                                    <a href="#" onclick="selectAll('searchable'); return false;">All</a> | 
                                    <a href="#" onclick="selectNone('searchable'); return false;">None</a>
                                </small>
                            </th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acf_fields as $field_name => $field_label): 
                            $mapping = $current_mappings[$field_name] ?? [];
                            $enabled = !empty($mapping);
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       name="acf_fields[<?php echo esc_attr($field_name); ?>][enabled]" 
                                       value="1" 
                                       <?php checked($enabled); ?>
                                       onchange="toggleFieldRow(this)">
                            </td>
                            <td><strong><?php echo esc_html($field_label); ?></strong><br>
                                <small>Field name: <?php echo esc_html($field_name); ?></small>
                            </td>
                            <td>
                                <input type="text" 
                                       name="acf_fields[<?php echo esc_attr($field_name); ?>][label]" 
                                       value="<?php echo esc_attr($mapping['label'] ?? $field_label); ?>"
                                       <?php echo !$enabled ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="checkbox" 
                                       name="acf_fields[<?php echo esc_attr($field_name); ?>][show_in_results]" 
                                       value="1" 
                                       <?php checked($mapping['show_in_results'] ?? false); ?>
                                       <?php echo !$enabled ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="checkbox" 
                                       name="acf_fields[<?php echo esc_attr($field_name); ?>][searchable]" 
                                       value="1" 
                                       <?php checked($mapping['searchable'] ?? false); ?>
                                       <?php echo !$enabled ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <select name="acf_fields[<?php echo esc_attr($field_name); ?>][position]" <?php echo !$enabled ? 'disabled' : ''; ?>>
                                    <option value="left" <?php selected($mapping['position'] ?? 'left', 'left'); ?>>Left Column</option>
                                    <option value="right" <?php selected($mapping['position'] ?? 'left', 'right'); ?>>Right Column</option>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="sas_save_settings" class="button-primary" value="Save Settings">
                </p>
            </form>
        </div>
        <?php else: ?>
        <div class="sas-admin-container">
            <div class="notice notice-warning">
                <p><strong>No ACF fields found</strong> - Make sure Advanced Custom Fields is installed and you have created field groups for the 'members' post type.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="consulting-services" class="tab-content">
        <div class="sas-admin-container">
            <h2>Consulting Services Management</h2>
            <p>Manage the list of consulting services that appear in the search dropdown.</p>
            
            <div class="consulting-services-manager">
                <div class="add-service-section">
                    <h3>Add New Consulting Service</h3>
                    <form method="post" action="" class="add-service-form">
                        <input type="text" 
                               name="new_consulting_service" 
                               placeholder="Enter new consulting service name"
                               class="regular-text"
                               required>
                        <button type="submit" name="add_new_service" class="button button-secondary">Add Service</button>
                    </form>
                </div>
                
                <div class="services-list-section">
                    <h3>Current Consulting Services</h3>
                    <div class="services-list">
                        <?php if (!empty($consulting_services)): ?>
                            <?php foreach ($consulting_services as $index => $service): ?>
                                <div class="service-item">
                                    <input type="text" 
                                           name="consulting_services[<?php echo $index; ?>]" 
                                           value="<?php echo esc_attr($service); ?>"
                                           class="regular-text service-input"
                                           onchange="updateService(this)">
                                    <button type="button" 
                                            class="button button-small button-link-delete remove-service"
                                            onclick="removeService(this)">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-services">No consulting services configured yet. Add some using the form above.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyShortcode() {
        const shortcodeInput = document.getElementById('shortcodeToCopy');
        const copyButton = document.getElementById('copyButton');

        shortcodeInput.select();
        shortcodeInput.setSelectionRange(0, 99999);

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                const originalButtonText = copyButton.textContent;
                const originalClasses = copyButton.className;

                copyButton.textContent = 'Copied!';
                copyButton.classList.add('bg-green-500');
                copyButton.classList.remove('copy-button');

                setTimeout(() => {
                    copyButton.textContent = originalButtonText;
                    copyButton.className = originalClasses;
                }, 2000);
            } else {
                console.error('Failed to copy text using execCommand.');
                alert('Copy failed. Please manually copy the shortcode: ' + shortcodeInput.value);
            }
        } catch (err) {
            console.error('Error copying text:', err);
            alert('Error copying. Please manually copy the shortcode: ' + shortcodeInput.value);
        }
    }

    function toggleFieldRow(checkbox) {
        const row = checkbox.closest('tr');
        const inputs = row.querySelectorAll('input:not([type="checkbox"]), select');
        const checkboxes = row.querySelectorAll('input[type="checkbox"]:not(:first-child)');
        
        if (checkbox.checked) {
            inputs.forEach(input => input.disabled = false);
            checkboxes.forEach(cb => cb.disabled = false);
            row.style.opacity = '1';
        } else {
            inputs.forEach(input => input.disabled = true);
            checkboxes.forEach(cb => {
                cb.disabled = true;
                cb.checked = false;
            });
            row.style.opacity = '0.5';
        }
    }

    // Initialize row states on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][onchange="toggleFieldRow(this)"]');
        checkboxes.forEach(checkbox => {
            toggleFieldRow(checkbox);
        });
    });

    function removeService(button) {
        if (confirm('Are you sure you want to remove this consulting service?')) {
            const serviceItem = button.closest('.service-item');
            const serviceName = serviceItem.querySelector('.service-input').value;
            
            // Create a form to submit the removal
            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            
            // Add the service to remove
            const removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_service';
            removeInput.value = serviceName;
            form.appendChild(removeInput);
            
            // Add all remaining services (excluding the one being removed)
            const allServices = document.querySelectorAll('.service-input');
            let index = 0;
            allServices.forEach(input => {
                if (input !== serviceItem.querySelector('.service-input')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `consulting_services[${index}]`;
                    hiddenInput.value = input.value;
                    form.appendChild(hiddenInput);
                    index++;
                }
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function updateService(input) {
        const serviceName = input.value.trim();
        const originalValue = input.defaultValue;
        
        if (serviceName === '') {
            alert('Service name cannot be empty.');
            input.value = originalValue;
            return;
        }
        
        if (serviceName !== originalValue) {
            // Create a form to submit the update
            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            
            // Add the update action
            const updateInput = document.createElement('input');
            updateInput.type = 'hidden';
            updateInput.name = 'update_service';
            updateInput.value = originalValue;
            form.appendChild(updateInput);
            
            const newValueInput = document.createElement('input');
            newValueInput.type = 'hidden';
            newValueInput.name = 'new_service_value';
            newValueInput.value = serviceName;
            form.appendChild(newValueInput);
            
            // Add all current services
            const allServices = document.querySelectorAll('.service-input');
            allServices.forEach((serviceInput, index) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `consulting_services[${index}]`;
                hiddenInput.value = serviceInput === input ? serviceName : serviceInput.value;
                form.appendChild(hiddenInput);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function switchTab(event, tabId) {
        event.preventDefault();
        
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all tabs
        const tabLinks = document.querySelectorAll('.nav-tab');
        tabLinks.forEach(link => {
            link.classList.remove('nav-tab-active');
        });
        
        // Show selected tab content
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        
        // Add active class to clicked tab
        event.target.classList.add('nav-tab-active');
        
        // Store active tab in localStorage for persistence
        localStorage.setItem('sas_active_tab', tabId);
    }

    // Initialize tabs on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Restore last active tab from localStorage
        const activeTab = localStorage.getItem('sas_active_tab') || 'acf-fields';
        
        // Hide all tab contents first
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all tabs
        const tabLinks = document.querySelectorAll('.nav-tab');
        tabLinks.forEach(link => {
            link.classList.remove('nav-tab-active');
        });
        
        // Show the active tab
        const activeTabContent = document.getElementById(activeTab);
        if (activeTabContent) {
            activeTabContent.classList.add('active');
        }
        
        // Add active class to the corresponding tab link
        const activeTabLink = document.querySelector(`a[href="#${activeTab}"]`);
        if (activeTabLink) {
            activeTabLink.classList.add('nav-tab-active');
        }
    });

    // Bulk actions for ACF Field Mapping
    function selectAll(type) {
        const checkboxes = getCheckboxesByType(type);
        checkboxes.forEach(checkbox => {
            if (type === 'enable') {
                checkbox.checked = true;
                toggleFieldRow(checkbox);
            } else {
                // Only check non-disabled checkboxes for show_in_results and searchable
                if (!checkbox.disabled) {
                    checkbox.checked = true;
                }
            }
        });
    }

    function selectNone(type) {
        const checkboxes = getCheckboxesByType(type);
        checkboxes.forEach(checkbox => {
            if (type === 'enable') {
                checkbox.checked = false;
                toggleFieldRow(checkbox);
            } else {
                checkbox.checked = false;
            }
        });
    }

    function getCheckboxesByType(type) {
        const selectors = {
            'enable': 'input[name*="[enabled]"]',
            'show_in_results': 'input[name*="[show_in_results]"]',
            'searchable': 'input[name*="[searchable]"]'
        };
        
        return document.querySelectorAll(selectors[type] || '');
    }
</script>
<form id="sas-search-form" class="sas-search-form">
    <div class="sas-form-container">
        <!-- Search Section -->
        <div class="search-section">
            <h2 class="section-title">Search for</h2>
            <div class="sas-grid-container">
                <!-- Default hardcoded fields -->
                <div class="sas-form-group">
                    <label for="companyName" class="form-label">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="sas-form-input"
                        placeholder="Company Name" />
                </div>
                <div class="sas-form-group">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" id="firstName" name="firstName" class="sas-form-input"
                        placeholder="First Name" />
                </div>
                <div class="sas-form-group">
                    <label for="surname" class="form-label">Surname</label>
                    <input type="text" id="surname" name="surname" class="sas-form-input" placeholder="Surname" />
                </div>
                
                <!-- Dynamic ACF searchable fields -->
                <?php 
                $field_mappings = get_option('sas_acf_field_mappings', []);
                
                // Exclude default fields that are already shown above
                $default_fields = ['company', 'firstname', 'surname'];
                
                foreach ($field_mappings as $field_name => $field_config) {
                    if (!empty($field_config['searchable']) && !in_array($field_name, $default_fields)) {
                        $field_label = esc_html($field_config['label']);
                        $field_id = 'acf_' . esc_attr($field_name);
                        ?>
                        <div class="sas-form-group">
                            <label for="<?php echo $field_id; ?>" class="form-label"><?php echo $field_label; ?></label>
                            <input type="text" 
                                   id="<?php echo $field_id; ?>" 
                                   name="<?php echo esc_attr($field_name); ?>" 
                                   class="sas-form-input"
                                   placeholder="<?php echo $field_label; ?>" />
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <!-- Consulting Services Offered Section -->
        <div>
            <h2 class="section-title">Consulting Services Offered (select all that apply)</h2>
            <div class="select-wrapper">
                <select name="filter[5510]" id="consultingServices" class="select2-offscreen" multiple="multiple"
                    placeholder="Click to view/search list" tabindex="-1">
                    <?php
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
                    
                    if (!empty($consulting_services)) {
                        foreach ($consulting_services as $service) {
                            if (!empty(trim($service))) {
                                echo '<option value="' . esc_attr($service) . '">' . esc_html($service) . '</option>' . "\n";
                            }
                        }
                    } else {
                        echo '<option value="Other">Other</option>' . "\n";
                    }
                    ?>
                </select>
                <div id="customSelectDisplay"
                    class="select2-container select2-container--default select2-container--below select2-container--focus select2-container--open select2-container--above select2-container--active select2-container--enabled select2-container--open select2-container--below">
                    <span class="select2-selection select2-selection--multiple" role="combobox" aria-haspopup="true"
                        aria-expanded="true" tabindex="-1">
                        <ul class="select2-selection__rendered" id="selectedOptionsContainer">
                            <!-- Options Here -->
                            <li class="select2-search select2-search--inline">
                                <input class="select2-search__field" type="search" tabindex="0" autocomplete="off"
                                autocorrect="off" autocapitalize="none" spellcheck="false" role="textbox"
                                aria-autocomplete="list" placeholder="Click to view/search list">
                            </li>
                        </ul>
                    </span>
                </div>
                <!-- Custom dropdown list (initially hidden) -->
                <div id="customDropdownList" class="custom-select-dropdown hidden">
                    <!-- Options will be populated here by JavaScript -->
                </div>
            </div>
        </div>

        <button type="submit" class="search-button" id="sas-custom-search-buton">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.39L19.707 19l-1.414 1.414-5.25-5.25A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            Search
        </button>
    </div>
</form>

<div id="sas-search-result" class="hidden">
    <h2>Search Results</h2>
</div>

<style>
#sas-search-result {
    margin-top: 4rem;
}

/* Card container styling */
.contact-card {
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    width: 100%;
    /* max-width: 800px; */
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    margin-bottom: 2rem;
}

.contact-card p {
    font-weight: 600;
    margin-top: 0;
}

.contact-card a {
    text-decoration: none;
}

.contact-card p {
    font-weight: 600;
    margin-top: 0;
}

@media (min-width: 768px) {
    .contact-card {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1.5rem 2rem;
        gap: 2rem;
    }
}

.contact-info-left {
    flex: 2;
    display: flex;
    flex-direction: column;
}

.contact-info-left h3 {
    font-size: 1.125rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.25rem;
    margin-top: 0;
}

.contact-info-left p {
    font-size: 0.875rem;
    color: #555;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}

.contact-info-left .consulting-services {
    margin-top: 0.5rem;
}

.contact-info-left .consulting-services .info-label {
    margin-bottom: 0.25rem;
}

.contact-info-left .consulting-services .info-value {
    line-height: 1.5;
}

.contact-info-right {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex:1;
}

.info-line {
    font-size: 0.875rem;
}

@media (min-width: 768px) {
    .info-line {
        justify-content: flex-end;
    }
}

.info-label {
    font-weight: bold;
    color: #333;
    margin-right: 0.5rem;
    white-space: nowrap;
    display: block;
    margin-bottom: 0;
    margin-top: 0;
}

.info-value {
    color: #555;
    flex-grow: 1;
}

.info-value.email {
    color: #8a2be2;
    text-decoration: none;
}

.card-buttons {

    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

@media (max-width: 767px) {
    .card-buttons {
        position: static;
        margin-top: 1rem;
        align-self: flex-end;
    }
}

.action-button {
    background-color: #8a2be2;
    color: #fff;
    border: none;
    border-radius: 0.25rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: bold;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: background-color 0.2s ease;
    min-width: 60px;
}

.action-button:hover {
    background-color: #6a0dad;
}

.action-button svg {
    margin-right: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    fill: currentColor;
}

.action-button.pdf-button {
    background-color: #6c757d;
}

.action-button.pdf-button:hover {
    background-color: #5a6268;
}


@media print{
    .sas-search-form, 
    .sas-result-header,
    .pdf-button,
    header,
    footer,
    #sidebar,
    .entry-title.main_title
    {
        display: none;
    }
}
</style>
<script>
    const PrintResults = () => {
        window.print();
    }
</script>
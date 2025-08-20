<?php

/*
Plugin Name: SAS Custom Find Member
Plugin URI:  https://github.com/Scale-it-Sas/sas_custom_find_member#sas-find-member-plugin-for-wordpress
Description: A custom feature allows you to search post types using custom fields and categories.
Version:     2.0.0
Author:      Kieren - SAS Server Engineer
*/

if (! defined('ABSPATH')) {
    exit;
}

if (!class_exists('SASCustomFindMember')) {
    class SASCustomFindMember
    {
        public function __construct()
        {
            // UI side
            add_action('wp_enqueue_scripts', [$this, 'load_styles']);
            add_action('wp_enqueue_scripts', [$this, 'load_scripts']);
            add_shortcode('display_find_member_form', [$this, 'display_form']);
            // Admin UI
            add_action('admin_enqueue_scripts', [$this, 'load_admin_styles']);
            add_action('admin_menu', [$this, 'add_admin_menu_page']);
            // Ajax
            add_action('wp_enqueue_scripts', [$this, 'sas_ajax_obj']);
            add_action('wp_ajax_sas_find_member', [$this, 'sas_handle_member_search']);
            add_action('wp_ajax_nopriv_sas_find_member', [$this, 'sas_handle_member_search']);
        }
        public function add_admin_menu_page () {
            add_menu_page(
                'SAS Find Member',
                'SAS Find Member', 
                'manage_options',                     
                'sas-find-member',
                null,
                'dashicons-universal-access-alt',
                60                                   
            );

            add_submenu_page(
                'sas-find-member',
                'SAS Find Member Settings', 
                'SAS Find Member Settings', 
                'manage_options', 
                'sas-find-member', 
                [$this, 'display_settings']  
            );
        }
        public function display_settings(){
            ob_start();
            $this->load_ui('settings');
            echo ob_get_clean();
        }
        public function display_form()
        {
            ob_start();
            $this->load_ui('search-form');
            return ob_get_clean();
        }
        public function load_styles()
        {
            if (! is_admin()) {
                wp_enqueue_style(
                    'sas-custom-css',
                    plugin_dir_url(__FILE__) . 'UI/assets/style.css'
                );
            }
        }
        public function load_admin_styles($hook) {
            // Only load on our plugin's admin page
            if ($hook !== 'toplevel_page_sas-find-member') {
                return;
            }
            
            wp_enqueue_style(
                'sas-find-member-settings-style',
                plugin_dir_url(__FILE__) . 'UI/assets/settings.css',
                [],
                filemtime(plugin_dir_path(__FILE__) . 'UI/assets/settings.css')
            );
        }

        public function load_scripts()
        {
            if (! is_admin()) {
                wp_enqueue_script(
                    'sas-custom-script',
                    plugin_dir_url(__FILE__) . 'UI/assets/index.js',
                    ['jquery'], // ← Add this line
                    filemtime(plugin_dir_path(__FILE__) . 'UI/assets/index.js'),
                    true
                );
            }
        }
        public function sas_ajax_obj()
        {
            if (! is_admin()) {
                wp_localize_script('sas-custom-script', 'sas_ajax_obj', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('sas_ajax_nonce'),
                    'css_url'  => plugin_dir_url(__FILE__) . 'UI/assets/style.css'
                ]);
            }
        }
        private function load_ui($file)
        {
            $ui_file = plugin_dir_path(__FILE__) . 'UI/' . $file . '.php';

            if (file_exists($ui_file)) {
                require_once $ui_file;
            } else {
                echo "<p>UI file not found: $file</p>";
            }
        }

        public function get_acf_fields_for_post_type($post_type = 'members') 
        {
            $acf_fields = [];
            if (function_exists('acf_get_field_groups')) {
                $field_groups = acf_get_field_groups(['post_type' => $post_type]);
                foreach ($field_groups as $group) {
                    $fields = acf_get_fields($group['key']);
                    if ($fields) {
                        foreach ($fields as $field) {
                            $acf_fields[$field['name']] = [
                                'label' => $field['label'],
                                'type' => $field['type'],
                                'key' => $field['key']
                            ];
                        }
                    }
                }
            }
            return $acf_fields;
        }

        public function get_field_mappings() 
        {
            return get_option('sas_acf_field_mappings', []);
        }

        public function is_acf_active() 
        {
            return function_exists('acf_get_field_groups');
        }

        public function validate_field_mapping($field_name, $config) 
        {
            if (!is_array($config)) {
                return false;
            }
            
            $required_keys = ['label', 'show_in_results', 'searchable', 'position'];
            foreach ($required_keys as $key) {
                if (!isset($config[$key])) {
                    return false;
                }
            }
            
            if (!in_array($config['position'], ['left', 'right'])) {
                return false;
            }
            
            return true;
        }


        function sas_handle_member_search()
        {
            if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'sas_ajax_nonce')) {
                wp_send_json_error('Invalid nonce');
            }

            $keyword = $_POST['keyword'] ?? [];
            $paged   = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

            $meta_query = [];
            $tax_query = [];
            $meta_conditions = [];

            // Get field mappings
            $field_mappings = get_option('sas_acf_field_mappings', []);

            // Build meta conditions dynamically based on field mappings
            foreach ($field_mappings as $field_name => $field_config) {
                if (!empty($field_config['searchable']) && !empty($keyword[$field_name])) {
                    $search_value = sanitize_text_field($keyword[$field_name]);
                    
                    // For repeater fields and multiple values, we need to search in serialized data
                    // This handles ACF repeater fields, multiple select fields, etc.
                    $meta_conditions[] = [
                        'relation' => 'OR',
                        [
                            'key' => $field_name,
                            'value' => $search_value,
                            'compare' => 'LIKE',
                        ],
                        [
                            'key' => $field_name . '_%',
                            'value' => $search_value,
                            'compare' => 'LIKE',
                        ]
                    ];
                }
            }

            // Handle hardcoded default fields (company, first_name, surname)
            if (!empty($keyword['company'])) {
                $meta_conditions[] = [
                    'key' => 'company',
                    'value' => sanitize_text_field($keyword['company']),
                    'compare' => 'LIKE',
                ];
            }
            if (!empty($keyword['first_name'])) {
                $meta_conditions[] = [
                    'key' => 'firstname',
                    'value' => sanitize_text_field($keyword['first_name']),
                    'compare' => 'LIKE',
                ];
            }
            if (!empty($keyword['surname'])) {
                $meta_conditions[] = [
                    'key' => 'surname',
                    'value' => sanitize_text_field($keyword['surname']),
                    'compare' => 'LIKE',
                ];
            }

            // Build the final meta query
            if (!empty($meta_conditions)) {
                if (count($meta_conditions) > 1) {
                    $meta_query = array_merge(['relation' => 'AND'], $meta_conditions);
                } else {
                    $meta_query = $meta_conditions;
                }
            }

            if (!empty($keyword['services']) && is_array($keyword['services'])) {
                $tax_query[] = [
                    'taxonomy' => 'category',
                    'field'    => 'name',
                    'terms'    => array_map('sanitize_text_field', $keyword['services']),
                    'operator' => 'IN',
                ];
            }
            
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }

            $args = [
                'post_type'      => 'members',
                'posts_per_page' => 20,
                'paged'          => $paged,
            ];

            if (!empty($meta_query)) {
                $args['meta_query'] = $meta_query;
            }
            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }

            $query = new WP_Query($args);

            if (ob_get_length()) {
                ob_end_clean();
            }

            ob_start();

            if ($query->have_posts()) {
                $foundResult = $query->found_posts;
                echo '<div class="sas-result-header">';
                    echo '<div><h3 class="sas-found-result-txt"><strong>Found Result: '. $foundResult .'.</strong></h3></div>';

                    echo '<button class="action-button pdf-button" type="button" onclick="PrintResults()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 9.586V4a1 1 0 011-1zM2 15a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2z"
                                    clip-rule="evenodd" />
                            </svg>
                            PDF
                        </button>';
                echo '</div>';
                echo '<div class="sas-results">';
                while ($query->have_posts()) {
                    $query->the_post();

                    // Get dynamic consulting services
                    $services = get_the_terms(get_the_ID(), 'category');
                    $services_list = '';
                    $show_consulting_services = false;
                    
                    if ($services && !is_wp_error($services)) {
                        // Filter out 'Uncategorized' category
                        $filtered_services = array_filter($services, function($service) {
                            return strtolower($service->name) !== 'uncategorized';
                        });
                        
                        if (!empty($filtered_services)) {
                            $services_names = wp_list_pluck($filtered_services, 'name');
                            $services_list = implode(', ', $services_names);
                            $show_consulting_services = true;
                        }
                    }
                ?>
                <div class="member-result contact-card">
                    <div class="contact-info-left">
                        <h3><?php the_title(); ?></h3>
                        <?php 
                        // Get field mappings
                        $field_mappings = get_option('sas_acf_field_mappings', []);
                        
                        // Display mapped fields in left column
                        foreach ($field_mappings as $field_name => $field_config) {
                            if (!empty($field_config['show_in_results']) && $field_config['position'] === 'left') {
                                $field_value = get_field($field_name);
                                if (!empty($field_value)) {
                                    // Handle different field types
                                    if (is_array($field_value)) {
                                        // Handle repeater fields or multiple values
                                        $display_values = [];
                                        foreach ($field_value as $value) {
                                            if (is_array($value)) {
                                                // For repeater fields, extract sub-field values
                                                $sub_values = array_filter($value, function($v) { return !empty($v); });
                                                if (!empty($sub_values)) {
                                                    $display_values[] = implode(' - ', $sub_values);
                                                }
                                            } else {
                                                $display_values[] = $value;
                                            }
                                        }
                                        $display_value = implode(', ', $display_values);
                                    } else {
                                        $display_value = $field_value;
                                    }
                                    
                                    if (!empty($display_value)) {
                                        echo '<p>' . esc_html($display_value) . '</p>';
                                    }
                                }
                            }
                        }
                        
                        // Fallback display for default fields not in mappings
                        if (!isset($field_mappings['company'])) {
                            echo '<p>' . esc_html(get_field('company')) . '</p>';
                        }
                        if (!isset($field_mappings['location'])) {
                            echo '<p>' . esc_html(get_field('location')) . '</p>';
                        }
                        ?>
                        <?php if ($show_consulting_services): ?>
                        <div class="info-line consulting-services">
                            <p class="info-label">Consulting Services:</p>
                            <span class="info-value"><?php echo esc_html($services_list); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="contact-info-right">
                        <?php
                        // Display mapped fields in right column
                        foreach ($field_mappings as $field_name => $field_config) {
                            if (!empty($field_config['show_in_results']) && $field_config['position'] === 'right') {
                                $field_value = get_field($field_name);
                                if (!empty($field_value)) {
                                    $label = esc_html($field_config['label']);
                                    
                                    // Handle different field types
                                    if (is_array($field_value)) {
                                        // Handle repeater fields or multiple values
                                        $display_values = [];
                                        foreach ($field_value as $value) {
                                            if (is_array($value)) {
                                                // For repeater fields, extract sub-field values
                                                $sub_values = array_filter($value, function($v) { return !empty($v); });
                                                if (!empty($sub_values)) {
                                                    $display_values[] = implode(' - ', $sub_values);
                                                }
                                            } else {
                                                $display_values[] = $value;
                                            }
                                        }
                                        $display_value = implode(', ', $display_values);
                                    } else {
                                        $display_value = $field_value;
                                    }
                                    
                                    if (!empty($display_value)) {
                                        echo '<div class="info-line">';
                                        echo '<span class="info-label">' . $label . ':</span>';
                                        
                                        // Special handling for email fields (could be multiple)
                                        if (strpos(strtolower($field_name), 'email') !== false || strpos(strtolower($label), 'email') !== false) {
                                            $emails = is_array($field_value) ? $field_value : [$field_value];
                                            $email_links = [];
                                            foreach ($emails as $email) {
                                                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                                    $email_links[] = '<a href="mailto:' . esc_attr($email) . '" class="info-value email">' . esc_html($email) . '</a>';
                                                }
                                            }
                                            echo implode(', ', $email_links);
                                        } else {
                                            echo '<span class="info-value">' . esc_html($display_value) . '</span>';
                                        }
                                        echo '</div>';
                                    }
                                }
                            }
                        }
                        
                        // Fallback display for default fields not in mappings
                        if (!isset($field_mappings['phone'])) {
                            echo '<div class="info-line">';
                            echo '<span class="info-label">Phone:</span>';
                            echo '<span class="info-value">' . esc_html(get_field('phone')) . '</span>';
                            echo '</div>';
                        }
                        if (!isset($field_mappings['fax'])) {
                            $fax_value = get_field('fax');
                            if (!empty($fax_value)) {
                                echo '<div class="info-line">';
                                echo '<span class="info-label">Fax:</span>';
                                echo '<span class="info-value">' . esc_html($fax_value) . '</span>';
                                echo '</div>';
                            }
                        }
                        if (!isset($field_mappings['email'])) {
                            echo '<div class="info-line">';
                            echo '<span class="info-label">Email:</span>';
                            echo '<a href="mailto:' . esc_attr(get_field('email')) . '" class="info-value email">' . esc_html(get_field('email')) . '</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <div class="card-buttons">
                        <a class="action-button pdf-button" href="<?php echo esc_html( get_permalink() ); ?>" data-member-id="<?php echo get_the_ID(); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L9 9.586V4a1 1 0 011-1zM2 15a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2z"
                                    clip-rule="evenodd" />
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>
                <?php
                }
                echo '</div>';

                // Improved pagination
                $total_pages = $query->max_num_pages;
                if ($total_pages > 1) {
                    echo '<div class="sas-pagination">';
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active_class = ($i === $paged) ? 'sas-pgn-item-active' : '';
                        echo '<a href="#" data-page="' . esc_attr($i) . '" class="sas-pgn-item ' . $active_class . '">' . esc_html($i) . '</a>';
                    }
                    echo '</div>';
                }

                wp_reset_postdata();
            } else {
                echo '<p>No members found matching your criteria.</p>';
            }

            $output = ob_get_clean();
            wp_send_json_success($output);

            wp_die();
        }
    }

    $sas_custom_ui = new SASCustomFindMember();
}
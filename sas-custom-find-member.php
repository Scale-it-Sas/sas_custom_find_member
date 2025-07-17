<?php

/*
Plugin Name: SAS Custom Find Member
Plugin URI:  https://sitesatscale.com/
Description: 
Version:     1.0.0
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
        public function load_admin_styles() {
            wp_enqueue_style(
                'sas-find-member-settings-style',
                plugin_dir_url(__FILE__) . 'UI/assets/settings.css',
                [],
                '1.0.0'
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

            if (!empty($meta_conditions)) {
                $meta_query = array_merge(['relation' => 'OR'], $meta_conditions);
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
                    if ($services && !is_wp_error($services)) {
                        $services_names = wp_list_pluck($services, 'name');
                        $services_list = implode(', ', $services_names);
                    }
                ?>
                <div class="member-result contact-card">
                    <div class="contact-info-left">
                        <h3><?php the_title(); ?></h3>
                        <p><?php echo esc_html(get_field('company')); ?></p>
                        <p><?php echo esc_html(get_field('location')); ?></p>
                        <div class="info-line consulting-services">
                            <p class="info-label">Consulting Services:</p>
                            <span class="info-value"><?php echo esc_html($services_list); ?></span>
                        </div>
                    </div>
                    <div class="contact-info-right">
                        <div class="info-line">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo esc_html(get_field('phone')); ?></span>
                        </div>
                        <?php if(!esc_html(get_field('fax')) == '') { ?>
                        <div class="info-line">
                            <span class="info-label">Fax:</span>
                            <span class="info-value"><?php echo esc_html(get_field('fax')); ?></span>
                        </div>
                        <?php } ?>
                        <div class="info-line">
                            <span class="info-label">Email:</span>
                            <a href="mailto:<?php echo esc_attr(get_field('email')); ?>" class="info-value email">
                                <?php echo esc_html(get_field('email')); ?>
                            </a>
                        </div>
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
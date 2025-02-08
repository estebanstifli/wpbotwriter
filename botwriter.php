<?php
/* 
Plugin Name: BotWriter
Plugin URI:  https://www.wpbotwriter.com
Description: Plugin for automatically generating posts using artificial intelligence. Create content from scratch with AI and generate custom images. Optimize content for SEO, including tags, titles, and image descriptions. Advanced features like ChatGPT, automatic content creation, image generation, SEO optimization, and AI training make this plugin a complete tool for writers and content creators.
Version: 1.3.0
Author: estebandezafra
Requires PHP: 7.0
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: botwriter
Domain Path: /languages
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

define('BOTWRITER_URL', plugin_dir_url(__FILE__));


require plugin_dir_path( __FILE__ ) . 'includes/posts.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require plugin_dir_path( __FILE__ ) . 'includes/logs.php';
require plugin_dir_path( __FILE__ ) . 'includes/announcements.php';



// Enqueque JS Files
function botwriter_enqueue_scripts() { 
    $my_plugin_dir = plugin_dir_url(__FILE__);        
    $screen = get_current_screen();
    $slug = $screen->id;


	
    wp_register_script( 'bootstrapjs',$my_plugin_dir.'/assets/js/bootstrap.min.js' , array('jquery'), false, true );
    wp_enqueue_script( 'bootstrapjs' );
    
    wp_register_script( 'botwriter_bootstrap_bundle',$my_plugin_dir.'/assets/js/bootstrap.bundle.min.js' , array('jquery','botwriter_jquery_ui'), false, true );
    wp_enqueue_script( 'botwriter_bootstrap_bundle' );

    wp_register_script( 'botwriter_botwriter',$my_plugin_dir.'/assets/js/botwriter.js' , array('jquery'), false, true );
    wp_enqueue_script( 'botwriter_botwriter' );

    wp_enqueue_script('botwriter-admin-ajax-status', $my_plugin_dir.'/assets/js/admin-ajax-status.js', ['jquery'], null, true);
    wp_localize_script('botwriter-admin-ajax-status', 'botwriter_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('botwriter_cambiar_status_nonce')
    ]);


    if ($slug==="botwriter_page_botwriter_announcements") {
        wp_enqueue_script('botwriter-dismiss-script', $my_plugin_dir .  'js/botwriter_dismiss.js', array('jquery'), null, true);    
        wp_localize_script('botwriter-dismiss-script','botwriterData',
            array(
                'nonce'   => wp_create_nonce('botwriter_dismiss_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }

    if ($slug==="botwriter_page_botwriter_automatic_post_new") {
        wp_register_script('botwriter_automatic_posts', $my_plugin_dir . 'assets/js/posts.js', array('jquery'), false, true);
        wp_enqueue_script('botwriter_automatic_posts');
    }
    

}
add_action('admin_enqueue_scripts','botwriter_enqueue_scripts');





if (!function_exists('deactivate_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}


function botwriter_enqueue_styles(){
    $my_plugin_dir = plugin_dir_url(__FILE__);
    $screen = get_current_screen();
    $slug = $screen->id;
	
    // Only enqueue styles for a specific admin screen
    if($slug === 'botwriter_page_botwriter_automatic_post_new' || $slug === 'botwriter_page_botwriter_automatic_posts' || $slug === 'botwriter_page_botwriter_prueba' || $slug === 'botwriter_page_botwriter_settings' || $slug === 'botwriter_page_botwriter_logs' || $slug === 'botwriter_page_check_Api') {    
        // Register and enqueue styles with dynamic versioning for better caching
        wp_register_style('botwriter_bootstrap', $my_plugin_dir . 'assets/css/bootstrap.min.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/bootstrap.min.css'));
        wp_enqueue_style('botwriter_bootstrap');
        
        wp_register_style('botwriter_jquery_ui', $my_plugin_dir . 'assets/css/jquery-ui.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/jquery-ui.css'));
        wp_enqueue_style('botwriter_jquery_ui');

        wp_register_style('botwriter_loader', $my_plugin_dir . 'assets/css/loader.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/loader.css'));
        wp_enqueue_style('botwriter_loader');

        wp_register_style('botwriter_style', $my_plugin_dir . 'assets/css/style.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css'));
        wp_enqueue_style('botwriter_style');
    }
}

add_action('admin_enqueue_scripts', 'botwriter_enqueue_styles');



  
// Hook to add the admin menu
add_action('admin_menu', 'botwriter_add_admin_menu');

function botwriter_add_admin_menu() {
    add_menu_page(
        __('BotWriter', 'botwriter'),  // Translatable menu title
        __('BotWriter', 'botwriter'),  // Translatable page title
        'manage_options',                  // Capabilities (only administrators)
        'botwriter_menu',            // Page slug
        'botwriter_admin_page',          // Callback function to display content
        plugin_dir_url(__FILE__) . '/assets/images/icono25.png', // Path to custom icon
        90                                 // Position in the menu
    );

    add_submenu_page('botwriter_menu',
     __('Automatic Posts', 'botwriter'), // Translatable page title
     __('Automatic Posts', 'botwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'botwriter_automatic_posts',// Page slug
      'botwriter_automatic_posts_page' // Callback function to display content
    );

    add_submenu_page('botwriter_menu',
     __('Add New', 'botwriter'), // Translatable page title
     __('Add New', 'botwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'botwriter_automatic_post_new',// Page slug
      'botwriter_form_page_handler' // Callback function to display content
    );

    /* for debugging 
    add_submenu_page('botwriter_menu', 
     __('Test_call', 'botwriter'), // Translatable page title
     __('Test_call', 'botwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'botwriter_prueba',// Page slug
      'botwriter_prueba' // Callback function to display content
    );
    */
    
    
    add_submenu_page('botwriter_menu',
     __('Settings', 'botwriter'), // Translatable page title
     __('Settings', 'botwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'botwriter_settings',// Page slug
      'botwriter_settings_page_handler' // Callback function to display content
    );

    add_submenu_page('botwriter_menu',
     __('Logs', 'botwriter'), // Translatable page title
     __('Logs', 'botwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'botwriter_logs',// Page slug
      'botwriter_logs_page_handler' // Callback function to display content
      
    );
}



function botwriter_prueba() {
    if (!current_user_can('manage_options')) {
        return;
    }    
    
    ?>

    <h1>Prueba...</h1>
    <div>        
        Llamando a la funcion que ejecuta las tareas
    </div>

    <?php
    botwriter_scheduled_events_execute_tasks();

}






// First screen of the plugin
function botwriter_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>    
    <div class="wrap">
        <h1><?php echo esc_html__('BotWriter - AI-Powered Content Creation', 'botwriter'); ?></h1>        
        <p><?php echo esc_html__('BotWriter is a WordPress plugin that uses artificial intelligence (AI) to rewrite existing content or generate completely new content. It integrates with sources such as WordPress, RSS, and Google News to provide unique and SEO-optimized content.', 'botwriter'); ?></p>
        <h2><?php echo esc_html__('Key Features:', 'botwriter'); ?></h2>
        <ul>
            <li><?php echo esc_html__('✔ AI-generated content and rewrites', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ SEO-optimized posts with titles, tags, and metadata', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ Fetch content from WordPress, RSS, and Google News', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ Automated publishing with customizable schedules', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ AI-powered image generation for posts', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ Prevents duplicate content and spam', 'botwriter'); ?></li>
            <li><?php echo esc_html__('✔ Easy-to-use interface with no technical knowledge required', 'botwriter'); ?></li>
        </ul>
        <h2><?php echo esc_html__('How to Get Started?', 'botwriter'); ?></h2>
        <p><?php echo esc_html__('1. Create tasks in Automatic Post.', 'botwriter'); ?></p>        
        <p><?php echo esc_html__('2. The plugin will automatically generate the posts!', 'botwriter'); ?></p>
        <p><?php echo esc_html__('3. (Optional) Configure the settings if needed.', 'botwriter'); ?></p>
        <p><?php echo esc_html__('4. (Optional) Check the logs to see the status of the tasks.', 'botwriter'); ?></p>

        <h2><?php echo esc_html__('Free Plan or Premium Plan', 'botwriter'); ?></h2>
        <p><?php echo esc_html__('BotWriter offers a free plan with limited features. To access all the features, you can upgrade to a premium plan.', 'botwriter'); ?> <a href="https://www.wpbotwriter.com" target="_blank"><?php echo esc_html__('Click here to see the plans.', 'botwriter'); ?></a></p>        
    </div>
    
    <?php
}



// Hook that runs on plugin activation
register_activation_hook(__FILE__, 'botwriter_plugin_activate');

function botwriter_plugin_activate() {    
    $site_url = get_site_url();
    $admin_email = get_option('admin_email');
    
    $remote_url = 'https://wpbotwriter.com/public/activation.php';
    
    //options
    $api_key = get_option('botwriter_api_key');    
    if ($api_key) {        
        return;
    }

    if (get_option('botwriter_paused_tasks') === false) {
        update_option('botwriter_paused_tasks', "2");
    }
    
    if (get_option('botwriter_email') === false) {
        update_option('botwriter_email', get_option('admin_email'));
    }

    if (get_option('botwriter_email_confirmed') === false) {
        update_option('botwriter_email_confirmed', '0');
    }

    if (get_option('botwriter_cron_active') === false) {
        update_option('botwriter_cron_active', '1');
    }
    
    if (get_option('botwriter_ai_image_size') === false) {
        update_option('botwriter_ai_image_size', 'square_hd');
    }
    
    if (get_option('botwriter_sslverify') === false) {
        update_option('botwriter_sslverify', 'yes');
    }


        
    $data = array(
        'user_domainname' => $site_url,
        'email_blog' => $admin_email,
    );

    $ssl_verify = get_option('botwriter_sslverify');
    if ($ssl_verify === 'no') {
        $ssl_verify = false;
    } else {
        $ssl_verify = true;
    }   
    
    $challenge_response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => $ssl_verify, 
    ));

    
    
    if (is_wp_error($challenge_response)) {
        $error_message = $challenge_response->get_error_message();
        //error_log("Error sending data to $remote_url: $error_message");
        return;
    }

    $challenge_body = wp_remote_retrieve_body($challenge_response);
    $challenge_result = json_decode($challenge_body, true);

    
    if (!isset($challenge_result['status']) || $challenge_result['status'] !== 'success' || !isset($challenge_result['challenge'])) {
        //error_log('Invalid challenge response from server.');
        return;
    }

    $challenge = $challenge_result['challenge'];

    
    $secret_key = '1c7b2be420b05ec389c6b7fd59ec5d7db0e457425a81fc88312dee66f3c2c663'; 
    $challenge_response_hash = hash_hmac('sha256', $challenge, $secret_key);

    
    $response_data = array(
        'user_domainname' => $site_url,
        'email_blog' => $admin_email,
        'challenge_response' => $challenge_response_hash,
    );

    $final_response = wp_remote_post($remote_url, array(
        'method'    => 'POST', 
        'body'      => $response_data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => $ssl_verify, 
    ));


    if (is_wp_error($final_response)) {
        $error_message = $final_response->get_error_message();
        //error_log("Error sending challenge response to $remote_url: $error_message");
    } else {        
        $body = wp_remote_retrieve_body($final_response);
        $result = json_decode($body, true);

        if (isset($result['status']) && $result['status'] === 'success' && isset($result['api_key'])) {            
            update_option('botwriter_api_key', sanitize_text_field($result['api_key']));
            //error_log('API Key received and stored successfully.');            
            
        } else {
            //error_log('API Key not received or invalid response.');
        }    
    }
}


// Compatibility check for different WordPress versions
add_action('plugins_loaded', 'botwriter_compatibility_check');

function botwriter_compatibility_check() {
    global $wp_version;

    if (version_compare($wp_version, '4.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));

        wp_die(esc_html__('This plugin requires WordPress 4.0 or higher', 'botwriter'));
    }
}



// funciones extra 
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
  
  
  function botwriter_create_table() {
    global $wpdb;
    try {

        $tasks_table_name = $wpdb->prefix . 'botwriter_tasks';        
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tasks_table_name)) !== $tasks_table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $tasks_sql = "CREATE TABLE $tasks_table_name (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_status` VARCHAR(20) NOT NULL,
                `task_name` VARCHAR(255) NOT NULL,
                `writer` VARCHAR(255) NOT NULL, 
                `narration`  VARCHAR(255),
                `custom_style`  VARCHAR(255),
                `post_language` VARCHAR(255) NOT NULL,                
                `post_length` VARCHAR(255) NOT NULL,
                `custom_post_length` VARCHAR(255) NOT NULL,
                `days` VARCHAR(255) NOT NULL,
                `times_per_day` INT NOT NULL,
                `execution_count` INT DEFAULT 0,
                `last_execution_date` DATE DEFAULT NULL,
                `last_execution_time` TIMESTAMP DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `status` int DEFAULT 1,
                `website_name` VARCHAR(255),
                `website_type` VARCHAR(255),
                `domain_name` VARCHAR(255) NOT NULL,
                `category_id` VARCHAR(255),
                `website_category_id` VARCHAR(255),
                `website_category_name` VARCHAR(255),
                `aigenerated_title` TEXT NOT NULL,
                `aigenerated_content` TEXT NOT NULL,
                `aigenerated_tags` TEXT NOT NULL,
                `aigenerated_image` TEXT NOT NULL,
                `post_count` VARCHAR(255),
                `post_order` VARCHAR(255),
                `title_prompt` TEXT NOT NULL,
                `content_prompt` TEXT NOT NULL,
                `tags_prompt` TEXT NOT NULL,
                `image_prompt` TEXT NOT NULL,
                `image_generating_status` VARCHAR(255),
                `author_selection` VARCHAR(255),
                `news_time_published` VARCHAR(255),
                `news_language` VARCHAR(255),
                `news_country` VARCHAR(255),
                `news_keyword` VARCHAR(255),
                `news_source` VARCHAR(255),
                `rss_source` VARCHAR(255),
                `ai_keywords` TEXT NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($tasks_sql);
        }

        // Table botwriter_logs
        $logs_table_name = $wpdb->prefix . 'botwriter_logs';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $logs_table_name)) !== $logs_table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $logs_sql = "CREATE TABLE $logs_table_name (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_task` int(11) NOT NULL, 
                `id_task_server` int(11) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `last_execution_time` TIMESTAMP DEFAULT 0, 
                `intentosfase1` int(11) NOT NULL DEFAULT 0,
                `intentosfase2` int(11) NOT NULL DEFAULT 0,                
                `task_status` VARCHAR(255),
                `error` TEXT,
                `link_post_original` TEXT,
                `id_post_published` int(11) default 0,                
                `post_status` VARCHAR(20) NOT NULL,
                `task_name` VARCHAR(255) NOT NULL,
                `writer` VARCHAR(255) NOT NULL,
                `narration`  VARCHAR(255),
                `custom_style`  VARCHAR(255),
                `post_language` VARCHAR(255) NOT NULL,
                `post_length` VARCHAR(255) NOT NULL,                                                
                `custom_post_length` VARCHAR(255) NOT NULL,                                                
                `website_name` VARCHAR(255),
                `website_type` VARCHAR(255),
                `domain_name` VARCHAR(255) NOT NULL,
                `category_id` VARCHAR(255),
                `website_category_id` VARCHAR(255),
                `aigenerated_title` TEXT NOT NULL,
                `aigenerated_content` TEXT NOT NULL,
                `aigenerated_tags` TEXT NOT NULL,
                `aigenerated_image` TEXT NOT NULL,
                `post_count` VARCHAR(255),
                `post_order` VARCHAR(255),
                `title_prompt` TEXT NOT NULL,
                `content_prompt` TEXT NOT NULL,
                `tags_prompt` TEXT NOT NULL,
                `image_prompt` TEXT NOT NULL,
                `image_generating_status` VARCHAR(255),
                `author_selection` VARCHAR(255),
                `news_time_published` VARCHAR(255),
                `news_language` VARCHAR(255),
                `news_country` VARCHAR(255),
                `news_keyword` VARCHAR(255),
                `news_source` VARCHAR(255),
                `rss_source` VARCHAR(255),
                `ai_keywords` TEXT NOT NULL,                
                PRIMARY KEY (`id`)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($logs_sql);
        }

    } catch (Exception $e) {
        
        //error_log("Error creating botwriter tables: " . $e->getMessage());
    }
  }
  register_activation_hook(__FILE__, 'botwriter_create_table');
  



// Extending class
class botwriter_tasks_Table extends WP_List_Table
{    
    // Define table columns
    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'writer'        => __('Writer', 'botwriter'),
                'task_name'          => __('Task Name', 'botwriter'),                                
                'days'          => __('Days', 'botwriter'),
                'category_id'   => __('Categories', 'botwriter'),
                'times_per_day' => __('Times per Day', 'botwriter'),
                'status'        => __('Status', 'botwriter') 

        );
        return $columns;
    }


    // define $table_data property
    private $table_data;

    // Bind table with columns, data and all
    function prepare_items()
    {
        //data
        if ( isset( $_POST['s'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'botwriter_nonce' ) ) {
          $search_query = sanitize_text_field(wp_unslash($_POST['s']));
          $this->table_data = $this->get_table_data($search_query);
        } else {
          $this->table_data = $this->get_table_data();
        }
      

        $columns = $this->get_columns();
        $hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_list_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_list_tablecolumnshidden', true) : array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'name';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);
        $this->process_bulk_action();
        $this->table_data = $this->get_table_data();

        usort($this->table_data, array($this, 'usort_reorder'));

        /* pagination */ 
        $per_page = $this->get_items_per_page('elements_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
        
        $this->items = $this->table_data;
    }

     

        function column_task_name($item){
            $slug='botwriter_automatic_post_new';
            $page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';

            $url_edit= wp_nonce_url('?page=' . $slug . '&id=' . $item['id'], "botwriter_tasks_action");
            $url_delete= wp_nonce_url('?page=' . $page . '&action=delete&id=' . $item['id'], "botwriter_tasks_action");

            $actions = array(
                'edit' => sprintf('<a href="%s">%s</a>', $url_edit, __('Edit', 'botwriter')),                
                'delete' => sprintf('<a href="%s">%s</a>', $url_delete, __('Delete', 'botwriter')),
            );

            $id=$item['id'];
            return sprintf('%s %s',
                "<a class='row-title' href='?page=$slug&id=$id&_wpnonce=" . wp_create_nonce('botwriter_tasks_action') . "'>" . $item['task_name'] . "</a>",
                $this->row_actions($actions)
            );
        }

        function column_writer($item){
            $dir_images_writers = plugin_dir_url(__FILE__) . 'assets/images/writers/';
            $writer=$item['writer'];
            $writer = strtolower($writer);

            $slug='botwriter_automatic_post_new';
            $id=$item['id'];         
            $link="<a class='row-title' href='?page=$slug&id=$id'>";
            $img= '<img src="' . esc_url($dir_images_writers . $writer . '.jpeg') . '" alt="' . esc_attr($writer) . '" class="writer-photo">';
            return $link . $img . '</a>';
            
        }

        

        function column_status($item){
            
            $status = $item['status'];
            $status_opuesto = $status ? 0 : 1;
            $icono = $status ? 'dashicons-yes' : 'dashicons-dismiss';
            $texto_status = $status ? 'Desactivate' : 'Activate';
        
            return sprintf(
                '<a href="#" class="icono-status dashicons %s" data-id="%d" data-status="%d" title="%s"></a>',
                $icono,
                $item['id'],
                $status_opuesto,
                $texto_status
            );

            
        }


    function column_category_id($item) {
        $categories = get_categories();
        $category_name = '';
        foreach ($categories as $category) {
            $aux_cateforias=explode(',',$item['category_id']);
            if (in_array($category->term_id, $aux_cateforias)) {
                $category_name .= $category->name . ', ';
            }
            
        }        
        $category_name = rtrim($category_name, ', ');
        return $category_name;
    }


    
      

       // To show bulk action dropdown
    function get_bulk_actions()
    {
            $actions = array(
                    'delete_all'    => __('Delete', 'botwriter'),
                    
            );
            return $actions;
    }

    function process_bulk_action()
    {        
        // Verify nonce
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), "botwriter_tasks_action")) {
            return;
        }
         
        global $wpdb;

        $table = $wpdb->prefix . 'botwriter_tasks';

        if ('delete_all' === $this->current_action() || ('delete' === $this->current_action() && isset($_REQUEST['id']))) {
            $request_id = isset($_REQUEST['id']) ? array_map('absint', (array) $_REQUEST['id']) : array();

            if (!empty($request_id)) {
                // Prepare the DELETE query with proper escaping
                $placeholders = implode(',', array_fill(0, count($request_id), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id IN({$placeholders})", $request_id));
            }
        }
    }

    

      // Get table data
      private function get_table_data( $search = '' ) {
        global $wpdb;
    
        $table = $wpdb->prefix."botwriter_tasks";
        
    
        if ( ! empty( $search ) ) {
            $prepared_search = $wpdb->esc_like( $search );
            $prepared_search = '%' . $wpdb->esc_like( $search ) . '%';
    
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE name LIKE %s ",
                    $prepared_search
                ),
                ARRAY_A
            );
        } else {         
          return $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);                            
        }
    }
    
    function column_default($item, $column_name)
    {

          switch ($column_name) {
                case 'id':
                case 'website_type':             
                case 'website_name':                
                case 'task_name':               
                case 'category_id':          
                case 'website_category_id':
                default:
                    return $item[$column_name];
          }
    }

    function column_cb($item){
        return sprintf(
                '<input type="checkbox" name="id[]" value="%s" />',
                $item['id']
        );
    }

    public function get_sortable_columns(){ 
      $sortable_columns = array(
            'task_name'  => array('task_name', false),            
            'days'  => array('days', false),
            'category_id'   => array('category_id', false),        
            'id'   => array('id', true)
      );
      return $sortable_columns;
    }

      // Sorting function
      function usort_reorder($a, $b)
      { 
          // If no sort, default to task_name          
          $sanitized_orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : '';

          $orderby = (!empty($sanitized_orderby)) ? $sanitized_orderby : 'task_name';
  
          $order = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'asc';

          // filtrar order solo asd o desc
            $order = in_array($order, array('asc', 'desc')) ? $order : 'asc';
            // filter orderby only allowed columns
            $orderby = in_array($orderby, array('task_name', 'days', 'category_id', 'id')) ? $orderby : 'task_name';
            

          
  
          // Determine sort order
          $result = strcmp($a[$orderby], $b[$orderby]);
  
          // Send final sort direction to usort
          return ($order === 'asc') ? $result : -$result;
      }

} // end class botwriter_tasks_Table






function botwriter_validate_website($item,$is_manual = false)
{
    
    $messages = array();

    
    if (empty($item['task_name'])) $messages[] = __('Task Name is required', 'botwriter');
    if (empty($item['category_id'])) $messages[] = __('Category is required', 'botwriter');
    if (empty($item['website_type'])) $messages[] = __('Website Type is required', 'botwriter');


    if($item['website_type'] == 'wordpress'){
        if (empty($item['domain_name'])) { 
            $messages[] = __('Domain Name is required', 'botwriter');
        }
        if( !botwriter_isValidDomain(sanitize_text_field($item['domain_name'])) ){
            $messages[] = __('Domain name should be valid.', 'botwriter');
        }
    }

    if ($item['website_type'] == 'rss') {
        if (empty($item['rss_source'])) {
            $messages[] = __('RSS Source is required', 'botwriter');
        }
    }

    if ($item['website_type'] == 'news') {
        if (empty($item['news_keyword'])) {
            $messages[] = __('News keyword is required', 'botwriter');
        }
    }
    
    if (empty($item['category_id'])) $messages[] = __('Category is required', 'botwriter');

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function botwriter_isValidDomain($domain) {
    // WordPress wp_http_validate_url
    $valid_url = wp_http_validate_url( $domain);
      
    if (!is_wp_error($valid_url)) {
        return true;
    } else {
        return false;
    }
  }
  
  
  function botwriter_is_site_working($site_url, $site_type) {
    $response = false;
  
    if ($site_type === 'wordpress') {
        // Check if the WordPress REST API is accessible
        $api_url = rtrim($site_url, '/') . '/wp-json/wp/v2/posts';
        $headers = @get_headers($api_url);
        if ($headers && strpos($headers[0], '200') !== false) {
            $response = true;
        }
    } elseif ($site_type === 'rss') {
        // Check if the RSS feed is accessible
        $rss = @simplexml_load_file($site_url);
        if ($rss) {
            $response = true;
        }
    }
  
    return $response;
  }
  

//wp-cron:
 

// Add a custom schedule for cron jobs

function botwriter_add_custom_cron_schedule($schedules) {
    if (!isset($schedules['every_30'])) {
        $schedules['every_30'] = array(
            'interval' => 30, // 30 seconds
            'display'  => __('Every thirty seconds', 'botwriter')
        );        
    }
    return $schedules; 
}
add_filter('cron_schedules', 'botwriter_add_custom_cron_schedule'); 

// Schedule the cron job during plugin activation
function botwriter_scheduled_events_plugin_activate() {
    if (get_option('botwriter_cron_active')=="0") {
        return;
    }
    if (!wp_next_scheduled('botwriter_scheduled_events_plugin_cron')) {
        wp_schedule_event(time(), 'every_30', 'botwriter_scheduled_events_plugin_cron');                
    } 
}
register_activation_hook(__FILE__, 'botwriter_scheduled_events_plugin_activate');

// Register the cron task
add_action('botwriter_scheduled_events_plugin_cron', 'botwriter_scheduled_events_execute_tasks');




// Clear the cron job upon plugin deactivation
function botwriter_scheduled_events_plugin_deactivate() {
    wp_clear_scheduled_hook('botwriter_scheduled_events_plugin_cron');    
}
register_deactivation_hook(__FILE__, 'botwriter_scheduled_events_plugin_deactivate');




 function botwriter_scheduled_events_execute_tasks() {
    global $wpdb;
    $table_name_tasks = $wpdb->prefix . 'botwriter_tasks';
    $table_name_logs = $wpdb->prefix . 'botwriter_logs';

    // Get the current day and date
    $current_day = gmdate('l');
    $current_date = gmdate('Y-m-d');

    // Translate the day for matching with stored values
    $days_translations = [
        'Monday' => __('Monday', 'botwriter'),
        'Tuesday' => __('Tuesday', 'botwriter'),
        'Wednesday' => __('Wednesday', 'botwriter'),
        'Thursday' => __('Thursday', 'botwriter'),
        'Friday' => __('Friday', 'botwriter'),
        'Saturday' => __('Saturday', 'botwriter'),
        'Sunday' => __('Sunday', 'botwriter'), 
    ];
    $current_day_translated = $days_translations[$current_day];

    //PPHASE 2
    botwriter_execute_events_pass2();
     
    
    //PHASE 1 Execute each event if it meets the conditions
    // Get tasks scheduled for today and status=1  
    $tasks = (array) $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name_tasks} WHERE days LIKE %s AND status = %d", '%' . $wpdb->esc_like($current_day_translated) . '%', 1));
    foreach ($tasks as $task) {        
        // Reset execution count daily
        $task = (array) $task;
        if ($task["last_execution_date"] !== $current_date) {
            $wpdb->update($table_name_tasks, ['execution_count' => 0, 'last_execution_date' => $current_date], ['id' => $task["id"]]);            
            $task["execution_count"] = 0;
        }

        // Check if the task can still be executed based on its daily limit
        if ($task["execution_count"] < $task["times_per_day"]) {                            
                $last_execution_time = $task["last_execution_time"];
                $now = current_time('timestamp');
                $diff = $now - strtotime($last_execution_time);
                $pause = get_option('botwriter_paused_tasks');
                $pause = is_numeric($pause) ? intval($pause) : 2;
                
                if ($diff > 60 * $pause) { //                 
                    $event=$task;                                                       
                    $event["task_status"] = "pending";            
                    $event["id_task"] = $task["id"];              
                    $event["intentosfase1"] = 0;
                    $id_log=botwriter_logs_register($event);  // create log in db
                    $event["id"]=$id_log;                
                    
                    botwriter_send1_data_to_server( (array) $event);                                      
                    // Update execution count in the database and last_execution_time
                    $current_time = gmdate('Y-m-d H:i:s', current_time('timestamp'));
                    $wpdb->update($table_name_tasks, ['execution_count' => $task["execution_count"] + 1,'last_execution_time'=>$current_time], ['id' => $task["id"]]);            
                }            
        }
    }  // end tasks

   

}

function botwriter_execute_events_pass2(){  
    
    // check if the task is still in queue or finished
    global $wpdb;    
    $table_name_logs = $wpdb->prefix . 'botwriter_logs';
    // INQUEUE
    $events2 = (array) $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name_logs} WHERE task_status=%s", 'inqueue'));    
    foreach ($events2 as $event) {
        $event = (array) $event;
        // Execute the event        
        botwriter_send2_data_to_server( (array) $event);                
    } // end INQUEUE


    //IN ERROR, depending on the attempt, it is resent later or marked as finished
    $events1 = (array) $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name_logs} WHERE task_status=%s AND intentosfase1 < %d", 'error', 8));
    $intento_tiempo = array(0=>0,1=>0,2=>5,3=>10,4=>30,5=>60,6=>120,7=>240,8=>480); // minutos    
    foreach ($events1 as $event) {
        $event = (array) $event;
        // Execute the event if the time has passed 
        $intentosfase1 = $event["intentosfase1"];
        $tiempo = $intento_tiempo[$intentosfase1+1];
        $created_at = strtotime($event["created_at"]);        
        $now = current_time('timestamp');

        $diff = $now - $created_at;       
        if ($diff > $tiempo * 60) {        
            botwriter_send1_data_to_server( (array) $event);
        }
        
    } // END LOGS IN ERROR
   

}


function botwriter_generate_post($data){
    // Create a new post    
    $post_id = wp_insert_post(array(
        'post_title' => $data['aigenerated_title'],
        'post_content' => $data['aigenerated_content'],
        'post_status' => $data['post_status'],
        'post_author' => $data['author_selection'],
        'post_category' => explode(',', $data['category_id']),        
    ));
    
    if ($post_id === 0) {
        //error_log('Error creating post');
        return false;
    }

    // Add tags to the post
    $tags = explode(',', $data['aigenerated_tags']);
    wp_set_post_tags($post_id, $tags);

    // Add image to the post    
    botwriter_attach_image_to_post($post_id, $data['aigenerated_image'], $data['aigenerated_title']);
    return $post_id;
}
 
 
 
 
// Function to send data to the server pass1
function botwriter_send1_data_to_server($data) {
    
    
    Global $botwriter_version;
    $remote_url = 'https://wpbotwriter.com/public/redis_api_cola.php';
     
    if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
        
    $plugin_file = plugin_dir_path(__FILE__) . basename(__FILE__);        
    $plugin_data = get_plugin_data($plugin_file);    
    $botwriter_version = $plugin_data['Version'];

    // settings
    $data['version'] = $botwriter_version;
    $data['api_key'] = get_option('botwriter_api_key');
    $data["user_domainname"] = esc_url(get_site_url());
    $data["ai_image_size"]=get_option('botwriter_ai_image_size');


     
    $current_time = gmdate('Y-m-d H:i:s', current_time('timestamp'));            
    $data["last_execution_time"]=$current_time;
    
    $last_execution_time = $data["last_execution_time"];    
    
    
    $category_ids=array_map('intval', explode(',', $data['category_id']));
    $titles = botwriter_get_logs_titles($data['id_task']);
    if ($titles === false) {
        $data['titles'] = '';
    } else {
        $data['titles'] = implode(' | ', $titles);
    }
            

    // add the links of the posts where it has been copied
    $links = botwriter_get_logs_links($data['id_task']);
    if ($links === false) {
        $data['links'] = '';
    } else {
        $data['links'] = implode(',', $links);
    }    

    // post_lenght
    if ($data['post_length'] === 'custom') {
        $data['post_length'] = $data['custom_post_length'];
    } 


      

    $data["error"]= "";

    $ssl_verify = get_option('botwriter_sslverify');


    if ($ssl_verify === 'no') {
        $ssl_verify = false;
    } else {
        $ssl_verify = true;
    }   



    $response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => $ssl_verify, 
    ));

    $data["intentosfase1"]++;
    botwriter_logs_register($data, $data["id"]); 
    
    $last_execution_time=$data["last_execution_time"];
    

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();        
        //error_log("Error sending data to $remote_url: $error_message");        
        $data["task_status"]="error";
        botwriter_logs_register($data, $data["id"]);            
        return false;

    } else {
        
        if ($response['response']['code'] === 200) {
        
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);        
            if (isset($result['id_task_server']) && $result['id_task_server'] !== 0) { // ok                
                $data["id_task_server"]=$result['id_task_server'];
                $data["task_status"]='inqueue';                   
                botwriter_logs_register($data, $data["id"]);             
                return $result['id_task_server'];                 
            } else { // error                
                $data["task_status"]="error";
                botwriter_logs_register($data, $data["id"]);                
                //error_log('Error sending data to the server');
                return false;
            }            
        } else {  // error          
            $data["task_status"]="error";
            botwriter_logs_register($data, $data["id"]);            
            //error_log('Error sending data to the server');
            return false;
        }         
    }

    
}

// Function to send data to the server pass2
function botwriter_send2_data_to_server($data) {  
    
    $data['api_key'] = get_option('botwriter_api_key');
    $data["user_domainname"] = esc_url(get_site_url());

    $remote_url =  'https://wpbotwriter.com/public/redis_api_finish.php';
            
    $ssl_verify = get_option('botwriter_sslverify');
    if ($ssl_verify === 'no') {
        $ssl_verify = false;
    } else {
        $ssl_verify = true;
    }   

    $response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => $ssl_verify
    ));

    
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        //error_log("Error sending data to $remote_url: $error_message");
        $data["error"]= "Error sending data " . $error_message;
        return false;
    } else {
            
        if ($response['response']['code'] === 200) {
        
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
        
            //echo 'Data recive: <pre>' . print_r($result, true) . '</pre>'; 
            
            // results errors
            if (isset($result["task_status"]) && $result["task_status"] == "error") {
                $data["task_status"]="error";
                $data["error"]=$result["error"];                                
                if ($data["error"]=="Maximum monthly posts limit reached") {
                    botwriter_announcements_add("Maximum monthly posts limit reached", "You have reached the maximum monthly posts limit. Please upgrade your plan to continue using the plugin. <a href='https://wpbotwriter.com' target='_blank'>Go to upgrade</a>");
                    $data["intentosfase1"]=8;
                } 
                if ($data["error"]=="Payment date exceeded") {
                    botwriter_announcements_add("Payment date exceeded", "Your subscription payment date has exceeded. Please renew your subscription to continue using the plugin. <a href='https://wpbotwriter.com' target='_blank'>Go to renew</a>");
                    $data["intentosfase1"]=8;
                }
                if ($data["error"]=="API Key error") {
                    botwriter_announcements_add("API Key error", "Your API Key is invalid. Please check your API Key in the plugin settings. <a href='admin.php?page=botwriter_settings'>Go to Settings</a>");
                    $data["intentosfase1"]=8;
                }
                
                botwriter_logs_register($data, $data["id"]);
                return false; 
            }
            
            // result completed
            if (isset($result["task_status"]) && $result["task_status"] == "completed") {
                botwriter_logs_register($result, $data["id"]);                          
                
                $result=botwriter_logs_get($data["id"]);  // merge the result with the log
                $post_id=botwriter_generate_post($result);
                $result["id_post_published"]=$post_id;
               

                botwriter_logs_register($result, $data["id"]);               
                return $result;
            } 
            
            // other results, inqueue, pending, etc
            $now=current_time('timestamp');
            $last_execution_time = strtotime($data["last_execution_time"]);
            $diff = $now - $last_execution_time;
            if ($diff > 60 * 5) { // 5 minutes
                    $data["task_status"]="error";
                    $data["error"]="Error in server";
                    botwriter_logs_register($data, $data["id"]);                    
            } 
            return false;
                           
           
            
            


        } else {            // error
            // update log
            $data["task_status"]="error";
            botwriter_logs_register($data, $data["id"]);                        
            return false;
        }        
    }

    
        
}



 

function botwriter_cambiar_status_ajax() {
    check_ajax_referer('botwriter_cambiar_status_nonce', 'nonce');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nuevo_status = isset($_POST['status']) ? intval($_POST['status']) : 0;
    
    

    if ($id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'botwriter_tasks';

        $result = $wpdb->update(
            $table_name,
            ['status' => $nuevo_status],
            ['id' => $id],
            ['%d'],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success(['message' => 'Estado actualizado correctamente']);
        } else {
            wp_send_json_error(['message' => 'Error al actualizar el estado']);
        }
    } else {
        wp_send_json_error(['message' => 'ID inválido']);
    }

    wp_die();
}
add_action('wp_ajax_botwriter_cambiar_status', 'botwriter_cambiar_status_ajax');





function botwriter_attach_image_to_post($post_id, $image_url, $post_title) {
        if (!$image_url || !$post_id) {
            return 'Invalid post ID or image URL';
        }
        
  if ($image_url && $post_id) {            
            //$image_data = file_get_contents($image_url);
            $image_data = wp_remote_retrieve_body(wp_remote_get($image_url));
            $upload_dir = wp_upload_dir();
            $filename = sanitize_title($post_title) . '.jpg';
            
            if (wp_mkdir_p($upload_dir['path'])) { 
                $file = $upload_dir['path'] . '/' . $filename;                
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;                
            }
             
            global $wp_filesystem;
            if ( ! function_exists( 'WP_Filesystem' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            WP_Filesystem();
            if ( $wp_filesystem->put_contents( $file, $image_data, FS_CHMOD_FILE ) ) {
                //error_log('Imagen guardada exitosamente en: ' . $file);
            } else {
                //error_log('Error al guardar la imagen en: ' . $file);
            }
    
            $wp_filetype = wp_check_filetype($filename, null);
            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name($filename),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];
    
            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
            wp_update_attachment_metadata($attach_id, $attach_data);
            set_post_thumbnail($post_id, $attach_id);
    
            
        }
    }
   
    
 
    
?>
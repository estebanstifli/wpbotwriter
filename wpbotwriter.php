<?php
/*
Plugin Name: WpBotWriter
Plugin URI:  https://www.wpbotwriter.com
Description: Plugin for automatically generating posts using artificial intelligence. Create content from scratch with AI and generate custom images. Optimize content for SEO, including tags, titles, and image descriptions. Advanced features like ChatGPT, automatic content creation, image generation, AutoGPT, PDF content generation, SEO optimization, and AI training make this plugin a complete tool for writers and content creators.
Version: 1.1
Author: Esteban Stif Li
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpbotwriter
Domain Path: /languages
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

define('WPBOTWRITTER_URL', plugin_dir_url(__FILE__));
 
require plugin_dir_path( __FILE__ ) . 'includes/posts.php';
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require plugin_dir_path( __FILE__ ) . 'includes/logs.php';

// Enqueque JS Files
function wpbotwriter_enqueue_scripts() { 
    $my_plugin_dir = plugin_dir_url(__FILE__);        
    wp_register_script( 'bootstrapjs',$my_plugin_dir.'/assets/js/bootstrap.min.js' , array('jquery'), false, true );
    wp_enqueue_script( 'bootstrapjs' );

    
    wp_register_script( 'wpbotwriter_bootstrap_bundle',$my_plugin_dir.'/assets/js/bootstrap.bundle.min.js' , array('jquery','wpbotwriter_jquery_ui'), false, true );
    wp_enqueue_script( 'wpbotwriter_bootstrap_bundle' );

    wp_register_script( 'wpbotwriter_jquery_ui',$my_plugin_dir.'/assets/js/jquery-ui.min.js' , array('jquery'), false, true );
    wp_enqueue_script( 'wpbotwriter_jquery_ui' );

    wp_register_script( 'wpbotwriter_wpbotwriter',$my_plugin_dir.'/assets/js/wpbotwriter.js' , array('jquery'), false, true );
    wp_enqueue_script( 'wpbotwriter_wpbotwriter' );

    //wp_register_script( 'wpbotwriter_wpbotwriter_ai_modals',$my_plugin_dir.'/assets/js/wpbotwriter_ai_modals.js' , array('jquery'), false, true );
    //wp_enqueue_script( 'wpbotwriter_wpbotwriter_ai_modals' );

    //wp_register_script( 'wpbotwriter_wpbotwriter_rewriting_modal',$my_plugin_dir.'/assets/js/wpbotwriter_rewriting_modal.js' , array('jquery'), false, true );
    //wp_enqueue_script( 'wpbotwriter_rewriting_modal' );


    wp_enqueue_script('wpbotwriter-admin-ajax-status', $my_plugin_dir.'/assets/js/admin-ajax-status.js', ['jquery'], null, true);
    wp_localize_script('wpbotwriter-admin-ajax-status', 'wpbotwriter_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wpbotwriter_cambiar_status_nonce')
    ]);
    

}
add_action('admin_enqueue_scripts','wpbotwriter_enqueue_scripts');






// Load translation files
add_action('plugins_loaded', 'wpbotwriter_load_textdomain');
function wpbotwriter_load_textdomain() {
    load_plugin_textdomain('wpbotwriter', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

if (!function_exists('deactivate_plugins')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}


function wpbotwriter_enqueue_styles(){
    $my_plugin_dir = plugin_dir_url(__FILE__);
    $screen = get_current_screen();
    //error_log('Screen ID: ' . $screen->id); // Esto registra el ID de pantalla en los logs

    $slug = $screen->id;

    // Only enqueue styles for a specific admin screen
    if($slug === 'wpbotwriter_page_wpbotwriter_automatic_post_new' || $slug === 'wpbotwriter_page_wpbotwriter_automatic_posts' || $slug === 'wpbotwriter_page_wpbotwriter_prueba' || $slug === 'wpbotwriter_page_wpbotwriter_settings' || $slug === 'wpbotwriter_page_wpbotwriter_logs' || $slug === 'wpbotwriter_page_check_Api') {    
        // Register and enqueue styles with dynamic versioning for better caching
        wp_register_style('wpbotwriter_bootstrap', $my_plugin_dir . 'assets/css/bootstrap.min.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/bootstrap.min.css'));
        wp_enqueue_style('wpbotwriter_bootstrap');
        
        wp_register_style('wpbotwriter_jquery_ui', $my_plugin_dir . 'assets/css/jquery-ui.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/jquery-ui.css'));
        wp_enqueue_style('wpbotwriter_jquery_ui');

        wp_register_style('wpbotwriter_loader', $my_plugin_dir . 'assets/css/loader.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/loader.css'));
        wp_enqueue_style('wpbotwriter_loader');

        wp_register_style('wpbotwriter_style', $my_plugin_dir . 'assets/css/style.css', array(), filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css'));
        wp_enqueue_style('wpbotwriter_style');
    }
}

add_action('admin_enqueue_scripts', 'wpbotwriter_enqueue_styles');



  
// Hook to add the admin menu
add_action('admin_menu', 'wpbotwriter_add_admin_menu');

function wpbotwriter_add_admin_menu() {
    add_menu_page(
        __('WpBotWriter', 'wpbotwriter'),  // Translatable menu title
        __('WpBotWriter', 'wpbotwriter'),  // Translatable page title
        'manage_options',                  // Capabilities (only administrators)
        'wpbotwriter_menu',            // Page slug
        'wpbotwriter_admin_page',          // Callback function to display content
        plugin_dir_url(__FILE__) . '/assets/images/icono25.png', // Path to custom icon
        90                                 // Position in the menu
    );

    add_submenu_page('wpbotwriter_menu',
     __('Automatic Posts', 'wpbotwriter'), // Translatable page title
     __('Automatic Posts', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'wpbotwriter_automatic_posts',// Page slug
      'wpbotwriter_automatic_posts_page' // Callback function to display content
    );

    add_submenu_page('wpbotwriter_menu',
     __('Add New', 'wpbotwriter'), // Translatable page title
     __('Add New', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'wpbotwriter_automatic_post_new',// Page slug
      'wpbotwriter_form_page_handler' // Callback function to display content
    );

    add_submenu_page('wpbotwriter_menu', 
     __('Prueba', 'wpbotwriter'), // Translatable page title
     __('Prueba', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'wpbotwriter_prueba',// Page slug
      'wpbotwriter_prueba' // Callback function to display content
    );

    add_submenu_page('wpbotwriter_menu',
     __('check api', 'wpbotwriter'), // Translatable page title
     __('check api', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'check_Api',// Page slug
      'wpbotwriter_check_api_key' // Callback function to display content
    );

    add_submenu_page('wpbotwriter_menu',
     __('Settings', 'wpbotwriter'), // Translatable page title
     __('Settings', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'wpbotwriter_settings',// Page slug
      'wpbotwriter_settings_page_handler' // Callback function to display content
    );

    add_submenu_page('wpbotwriter_menu',
     __('Logs', 'wpbotwriter'), // Translatable page title
     __('Logs', 'wpbotwriter'), // Translatable menu title
     'manage_options', // Capabilities (only administrators)
      'wpbotwriter_logs',// Page slug
      'wpbotwriter_logs_page_handler' // Callback function to display content
      
    );
}



function wpbotwriter_prueba() {
    if (!current_user_can('manage_options')) {
        return;
    }    
    ?>

    <h1>Prueba</h1>
    <div>
        <p>Prueba</p>
        Llamando a la funcion cron
    </div>

    <?php
    wpbotwriter_scheduled_events_execute_tasks();

}






// Function to display the settings page
function wpbotwriter_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('wpbotwriter_messages', 'wpbotwriter_message', __('Settings saved', 'wpbotwriter'), 'updated');
    }

    settings_errors('wpbotwriter_messages');
    ?>
    <div class="wrap">
        <h1><?php echo __('WpBotWriter Settings', 'wpbotwriter'); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wpbotwriter_settings_group');
            do_settings_sections('wpbotwriter_settings');
            submit_button(__('Save Settings', 'wpbotwriter'));
            ?>
        </form>
    </div>
    <?php
}





// Register plugin settings
add_action('admin_init', 'wpbotwriter_register_settings');

function wpbotwriter_register_settings() {
    register_setting('wpbotwriter_settings_group', 'wpbotwriter_settings');

    add_settings_section(
        'wpbotwriter_section',
        __('General Settings', 'wpbotwriter'),  // Translatable section title
        'wpbotwriter_section_callback',
        'wpbotwriter_settings'
    );

    add_settings_field(
        'wpbotwriter_remote_url',
        __('Server URL', 'wpbotwriter'),       // Translatable field label
        'wpbotwriter_remote_url_callback',
        'wpbotwriter_settings',
        'wpbotwriter_section'
    );

    add_settings_field(
        'wpbotwriter_custom_message',
        __('Custom Message', 'wpbotwriter'),  // Translatable field label
        'wpbotwriter_custom_message_callback',
        'wpbotwriter_settings',
        'wpbotwriter_section'
    );
}

function wpbotwriter_section_callback() {
    echo '<p>' . __('General settings for the WpBotWriter plugin.', 'wpbotwriter') . '</p>';
}

function wpbotwriter_remote_url_callback() {
    $options = get_option('wpbotwriter_settings');
    ?>
    <input type="text" name="wpbotwriter_settings[remote_url]" value="<?php echo isset($options['remote_url']) ? esc_attr($options['remote_url']) : ''; ?>" style="width: 400px;" />
    <p class="description"><?php echo __('Enter the server URL to send data.', 'wpbotwriter'); ?></p>
    <?php
}

function wpbotwriter_custom_message_callback() {
    $options = get_option('wpbotwriter_settings');
    ?>
    <input type="text" name="wpbotwriter_settings[custom_message]" value="<?php echo isset($options['custom_message']) ? esc_attr($options['custom_message']) : ''; ?>" style="width: 400px;" />
    <p class="description"><?php echo __('Write a custom message to be sent.', 'wpbotwriter'); ?></p>
    <?php
}

// Hook that runs on plugin activation
register_activation_hook(__FILE__, 'wpbotwriter_plugin_activate');

function wpbotwriter_plugin_activate() {
    $site_url = get_site_url();
    $admin_email = get_option('admin_email');

    $options = get_option('wpbotwriter_settings');
    $remote_url = isset($options['remote_url']) ? $options['remote_url'] : 'https://wpbotwriter.com/public/activation.php';
    error_log('Remote URL: ' . $remote_url);
    error_log('Site URL: ' . $site_url);
    error_log('Admin Email: ' . $admin_email);



    $data = array(
        'user_domainname' => $site_url,
        'email_blog' => $admin_email,
    );

    $response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => false, // Desactiva la verificación del certificado SSL
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Error sending data to $remote_url: $error_message");
    } else {
        // Procesar la respuesta si la solicitud fue exitosa
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        

        error_log('Data received: ' . print_r($result, true));

        if (isset($result['status']) && $result['status'] === 'success' && isset($result['api_key'])) {
            // Guardar la API Key devuelta en las opciones del plugin
            update_option('wpbotwriter_api_key', sanitize_text_field($result['api_key']));
            error_log('API Key received and stored successfully.');
            // coge la api key para probar que se ha guardao bien:
            $api_key = get_option('wpbotwriter_api_key');
            error_log('API Key: ' . $api_key);
        } else {
            error_log('API Key not received or invalid response.');
        }    
    }
    
}

// Compatibility check for different WordPress versions
add_action('plugins_loaded', 'wpbotwriter_compatibility_check');

function wpbotwriter_compatibility_check() {
    global $wp_version;

    if (version_compare($wp_version, '1.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WordPress 4.0 or higher', 'wpbotwriter'));
    }
}



// funciones extra 
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
  
  
  function wpbotwriter_create_table() {
    global $wpdb;
    try {

        $tasks_table_name = $wpdb->prefix . 'wpbotwriter_tasks';
        $tasks_prepared_query = $wpdb->prepare("SHOW TABLES LIKE %s", $tasks_table_name);

        if ($wpdb->get_var($tasks_prepared_query) !== $tasks_table_name) {
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
                `days` VARCHAR(255) NOT NULL,
                `times_per_day` INT NOT NULL,
                `execution_count` INT DEFAULT 0,
                `last_execution_date` DATE DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `status` int DEFAULT 1,
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
            dbDelta($tasks_sql);
        }

        // Table wpbotwriter_logs
        $logs_table_name = $wpdb->prefix . 'wpbotwriter_logs';
        $logs_prepared_query = $wpdb->prepare("SHOW TABLES LIKE %s", $logs_table_name);

        if ($wpdb->get_var($logs_prepared_query) !== $logs_table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $logs_sql = "CREATE TABLE $logs_table_name (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_task` int(11) NOT NULL, 
                `id_task_server` int(11) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
        
        error_log("Error creating wpbotwriter tables: " . $e->getMessage());
    }
  }
  register_activation_hook(__FILE__, 'wpbotwriter_create_table');
  

// luego habra que modificarlo:
    // Plugin menu callback function
/*    
function wpbotwriter_automaticPosts_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
      // Creating an instance
      $table = new wpbotwriter_tasks_Table();

      echo '<div class="wrap"><h2>Tasks Admin Table</h2>';
      echo '<form method="post">';

      // Add nonce field
      wp_nonce_field('wpbotwriter_nonce', '_wpnonce');
      // Prepare table
      $table->prepare_items();
      // Search form
      $table->search_box('search', 'search_id');
      // Display table
      $table->display();
      echo '</div></form>';
}
      */


// Extending class
class wpbotwriter_tasks_Table extends WP_List_Table
{    
    // Define table columns
    function get_columns()
    {
        $columns = array(
                'cb'            => '<input type="checkbox" />',
                'writer'        => __('Writer', 'wpbotwriter'),
                'task_name'          => __('Task Name', 'wpbotwriter'),                                
                'days'          => __('Days', 'wpbotwriter'),
                'category_id'   => __('Categories', 'wpbotwriter'),
                'times_per_day' => __('Times per Day', 'wpbotwriter'),
                'status'        => __('Status', 'wpbotwriter') 

        );
        return $columns;
    }


    // define $table_data property
    private $table_data;

    // Bind table with columns, data and all
    function prepare_items()
    {
        //data
        if ( isset( $_POST['s'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'wpbotwriter_nonce' ) ) {
          $search_query = sanitize_text_field($_POST['s']);
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

            $slug='wpbotwriter_automatic_post_new';

            $actions = array(
                'edit' => sprintf('<a href="?page=' . $slug . '&id=%s">%s</a>', $item['id'], __('Edit', 'wpbotwriter')),
                'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', sanitize_text_field($_REQUEST['page']), $item['id'], __('Delete', 'wpbotwriter')),
            );

            $id=$item['id'];
            return sprintf('%s %s',
                "<a class='row-title' href='?page=$slug&id=$id'>" . $item['task_name'] . "</a>",
                $this->row_actions($actions)
            );
        }

        function column_writer($item){
            $dir_images_writers = plugin_dir_url(__FILE__) . 'assets/images/writers/';
            $writer=$item['writer'];
            $writer = strtolower($writer);

            $slug='wpbotwriter_automatic_post_new';
            $id=$item['id'];
            // devolver la imagen del escritor
            $link="<a class='row-title' href='?page=$slug&id=$id'>";
            $img= '<img src="' . esc_url($dir_images_writers . $writer . '.jpeg') . '" alt="' . esc_attr($writer) . '" class="writer-photo">';
            return $link . $img . '</a>';
            
        }

        function column_status($item){
            
            $status = $item['status'];
            $status_opuesto = $status ? 0 : 1;
            $icono = $status ? 'dashicons-yes-alt' : 'dashicons-dismiss';
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
            // si la categoria esta en el array de categorias
            if (in_array($category->term_id, $aux_cateforias)) {
                $category_name .= $category->name . ', ';
            }
            
        }
        // quita la ultima coma
        $category_name = rtrim($category_name, ', ');
        return $category_name;
    }


    
      

       // To show bulk action dropdown
    function get_bulk_actions()
    {
            $actions = array(
                    'delete_all'    => __('Delete', 'wpbotwriter'),
                    
            );
            return $actions;
    }

    function process_bulk_action()
    {
    
        global $wpdb;

        $table = $wpdb->prefix . 'wpbotwriter_tasks';

        if ('delete_all' === $this->current_action() || ('delete' === $this->current_action() && isset($_REQUEST['id']))) {
            $request_id = isset($_REQUEST['id']) ? array_map('absint', (array) $_REQUEST['id']) : array();

            if (!empty($request_id)) {
                // Prepare the DELETE query with proper escaping
                $placeholders = implode(',', array_fill(0, count($request_id), '%d'));
                $query = $wpdb->prepare("DELETE FROM $table WHERE id IN($placeholders)", $request_id);

                // Execute the query
                $wpdb->query($query);
            }
        }
    }

    

      // Get table data
      private function get_table_data( $search = '' ) {
        global $wpdb;
    
        $table = $wpdb->prefix."wpbotwriter_tasks";
        
    
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
         
          return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}",
                $table
            ),
            ARRAY_A
        );
        
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
          
          $sanitized_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';

          $orderby = (!empty($sanitized_orderby)) ? $sanitized_orderby : 'task_name';
  
          // If no order, default to asc
          $sanitized_get_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 'asc';
          
          $order = (!empty($sanitized_get_id)) ? $sanitized_get_id : 'asc';
  
          // Determine sort order
          $result = strcmp($a[$orderby], $b[$orderby]);
  
          // Send final sort direction to usort
          return ($order === 'asc') ? $result : -$result;
      }

} // end class wpbotwriter_tasks_Table




// funciones una a una:

function wpbotwriter_validate_website($item,$is_manual = false)
{
    return true; 
    $messages = array();

    echo 'website_type: ' . $item['website_type'] . '<br>';
    echo 'website_name: ' . $item['website_name'] . '<br>';
    echo 'domain_name: ' . $item['domain_name'] . '<br>';
    echo 'category_id: ' . $item['category_id'] . '<br>';
    echo 'website_category_id way: ' . $item['website_category_id'] . '<br>';
    
    

    if($item['website_type'] !== 'ai'){

      if( !wpbotwriter_isValidDomain(sanitize_text_field($item['domain_name'])) ){
        $messages[] = __('Domain name should be valid.', 'wpbotwriter');
      }

      if($item['website_type'] !== 'news'){
        if (empty($item['domain_name'])) $messages[] = __('Domain Name is required', 'wpbotwriter');

      

      if(!wpbotwriter_is_site_working(sanitize_text_field($item['domain_name']), sanitize_text_field($item['website_type']))){
        $messages[] = __('Domain name should be valid.', 'wpbotwriter');
      }

      }else{

        if (empty($item['news_keyword'])) $messages[] = __('Keyword is required', 'wpbotwriter');

      }

      

    }else{

      if(empty($item['title_prompt']) && empty($item['content_prompt']) && empty($item['tags_prompt']) && empty($item['image_prompt']) ){
        $messages[] = __('You should enter at least 1 prompt! ', 'wpbotwriter');
      }

      if(str_contains($item['content_prompt'],'wpbotwriter-promptcode') && ! wpbotwriter_validate_prompt_code($item['content_prompt'] )){
        $messages[] = __('invalid prompt code. ', 'wpbotwriter');
      }

      if(str_contains($item['title_prompt'],'wpbotwriter-promptcode') || str_contains($item['tags_prompt'],'wpbotwriter-promptcode') || str_contains($item['image_prompt'],'wpbotwriter-promptcode') ){
        $messages[] = __('only content prompt can use prompt codes. ', 'wpbotwriter');
      }


    }   

    //if (empty($item['website_name']) && !$is_manual) $messages[] = __('Website name is required', 'wpbotwriter');
    if (empty($item['category_id'])) $messages[] = __('Category is required', 'wpbotwriter');

    if($item['website_type'] === 'news'){

      if($item['news_country'] === 'any' && $item['news_language'] !== 'any' ){
        $messages[] = __('you should select a country! ', 'wpbotwriter');


      }

    }

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbotwriter_isValidDomain($domain) {
    // WordPress wp_http_validate_url
    $valid_url = wp_http_validate_url( $domain);
      
    if (!is_wp_error($valid_url)) {
        return true;
    } else {
        return false;
    }
  }
  
  
  function wpbotwriter_is_site_working($site_url, $site_type) {
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
function wpbotwriter_add_custom_cron_schedule($schedules) {
    if (!isset($schedules['every_minute'])) {
        $schedules['every_minute'] = array(
            'interval' => 60, // 60 seconds
            'display'  => __('Every Minute', 'wpbotwriter')
        );        
    }
    return $schedules;
}
add_filter('cron_schedules', 'wpbotwriter_add_custom_cron_schedule');


// Schedule the cron job during plugin activation
function wpbotwriter_scheduled_events_plugin_activate() {
    if (!wp_next_scheduled('wpbotwriter_scheduled_events_plugin_cron')) {
        wp_schedule_event(time(), 'every_minute', 'wpbotwriter_scheduled_events_plugin_cron');                
    } 
}
register_activation_hook(__FILE__, 'wpbotwriter_scheduled_events_plugin_activate');

// Clear the cron job upon plugin deactivation
function wpbotwriter_scheduled_events_plugin_deactivate() {
    wp_clear_scheduled_hook('wpbotwriter_scheduled_events_plugin_cron');    
}
register_deactivation_hook(__FILE__, 'wpbotwriter_scheduled_events_plugin_deactivate');

// Register the cron task
add_action('wpbotwriter_scheduled_events_plugin_cron', 'wpbotwriter_scheduled_events_execute_tasks');

 function wpbotwriter_scheduled_events_execute_tasks() {
    global $wpdb;
    $table_name_tasks = $wpdb->prefix . 'wpbotwriter_tasks';
    $table_name_logs = $wpdb->prefix . 'wpbotwriter_logs';

    // Get the current day and date
    $current_day = date('l');
    $current_date = date('Y-m-d');

    // Translate the day for matching with stored values
    $days_translations = [
        'Monday' => __('Monday', 'scheduled-events-plugin'),
        'Tuesday' => __('Tuesday', 'scheduled-events-plugin'),
        'Wednesday' => __('Wednesday', 'scheduled-events-plugin'),
        'Thursday' => __('Thursday', 'scheduled-events-plugin'),
        'Friday' => __('Friday', 'scheduled-events-plugin'),
        'Saturday' => __('Saturday', 'scheduled-events-plugin'),
        'Sunday' => __('Sunday', 'scheduled-events-plugin'), 
    ];
    $current_day_translated = $days_translations[$current_day];

    //echo "vamos...<br>\n";
    //pass2 Have to check if the task is still in queue or finished
    wpbotwriter_execute_events_pass2();    
    

    //pass 1 Execute each event if it meets the conditions
    // Get events scheduled for today and status=1  
    $events = (array) $wpdb->get_results("SELECT * FROM $table_name_tasks WHERE days LIKE '%$current_day_translated%' AND status=1");
    foreach ($events as $event) {        
        // Reset execution count daily
        $event = (array) $event;
        if ($event["last_execution_date"] !== $current_date) {
            $wpdb->update($table_name_tasks, ['execution_count' => 0, 'last_execution_date' => $current_date], ['id' => $event["id"]]);            
            $event["execution_count"] = 0;
        }

        // Check if the event can still be executed based on its daily limit
        if ($event["execution_count"] < $event["times_per_day"]) {
            // Execute the event
            echo('Executing event pass 1: ' . $event["task_name"] . ' - Times: ' . ($event["execution_count"] + 1) . "<br>\n");
            // register in log
            $event["task_status"] = "pending";
            $event["id_task"] = $event["id"];  
            $id_log=wpbotwriter_logs_register($event);
                        
            $id_task_server=wpbotwriter_send1_data_to_server( (array) $event);            
            if ($id_task_server === false ) {
                wpbotwritter_intentofase1_add($event["id"]);
            } else {
                echo "Task id server y actualizamos: " . $id_task_server . " en el log:" . $id_log . "<br>\n";                
                $result=$wpdb->update($table_name_logs, ['id_task_server' => $id_task_server, 'task_status' => 'inqueue'], ['id' => $id_log]);
                if ($result === false) {
                    // Error en la consulta
                    error_log('Error en la actualización: ' . $wpdb->last_error);
                    echo 'Hubo un error en la actualización.';
                } elseif ($result === 0) {
                    // Consulta exitosa pero no afectó filas
                    //echo 'Consulta ejecutada, pero no hubo cambios en la base de datos.';
                } else {
                    // Consulta exitosa y filas afectadas
                    //echo 'Consulta ejecutada exitosamente, filas afectadas: ' . $result;
                }
            }                         
            // Update execution count in the database
            $wpdb->update($table_name_tasks, ['execution_count' => $event["execution_count"] + 1], ['id' => $event["id"]]);
        }
    }
}

function wpbotwriter_execute_events_pass2(){    
    // check if the task is still in queue or finished
    global $wpdb;    
    $table_name_logs = $wpdb->prefix . 'wpbotwriter_logs';
    $events2 = (array) $wpdb->get_results("SELECT * FROM $table_name_logs WHERE task_status='inqueue'");
    foreach ($events2 as $event) {
        $event = (array) $event;
        // Execute the event
        echo('Executing paso2 id_task_server: ' . $event["id_task_server"] . ' - <br>\n');                            
        $result=wpbotwriter_send2_data_to_server( (array) $event);
        if ($result === false ) {
            wpbotwritter_intentofase2_add($event["id"]);
        } else {
            if ($result["task_status"] === 'completed') {
                echo "Task id server y actualizamos: " . $event["id_task_server"] . " en el log:" . $event["id"] . "<br>\n";                                
                
                wpbotwriter_logs_register($result, $event["id"]);                
                //genera el post
                $result=wpbotwriter_logs_get($event["id"]);  // mezclamos el resultado con el log
                $post_id=wpbotwriter_generate_post($result);

                $result["id_post_published"]=$post_id;
                wpbotwriter_logs_register($result, $event["id"]);
                echo "Post generado: " . $post_id . "<br>\n";
            }
        }            
    }
    
}

//crea la funcion que genera el post
function wpbotwriter_generate_post($data){
    // Create a new post
    echo '<h1>Data to generate post:</h1> <pre>' . print_r($data, true) . '</pre>';    
    $post_id = wp_insert_post(array(
        'post_title' => $data['aigenerated_title'],
        'post_content' => $data['aigenerated_content'],
        'post_status' => $data['post_status'],
        'post_author' => $data['author_selection'],
        'post_category' => explode(',', $data['category_id']),        
    ));
    // ver si se ha creado el post
    if ($post_id === 0) {
        error_log('Error creating post');
        return false;
    }

    // Add tags to the post
    $tags = explode(',', $data['aigenerated_tags']);
    wp_set_post_tags($post_id, $tags);

    // Add image to the post
    echo "Adjuntado Imagen: " . $data['aigenerated_image'] . "<br>\n";
    wpbotwriter_attach_image_to_post($post_id, $data['aigenerated_image'], $data['aigenerated_title']);
    return $post_id;
}
 



// Function to send data to the server pass1, recibe el task_id 
function wpbotwriter_send1_data_to_server($data) {
    $remote_url = 'https://wpbotwriter.com/public/api_cola.php';
    
    $data['api_key'] = get_option('wpbotwriter_api_key');
    $data["user_domainname"] = esc_url(get_site_url());
    
    $category_ids=array_map('intval', explode(',', $data['category_id']));
    $titles = wpbotwritter_get_logs_titles($data['id']);
    if ($titles === false) {
        $data['titles'] = '';
    } else {
        $data['titles'] = implode(' | ', $titles);
    }
    
    // añadimos los links de los post en los que se ha copiado
    $links = wpbotwritter_get_logs_links($data['id']);
    if ($links === false) {
        $data['links'] = '';
    } else {
        $data['links'] = implode(',', $links);
    }    

    echo 'Data to send1: <pre>' . print_r($data, true) . '</pre>';
      

    $ids = wpbotwritter_get_logs_ids($data['id']);
    if ($ids === false) {
        $data['ids_posts'] = '';
    } else {
        $data['ids_posts'] = implode(',', $ids);
    }
    

    $data["error"]= "";

    $response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => false, // Desactiva la verificación del certificado SSL
    ));


    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Error sending data to $remote_url: $error_message";
        error_log("Error sending data to $remote_url: $error_message");        
        return false;
    } else {
        // Procesar la respuesta si la solicitud fue exitosa
        echo 'Data recived1: <pre>' . print_r($response, true) . '</pre>';
        if ($response['response']['code'] === 200) {
            // Procesar la respuesta si la solicitud fue exitosa
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
        
            if (isset($result['task_id_server']) && $result['task_id_server'] !== 0) {
                return $result['task_id_server'];                
            } else {
                error_log('Error sending data to the server');
                return false;
            }            
        } else {            
            error_log('Error sending data to the server');
            return false;
        }        
    }
}

// Function to send data to the server pass2, recibe la respuesta si esta completa
function wpbotwriter_send2_data_to_server($data) { 
    $data['api_key'] = get_option('wpbotwriter_api_key');
    $data["user_domainname"] = esc_url(get_site_url());

    
    echo 'Data to send2: <pre>' . print_r($data, true) . '</pre>';

    $remote_url =  'https://wpbotwriter.com/public/api_finish.php';
            

    $response = wp_remote_post($remote_url, array(
        'method'    => 'POST',
        'body'      => $data,
        'timeout'   => 45,
        'headers'   => array(),
        'sslverify' => false, // Desactiva la verificación del certificado SSL
    ));


    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Error sending data to $remote_url: $error_message");
        $data["error"]= "Error sending data " . $error_message;
        return false;
    } else {
        // Procesar la respuesta si la solicitud fue exitosa
        echo 'Data recived2: <pre>' . print_r($response, true) . '</pre>';
        if ($response['response']['code'] === 200) {
            // Procesar la respuesta si la solicitud fue exitosa
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
            // ver si estan los datos ok, si no devolver false y error
            echo 'Datos recibidos: <pre>' . print_r($result, true) . '</pre>';
            echo "Imagen: " . $result['aigenerated_image'] . "<br>\n";
            //sacar la imagen del post
            echo "<img src='" . $result['aigenerated_image'] . "' alt='imagen del post' />";            
            return $result;            
        } else {
            $data["error"]= "Error sending data. Response code: " . $response['response']['code'];
            error_log('Error sending data to the server');
            return false;
        }        
    }

}

// actualiza el log con el id del post
function wpbotwritter_intentofase1_add($id_log) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs';

    // Incrementar el campo intentos
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_name 
             SET intentosfase1 = intentosfase1 + 1 
             WHERE id = %d",
            $id_log
        )
    );

    if ($updated === false) {
        return 0; // Devuelve 0 si la consulta falla
    }

    // Obtener el nuevo valor de intentos
    $result = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT intentosfase1 FROM $table_name WHERE id = %d",
            $id_log
        )
    );

    // Devolver el valor como entero o 0 si es nulo
    return ($result !== null) ? intval($result) : 0;
}

function wpbotwritter_intentofase2_add($id_log) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs';

    // Incrementar el campo intentos
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_name 
             SET intentosfase2 = intentosfase2 + 1 
             WHERE id = %d",
            $id_log
        )
    );

    if ($updated === false) {
        return 0; // Devuelve 0 si la consulta falla
    }

    // Obtener el nuevo valor de intentos
    $result = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT intentosfase2 FROM $table_name WHERE id = %d",
            $id_log
        )
    );

    // Devolver el valor como entero o 0 si es nulo
    return ($result !== null) ? intval($result) : 0;
}


 

function wpbotwriter_cambiar_status_ajax() {
    check_ajax_referer('wpbotwriter_cambiar_status_nonce', 'nonce');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nuevo_status = isset($_POST['status']) ? intval($_POST['status']) : 0;
    // enviar al error log
    error_log('Cambiar status');    
    error_log('ID: ' . $id);
    error_log('Status: ' . $nuevo_status);


    if ($id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpbotwriter_tasks';

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
add_action('wp_ajax_wpbotwriter_cambiar_status', 'wpbotwriter_cambiar_status_ajax');





function wpbotwriter_attach_image_to_post($post_id, $image_url, $post_title) {
        if (!$image_url || !$post_id) {
            return 'Invalid post ID or image URL';
        }
        

        // Descargar la imagen y adjuntarla al artículo
        if ($image_url && $post_id) {
            echo "Descargando y adjuntando la imagen...<br>";
            $image_data = file_get_contents($image_url);
            $upload_dir = wp_upload_dir();
            // $filename = al titulo del post slugificado
            $filename = sanitize_title($post_title) . '.jpg';
            
            echo "descargando imagen:" . $filename . "<br> Directorio de subida: <br>";
            echo $upload_dir['path'];
            echo "<br>";

            
            if (wp_mkdir_p($upload_dir['path'])) {
                $file = $upload_dir['path'] . '/' . $filename;
                error_log('Directorio creado exitosamente: ' . $upload_dir['path']);
            } else {
                $file = $upload_dir['basedir'] . '/' . $filename;
                error_log('No se pudo crear el directorio, usando el directorio base: ' . $upload_dir['basedir']);
            }
            
            echo "Guardando imagen en: $file<br>";

            if (file_put_contents($file, $image_data) !== false) {
                error_log('Imagen guardada exitosamente en: ' . $file);
            } else {
                error_log('Error al guardar la imagen en: ' . $file);
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
    
            echo "Imagen adjuntada al artículo.<br>";
        }
    }
  
    // funcion que pueda que no exista
    if (!function_exists('sanitize_textarea_field')) {
        function sanitize_textarea_field($str) {
            // Asegurarse de que el valor es una cadena
            if (!is_string($str)) {
                return '';
            }
    
            // Remover espacios al principio y al final
            $str = trim($str);
    
            // Deshacer entidades HTML para evitar doble codificación
            $str = wp_specialchars_decode($str, ENT_QUOTES);
    
            // Convertir caracteres especiales a sus equivalentes HTML para evitar código malicioso
            $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    
            // Remover caracteres de control
            $str = preg_replace('/[\x00-\x1F\x7F]/', '', $str);
    
            // Retornar la cadena saneada
            return $str;
        }
    }
	if (!function_exists('wp_unslash')) {
    /**
     * Replica básica de la función wp_unslash() de WordPress.
     *
     * Esta función elimina las barras invertidas añadidas por PHP para los valores escapados.
     *
     * @param string|array $value La cadena o el arreglo que necesita eliminar las barras invertidas.
     * @return string|array El valor sin las barras invertidas.
     */
		function wp_unslash($value) {
			if (is_array($value)) {
				return array_map('wp_unslash', $value);
			}
			return stripslashes($value);
		}
	}

    function wpbotwriter_validate_prompt_code($input) {
        // Search for the "autowp-promptcode" structure and get its content
        preg_match('/\[autowp-promptcode\](.*?)\[\/autowp-promptcode\]/s', $input, $matches);
         
        if (empty($matches) || count($matches) < 2) {
            // If the "autowp-promptcode" structure cannot be found or its content is missing, return false
            return false;
        }
        
        $content = $matches[1]; // Get the content of "autowp-promptcode"
        $fields = explode(',', $content); // Split the fields by comma
        
        // Check if the required number of fields is present and each field is filled
        if (count($fields) != 6) {
            return false;
        }
        
        foreach ($fields as $field) {
            $aux=trim($field);
            if (empty($aux)) {
                return false;
            }
        }
        
        return true; // Return true if valid
    }
 
    if (!function_exists('str_contains')) {
        /**
         * Comprueba si una cadena contiene una subcadena específica.
         *
         * @param string $haystack La cadena en la que se busca.
         * @param string $needle La subcadena a buscar.
         * @return bool Verdadero si $needle está en $haystack, falso en caso contrario.
         */
        function str_contains($haystack, $needle) {
            // Asegurarse de que los parámetros sean cadenas
            if (!is_string($haystack) || !is_string($needle)) {
                return false;
            }
    
            // Si la subcadena está vacía, siempre devolver falso
            if ($needle === '') {
                return false;
            }
    
            return strpos($haystack, $needle) !== false;
        }
    }
?>
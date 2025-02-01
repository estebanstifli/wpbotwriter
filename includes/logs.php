<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

function botwriter_logs_page_handler() {

    // Check if the user has the necessary capability
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['botwriter_logs_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['botwriter_logs_nonce'])), 'botwriter_logs_action')
        ) {
            // The nonce is valid, continue execution
        } else {
            wp_die(esc_html__('Security check failed', 'botwriter'));
        }
    }

    // Instantiate the logs table class
    $logs_table = new botwriter_Logs_Table();
    $logs_table->prepare_items();

    // Display the page
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('BotWriter Logs', 'botwriter') . '</h1>';
    echo '<form method="post">';
    wp_nonce_field('botwriter_logs_action', 'botwriter_logs_nonce');
    $logs_table->display();
    echo '</form>';
    echo '</div>';
}



class botwriter_Logs_Table extends WP_List_Table {

    private $logs_data;

    public function __construct() {
        parent::__construct(array(
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false,
        ));
    }

    public function get_columns() {
        return array(
            'created_at'         => __('Created At', 'botwriter'),
            'writer'             => __('Writer', 'botwriter'),
            'task_name'          => __('Task Name', 'botwriter'),
            'task_status'        => __('Task Status', 'botwriter'),
            'aigenerated_title'  => __('AI Generated Title', 'botwriter'),
            'aigenerated_image'  => __('AI Generated Image', 'botwriter'),
            'id_post_published' => __('ID Post Published', 'botwriter'),
            
        );
    }

    function column_writer($item){
        $dir_images_writers = BOTWRITER_URL . '/assets/images/writers/';
        $writer=$item['writer'];
        $writer = strtolower($writer);

        
        $img= '<img src="' . esc_url($dir_images_writers . $writer . '.jpeg') . '" alt="' . esc_attr($writer) . '" class="writer-photo">';
        return $img;
        
    }

    // Create a function for the id_post_published column that shows the link to edit the post, if it is 0 show blank    
    function column_id_post_published($item){
        $id_post_published = $item['id_post_published'];
        if ($id_post_published == 0) {
            return '';
        } else {
            $edit_post_link = get_edit_post_link($id_post_published);
            return '<a href="' . esc_url($edit_post_link) . '" >' . esc_html($id_post_published) . '</a>';
        }
    }
    
    
    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'botwriter_logs';
        $oder='desc';
        $orderby='created_at';

        // Get and sanitize sorting parameters
        $orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'created_at';
        $orderby = esc_sql($orderby);
        $order = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'desc';
        $order = esc_sql($order);
        
        
        $this->logs_data = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY $orderby $order", ARRAY_A);
        

        // Set up pagination
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->logs_data);
        
        $this->logs_data = array_slice($this->logs_data, (($current_page - 1) * $per_page), $per_page);
        
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
        
        // Set up column headers
        $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
        $this->items = $this->logs_data;
    }
    
    public function column_default($item, $column_name) {
        // Default handler for columns
        switch ($column_name) {
            case 'created_at':
            case 'task_status':
            case 'aigenerated_title':
                return esc_html($item[$column_name]); // Escape output for safety
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : ''; // Fallback for other columns
        }
    }

    public function column_task_status($item) {        
        // 4 cases: inqueue, pending, completed, error 
        $task_status = $item['task_status'];        
        $intento_tiempo = array(0=>0,1=>0,2=>5,3=>10,4=>30,5=>60,6=>120,7=>240,8=>480); // minutes
        $intentosfase1 = $item["intentosfase1"];
                    
            // if it is error show in red and show that it will retry in time * attempts
            if ($task_status == 'error') {            
                $id_task_server = $item['id_task_server'];
                $txt= '<span style="color:red;">Error (id server:' . $id_task_server . ')</span>';
                if ($item["error"]!='') {
                    $txt.= '<br>' . esc_html($item["error"]) . "<br>";
                }
                if ($intentosfase1 < 8) {  // these are the ones it has taken                    
                    $tiempo = $intento_tiempo[$intentosfase1+1]; // next attempt
                    $created_at = strtotime($item["created_at"]);
                    $tiempo_siguiente_intento = $created_at + $tiempo*60;            
                    $txt.= '<br>Attempt ' . $intentosfase1 . ' of 8';
                    $txt.= '<br>Will retry at ' . gmdate('Y-m-d H:i:s', $tiempo_siguiente_intento);                
                }                                
            } else {
                $txt=esc_html($task_status);
            }
            
            return $txt;

    }

    public function column_aigenerated_image($item) {
        // check if the post is published
        $id_post_published = $item['id_post_published'];
        if ($id_post_published != 0) {
            // get the post image url
            $post_thumbnail_id = get_post_thumbnail_id($id_post_published);
            $post_thumbnail_url = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
            if ($post_thumbnail_url) {
                return '<img src="' . esc_url($post_thumbnail_url[0]) . '" alt="Post Image" width="50">';
            }
        }

        // Render the image or fallback to text if invalid
        if (filter_var($item['aigenerated_image'], FILTER_VALIDATE_URL)) {
            return '<img src="' . esc_url($item['aigenerated_image']) . '" alt="Generated Image" width="50">';
        } else {
            return esc_html($item['aigenerated_image']);
        }
    }

    public function no_items() {
        esc_html__('No logs found.', 'botwriter');
    }

    public function get_sortable_columns() {
        return array(
            'created_at' => array('created_at', true),
            'task_status' => array('task_status', false),
        );
    }

    
}



function botwriter_get_logs_links($id_task) {
    global $wpdb;
     

    $links = $wpdb->get_results($wpdb->prepare("SELECT link_post_original FROM {$wpdb->prefix}botwriter_logs WHERE id_task = %d ORDER BY id DESC LIMIT 50", $id_task), ARRAY_A);
    $links_array = [];
    if (empty($links)) {
        return false;
    } else {
        foreach ($links as $link) {
            if ($link['link_post_original'] != '') {
                $links_array[] = $link['link_post_original'];    
            }
        }
    }
    return $links_array;
    
}

            
function botwriter_get_logs_titles($id_task) {
    global $wpdb;
    

    $results = $wpdb->get_results($wpdb->prepare("SELECT aigenerated_title FROM {$wpdb->prefix}botwriter_logs WHERE id_task = %d ORDER BY id DESC LIMIT 50", $id_task), ARRAY_A);
    $titles_array = [];
    if (empty($results)) {
        return false;
    } else {
        foreach ($results as $result) {
            if ($result['aigenerated_title'] != '') {
                $titles_array[] = $result['aigenerated_title'];
                //echo "<br>title in the db: " . $result['aigenerated_title'];
            }
        }
    }
    return $titles_array; 
    
}
 
 


// Register a log in the botwriter_logs table (insert or update)
function botwriter_logs_register($data, $id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'botwriter_logs';

    // Validate that $data is an array
    if (!is_array($data)) {
        return false; // Or throw an exception as needed
    }

    // List of allowed keys
    $allowed_keys = array(
        'id_task',
        'id_task_server',
        'post_status',
        'task_name',
        'writer',
        'narration',
        'custom_style',
        'post_language',
        'post_length',
        'link_post_original',
        'id_post_published',
        'task_status',
        'error',
        'website_name',
        'website_type',
        'domain_name',
        'category_id',
        'website_category_id',
        'aigenerated_title',
        'aigenerated_content',
        'aigenerated_tags',
        'aigenerated_image',
        'post_count',
        'post_order',
        'title_prompt',
        'content_prompt',
        'tags_prompt',
        'image_prompt',
        'image_generating_status',
        'author_selection',
        'news_time_published',
        'news_language',
        'news_country',
        'news_keyword',
        'news_source',
        'rss_source',
        'ai_keywords',
        'intentosfase1',
        'last_execution_time'

    );

    // Create the array with only the existing values in $data
    $insert_data = array();
    foreach ($allowed_keys as $key) {
        if (isset($data[$key])) {
            $insert_data[$key] = $data[$key];
        }
    }

    if ($id) {
        // Update the existing record
        $where = array('id' => $id);
        $updated = $wpdb->update($table_name, $insert_data, $where);

        // Return the result of the update
        return $updated !== false ? $id : false;
    } else {
        // Insert a new record
        // Add the creation date
        $current_time = current_time('mysql');
        $insert_data['created_at'] = $current_time;

        $wpdb->insert($table_name, $insert_data);

        // Return the ID of the new record
        return $wpdb->insert_id;
    }
}

// Function that given an id returns an array with the data of a log
function botwriter_logs_get($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'botwriter_logs';

    $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);

    return $log;
}

?>
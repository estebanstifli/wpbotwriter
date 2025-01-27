<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

function wpbotwriter_logs_page_handler() {
    $timezone = get_option('timezone_string');
    if (!$timezone) {
        $timezone = 'UTC';
    }
    date_default_timezone_set($timezone);
    

    $current_time = current_time('mysql'); // Hora en formato MySQL (Y-m-d H:i:s)    
    $current_timestamp = current_time('timestamp'); // Timestamp ajustado a la zona horaria del sitio
    



    // Check if the user has the necessary capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Instantiate the logs table class
    $logs_table = new WPBotWriter_Logs_Table();
    $logs_table->prepare_items();

    // Display the page
    echo '<div class="wrap">';
    echo '<h1>' . __('WPBotWriter Logs', 'wpbotwriter') . '</h1>';
    echo '<form method="post">';
    $logs_table->display();
    echo '</form>';
    echo '</div>';
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPBotWriter_Logs_Table extends WP_List_Table {

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
            'created_at'         => __('Created At', 'wpbotwriter'),
            'writer'             => __('Writer', 'wpbotwriter'),
            'task_name'          => __('Task Name', 'wpbotwriter'),
            'task_status'        => __('Task Status', 'wpbotwriter'),
            'aigenerated_title'  => __('AI Generated Title', 'wpbotwriter'),
            'aigenerated_image'  => __('AI Generated Image', 'wpbotwriter'),
            'id_post_published' => __('ID Post Published', 'wpbotwriter'),
            'intentosfase1' => __('Intentos Fase 1', 'wpbotwriter'),
        );
    }

    function column_writer($item){
        $dir_images_writers = WPBOTWRITTER_URL . '/assets/images/writers/';
        $writer=$item['writer'];
        $writer = strtolower($writer);

        
        $img= '<img src="' . esc_url($dir_images_writers . $writer . '.jpeg') . '" alt="' . esc_attr($writer) . '" class="writer-photo">';
        return $img;
        
    }

    // haz una funcion para la columna id_post_published que muestre el link al post para editarlo si es 0 que muestre blanco    
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
        $table_name = $wpdb->prefix . 'wpbotwriter_logs';
    
        // Obtén los parámetros de ordenación
        $orderby = !empty($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'created_at';
        $order = !empty($_GET['order']) ? esc_sql($_GET['order']) : 'desc';
    
        // Consulta con orden dinámico
        $query = "SELECT * FROM $table_name ORDER BY $orderby $order";
        $this->logs_data = $wpdb->get_results($query, ARRAY_A);
    
        // Configurar paginación
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->logs_data);
    
        $this->logs_data = array_slice($this->logs_data, (($current_page - 1) * $per_page), $per_page);
    
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    
        // Configurar encabezados de columna
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
        // 4 casos: inqueue, pending, completed, error 
        $task_status = $item['task_status'];        
        $intento_tiempo = array(0=>0,1=>0,2=>5,3=>10,4=>30,5=>60,6=>120,7=>240,8=>480); // minutos
        $intentosfase1 = $item["intentosfase1"];
                    
            // si es error mostrar en rojo y mostrar que se reintentará en tiempo * intentos
            if ($task_status == 'error') {            
                $id_task_server = $item['id_task_server'];
                $txt= '<span style="color:red;">Error (id server:' . $id_task_server . ')</span>';
                if ($item["error"]!='') {
                    $txt.= '<br>' . esc_html($item["error"]) . "<br>";
                }
                if ($intentosfase1 < 8) {  // son los que lleva                    
                    $tiempo = $intento_tiempo[$intentosfase1+1]; // next intento
                    $created_at = strtotime($item["created_at"]);
                    $tiempo_siguiente_intento = $created_at + $tiempo*60;            
                    $txt.= '<br>Intento ' . $intentosfase1 . ' de 8';
                    $txt.= '<br>Will retry at ' . date('Y-m-d H:i:s', $tiempo_siguiente_intento);                
                }                                
            } else {
                $txt=esc_html($task_status);
            }
            
            return $txt;

    }

    public function column_aigenerated_image($item) {
        // Render the image or fallback to text if invalid
        if (filter_var($item['aigenerated_image'], FILTER_VALIDATE_URL)) {
            return '<img src="' . esc_url($item['aigenerated_image']) . '" alt="Generated Image" width="50">';
        } else {
            return esc_html($item['aigenerated_image']);
        }
    }

    public function no_items() {
        _e('No logs found.', 'wpbotwriter');
    }

    public function get_sortable_columns() {
        return array(
            'created_at' => array('created_at', true),
            'task_status' => array('task_status', false),
        );
    }

    public function display_rows() {
        foreach ($this->items as $item) {
            echo '<tr>';
            foreach ($this->get_columns() as $column_name => $column_display_name) {
                if (method_exists($this, 'column_' . $column_name)) {
                    echo '<td>' . $this->{'column_' . $column_name}($item) . '</td>';
                } else {
                    echo '<td>' . $this->column_default($item, $column_name) . '</td>';
                }
            }
            echo '</tr>';
        }
    }
}


// crea una funcion que devuelva todos los links de un id_task maximo 50
function wpbotwritter_get_logs_links($id_task) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs'; 

    $query = "SELECT link_post_original FROM $table_name WHERE id_task = $id_task order by id desc limit 50";
    echo "<br>query: " . $query;
    $links = $wpdb->get_results($query, ARRAY_A);    
    $links_array = [];
    if (empty($links)) {
        return false;
    } else {
        foreach ($links as $link) {
            if ($link['link_post_original'] != '') {
                $links_array[] = $link['link_post_original'];
                echo "<br>link_post_original en la bd: " . $link['link_post_original'];                
            }
        }
    }
    return $links_array;
    
}

            
function wpbotwritter_get_logs_titles($id_task) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs';

    $query = "SELECT aigenerated_title FROM $table_name WHERE id_task = $id_task order by id desc limit 50";
    //echo "<br>query: " . $query;
    $results = $wpdb->get_results($query, ARRAY_A);    
    $titles_array = [];
    if (empty($results)) {
        return false;
    } else {
        foreach ($results as $result) {
            if ($result['aigenerated_title'] != '') {
                $titles_array[] = $result['aigenerated_title'];
                //echo "<br>titulo en la bd: " . $result['aigenerated_title'];
            }
        }
    }
    return $titles_array; 
    
}




// Registra un log en la tabla wpbotwriter_logs (inserta o actualiza)
function wpbotwriter_logs_register($data, $id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs';

    // Validar que $data es un array
    if (!is_array($data)) {
        return false; // O lanzar una excepción según sea necesario
    }

    // Lista de claves permitidas
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
        'intentosfase1'
    );

    // Crear el array con solo los valores existentes en $data
    $insert_data = array();
    foreach ($allowed_keys as $key) {
        if (isset($data[$key])) {
            $insert_data[$key] = $data[$key];
        }
    }

    if ($id) {
        // Actualizar el registro existente
        $where = array('id' => $id);
        $updated = $wpdb->update($table_name, $insert_data, $where);

        // Retornar el resultado de la actualización
        return $updated !== false ? $id : false;
    } else {
        // Insertar un nuevo registro
        // Añadir la fecha de creación
        $current_time = current_time('mysql');
        $insert_data['created_at'] = $current_time;

        $wpdb->insert($table_name, $insert_data);

        // Retornar el ID del nuevo registro
        return $wpdb->insert_id;
    }
}

// funcion que dado un id devuleva un array con los datos de un log
function wpbotwriter_logs_get($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_logs';

    $query = "SELECT * FROM $table_name WHERE id = $id";
    $log = $wpdb->get_row($query, ARRAY_A);

    return $log;
}


?>

<?php


// muestra todos los posts automaticos
function wpbotwriter_automatic_posts_page()
{
    global $wpdb;

    $table = new wpbotwriter_tasks_Table();
    
    $table->prepare_items();

    $message = '';
    if ('delete_all' === $table->current_action()) {

      // Good idea to make sure things are set before using them
      $deleted_items_ids = isset($_POST['id']) ? array_map('absint', (array)$_POST['id']) : array();
  
      // Sanitize the array using array_map and absint
      $deleted_items_ids = array_map('absint', $deleted_items_ids);
  
      // Validate the IDs, make sure they are positive integers
      $deleted_items_ids = array_filter($deleted_items_ids, create_function('$id', 'return $id > 0;'));
  
      $message = 'Items deleted: ' . count($deleted_items_ids);
  
      // Escape the message before outputting it
      $message = esc_html($message);
   }
  

     if ('delete' === $table->current_action()) {
        // Sanitize and escape the 'id' parameter
        $deleted_item_id = isset($_REQUEST['id']) ? absint($_REQUEST['id']) : 0;
        
        // Escape the output and use the sprintf function for better readability
        $message = 'Item deleted : '  .  $deleted_item_id;
      }
  
    
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php esc_html_e('Tasks', 'wpbotwriter'); ?> <a class="add-new-h2"
        href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=wpbotwriter_automatic_post_new')); ?>"><?php esc_html_e('Add new', 'wpbotwriter'); ?></a>
    </h2>
    
    <?php 
    if($message && !empty($message)){
      echo '<div class="updated below-h2" id="message"><p>' . esc_html($message) . '</p></div>'; 

    }
    ?>

    <form id="contacts-table" method="POST">
    <?php
    // Sanitize and escape the 'page' parameter
    $page_value = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
    ?>
    <input type="hidden" name="page" value="<?php echo esc_html($page_value); ?>"/>
    <?php $table->display() ?>
</form>


</div>
<?php
}

// maneja la pagina de agregar nuevo post automatico
function wpbotwriter_form_page_handler(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpbotwriter_tasks'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,

        'task_name'             => '',
        'writer'                => 'orion',  
        'narration'             => 'Descriptive',
        'custom_style'          => '',
        'post_language' => '',
        'post_length' => '800',
        'post_status'      => 'publish',
        'days'             => '',
        'times_per_day'    => 1,
        'execution_count' => 0, // Initialize execution count to zero
        'last_execution_date' => null, // Set last execution date to null initially
        'website_type'      => 'wordpress',

        'website_name'              => '',                
        'domain_name'              => '',
        'category_id'              => '',
        'website_category_id'      => '',
        'aigenerated_title'        => '',
        'aigenerated_content'      => '',
        'aigenerated_tags'         => '',
        'aigenerated_image'        => '',
        'post_count'               => '',
        'post_order'               => '',
        'title_prompt'             => '',
        'content_prompt'           => '',
        'tags_prompt'              => '',
        'image_prompt'             => '',
        'image_generating_status'  => '',
        'author_selection'         => '',

        'news_keyword'             => '',
        'news_country'             => '',
        'news_language'            => '', 
        'news_time_published'      => '',
        'news_source'              => '',
        'rss_source'               => '',
        'ai_keywords'              => '',


      
        
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), basename(__FILE__))) {
        $days = isset($_POST['days']) ? implode(",", array_map('sanitize_text_field', $_POST['days'])) : "";
        $times_per_day = intval($_POST['times_per_day']);
        
        
        // Process only the specific values needed
        $item = array(
          'id'                => isset($_POST['id']) ? intval($_POST['id']) : 0,

          'task_name'         => sanitize_text_field($_POST['task_name']),
          'post_status'       => sanitize_text_field($_POST['post_status']),
          'days'              => $days,
          'times_per_day'     => $times_per_day,                    
          'writer'            => sanitize_text_field($_POST['writer']),
          'narration'         => sanitize_text_field($_POST['narration']),
          'post_length'         => sanitize_text_field($_POST['post_length']),
          'custom_style'      => sanitize_text_field($_POST['custom_style']),
          'post_language'     => sanitize_text_field($_POST['post_language']),

          'website_type'      => sanitize_text_field($_POST['website_type']),
          'website_name'      => '',
          'domain_name'       => sanitize_url($_POST['domain_name']),
          'category_id'       => isset($_POST['category_id']) ? array_map('intval', $_POST['category_id']) : array(),
          'website_category_id'=> isset($_POST['website_category_id']) ? array_map('intval', $_POST['website_category_id']) : array(),
          'aigenerated_title'  => '',
          'aigenerated_content'=> '',
          'aigenerated_tags'   => '',
          'aigenerated_image'  => '',
          'post_count'         => 5,
          'post_order'         => '',
          'title_prompt'      => '',

          'content_prompt' => '',
          
          'tags_prompt'       => '',
          'image_prompt'      => '',
          'image_generating_status' => '',
          
          'author_selection'  => sanitize_text_field($_POST['author_selection']),

          'news_keyword'      => sanitize_text_field($_POST['news_keyword']),
          'news_country'      => sanitize_text_field($_POST['news_country']),
          'news_language'     => sanitize_text_field($_POST['news_language']),
          'news_time_published' => sanitize_text_field($_POST['news_time_published']),
          'news_source'       => sanitize_text_field($_POST['news_source']),

          'rss_source'        => sanitize_text_field($_POST['rss_source']),
          'ai_keywords'       => sanitize_text_field($_POST['ai_keywords'])
          



        );
        //Convert category_id array to text
        $category_ids = implode(",", $item['category_id']);
        $item['category_id'] = $category_ids;

        //Convert website_category_id to text    
        $website_category_ids = implode(",", $item['website_category_id']);
         $item['website_category_id'] = $website_category_ids;
        
        

        
        

        // Set WP-CRON 

        $settings = unserialize(get_option('wpbotwriter_settings'));

        $wpcron_status = $settings['wpcron_status'];

        $wpcron_status ='1'; // mio
        /*
        if(!isset($wpcron_status)){
          wpbotwriter_update_wp_cron_status('1');
        }

        $time_value_type = $settings['selected_time_type'];

        $user_wpcron_time = wpbotwriter_get_wpcron_time($time_value_type);
        $next_two_minutes = time() + 2 * 60;

        // Schedule WP-Cron
            if (!wp_next_scheduled('wpbotwriter_cron')) {
            wp_schedule_event($next_two_minutes, $user_wpcron_time, 'wpbotwriter_cron');
            
          } else {
            wp_clear_scheduled_hook('wpbotwriter_cron');
            wp_schedule_event($next_two_minutes, $user_wpcron_time, 'wpbotwriter_cron');
          }
          */



          $item_valid = wpbotwriter_validate_website($item);
          if ($item_valid === true) {

            /*
            echo "<pre>";            
            foreach ($item as $key => $value) {
              echo $key . " => " . $value . " => " . gettype($value) . "<br>";
            }

            echo "</pre>";
            */

            //$item = array_map('sanitize_text_field', $item); // Sanitize all inputs
              if ($item['id'] == 0) {
                  $result = $wpdb->insert($table_name, $item);
                  // mostrar el error de la base de datos
                  //echo "console.log('" . $wpdb->last_error . "')";


                  $item['id'] = $wpdb->insert_id;
                  if ($result) {
                      $message = __('New task was successfully saved!', 'wpbotwriter');
                  } else {
                      $notice = __('There was an error while saving item', 'wpbotwriter');
                  }
              } else {
                  $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                   
                  //echo $wpdb->last_error;
                  //echo "consulta: " . $wpdb->last_query . "<br><br>";
                
               // error_log('Error en la consulta: ' . $wpdb->last_error);
               // error_log('Consulta ejecutada: ' . $wpdb->last_query);

               if ($result !== false) {
                    if ($result === 0) {
                      $message = __('No changes were made, but the update was successful.', 'wpbotwriter');
                    } else {
                      $message = __('New task was successfully updated!', 'wpbotwriter');
                    }
                } else {
                      $notice = __('There was an error while updating item: ' . $wpdb->last_error, 'wpbotwriter');
                }
              }
          } else {
              
              $notice = $item_valid;
          }
    }
    else {
        
      $item = $default;
      if (isset($_REQUEST['id'])) {
          $sanitized_id = absint($_REQUEST['id']); // Sanitize as an integer
          $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $sanitized_id), ARRAY_A);
          if (!$item) {
              $item = $default;
              $notice = __('Item not found', 'wpbotwriter');
          }
      }
      
    }

    
    add_meta_box('wpbotwriter_post_form_meta_box', __('Task Form', 'wpbotwriter'), 'wpbotwriter_post_form_meta_box_handler', 'wpbotwriter_automatic_post_new', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php esc_html_e('Add New', 'wpbotwriter'); ?> <a class="add-new-h2"
        href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=wpbotwriter_automatic_posts')); ?>"><?php esc_html_e('Back to List', 'wpbotwriter'); ?></a>
    </h2>


    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo esc_attr($notice) ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo esc_attr($message) ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(basename(__FILE__))); ?>"/>
        
        <input type="hidden" name="id" value="<?php echo esc_attr($item['id']) ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('wpbotwriter_automatic_post_new', 'normal', $item); ?>
                    <input type="submit" value="<?php esc_attr_e('Save', 'wpbotwriter')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}
 

function wpbotwriter_get_categories_from_wordpress_website($user_domainname, $user_email, $website_domainname) {

  $url = "https://wpbotwriter.com/public/getWebsiteCategories.php";
    
  // Datos que serán enviados en la solicitud POST
  $postData = [
      'user_domainname' => $user_domainname,
      'user_email' => $user_email,
      'website_domainname' => $website_domainname
  ];

  // Realizar la solicitud POST usando wp_remote_post
  $response = wp_remote_post($url, [
      'body' => $postData
  ]);

  // Verificar si hubo un error en la solicitud
  if (is_wp_error($response)) {
      $error_msg = $response->get_error_message();
      die("Error: " . $error_msg);
  }

  // Obtener el cuerpo de la respuesta
  $response_body = wp_remote_retrieve_body($response);

  // Decodificar respuesta JSON
  $categories = json_decode($response_body, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
      die("Error decoding JSON response");
  }

  // Retornar categorías
  return $categories;
    
    
    
}


function wpbotwriter_get_admin_email(){
    $admin_email = get_option('admin_email', false);
    if ($admin_email !== false) {


    } else {
      $admin_email = 'email@example.com';
    }
    return $admin_email;
}
  

  

function wpbotwriter_post_form_meta_box_handler($item)
{
  Global $wpbotwriter_languages,$wpbotwriter_countries;
  // Get the selected days
  $selected_days = isset($item["days"]) ? explode(",", $item["days"]) : array();
  $times_per_day = isset($item["times_per_day"]) ? $item["times_per_day"] : 1;

  
  
  
  $dir_images_writers = plugin_dir_url(dirname(__FILE__)) . '/assets/images/writers/';
  $dir_images_icons = plugin_dir_url(dirname(__FILE__)) . '/assets/images/icons/';


    ?>
<div id="loading">
<div class="loader"> 
  <div class="inner one"></div>
  <div class="inner two"></div>
  <div class="inner three"></div>
</div>
</div>
<script>
  jQuery(window).on("load", function() {
    console.log("Start to process");
    jQuery("#loading").hide();
  });
</script>




<tbody >
<form>
  <div class="form2bc">
  <div class="container">
  <form class="row g-3">
      <?php
      //Get admin domain name
      $wpbotwriter_admin_email = wpbotwriter_get_admin_email();
      $wpbotwriter_domain_name = esc_url(get_site_url());
      $is_empty = empty($item['domain_name']);
      ?>

      <input type="hidden" id="wpbotwriter_admin_email" value="<?php echo esc_attr($wpbotwriter_admin_email); ?>">
      <input type="hidden" id="wpbotwriter_domain_name" value="<?php echo esc_attr($wpbotwriter_domain_name); ?>">
      
    <div class="col-md-6">
      <label class="form-label">Task Name:</label>
      <input id="task_name" name="task_name" type="text" class="form-control" value="<?php echo esc_attr($item['task_name']); ?>" required>
    </div>
    <br>

    <?php
      $pro_writers = ['max', 'cloe', 'gael']; // Writers available only for Pro users in future
      $is_pro_user = true; // Set to true to make all writers available if the user is Pro
    ?>

  <!-- Writers -->
<div class="col-md-6">
  <label class="form-label"><?php echo esc_html__('Writer:', 'wpbotwriter'); ?></label>
  <div class="writer-options">

    <!-- Orion, the Versatile Assistant (Free) -->
    <label class="writer-option">
      <input type="radio" name="writer" value="orion" required <?php if ($item['writer'] === 'orion') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'orion.jpeg'); ?>" alt="<?php echo esc_attr__('Orion', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Orion, the Versatile Assistant', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Adaptable and insightful, perfect for a wide range of topics and styles.', 'wpbotwriter'); ?></p>
      </div>
    </label>

    <!-- Lucida, the Analytical Critic (Free) -->
    <label class="writer-option">
      <input type="radio" name="writer" value="lucida" required <?php if ($item['writer'] === 'lucida') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'lucida.jpeg'); ?>" alt="<?php echo esc_attr__('Lucida', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Lucida, the Analytical Critic', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Precise and direct, perfect for deep analysis in complex topics.', 'wpbotwriter'); ?></p>
      </div>
    </label>

    <!-- Max, the Adventurous Narrator (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="max" required <?php echo $item['writer'] === 'max' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'max.jpeg'); ?>" alt="<?php echo esc_attr__('Max', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Max, the Adventurous Narrator', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Passionate and descriptive, ideal for stories of travel and culture.', 'wpbotwriter'); ?></p>
      </div>
    </label>

    <!-- Cloe, the Ironic Cultural Critic (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="cloe" required <?php echo $item['writer'] === 'cloe' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'cloe.jpeg'); ?>" alt="<?php echo esc_attr__('Cloe', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Cloe, the Ironic Cultural Critic', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Sarcastic and witty, perfect for cultural and social reviews.', 'wpbotwriter'); ?></p>
      </div>
    </label>

    <!-- Gael, the Reflective Poet (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="gael" required <?php echo $item['writer'] === 'gael' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'gael.jpeg'); ?>" alt="<?php echo esc_attr__('Gael', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Gael, the Reflective Poet', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Introspective and poetic, ideal for philosophical and emotional themes.', 'wpbotwriter'); ?></p>
      </div>
    </label>

    <!-- Custom (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="custom" required <?php echo $item['writer'] === 'custom' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'custom.jpeg'); ?>" alt="<?php echo esc_attr__('Custom', 'wpbotwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Custom, the User-Selected Style Bot', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Allows the user to choose a specific narrative style..', 'wpbotwriter'); ?></p>
        <select class="form-select" id="narration" name="narration" onchange="toggleCustomStyleInput()">
        <?php
        $styles = [
            "Descriptive" => "Descriptive",
            "Narrative" => "Narrative",
            "Explanatory" => "Explanatory",
            "Argumentative" => "Argumentative",
            "Comparative" => "Comparative",
            "Process Analysis" => "Process Analysis",
            "Allegorical" => "Allegorical",
            "Chronological" => "Chronological",
            "Ironic" => "Ironic",
            "ConsistencyAndRepetition" => "Consistency and Repetition",
            "LanguagePlayAndPoeticExpression" => "Language Play and Poetic Expression",
            "InternalMonologue" => "Internal Monologue",
            "Dialogical" => "Dialogical",
            "Custom" => "Custom" // Agregar opción personalizada
        ];
        foreach ($styles as $value => $name) {
            $selected = ($item["narration"] == $value) ? 'selected' : '';
            echo "<option value='$value' $selected>$name</option>";
        }
        ?>
        </select>
        <!-- Campo adicional para que el usuario escriba su estilo personalizado -->
        <div id="customStyleInput" style="display: none; margin-top: 10px;">
            <label for="customStyle" class="form-label">Specify Custom Style:</label>
            <input type="text" class="form-control" id="custom_style" name="custom_style" placeholder="Enter your custom writing style" value="<?php echo esc_attr($item['custom_style']); ?>">
        </div>

      </div>

    </label>

  </div>

  <?php if (!$is_pro_user): ?>
    <p class="upgrade-message">
      Want to unlock more writers? <a href="link-to-upgrade">Upgrade to the Pro version here.</a>
    </p>
  <?php endif; ?>
</div>
<br>

    <div class="col-md-6">
      <label class="form-label">Author Selection</label>
      <select name="author_selection" class="form-select">
        <?php
        $authors = get_users();

        foreach ($authors as $author) {
          $author_id = $author->ID;
          $author_name = $author->display_name;
          $author_description = get_the_author_meta('description', $author_id); // Get the author's description

          if ($item['author_selection'] === strval($author_id)) {
            echo '<option value="' . esc_attr($author_id) . '" selected>' . esc_html($author_name) . '</option>';
            continue;
          }

          echo '<option value="' . esc_attr($author_id) . '">' . esc_html($author_name) . '</option>';
        }
        ?>
      </select>
      <p class="form-text">Select an author from the list.</p>
    </div>

    <div class="col-md-6">
      <label for="post_status" class="form-label">Post Status:</label>
      <select id="post_status" name="post_status" class="form-select">        
        <option value="publish" <?php if ($item['post_status'] === 'publish') {
                                echo 'selected';
                              } ?>>Publish</option>
        <option value="draft" <?php if ($item['post_status'] === 'draft') {
                              echo 'selected';
                            } ?>>Draft</option>
      </select>      
      <p class="form-text">Select the status of the post. Choose 'Draft' if you want to revise it before publishing.</p>      
    </div>

    <div class="col-md-6">
            <label class="form-label"><?php _e('Days of the Week:', 'wpbotwriter'); ?></label><br>
            <?php 
            $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days_of_week as $day) {
                $is_checked = in_array($day, $selected_days) ? 'checked' : '';
                echo "<input type='checkbox' name='days[]' value='$day' $is_checked> " . __($day, 'wpbotwriter') . "<br>";
            }
            ?>
            <p class="form-text">Select the days on which you want to write and publish.</p>            
    </div>

    <div class="col-md-6">
            <label  class="form-label"><?php _e('Post per Day:', 'wpbotwriter'); ?></label>
            <input type="number" name="times_per_day" min="1" value="<?php echo $times_per_day ?>" required>
    </div>
    <br>
    <div class="col-md-6">
      <label for="category_id" class="form-label">Categories:</label>
      <select id="category_id" name="category_id[]" required multiple class="form-select">
        <?php
        //Get selected categories
        $selected_categories = $item['category_id'];
        //Turn categories to array list
        $selected_categories = explode(',', $selected_categories);

        $categories = get_categories(array(
          'orderby' => 'name',
          'order' => 'ASC',
          'hide_empty' => false
        ));

        foreach ($categories as $category) {

          if (isset($selected_categories) && in_array($category->term_id, $selected_categories)) {
            echo '<option value="' . esc_attr($category->term_id) . '" selected>' . esc_html($category->name) . '</option>';
            continue;
          }
          echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
        }
        ?>
      </select>
      <p class="form-text">Select one or more categories where the posts will be published.</p>  
    </div>

    <?php
    $default_language_code = substr(get_locale(), 0, 2); // Obtiene el idioma predeterminado desde la configuración
    
    // Asignar el idioma predeterminado si no hay idioma guardado
    if (empty($item['post_language']) || $item['post_language'] == '') {
        $item['post_language'] = $default_language_code;
    }
?>

        <div class="col-md-6">
            <label for="post_language" class="form-label">Post Language:</label>
            <select class="form-select" id="post_language" name="post_language">
                <?php
                    foreach ($wpbotwriter_languages as $code => $name) {                        
                        $selected = ($item['post_language'] == $code) ? 'selected' : '';    
                        echo "<option value='" . esc_attr($code) . "' $selected>" . esc_html($name) . "</option>";
                    }
                ?>
            </select>
        </div>


    
      

<br>

<div class="col-md-6">
  <label class="form-label"><?php echo esc_html__('Source of Ideas:', 'wpbotwriter'); ?></label>
  
  <!-- Radio Button Options with Icons -->
  <div class="source-options">
  
    <!-- WordPress External -->
    <label class="source-option">
      <input type="radio" name="website_type" value="wordpress" required <?php if ($item['website_type'] === 'wordpress') {
                                                                      echo 'checked';
                                                                    } ?>>	  
      <img src="<?php echo esc_url($dir_images_icons . 'externalwp100.png'); ?>" alt="WordPress External" class="source-icon">
        <div class="writer-info">
        <strong><?php echo esc_html__('WordPress External', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Inspired by articles from an external WordPress, potentially in other languages. It rewrites the content and designs a completely new image.', 'wpbotwriter'); ?></p>
      </div>
    </label>
    
    <!-- Google News -->
    <label class="source-option">
      <input type="radio" name="website_type" value="news" <?php if ($item['website_type'] === 'news') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_icons . 'news100.png'); ?>" alt="Google News" class="source-icon">
        <div class="writer-info">
        <strong><?php echo esc_html__('Google News', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts trending news from Google News to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'wpbotwriter'); ?></p>
        </div>
    </label>
    
    <!-- RSS (Web with RSS) -->
    <label class="source-option">
      <input type="radio" name="website_type" value="rss" <?php if ($item['website_type'] === 'rss') {
                                                                      echo 'checked';
                                                                    } ?>>
      
      <img src="<?php echo esc_url($dir_images_icons . 'rss100.png'); ?>" alt="RSS" class="source-icon">
      <div class="writer-info">
        <strong><?php echo esc_html__('RSS (Web with RSS)', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts articles from a website with an RSS feed to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'wpbotwriter'); ?></p>      
      </div>
    </label>
    
    <!-- Articles from the Own Blog --> 
    <label class="source-option">
      <input type="radio" name="website_type" value="ai" <?php if ($item['website_type'] === 'ai') {
                                                                      echo 'checked';
                                                                    } ?>>

      <img src="<?php echo esc_url($dir_images_icons . 'sameblog100.png'); ?>" alt="Own Blog Articles" class="source-icon">
      <div class="writer-info">
        <strong><?php echo esc_html__('IA Articles from topics or keywords', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Create News Articles from topics or keywords.', 'wpbotwriter'); ?></p>      
      </div>
    </label>
    
    <!-- External Research -->
     <!-- hidden 
    <label class="source-option">
      <input type="radio" name="website_type" value="external_research" <?php if ($item['website_type'] === 'external_research') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_icons . 'external100.png'); ?>" alt="External Research" class="source-icon">
      <div class="writer-info">
        <strong><?php echo esc_html__('External Research', 'wpbotwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts information from external sources to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'wpbotwriter'); ?></p>
      </div>
    </label>    
    !-->

  </div>
</div>
<br>
<!-- Url of Site of Extern Wordpress -->
<div class="col-md-6" id="div_website_domainname">
  <label class="form-label">Url of Site of Extern Wordpress:</label>
  <input id="domain_name" name="domain_name" type="text" class="form-control" value="<?php echo esc_attr($item['domain_name']); ?>" >
  <p class="form-text">Enter the URL of the external WordPress site from which you want to get ideas. Later get the categories.</p>
</div>


<!-- website_category_id -->
<div class="col-md-6" id="div_website_category_id">
      <label for="website_category_id" class="form-label">External Website Categories:</label><br>
      <select id="website_category_id" name="website_category_id[]" multiple class="form-select" <?php
                                                                                            if ($is_empty) {
                                                                                              echo 'style="display: none;"';
                                                                                            }
                                                                                            ?>>
        <?php

        //Get selected categories
        $selected_website_categories = $item['website_category_id'];
        //Turn categories to array list
        $selected_website_categories = explode(',', $selected_website_categories);

        //Get website categories from domain
        if (!$is_empty) {          
          $wpbotwriter_wp_website_domain_name = esc_url($item['domain_name']);
          $website_categories = wpbotwriter_get_categories_from_wordpress_website($wpbotwriter_domain_name, $wpbotwriter_admin_email, $wpbotwriter_wp_website_domain_name);

          if (!$website_categories['error']) {

            foreach ($website_categories as $website_category) {
              if (in_array($website_category['id'], $selected_website_categories)) {
                echo '<option value="' . esc_attr($website_category['id']) . '" selected>' . esc_html($website_category['name']) . '</option>';
                continue;
              }
              echo '<option value="' . esc_attr($website_category['id']) . '">' . esc_html($website_category['name']) . '</option>';
            }
          }
        }
        ?>
      </select>
      <button type="button" class="btn btn-primary" onclick="refreshWebsiteCategories()">
        <i class="bi bi-arrow-clockwise"></i>
        <?php
        $category_button_name = 'Get Categories';

        if (!$is_empty) {
          $category_button_name = 'Refresh';
        }

        echo esc_html($category_button_name);
        ?>
      </button>
    </div>
    <br>


<!-- News -->
<div id="div_news">    
<div class="col-md-6" >
  <label class="form-label">Google News Keywords:</label>
  <input id="news_keyword" name="news_keyword" type="text" class="form-control" value="<?php echo esc_attr($item['news_keyword']); ?>" >  
</div>
<br>

<?php
$locale = get_locale(); 
$default_country_code = substr($locale, -2); // Obtiene el código del país ('ES')
$default_language_code = substr($locale, 0, 2); // Obtiene el código del idioma ('es')
?>

<div class="col-md-6">
    <label class="form-label" for="news_country">Google News Country:</label>
    <select name="news_country" class="form-select">
        <!-- ISO 3166-1 alpha-2 country codes -->
        <!-- Replace with actual country codes and names -->
        <?php
        foreach ($wpbotwriter_countries as $code => $name) {
            $selected = ($item['news_country'] == $code) ? 'selected' : (($code == strtolower($default_country_code) && empty($item['news_country'])) ? 'selected' : '');            
            echo "<option value='$code' $selected>$name</option>";
        }
        ?>
    </select>
</div>
<br>

<div class="col-md-6">
    <label class="form-label" for="news_language">Google News Language:</label>
    <select name="news_language" class="form-select">
        <!-- ISO 639-1 alpha-2 language codes -->
        <!-- Replace with actual language codes and names -->
        <?php
        
        foreach ($wpbotwriter_languages as $code => $name) {
          $selected = ($item['news_language'] == $code) ? 'selected' : (($code == strtolower($default_language_code) && empty($item['news_language'])) ? 'selected' : '');
          echo "<option value='" . esc_attr($code) . "' $selected>" . esc_html($name) . "</option>";
        }

        ?>
    </select>
</div>
<br>

<div class="col-md-6">
    <label class="form-label" for="news_time_published">Google News Time Published:</label>
    <select name="news_time_published" class="form-select">
        <?php
        $time_options = [            
            'h' => 'Last Hour',
            'd' => 'Last Day',
            'w' => 'Last Week',
            'y' => 'Last Year',
            '' => 'Anytime',
        ];

        foreach ($time_options as $value => $label) {
            $selected = ($item['news_time_published'] == $value) ? 'selected' : '';
            echo "<option value='$value' $selected>$label</option>";
        }
        ?> 
    </select>
</div>
<br>
<div class="col-md-6" >
  <label class="form-label">Google News Source:</label>
  <input id="news_source" name="news_source" type="text" class="form-control" value="<?php echo esc_attr($item['news_source']); ?>" >  
  <p class="form-text">Optional. Enter the source (website) of the news you want to get ideas from. For e.g. https://bbc.com or https://cnn.com</p>
</div>

</div>  
<!-- End News -->

<!-- RSS -->
<div class="col-md-6" id="div_rss">
  <label class="form-label">RSS Feed URL:</label>
  <input id="rss_source" name="rss_source" type="text" class="form-control" value="<?php echo esc_attr($item['rss_source']); ?>" >
  <p class="form-text">Enter the URL of the RSS feed from which you want to get ideas.</p>
  <button type="button" class="btn btn-primary" onclick="fetchRSSFeed()">Check RSS Feed</button>
  <div id="rss_response"></div>
</div>
<!-- End RSS -->

<!-- AI -->
<div class="col-md-6" id="div_ai">
  <label class="form-label">AI Keywords:</label>
  <textarea id="ai_keywords" name="ai_keywords" class="form-control" rows="6"><?php echo esc_textarea($item['ai_keywords']); ?></textarea>
  <p class="form-text">Enter the keywords or topics you want to get ideas from, separated by commas or spaces.</p>      
</div> 
<br>


<!-- End AI -->

<!-- Post Length -->
<div class="col-md-6">
  <label class="form-label">Post Length:</label>
  <select id="post_length" name="post_length" class="form-select" onchange="toggleCustomLengthInput()">
    <option value="400" <?php echo ($item['post_length'] == 400) ? 'selected' : ''; ?>>Short (400 words)</option>
    <option value="800" <?php echo ($item['post_length'] == 800) ? 'selected' : ''; ?>>Medium (800 words)</option>
    <option value="1600" <?php echo ($item['post_length'] == 1600) ? 'selected' : ''; ?>>Long (1600 words)</option>
    <option value="custom" <?php echo (!in_array($item['post_length'], [400, 800, 1600])) ? 'selected' : ''; ?>>Custom</option>
  </select>
  <p class="form-text">Select the desired post length or choose custom to enter a specific number of words.</p>
</div>

<!-- Custom Length Input -->
<div class="col-md-6" id="customLengthInput" style="display: <?php echo (!in_array($item['post_length'], [400, 800, 1600])) ? 'block' : 'none'; ?>;">
  <label class="form-label">Custom Length (max 3000):</label>
  <input id="custom_post_long" type="number" class="form-control" value="<?php echo (!in_array($item['post_length'], [400, 800, 1600])) ? esc_attr($item['post_length']) : ''; ?>" onchange="updatePostLength()">
  <p class="form-text">Enter the number of words you want the post to have.</p>
</div>
<br>

<script>
  function toggleCustomLengthInput() {
    var selectElement = document.getElementById("post_length");
    var customInput = document.getElementById("customLengthInput");
    var customPostLong = document.getElementById("custom_post_long");

    if (selectElement.value === "custom") {
      customInput.style.display = "block";
      customPostLong.setAttribute("name", "post_long");
    } else {
      customInput.style.display = "none";
      customPostLong.removeAttribute("name");
    }
  }

  function updatePostLength() {
    var selectElement = document.getElementById("post_length");
    var customPostLong = document.getElementById("custom_post_long");

    if (customPostLong.value && selectElement.value === "custom") {
      selectElement.value = customPostLong.value;
    }
  }
</script>
<!-- End Post Length -->


<script>
  // wordpress, news
  // muestra u oculta el campo de la url del sitio externo de wordpress si selecciona la opcion de wordpress externo
  document.addEventListener('DOMContentLoaded', () => {
  
    toggleCustomStyleInput();

  
  const website_type = document.querySelectorAll('input[name="website_type"]');
  const div_website_domainname = document.getElementById('div_website_domainname');
  const div_website_category_id = document.getElementById('div_website_category_id');
  const elemento_domain_name = document.getElementById('domain_name');
  const div_news = document.getElementById('div_news');
  const elemento_news_keyword = document.getElementById('news_keyword');
  
  const div_rss = document.getElementById('div_rss');
  const elemento_rss_source = document.getElementById('rss_source');

  const div_ai = document.getElementById('div_ai');



  // Función para actualizar la interfaz en base al valor seleccionado
    const updateUI = (value) => {
      if (value === 'wordpress') {
        div_website_domainname.style.display = 'block';
        div_website_category_id.style.display = 'block';
        website_category_id.required = true;
        elemento_domain_name.required = true;
      } else {
        div_website_domainname.style.display = 'none';
        div_website_category_id.style.display = 'none';
        elemento_domain_name.required = false;
        website_category_id.required = false;
      }
      if (value === 'news') {
        div_news.style.display = 'block';
        elemento_news_keyword.required = true;
      } else {
        div_news.style.display = 'none';
        elemento_news_keyword.required = false;
      }
      if (value === 'rss') {
        div_rss.style.display = 'block';
        elemento_rss_source.required = true;
      } else {
        div_rss.style.display = 'none';
        elemento_rss_source.required = false;
      }
      if (value === 'ai') {
        div_ai.style.display = 'block';
      } else {
        div_ai.style.display = 'none';
      }

    };

  // Verifica el valor inicial del radio seleccionado al cargar
    website_type.forEach((radio) => {
      if (radio.checked) {
        updateUI(radio.value); // Ejecuta la función con el valor inicial
      }

      // Escucha cambios posteriores
        radio.addEventListener('change', () => {
          updateUI(radio.value); // Actualiza cuando cambie
        });
    });
  }); 

</script>


    
    

<script>
// JavaScript para mostrar/ocultar el campo personalizado según la selección
function toggleCustomStyleInput() {
    var selectElement = document.getElementById("narration");
    var customInput = document.getElementById("customStyleInput");

    if (selectElement.value === "Custom") {
        customInput.style.display = "block";
    } else {
        customInput.style.display = "none";
    }
}
</script>


  </form>
</div>




  



</form>

	
</tbody>
<?php
}

function wpbotwriter_website_selection_page_handler(){
  ?>
  <!-- Add this in the <head> section of your HTML -->

  <div class="wrap">
    <h2><?php esc_attr_e('Add New Website', 'wpbotwriter')?></h2>
    <p><?php esc_attr_e('Please select the type of website you want to add:', 'wpbotwriter')?></p>
    <ul class="list-group">
      <li class="list-group-item">
      <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=wpbotwriter_automatic_post_new')); ?>" class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
          <img src="<?php echo esc_url( plugins_url( '../assets/images/wordpress-icon.png', __FILE__ ) ); ?>" alt="<?php echo esc_attr( 'WordPress Website' ); ?>" class="me-3" style="width: 80px; height: 80px;">
            <div>
              <h5><?php esc_attr_e('AI-Rewrite From Wordpress Website', 'wpbotwriter')?></h5>
              <p><?php esc_attr_e('Fetch posts from WordPress site and rewrite with artificial intelligence.', 'wpbotwriter')?></p>
            </div>
          </div>
        </a>
      </li>
      <li class="list-group-item">
      <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=add_new_rss_website_form')); ?>" class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
          <img src="<?php echo esc_url(plugins_url('../assets/images/rss-icon.png', __FILE__)); ?>" alt="<?php esc_attr_e('RSS Website'); ?>" class="me-3" style="width: 80px; height: 80px;">
            <div>
              <h5><?php esc_attr_e('AI-Rewrite From RSS Website', 'wpbotwriter')?></h5>
              <p><?php esc_attr_e('Fetch content with RSS and rewrite with artificial intelligence.', 'wpbotwriter')?></p>
            </div>
          </div>
        </a>
      </li>


      <li class="list-group-item">
      <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=add_new_ai_website_form')); ?>" class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
          <img src="<?php echo esc_url(plugins_url('../assets/images/robot-icon.png', __FILE__)); ?>" alt="<?php esc_attr_e('Artificial Intelligence'); ?>" class="me-3" style="width: 80px; height: 80px;">
            <div>
              <h5><?php esc_attr_e('Write AI-Generated from Scratch', 'wpbotwriter')?></h5>
              <p><?php esc_attr_e('Create original content from scratch with artificial intelligence!', 'wpbotwriter')?></p>
            </div>
          </div>
        </a>
      </li>

      <li class="list-group-item">
      <a href="<?php echo esc_url(get_admin_url(null, 'admin.php?page=add_new_news_website_form')); ?>" class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
          <img src="<?php echo esc_url(plugins_url('../assets/images/news.png', __FILE__)); ?>" alt="<?php esc_attr_e('Rewrite With AI From News'); ?>" class="me-3" style="width: 80px; height: 80px;">
            <div>
              <h5><?php esc_attr_e('Rewrite With AI From News', 'wpbotwriter')?></h5>
              <p><?php esc_attr_e('Create original content from scratch with artificial intelligence!', 'wpbotwriter')?></p>
            </div>
          </div>
        </a>
      </li>

         <!-- New item with modal trigger -->
   <li class="list-group-item">
        <a href="#" data-bs-toggle="modal" data-bs-target="#tutorialModal" class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <img src="<?php echo esc_url(plugins_url( '../assets/images/tutorial.png', __FILE__ )); ?>" alt="Tutorial" class="me-3" style="width: 80px; height: 80px;">
            <div>
              <h5><?php esc_html_e('Video Tutorials', 'wpbotwriter')?></h5>
              <p><?php esc_html_e('Learn how to add new process.', 'wpbotwriter')?></p>
            </div>
          </div>
        </a>
      </li>


       <!-- Bootstrap Modal -->
<div class="modal fade" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tutorialModalLabel">Video Tutorials</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="mb-3">
          <h5>Manual Processes:</h5>
          <p>After clicking the "Generate Post" button at the bottom, your process will start running in the background. The average duration of this process is around 5 minutes. If an error occurs, you can find detailed information at the top of your WordPress admin panel.</p>
        </div>

        <div class="mb-3">
          <h5>Automatic Processes:</h5>
          <p>For each added process, it will be automatically triggered at the specified time interval (you can set this in the settings, under the cron section). Therefore, after adding processes, you need to set the time and wait for the posts to be generated automatically.</p>
        </div>
        <!-- Video Tutorials -->
        <div class="mb-3">
          <h6>How to Generate Post with AI</h6>
          <iframe width="450" height="350" src="https://www.youtube.com/embed/p5KpM9eZftE" frameborder="0" allowfullscreen></iframe>
        </div>

        <div class="mb-3">
          <h6>How to Rewrite Post with AI From RSS Feed</h6>
          <iframe width="450" height="350" src="https://www.youtube.com/embed/A-wTvmlz7og" frameborder="0" allowfullscreen></iframe>
        </div>

        <div class="mb-3">
          <h6>How to Rewrite Post with AI From Any WordPress Website</h6>
          <iframe width="450" height="350" src="https://www.youtube.com/embed/xo9IbyZ_HXY" frameborder="0" allowfullscreen></iframe>
        </div>

        <div class="mb-3">
          <h6>How to Rewrite Any Post with AI From Google News</h6>
          <iframe width="450" height="350" src="https://www.youtube.com/embed/z8sM2953VBQ" frameborder="0" allowfullscreen></iframe>
        </div>
        <!-- End Video Tutorials -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



    </ul>
    
  </div>
  <?php
}



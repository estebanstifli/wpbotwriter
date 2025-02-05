<?php
function botwriter_automatic_posts_page()
{  

  
    $table = new botwriter_tasks_Table();
    $table->prepare_items();
    $message = '';    
    if ('delete_all' === $table->current_action()) {
            $message = 'Items deleted';
    }
    if ('delete' === $table->current_action()) {            
            $message = 'Item deleted';
    }

    $message = esc_html($message);
    ?>

    <div class="wrap">
        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h2>
            <?php esc_html_e('Tasks', 'botwriter'); ?>
            <a class="add-new-h2"
                href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=botwriter_automatic_post_new')); ?>">
                <?php esc_html_e('Add new', 'botwriter'); ?>
            </a>
        </h2>

        

        <?php if (!empty($message)) : ?>
            <div class="updated below-h2" id="message"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <form id="contacts-table" method="POST">
            <?php
            wp_nonce_field('botwriter_tasks_nonce_action', 'botwriter_tasks_nonce');            
            $page_value = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
            ?>
            <input type="hidden" name="page" value="<?php echo esc_html($page_value); ?>"/>
            <?php $table->display(); ?>
        </form>
    </div>

    <?php
}


function botwriter_form_page_handler(){
  
    global $wpdb;
    $table_name = $wpdb->prefix . 'botwriter_tasks'; 

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
        'custom_post_length' => '',
        'post_status'      => 'publish',
        'days'             => '',
        'times_per_day'    => 1,
        'execution_count' => 0, // Initialize execution count to zero
        'last_execution_date' => null, // Set last execution date to null initially
        'website_type'      => 'ai',

        'website_name'              => '',                
        'domain_name'              => '',
        'category_id'              => '',
        'website_category_id'      => '',
        'website_category_name'      => '',
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

        $days = isset($_POST['days']) ? implode(",", array_map('sanitize_text_field', wp_unslash($_POST['days']))) : "";
        $times_per_day = isset($_POST['times_per_day']) ? intval(wp_unslash($_POST['times_per_day'])) : 1;

        
        // Process only the specific values needed
        $item = array(
            'id'                => isset($_POST['id']) ? intval(wp_unslash($_POST['id'])) : 0,

            'task_name'         => isset($_POST['task_name']) ? sanitize_text_field(wp_unslash($_POST['task_name'])) : '',
            'post_status'       => isset($_POST['post_status']) ? sanitize_text_field(wp_unslash($_POST['post_status'])) : '',
            'days'              => $days,
            'times_per_day'     => $times_per_day,                    
            'writer'            => isset($_POST['writer']) ? sanitize_text_field(wp_unslash($_POST['writer'])) : '',
            'narration'         => isset($_POST['narration']) ? sanitize_text_field(wp_unslash($_POST['narration'])) : '',
            'post_length'       => isset($_POST['post_length']) ? sanitize_text_field(wp_unslash($_POST['post_length'])) : '',
            'custom_post_length'=> isset($_POST['custom_post_length']) ? sanitize_text_field(wp_unslash($_POST['custom_post_length'])) : '',
            'custom_style'      => isset($_POST['custom_style']) ? sanitize_text_field(wp_unslash($_POST['custom_style'])) : '',
            'post_language'     => isset($_POST['post_language']) ? sanitize_text_field(wp_unslash($_POST['post_language'])) : '',

            'website_type'      => isset($_POST['website_type']) ? sanitize_text_field(wp_unslash($_POST['website_type'])) : '',
            'website_name'      => '',
            'domain_name'       => isset($_POST['domain_name']) ? sanitize_url(wp_unslash($_POST['domain_name'])) : '',
            'category_id'       => isset($_POST['category_id']) ? array_map('intval', wp_unslash($_POST['category_id'])) : array(),
            'website_category_id'=> isset($_POST['website_category_id']) ? array_map('intval', wp_unslash($_POST['website_category_id'])) : array(),
            'website_category_name'=> isset($_POST['website_category_name']) ? sanitize_text_field(wp_unslash($_POST['website_category_name'])) : '',
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
            
            'author_selection'  => isset($_POST['author_selection']) ? sanitize_text_field(wp_unslash($_POST['author_selection'])) : '',

            'news_keyword'      => isset($_POST['news_keyword']) ? sanitize_text_field(wp_unslash($_POST['news_keyword'])) : '',
            'news_country'      => isset($_POST['news_country']) ? sanitize_text_field(wp_unslash($_POST['news_country'])) : '',
            'news_language'     => isset($_POST['news_language']) ? sanitize_text_field(wp_unslash($_POST['news_language'])) : '',
            'news_time_published' => isset($_POST['news_time_published']) ? sanitize_text_field(wp_unslash($_POST['news_time_published'])) : '',
            'news_source'       => isset($_POST['news_source']) ? sanitize_text_field(wp_unslash($_POST['news_source'])) : '',

            'rss_source'        => isset($_POST['rss_source']) ? sanitize_text_field(wp_unslash($_POST['rss_source'])) : '',
            'ai_keywords'       => isset($_POST['ai_keywords']) ? sanitize_text_field(wp_unslash($_POST['ai_keywords'])) : ''

        );
        //Convert category_id array to text
        $category_ids = implode(",", $item['category_id']);
        $item['category_id'] = $category_ids;

        //Convert website_category_id to text    
        $website_category_ids = implode(",", $item['website_category_id']);
        

         $item['website_category_id'] = $website_category_ids;
          

          $item_valid = botwriter_validate_website($item);
          if ($item_valid === true) {

        
            //$item = array_map('sanitize_text_field', $item); // Sanitize all inputs
              if ($item['id'] == 0) {
                  $result = $wpdb->insert($table_name, $item);
                  // mostrar el error de la base de datos
                  //echo "console.log('" . $wpdb->last_error . "')";


                  $item['id'] = $wpdb->insert_id;
                  if ($result) {
                      $message = __('New task was successfully saved!', 'botwriter');
                      ?>
                        <div id="redirecion" data-url="<?php echo esc_url( admin_url('admin.php?page=botwriter_automatic_posts') ); ?>"></div>
                      <?php
                                            
                  } else {
                      $notice = __('There was an error while saving item', 'botwriter');
                  }
              } else {
                  $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                   
        
               if ($result !== false) {
                    if ($result === 0) {
                      $message = __('No changes were made, but the update was successful.', 'botwriter');                                                                  
                      ?>
                        <div id="redirecion" data-url="<?php echo esc_url( admin_url('admin.php?page=botwriter_automatic_posts') ); ?>"></div>
                      <?php
                      
                    } else {
                      $message = __('New task was successfully updated!', 'botwriter');                                            
                      ?>
                      <div id="redirecion" data-url="<?php echo esc_url( admin_url('admin.php?page=botwriter_automatic_posts') ); ?>"></div>
                      <?php
                    }
                } else {
                        $notice = __('There was an error while updating item: ', 'botwriter') . $wpdb->last_error;
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
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $sanitized_id), ARRAY_A);
          if (!$item) {
              $item = $default;
              $notice = __('Item not found', 'botwriter');
          }
      }
      
    }

    
    add_meta_box('botwriter_post_form_meta_box', __('Task Form', 'botwriter'), 'botwriter_post_form_meta_box_handler', 'botwriter_automatic_post_new', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    
    <?php 
    $is_editing = isset($_REQUEST['id']) && intval($_REQUEST['id']) > 0;
    $title_text = $is_editing ? __('Edit Task', 'botwriter') : __('Add New', 'botwriter');
    ?>
    <h2><?php echo esc_html($title_text); ?> <a class="add-new-h2"
      href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=botwriter_automatic_posts')); ?>"><?php esc_html_e('Back to List', 'botwriter'); ?></a>
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
                    
                    <?php do_meta_boxes('botwriter_automatic_post_new', 'normal', $item); ?>
                    <input type="submit" value="<?php esc_attr_e('Save', 'botwriter')?>" id="submit" class="button-primary" name="submit" onclick="preSelectedOptions()">
                </div>
            </div>
        </div>
    </form>
</div>

<?php
}
 


function botwriter_get_admin_email(){
    $admin_email = get_option('admin_email', false);
    if ($admin_email !== false) {


    } else {
      $admin_email = 'email@example.com';
    }
    return $admin_email;
}
  

  

function botwriter_post_form_meta_box_handler($item)
{
  Global $botwriter_languages,$botwriter_countries;
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




<tbody >
<form>
  <div class="form2bc">
  <div class="container">
  <form class="row g-3">
      <?php
      //Get admin domain name
      $botwriter_admin_email = botwriter_get_admin_email();
      $botwriter_domain_name = esc_url(get_site_url());
      $is_empty = empty($item['domain_name']);
      ?>

      <input type="hidden" id="botwriter_admin_email" value="<?php echo esc_attr($botwriter_admin_email); ?>">
      <input type="hidden" id="botwriter_domain_name" value="<?php echo esc_attr($botwriter_domain_name); ?>">
      
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
  <label class="form-label"><?php echo esc_html__('Writer:', 'botwriter'); ?></label>
  <div class="writer-options">

    <!-- Orion, the Versatile Assistant (Free) -->
    <label class="writer-option">
      <input type="radio" name="writer" value="orion" required <?php if ($item['writer'] === 'orion') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'orion.jpeg'); ?>" alt="<?php echo esc_attr__('Orion', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Orion, the Versatile Assistant', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Adaptable and insightful, perfect for a wide range of topics and styles.', 'botwriter'); ?></p>
      </div>
    </label>

    <!-- Lucida, the Analytical Critic (Free) -->
    <label class="writer-option">
      <input type="radio" name="writer" value="lucida" required <?php if ($item['writer'] === 'lucida') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'lucida.jpeg'); ?>" alt="<?php echo esc_attr__('Lucida', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Lucida, the Analytical Critic', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Precise and direct, perfect for deep analysis in complex topics.', 'botwriter'); ?></p>
      </div>
    </label>

    <!-- Max, the Adventurous Narrator (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="max" required <?php echo $item['writer'] === 'max' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'max.jpeg'); ?>" alt="<?php echo esc_attr__('Max', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Max, the Adventurous Narrator', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Passionate and descriptive, ideal for stories of travel and culture.', 'botwriter'); ?></p>
      </div>
    </label>

    <!-- Cloe, the Ironic Cultural Critic (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="cloe" required <?php echo $item['writer'] === 'cloe' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'cloe.jpeg'); ?>" alt="<?php echo esc_attr__('Cloe', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Cloe, the Ironic Cultural Critic', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Sarcastic and witty, perfect for cultural and social reviews.', 'botwriter'); ?></p>
      </div>
    </label>

    <!-- Gael, the Reflective Poet (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="gael" required <?php echo $item['writer'] === 'gael' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'gael.jpeg'); ?>" alt="<?php echo esc_attr__('Gael', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Gael, the Reflective Poet', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Introspective and poetic, ideal for philosophical and emotional themes.', 'botwriter'); ?></p>
      </div>
    </label>

    <!-- Custom (Pro) -->
    <label class="writer-option <?php echo $is_pro_user ? '' : 'writer-pro-blurred'; ?>">
      <input type="radio" name="writer" value="custom" required <?php echo $item['writer'] === 'custom' ? 'checked' : ''; ?> <?php echo $is_pro_user ? '' : 'disabled'; ?>>
      <img src="<?php echo esc_url($dir_images_writers . 'custom.jpeg'); ?>" alt="<?php echo esc_attr__('Custom', 'botwriter'); ?>" class="writer-photo">
      <div class="writer-info">
        <strong><?php echo esc_html__('Custom, the User-Selected Style Bot', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Allows the user to choose a specific narrative style..', 'botwriter'); ?></p>
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
            echo "<option value='" . esc_attr($value) . "' " . esc_attr($selected) . ">" . esc_html($name) . "</option>";
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
            <label class="form-label"><?php esc_html_e('Days of the Week:', 'botwriter'); ?></label><br>
            <?php 
            $days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days_of_week as $day) {                                
                $is_checked = in_array($day, $selected_days) ? 'checked' : '';
                echo "<input type='checkbox' name='days[]' value='" . esc_attr($day) . "' " . esc_attr($is_checked) . "> " . esc_html($day) . "<br>";
              
            }
            ?>
            <p class="form-text">Select the days on which you want to write and publish.</p>            
    </div>

    <div class="col-md-6">
            <label  class="form-label"><?php esc_html_e('Post per Day:', 'botwriter'); ?></label>
            <input type="number" name="times_per_day" min="1" value="<?php echo esc_attr($times_per_day); ?>" required>
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
                    foreach ($botwriter_languages as $code => $name) {                        
                        $selected = ($item['post_language'] == $code) ? 'selected' : '';    
                        echo "<option value='" . esc_attr($code) . "' " . esc_attr($selected) . " >" . esc_html($name) . "</option>";
                    }
                ?>
            </select>
        </div>


    
      

<br>

<div class="col-md-6">
  <label class="form-label"><?php echo esc_html__('Source of Ideas:', 'botwriter'); ?></label>
  
  <!-- Radio Button Options with Icons -->
  <div class="source-options">
    <!-- Articles from the Own Blog --> 
    <label class="source-option">
      <input type="radio" name="website_type" value="ai" <?php if ($item['website_type'] === 'ai') {
                                                                      echo 'checked';
                                                                    } ?>>

      <img src="<?php echo esc_url($dir_images_icons . 'sameblog100.png'); ?>" alt="Own Blog Articles" class="source-icon">
      <div class="writer-info">
        <strong><?php echo esc_html__('IA Articles from topics or keywords', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Create News Articles from topics or keywords.', 'botwriter'); ?></p>      
      </div>
    </label>

    <!-- WordPress External -->
    <label class="source-option">
      <input type="radio" name="website_type" value="wordpress" required <?php if ($item['website_type'] === 'wordpress') {
                                                                      echo 'checked';
                                                                    } ?>>	  
      <img src="<?php echo esc_url($dir_images_icons . 'externalwp100.png'); ?>" alt="WordPress External" class="source-icon">
        <div class="writer-info">
        <strong><?php echo esc_html__('WordPress External', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Inspired by articles from an external WordPress, potentially in other languages. It rewrites the content and designs a completely new image.', 'botwriter'); ?></p>
      </div>
    </label>
    
    <!-- Google News -->
    <label class="source-option">
      <input type="radio" name="website_type" value="news" <?php if ($item['website_type'] === 'news') {
                                                                      echo 'checked';
                                                                    } ?>>
      <img src="<?php echo esc_url($dir_images_icons . 'news100.png'); ?>" alt="Google News" class="source-icon">
        <div class="writer-info">
        <strong><?php echo esc_html__('Google News', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts trending news from Google News to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'botwriter'); ?></p>
        </div>
    </label>
    
    <!-- RSS (Web with RSS) -->
    <label class="source-option">
      <input type="radio" name="website_type" value="rss" <?php if ($item['website_type'] === 'rss') {
                                                                      echo 'checked';
                                                                    } ?>>
      
      <img src="<?php echo esc_url($dir_images_icons . 'rss100.png'); ?>" alt="RSS" class="source-icon">
      <div class="writer-info">
        <strong><?php echo esc_html__('RSS (Web with RSS)', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts articles from a website with an RSS feed to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'botwriter'); ?></p>      
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
        <strong><?php echo esc_html__('External Research', 'botwriter'); ?></strong>
        <p><?php echo esc_html__('Extracts information from external sources to rewrite and adapt to your blog’s audience. You can select the topic or keyword.', 'botwriter'); ?></p>
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
      <input type="hidden" name="website_category_name" value="<?php echo esc_attr($item['website_category_name']); ?>">
      
      <?php
                                            if (!$is_empty) {
                                                echo esc_html__('Previously selected: ', 'botwriter') . esc_html($item['website_category_name']);
                                            }
                                            ?>




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
        foreach ($botwriter_countries as $code => $name) {
            $selected = ($item['news_country'] == $code) ? 'selected' : (($code == strtolower($default_country_code) && empty($item['news_country'])) ? 'selected' : '');            
            echo "<option value='" . esc_attr($code) . "' " . esc_attr($selected) . " >" . esc_html($name) . "</option>";
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
        
        foreach ($botwriter_languages as $code => $name) {
          $selected = ($item['news_language'] == $code) ? 'selected' : (($code == strtolower($default_language_code) && empty($item['news_language'])) ? 'selected' : '');
          echo "<option value='" . esc_attr($code) . "' " . esc_attr($selected) . " >" . esc_html($name) . "</option>";
          
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
            
            echo "<option value='" . esc_attr($value) . "' " . esc_attr($selected) . " >" . esc_html($label) . "</option>";

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
<!-- End Post Length -->



<!-- Custom Length Input -->
<div class="col-md-6" id="customLengthInput" style="display: <?php echo (!in_array($item['post_length'], [400, 800, 1600])) ? 'block' : 'none'; ?>;">
  <label class="form-label">Custom Length (max 4000):</label>
  <input id="custom_post_length"  name="custom_post_length" type="number" class="form-control" value="<?php echo esc_html($item['custom_post_length']) ?>" onchange="updatePostLength()">
  <p class="form-text">Enter the number of words you want the post to have.</p>
</div>
<br>




  </form>
</div>




  



</form>

	
</tbody>
<?php
}


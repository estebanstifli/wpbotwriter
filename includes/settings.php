<?php
function wpbotwriter_settings_page_handler() {
    // Verifica permisos
    if (!current_user_can('manage_options')) {
        return;
    }

    // Manejo de mensajes y notificaciones
    $notice = '';
    $message = '';

    // valores por defecto iniciales:
    get_option('wpbotwriter_email') == '' ? update_option('wpbotwriter_email', get_option('admin_email')) : '';
    get_option('wpbotwriter_email_confirmed') == '' ? update_option('wpbotwriter_email_confirmed', '0') : '';


    // Manejo de opciones enviadas
    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), basename(__FILE__))) {
        // Actualiza las opciones en la base de datos
        $settings = array(
            'wpbotwriter_images_size' => isset($_POST['wpbotwriter_images_size']) ? sanitize_text_field($_POST['wpbotwriter_images_size']) : 'medium',
            'wpbotwriter_images_quality' => isset($_POST['wpbotwriter_images_quality']) ? sanitize_text_field($_POST['wpbotwriter_images_quality']) : 'medium', 
            'wpbotwriter_sslverify' => isset($_POST['wpbotwriter_sslverify']) ? sanitize_text_field($_POST['wpbotwriter_sslverify']) : "yes",
            'wpbotwriter_email' => isset($_POST['wpbotwriter_email']) ? sanitize_text_field($_POST['wpbotwriter_email']) : get_option('admin_email'),
        );

        $settings["plan_id"]=0; // free plan

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }

        $message = __('Settings saved successfully.', 'wpbotwriter');
    }

    // Agrega metabox
    add_meta_box(
        'wpbotwriter_settings',
        __('WP BotWriter Settings', 'wpbotwriter'),
        'wpbotwriter_settings_meta_box_handler',
        'wpbotwriter_settings_page',
        'normal',
        'default'
    );
    ?>
    
    <script>
        jQuery(document).ready(function($) {
            wpbotwriter_getUserData();
        });
    </script>

    <div class="wrap">
        <h2><?php esc_html_e('Settings', 'wpbotwriter'); ?></h2>

        <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo esc_html($notice); ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(basename(__FILE__))); ?>" />
            <input id="wpbotwriter_api_key"  type="hidden" name="api_key" value="<?php echo esc_attr(get_option('wpbotwriter_api_key')); ?>" />            
            <input id="wpbotwriter_domain_name" type="hidden" name="url" value="<?php echo esc_attr(get_site_url()); ?>" />
            
            

            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php do_meta_boxes('wpbotwriter_settings_page', 'normal', null); ?>
                        <input type="submit" value="<?php esc_attr_e('Save Settings', 'wpbotwriter'); ?>" id="submit" class="button-primary" name="submit">
                    </div>
                </div>
            </div>
        </form>
        <did id='subscription'>
        
        <?php        
        if (get_option('plan_id') == 0) {
            echo "<h3>Free Plan</h3>";
            $site_url = urlencode(get_site_url());
            echo '<a href="https://wpbotwriter.com/info?user_domainname=' . $site_url . '" target="_blank" class="button-primary">Upgrade to Pro Plan</a> to get more features.';
        }
        else {
            
            
        }
        ?>
        

        </div>        
        <div id="response_div">            
        </div>


    </div>
    <?php
}


function wpbotwriter_settings_meta_box_handler() {
    // Obtiene las opciones desde la base de datos
    $settings = array(
        'wpbotwriter_images_size' => get_option('wpbotwriter_images_size', 'medium'),
        'wpbotwriter_images_quality' => get_option('wpbotwriter_images_quality', 'medium'),
        'wpbotwriter_sslverify' => get_option('wpbotwriter_sslverify', 'yes'),
    );

    ?>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('Images Size:', 'wpbotwriter'); ?></label>
    <select name="wpbotwriter_images_size" class="form-control">
        <option value="thumbnail" <?php selected($settings['wpbotwriter_images_size'], 'thumbnail'); ?>><?php esc_html_e('Thumbnail', 'wpbotwriter'); ?></option>
        <option value="medium" <?php selected($settings['wpbotwriter_images_size'], 'medium'); ?>><?php esc_html_e('Medium', 'wpbotwriter'); ?></option>
        <option value="large" <?php selected($settings['wpbotwriter_images_size'], 'large'); ?>><?php esc_html_e('Large', 'wpbotwriter'); ?></option>
        <option value="full" <?php selected($settings['wpbotwriter_images_size'], 'full'); ?>><?php esc_html_e('Full', 'wpbotwriter'); ?></option>
    </select>
    </div>
    <br>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('Images Quality:', 'wpbotwriter'); ?></label>
    <select name="wpbotwriter_images_quality" class="form-control">
        <option value="medium" <?php selected($settings['wpbotwriter_images_quality'], 'medium'); ?>><?php esc_html_e('Medium', 'wpbotwriter'); ?></option>
        <option value="high" <?php selected($settings['wpbotwriter_images_quality'], 'high'); ?>><?php esc_html_e('High', 'wpbotwriter'); ?></option>
    </select>
    </div>
    <br>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('SSL Verify:', 'wpbotwriter'); ?></label>
    <select name="wpbotwriter_sslverify" class="form-control">
        <option value="yes" <?php selected($settings['wpbotwriter_sslverify'], 'yes'); ?>><?php esc_html_e('Yes', 'wpbotwriter'); ?></option>
        <option value="no" <?php selected($settings['wpbotwriter_sslverify'], 'no'); ?>><?php esc_html_e('No', 'wpbotwriter'); ?></option>
    </select>
    </div>
    <br>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('Email:', 'wpbotwriter'); ?></label>    
        <input id="wpbotwriter_email" type="text" name="wpbotwriter_email" class="form-control" 
               value="<?php echo esc_attr(get_option('wpbotwriter_email')); ?>" 
                style="width: 50%;">
                <div id='button_confirm_email'> 
                <a href="javascript:void(0);" onclick="wpbotwriter_updateEmail();" class="button-primary">Send message to this email to register email</a>                
                </div>        
        <div id='response_email'>
            <?php if (get_option('wpbotwriter_email_confirmed') == '1'): ?>
            <strong><?php esc_html_e('REGISTERED!', 'wpbotwriter'); ?></strong>
            <?php endif; ?>
        </div>
    </div> 
    <br>
    

    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('API Key:', 'wpbotwriter'); ?></label>
        <input type="text" name="wpbotwriter_api_key" class="form-control" 
               value="<?php echo esc_attr(get_option('wpbotwriter_api_key')); ?>" 
                style="width: 50%;" readonly>
    </div>

    <br>
    <?php
}



// function to send data to comprueba_api_key.php to check if the api key is valid and get data 
function wpbotwriter_check_api_key() {
    $remote_url = 'https://wpbotwriter.com/public/comprueba_api_key.php';
    
    $data['api_key'] = get_option('wpbotwriter_api_key');
    $data['url'] = get_site_url();

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
        return false;
    } else {
        // Procesar la respuesta si la solicitud fue exitosa
        echo 'Data recived1: <pre>' . print_r($response, true) . '</pre>';
        if ($response['response']['code'] === 200) {
            // Procesar la respuesta si la solicitud fue exitosa
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);                        
            return $result;            
        } else {
            error_log('Error sending data to the server');
            return false;
        }        
    }    
}
 


?>
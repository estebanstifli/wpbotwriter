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
    get_option('wpbotwriter_cron_active') == '' ? update_option('wpbotwriter_cron_active', '1') : '';

    // Manejo de opciones enviadas
    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), basename(__FILE__))) {

        
        // Actualiza las opciones en la base de datos
        $settings = array(                        
            'wpbotwriter_ai_image_size' => isset($_POST['wpbotwriter_ai_image_size']) ? sanitize_text_field($_POST['wpbotwriter_ai_image_size']) : 'square_hd',            
            'wpbotwriter_sslverify' => isset($_POST['wpbotwriter_sslverify']) ? sanitize_text_field($_POST['wpbotwriter_sslverify']) : "yes",
            'wpbotwriter_email' => isset($_POST['wpbotwriter_email']) ? sanitize_text_field($_POST['wpbotwriter_email']) : get_option('admin_email'),
            'wpbotwriter_api_key' => isset($_POST['wpbotwriter_api_key']) ? sanitize_text_field($_POST['wpbotwriter_api_key']) : '',            
            'wpbotwriter_cron_active' => isset($_POST['wpbotwriter_cron_active']) ? sanitize_text_field($_POST['wpbotwriter_cron_active']) : '1',                    
        );

        $settings["plan_id"]=0; // free plan

        foreach ($settings as $key => $value) {
            update_option($key, $value);            
        }

        $message = __('Settings saved successfully.', 'wpbotwriter');
    }

    // si las 2 primeras letras de la api key son PK, entonces es una api key de pago
    if (substr(get_option('wpbotwriter_api_key'), 0, 2) == 'PK') {
        // obtiene el plan_id de la api key, que son las 6 letras despues de PK
        $plan_id = substr(get_option('wpbotwriter_api_key'), 2, 6);        
        update_option('plan_id', $plan_id);
    } else {
        update_option('plan_id', 0);
    }

    // active or deactivate cron job
    if (get_option('wpbotwriter_cron_active') == '1') {
        wpbotwriter_scheduled_events_plugin_activate();
    } else {
        wpbotwriter_scheduled_events_plugin_deactivate();
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
            <input id="wpbotwriter_domain_name" type="hidden" name="url" value="<?php echo esc_attr(get_site_url()); ?>" />
            
            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">                        
                        <?php do_meta_boxes('wpbotwriter_settings_page', 'normal', null); ?>                                                
                    </div>
                </div>
            </div>
        </form>
        <div id='subscription'>
        
        <?php        
        if (get_option('plan_id') == 0) {
            echo "<h3>Free Plan</h3>";
            $site_url = urlencode(get_site_url());
            echo '<a href="https://wpbotwriter.com/info?user_domainname=' . $site_url . '" target="_blank" class="button-primary">Upgrade to Pro Plan</a> to get more features.';
        }
        else {
            echo "<h3>Pro Plan</h3>";
            // poner un boton que llama al javascript para obtener los datos del usuario
            echo '<a href="javascript:void(0);" onclick="wpbotwriter_getUserData();" class="button-primary">Get Info of your Plan</a>';            
        }
        ?>
        
        </div>        

        <div id="response_div" style="min-height: 300px; height: auto;"></div>
    </div>
    <?php
}


function wpbotwriter_settings_meta_box_handler() {
    // Obtiene las opciones desde la base de datos
    $settings = array(        
        'wpbotwriter_ai_image_size' => get_option('wpbotwriter_ai_image_size', 'square_hd'),
        'wpbotwriter_sslverify' => get_option('wpbotwriter_sslverify', 'yes'),
        'wpbotwriter_cron_active' => get_option('wpbotwriter_cron_active', '1'),
    );

    ?>    
    <br>
    
    
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('AI Generated Images Size:', 'wpbotwriter'); ?></label>
        <select name="wpbotwriter_ai_image_size" class="form-control">
            <option value="square_hd" <?php selected($settings['wpbotwriter_ai_image_size'], 'square_hd'); ?>><?php esc_html_e('Square HD', 'wpbotwriter'); ?></option>
            <option value="square" <?php selected($settings['wpbotwriter_ai_image_size'], 'square'); ?>><?php esc_html_e('Square', 'wpbotwriter'); ?></option>
            <option value="portrait_4_3" <?php selected($settings['wpbotwriter_ai_image_size'], 'portrait_4_3'); ?>><?php esc_html_e('Portrait 4:3', 'wpbotwriter'); ?></option>
            <option value="portrait_16_9" <?php selected($settings['wpbotwriter_ai_image_size'], 'portrait_16_9'); ?>><?php esc_html_e('Portrait 16:9', 'wpbotwriter'); ?></option>
            <option value="landscape_4_3" <?php selected($settings['wpbotwriter_ai_image_size'], 'landscape_4_3'); ?>><?php esc_html_e('Landscape 4:3', 'wpbotwriter'); ?></option>
            <option value="landscape_16_9" <?php selected($settings['wpbotwriter_ai_image_size'], 'landscape_16_9'); ?>><?php esc_html_e('Landscape 16:9', 'wpbotwriter'); ?></option>
        </select>        
    </div>
    <br>


    
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Disable SSL verification for API requests:', 'wpbotwriter'); ?></label>
        <input type="checkbox" name="wpbotwriter_sslverify" value="no" <?php checked($settings['wpbotwriter_sslverify'], 'no'); ?>>
        
    </div>
    <br>
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Enable Cron Jobs:', 'wpbotwriter'); ?></label>
        <input type="hidden" name="wpbotwriter_cron_active" value="0"> <!-- Hidden field to handle the "unchecked" state -->
        <input type="checkbox" name="wpbotwriter_cron_active" value="1" <?php checked(get_option('wpbotwriter_cron_active'), '1'); ?>>
    </div>        
    <br>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('Email:', 'wpbotwriter'); ?></label>    
        <input id="wpbotwriter_email" type="text" name="wpbotwriter_email" class="form-control" 
               value="<?php echo esc_attr(get_option('wpbotwriter_email')); ?>" 
                style="width: 50%;">
                <div id='button_confirm_email'> 
                <a href="javascript:void(0);" onclick="wpbotwriter_updateEmail();" class="button">Send message to this email to register plugin</a>                
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
        <input id="wpbotwriter_api_key" type="text" name="wpbotwriter_api_key" class="form-control" 
               value="<?php echo esc_attr(get_option('wpbotwriter_api_key')); ?>" 
                style="width: 50%;">        
        <p>
            <?php printf(
                esc_html__('Modify the API Key if you subscribe to a payment plan, otherwise leave it as it is. If you need a new API Key, you can get it from %s.', 'wpbotwriter'),
                '<a href="' . esc_url('https://wpbotwriter.com') . '" target="_blank">Wp BotWriter</a>'
            ); ?>
        </p>
    </div>    
    <br>
    <input type="submit" value="<?php esc_attr_e('Save Settings', 'wpbotwriter'); ?>" id="submit" class="button-primary" name="submit">  
    <?php
}


?>
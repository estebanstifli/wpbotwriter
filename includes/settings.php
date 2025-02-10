<?php
function botwriter_settings_page_handler() {
    // Verifica permisos
    if (!current_user_can('manage_options')) {
        return;  
    }

    // Manejo de mensajes y notificaciones
    $notice = '';
    $message = '';

    // Manejo de opciones enviadas
    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), basename(__FILE__))) {

        
        // Actualiza las opciones en la base de datos
        $settings = array(                        
            'botwriter_ai_image_size' => isset($_POST['botwriter_ai_image_size']) ? sanitize_text_field(wp_unslash($_POST['botwriter_ai_image_size'])) : 'square_hd',            
            'botwriter_sslverify' => isset($_POST['botwriter_sslverify']) ? sanitize_text_field(wp_unslash($_POST['botwriter_sslverify'])) : "yes",
            'botwriter_email' => isset($_POST['botwriter_email']) ? sanitize_email(wp_unslash($_POST['botwriter_email'])) : get_option('admin_email'),
            'botwriter_api_key' => isset($_POST['botwriter_api_key']) ? sanitize_text_field(wp_unslash($_POST['botwriter_api_key'])) : '',            
            'botwriter_cron_active' => isset($_POST['botwriter_cron_active']) ? sanitize_text_field(wp_unslash($_POST['botwriter_cron_active'])) : '1',
            'botwriter_paused_tasks' => isset($_POST['botwriter_paused_tasks']) ? sanitize_text_field(wp_unslash($_POST['botwriter_paused_tasks'])) : '2',
        );

        $settings["botwriter_plan_id"]=0; // free plan

        foreach ($settings as $key => $value) {
            update_option($key, $value);            
        }

        $message = __('Settings saved successfully.', 'botwriter');
    }

    // si las 2 primeras letras de la api key son PK, entonces es una api key de pago
    if (substr(get_option('botwriter_api_key'), 0, 2) == 'PK') {
        // obtiene el plan_id de la api key, que son las 6 letras despues de PK
        $plan_id = substr(get_option('botwriter_api_key'), 2, 6);        
        update_option('botwriter_plan_id', $plan_id);
    } else {
        update_option('botwriter_plan_id', 0);
    }

    // active or deactivate cron job
    if (get_option('botwriter_cron_active') == '1') {
        botwriter_scheduled_events_plugin_activate();
    } else {
        botwriter_scheduled_events_plugin_deactivate();
    }

    // Agrega metabox
    add_meta_box(
        'botwriter_settings',
        __('WP BotWriter Settings', 'botwriter'),
        'botwriter_settings_meta_box_handler',
        'botwriter_settings_page',
        'normal',
        'default'
    );


    ?>
    
    

    <div class="wrap">
        <h2><?php esc_html_e('Settings', 'botwriter'); ?></h2>

        <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo esc_html($notice); ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <form id="form" method="POST">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(basename(__FILE__))); ?>" />                      
            <input id="botwriter_domain_name" type="hidden" name="url" value="<?php echo esc_attr(get_site_url()); ?>" />
            
            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">                        
                        <?php do_meta_boxes('botwriter_settings_page', 'normal', null); ?>                                                
                    </div>
                </div>
            </div>
        </form>
        <div id='subscription'>
        
        <?php        
        if (get_option('botwriter_plan_id') == 0) {
            echo "<h3>Free Plan</h3>";
            $site_url = urlencode(get_site_url());
            echo '<a href="https://wpbotwriter.com" target="_blank" class="button-primary">Upgrade to Pro Plan</a> to get more features.';
        }
        else {
            echo "<h3>Pro Plan</h3>";
            // poner un boton que llama al javascript para obtener los datos del usuario
            echo '<a href="javascript:void(0);" onclick="botwriter_getUserData();" class="button-primary">Get Info of your Plan</a>';            
        }
        ?>
        
        </div>        

        <div id="response_div" style="min-height: 300px; height: auto;"></div>
    </div>
    <?php
}


function botwriter_settings_meta_box_handler() {
    // Obtiene las opciones desde la base de datos
    $settings = array(        
        'botwriter_ai_image_size' => get_option('botwriter_ai_image_size', 'square_hd'),
        'botwriter_sslverify' => get_option('botwriter_sslverify', 'yes'),
        'botwriter_cron_active' => get_option('botwriter_cron_active', '1'),        
        'botwriter_paused_tasks' => get_option('botwriter_paused_tasks', '2'),        
    );

    ?>    
    <br>
    
    
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('AI Generated Images Size:', 'botwriter'); ?></label>
        <select name="botwriter_ai_image_size" class="form-control">
            <option value="square_hd" <?php selected($settings['botwriter_ai_image_size'], 'square_hd'); ?>><?php esc_html_e('Square HD', 'botwriter'); ?></option>
            <option value="square" <?php selected($settings['botwriter_ai_image_size'], 'square'); ?>><?php esc_html_e('Square', 'botwriter'); ?></option>
            <option value="portrait_4_3" <?php selected($settings['botwriter_ai_image_size'], 'portrait_4_3'); ?>><?php esc_html_e('Portrait 4:3', 'botwriter'); ?></option>
            <option value="portrait_16_9" <?php selected($settings['botwriter_ai_image_size'], 'portrait_16_9'); ?>><?php esc_html_e('Portrait 16:9', 'botwriter'); ?></option>
            <option value="landscape_4_3" <?php selected($settings['botwriter_ai_image_size'], 'landscape_4_3'); ?>><?php esc_html_e('Landscape 4:3', 'botwriter'); ?></option>
            <option value="landscape_16_9" <?php selected($settings['botwriter_ai_image_size'], 'landscape_16_9'); ?>><?php esc_html_e('Landscape 16:9', 'botwriter'); ?></option>
        </select>        
    </div>
    <br>

    
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Pause between daily posts of the same task (minutes):', 'botwriter'); ?></label>
        <div style="display: flex; align-items: center;">
            <input type="number" name="botwriter_paused_tasks" value="<?php echo esc_attr($settings['botwriter_paused_tasks']); ?>" min="2" style="margin-right: 10px;">
            <span><?php esc_html_e('minutes', 'botwriter'); ?></span>
        </div>
    </div>
    <br>
    
     
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Disable SSL verification for API requests: (Not recommended. Only for temporary testing)', 'botwriter'); ?></label>
        <input type="checkbox" name="botwriter_sslverify" value="no" <?php checked($settings['botwriter_sslverify'], 'no'); ?>>
        
    </div>
    <br>
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Enable Cron Jobs:', 'botwriter'); ?></label>
        <input type="hidden" name="botwriter_cron_active" value="0"> <!-- Hidden field to handle the "unchecked" state -->
        <input type="checkbox" name="botwriter_cron_active" value="1" <?php checked(get_option('botwriter_cron_active'), '1'); ?>>
    </div>        
    <br>
    <div class="col-md-6">
    <label class="form-label"><?php esc_html_e('Email:', 'botwriter'); ?></label>    
        <input id="botwriter_email" type="text" name="botwriter_email" class="form-control" 
               value="<?php echo esc_attr(get_option('botwriter_email')); ?>" 
                style="width: 50%;">
                <div id='button_confirm_email'> 
                <a href="javascript:void(0);" onclick="botwriter_updateEmail();" class="button">Send message to this email to register plugin</a>                
                </div>        
        <div id='response_email'>
            <?php if (get_option('botwriter_email_confirmed') == '1'): ?>
            <strong><?php esc_html_e('REGISTERED!', 'botwriter'); ?></strong>
            <?php endif; ?>
        </div>
    </div>  
    <br>    

    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('API Key:', 'botwriter'); ?></label>
        <input id="botwriter_api_key" type="text" name="botwriter_api_key" class="form-control" 
               value="<?php echo esc_attr(get_option('botwriter_api_key')); ?>" 
                style="width: 50%;">        
        <p>
            <?php 
            esc_html_e('Modify the API Key if you subscribe to a payment plan, otherwise leave it as it is. If you need a new API Key, you can get it from:', 'botwriter');                
            ?>
            <a href="https://wpbotwriter.com" target="_blank">BotWriter.com</a>
        </p>
    </div>    
    <br>
    <input type="submit" value="<?php esc_attr_e('Save Settings', 'botwriter'); ?>" id="submit" class="button-primary" name="submit">  
    <?php
}


?>
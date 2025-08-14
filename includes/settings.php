<?php
// Global notice variable to collect errors
$notice = '';
$message = '';

function botwriter_settings_page_handler() {
    global $notice, $message;

    // Verifica permisos
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.', 'botwriter'));
    } 

    // Manejo de opciones enviadas
    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), basename(__FILE__))) {
        // Actualiza las opciones en la base de datos
        $settings = array(
            'botwriter_ai_image_size' => isset($_POST['botwriter_ai_image_size']) ? sanitize_text_field(wp_unslash($_POST['botwriter_ai_image_size'])) : 'square_hd',
            'botwriter_sslverify' => isset($_POST['botwriter_sslverify']) ? sanitize_text_field(wp_unslash($_POST['botwriter_sslverify'])) : 'yes',
            'botwriter_email' => isset($_POST['botwriter_email']) ? sanitize_email(wp_unslash($_POST['botwriter_email'])) : get_option('admin_email'),
            'botwriter_api_key' => isset($_POST['botwriter_api_key']) ? sanitize_text_field(wp_unslash($_POST['botwriter_api_key'])) : '',
            'botwriter_cron_active' => isset($_POST['botwriter_cron_active']) ? sanitize_text_field(wp_unslash($_POST['botwriter_cron_active'])) : '1',
            'botwriter_paused_tasks' => isset($_POST['botwriter_paused_tasks']) ? sanitize_text_field(wp_unslash($_POST['botwriter_paused_tasks'])) : '2',
            'botwriter_openai_api_key' => isset($_POST['botwriter_openai_api_key']) ? sanitize_text_field(wp_unslash($_POST['botwriter_openai_api_key'])) : '',
        );

        $settings["botwriter_plan_id"] = 0; // free plan

        foreach ($settings as $key => $value) {
            // Encripta la OPEN API Key antes de guardarla
            if ($key === 'botwriter_openai_api_key') {
                // Validar y encriptar
                if (!empty($value)) {
                    if (botwriter_open_api_key_validate($value)) {
                        $value = botwriter_encrypt_api_key($value);
                    } else {
                        $value = get_option('botwriter_openai_api_key'); // Mantener valor anterior si la validación falla
                        $notice .= __('Failed to validate the OpenAI API Key. Please check the entered value.', 'botwriter') . '<br>';
                    }
                } else {
                    $value = ''; // Permitir borrar la API key
                }
            }
            update_option($key, $value);
        }

        // Si no hay errores, mostrar mensaje de éxito
        if (empty($notice)) {
            $message = __('Settings saved successfully.', 'botwriter');
        }

        // Si las 2 primeras letras de la api key son PK, actualizar plan_id
        if (substr(get_option('botwriter_api_key'), 0, 2) == 'PK') {
            $plan_id = substr(get_option('botwriter_api_key'), 2, 6);
            update_option('botwriter_plan_id', $plan_id);
        } else {
            update_option('botwriter_plan_id', 0);
        }

        // Activar o desactivar cron job
        if (get_option('botwriter_cron_active') == '1') {
            botwriter_scheduled_events_plugin_activate();
        } else {
            botwriter_scheduled_events_plugin_deactivate();
        }
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
        <div id="notice" class="error"><p><?php echo wp_kses_post($notice); ?></p></div>
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
        <div id='subscription'></div>
        <div id="response_div" style="min-height: 50px; height: auto;"></div>
        <h3>Other Tools</h3>
        <a href="javascript:void(0);" onclick="botwriter_reset_super1();" class="button-primary"><?php esc_html_e('Reset Plugin Super Task', 'botwriter'); ?></a>
    </div>
    <?php
}

function botwriter_settings_meta_box_handler() {
    $settings = array(
        'botwriter_ai_image_size' => get_option('botwriter_ai_image_size', 'square_hd'),
        'botwriter_sslverify' => get_option('botwriter_sslverify', 'yes'),
        'botwriter_cron_active' => get_option('botwriter_cron_active', '1'),
        'botwriter_paused_tasks' => get_option('botwriter_paused_tasks', '2'),
    );

    ?>
    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Your OpenAI API Key:', 'botwriter'); ?></label>
        <div style="display: flex; align-items: center;">
            <input id="botwriter_openai_api_key" type="password" name="botwriter_openai_api_key" class="form-control"
                   value="<?php echo esc_attr(botwriter_decrypt_api_key(get_option('botwriter_openai_api_key'))); ?>" style="width: 50%; margin-right: 10px;">
            <button type="button" id="toggle_api_key" class="button"><?php esc_html_e('Show', 'botwriter'); ?></button>
        </div>
        <p class="description"><?php esc_html_e('Enter your OpenAI API Key. Click the button to show or hide the key.', 'botwriter'); ?></p>
    </div>
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
        <input type="hidden" name="botwriter_cron_active" value="0">
        <input type="checkbox" name="botwriter_cron_active" value="1" <?php checked(get_option('botwriter_cron_active'), '1'); ?>>
    </div>
    <br>

    <div class="col-md-6">
        <label class="form-label"><?php esc_html_e('Email:', 'botwriter'); ?></label>
        <input id="botwriter_email" type="text" name="botwriter_email" class="form-control"
               value="<?php echo esc_attr(get_option('botwriter_email')); ?>" style="width: 50%;">
        <div id='button_confirm_email'>
            <a href="javascript:void(0);" onclick="botwriter_updateEmail();" class="button"><?php esc_html_e('Send message to this email to register plugin', 'botwriter'); ?></a>
        </div>
        <div id='response_email'>
            <?php if (get_option('botwriter_email_confirmed') == '1'): ?>
            <strong><?php esc_html_e('REGISTERED!', 'botwriter'); ?></strong>
            <?php endif; ?>
        </div>
    </div>
    <br>

    <div class="col-md-6" style="display:none;">
        <label class="form-label"><?php esc_html_e('API Key:', 'botwriter'); ?></label>
        <input id="botwriter_api_key" type="text" name="botwriter_api_key" class="form-control"
               value="<?php echo esc_attr(get_option('botwriter_api_key')); ?>" style="width: 50%;">
        <p>
            <?php esc_html_e('Modify the API Key if you subscribe to a payment plan, otherwise leave it as it is. If you need a new API Key, you can get it from:', 'botwriter'); ?>
            <a href="https://wpbotwriter.com" target="_blank">BotWriter.com</a>
        </p>
    </div>

    <br>

    <input type="submit" value="<?php esc_attr_e('Save Settings', 'botwriter'); ?>" id="submit" class="button-primary" name="submit">
    <?php
}

// Encriptar la API key usando OpenSSL con AES-256-ECB
function botwriter_encrypt_api_key($api_key) {
    global $notice;

    if (!function_exists('openssl_encrypt')) {
        // Fallback: guardar sin encriptar
        update_option('botwriter_openai_api_key', $api_key);
        $notice .= __('The OpenSSL extension is not available. The API Key will be stored unencrypted, which is not secure. Contact your hosting provider.', 'botwriter') . '<br>';
        return $api_key;
    }

    if (!defined('AUTH_KEY')) {
        $notice .= __('AUTH_KEY not found in wp-config.php. Please configure WordPress security keys.', 'botwriter') . '<br>';
        return get_option('botwriter_openai_api_key');
    }

    // Derivar una clave de 32 bytes a partir de AUTH_KEY
    $key = hash('sha256', AUTH_KEY, true);

    // Encriptar la API key con AES-256-ECB
    $encrypted = openssl_encrypt($api_key, 'AES-256-ECB', $key, 0);
    if ($encrypted === false) {
        $notice .= __('Failed to encrypt the API Key.', 'botwriter') . '<br>';
        return get_option('botwriter_openai_api_key');
    }

    // Almacenar el texto cifrado en wp_options
    $encrypted_base64 = base64_encode($encrypted);
    update_option('botwriter_openai_api_key', $encrypted_base64);

    return $encrypted_base64;
}

// Desencriptar la API key
function botwriter_decrypt_api_key($encrypted_api_key) {
    global $notice;

    if (!function_exists('openssl_decrypt')) {
        return $encrypted_api_key;
    }

    if (!defined('AUTH_KEY')) {
        $notice .= __('AUTH_KEY not found in wp-config.php. Unable to decrypt the API Key.', 'botwriter') . '<br>';
        return false;
    }

    $key = hash('sha256', AUTH_KEY, true);
    $encrypted = base64_decode($encrypted_api_key);

    if (!$encrypted) {
        $notice .= __('Unable to decode the stored API Key.', 'botwriter') . '<br>';
        return false;
    }

    $decrypted = openssl_decrypt($encrypted, 'AES-256-ECB', $key, 0);
    if ($decrypted === false) {
        $notice .= __('Unable to decrypt the API Key. Verify that WordPress security keys have not changed.', 'botwriter') . '<br>';
        return false;
    }

    return $decrypted;
}

// Validate OpenAI API Key format and test it
function botwriter_open_api_key_validate($input) {
    global $notice;

    $input = sanitize_text_field($input);

    // Validar el formato básico (por ejemplo, que comience con "sk-")
    if (strpos($input, 'sk-') !== 0) {
        $notice .= __('The OpenAI API Key must start with "sk-".', 'botwriter') . '<br>';
        return false;
    }

    // Realizar una solicitud de prueba a la API de OpenAI
    $response = wp_remote_get('https://api.openai.com/v1/models', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $input,
            'Content-Type' => 'application/json',
        ),
        'timeout' => 15,
    ));

    if (is_wp_error($response)) {
        $notice .= __('Error validating the API Key: ', 'botwriter') . $response->get_error_message() . '<br>';
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $notice .= sprintf(__('The OpenAI API Key is invalid or lacks access. Error code: %s', 'botwriter'), $response_code) . '<br>';
        return false;
    }

    return true;
}
?>
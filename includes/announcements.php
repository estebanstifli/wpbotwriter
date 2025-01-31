<?php

// Add an action to the 'admin_notices' hook to execute our function
add_action('admin_notices', 'botwriter_create_alert');

// Function to create admin alerts in the WordPress dashboard
function botwriter_create_alert() {
    // Get the alerts stored in WordPress options
    $alerts = get_option('botwriter_alerts');

    // Get the plugin settings
    $settings = get_option('botwriter_settings');

    // Unserialize the settings if they are not an array
    $settings = $settings ? unserialize($settings) : array(); // Use array() for PHP 5

    $announcements = get_option('botwriter_announcements'); // General announcements    
    $has_announcement = false;    
    if (!empty($announcements)) {
        foreach ($announcements as $announcement_id => $announcement) {                
            if (isset($announcement['active']) && $announcement['active'] == "1") {
                $has_announcement = true;
            }
        }
    }

    // Display the alerts or announcements if they exist
    if ($has_announcement) {
        echo '<div class="notice notice-info is-dismissible">'; 
        echo '<p><strong>BotWriter Announcement:</strong></p>';

        // Display general alerts
        if (!empty($alerts)) { 
            echo '<p>' . esc_html($alerts) . '</p>';
        }

        if (!empty($announcements)) {
            foreach ($announcements as $announcement_id => $announcement) {                
                if (isset($announcement['active']) && $announcement['active'] == "1") {
                    echo '<p>' . esc_html($announcement['title']) . ': ' . wp_kses_post($announcement['message']) . '</p>';
                    echo '<button data-announcement-id="' . esc_attr($announcement_id) . '" class="button dismiss-announcement">Dismiss</button>';
                }
            }
        }

        // Button to upgrade membership 
        echo '<p><a href="' . esc_url('https://wpbotwriter.com') . '" target="_blank" class="button button-primary">' . esc_html__('Upgrade Membership', 'botwriter') . '</a></p>';
        echo '</div>';

        // Add JavaScript code to handle the "Dismiss" event
        echo '<script type="text/javascript">
            jQuery(document).on("click", ".dismiss-announcement", function() {
                var announcement_id = jQuery(this).data("announcement-id");

                var data = {
                    action: "botwriter_dismiss_announcement",
                    security: "' . esc_js(wp_create_nonce("botwriter_dismiss_nonce")) . '",
                    announcement_id: announcement_id
                };
                console.log(data);

                jQuery.post(ajaxurl, data, function(response) {
                    if(response.success) {
                        location.reload(); // Reload the page after hiding the announcement
                    }
                });
            });
        </script>';
    }
}



function botwriter_dismiss_announcement() {
  check_ajax_referer('botwriter_dismiss_nonce', 'security');
  
  if (isset($_POST['announcement_id'])) {
      $announcement_id = sanitize_text_field(wp_unslash($_POST['announcement_id']));
      $announcements = get_option('botwriter_announcements'); 

        if (isset($announcements[$announcement_id])) {
            $announcements[$announcement_id]['active'] = 0;
            update_option('botwriter_announcements', $announcements);
        }
      
      wp_send_json_success();
  } else {
      wp_send_json_error();
  }
}
add_action('wp_ajax_botwriter_dismiss_announcement', 'botwriter_dismiss_announcement');



function botwriter_announcements_add($title, $message) {    
    $announcements = get_option('botwriter_announcements', []);

    foreach ($announcements as $announcement) {
        if ($announcement['title'] == $title && $announcement['message'] == $message && $announcement['active'] == 1) {
            return;
        }
    }
    
    $new_announcement = [
        'title' => $title,
        'message' => $message,
        'active' => 1
    ];

    $announcements[] = $new_announcement;    
    update_option('botwriter_announcements', $announcements);
}


// Función que se ejecuta al activar el plugin
/*
function botwriter_announcements_activation() {
  if ( ! wp_next_scheduled( 'botwriter_fetch_announcements' ) ) {
      wp_schedule_event( time(), 'ten_minutes', 'botwriter_fetch_announcements' );
  }
}
register_activation_hook( __FILE__, 'botwriter_announcements_activation' );

// Función que se ejecuta al desactivar el plugin
function botwriter_announcements_deactivation() {
  wp_clear_scheduled_hook( 'botwriter_fetch_announcements' );
}
register_deactivation_hook( __FILE__, 'botwriter_announcements_deactivation' );

// Añadir eventos programados (intervalos)
function botwriter_custom_intervals( $schedules ) {
  $schedules['ten_minutes'] = array(
      'interval' => 600, // 600 segundos = 10 minutos
      'display'  => __( 'Every 10 Minutes' ),
  );
  return $schedules;
}
add_filter( 'cron_schedules', 'botwriter_custom_intervals' );

// Función para obtener anuncios de la API
function botwriter_fetch_announcements() {
  // Obtener la opción botwriter_settings y deserializar
  $settings_option = get_option( 'botwriter_settings' );
  
  if ( empty( $settings_option ) ) {
      return; // Si no hay configuración, detener
  }

  // Obtener el valor de api_key de la configuración
  $settings = maybe_unserialize( $settings_option );
  $api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

  $api_url = 'https://https://wpbotwriter.com/public//announcements.php';

  // Configurar la solicitud API según si hay api_key
  $request_url = ! empty( $api_key ) ? add_query_arg( 'api_key', $api_key, $api_url ) : $api_url;

  // Hacer la solicitud a la API
  $response = wp_remote_get( $request_url, array(
      'timeout'   => 15,
      'sslverify' => false,
      'headers'   => array(
          'Content-Type' => 'application/json',
          'Accept'       => 'application/json',
      )
  ) );

  if ( is_wp_error( $response ) ) {
      return; // Si hay un error, detener
  }

  $body = wp_remote_retrieve_body( $response );
  $announcements = json_decode( $body, true );

  if (  is_array( $announcements ) ) {
      // Guardar los anuncios en la opción de WordPress
      update_option( 'botwriter_announcements', $announcements );
  }
}

add_action( 'botwriter_fetch_announcements', 'botwriter_fetch_announcements' );
*/

?>
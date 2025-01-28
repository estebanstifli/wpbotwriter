<?php

// Agregar una acción al hook 'admin_notices' para ejecutar nuestra función
add_action('admin_notices', 'wpbotwriter_create_alert');

// Función para crear alertas administrativas en el panel de WordPress
function wpbotwriter_create_alert() {
    // Obtener las alertas almacenadas en las opciones de WordPress
    $alerts = get_option('wpbotwriter_alerts');

    // Obtener la configuración del plugin
    $settings = get_option('wpbotwriter_settings');

    // Deserializar la configuración si no es un array
    $settings = $settings ? unserialize($settings) : array(); // Usar array() para PHP 5

   
    $announcements = get_option('wpbotwriter_announcements'); // Anuncios generales
    $has_announcement = false;
    foreach ($announcements as $announcement_id => $announcement) {                
        if (isset($announcement['active']) && $announcement['active'] == "1") {
            $has_announcement = true;
        }
    }

    
    // Mostrar las alertas o anuncios si existen
    if ($has_announcement) {
        echo '<div class="notice notice-info is-dismissible">'; 
        echo '<p><strong>Wp BotWriter Announcement:</strong></p>';
 
        // Mostrar alertas generales
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

        // Botón para mejorar la membresía
        echo '<p><a href="https://wpbotwriter.com" target="_blank" class="button button-primary">Upgrade Membership</a></p>';
        echo '</div>';

        // Añadir código JavaScript para manejar el evento de "Dismiss"
        echo '<script type="text/javascript">
            jQuery(document).on("click", ".dismiss-announcement", function() {
                var announcement_id = jQuery(this).data("announcement-id");

                var data = {
                    action: "wpbotwriter_dismiss_announcement",
                    security: "' . wp_create_nonce("wpbotwriter_dismiss_nonce") . '",
                    announcement_id: announcement_id
                };
                console.log(data);

                jQuery.post(ajaxurl, data, function(response) {
                    if(response.success) {
                        location.reload(); // Recargar la página después de ocultar el anuncio
                    }
                });
            });
        </script>';
    }

    
    /*
    if (empty($settings['api_email']) || empty($settings['api_key'])) {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>wpbotwriter Setup Required</strong></p>';
        echo '<p>wpbotwriter no está configurado correctamente. Para usar este plugin, necesitas completar el proceso de configuración.</p>';
        echo '<p>Si no completas la configuración, wpbotwriter no funcionará correctamente.</p>';
        echo '<p><a href="admin.php?page=wpbotwriter-setup" class="button button-primary">Go to Setup</a></p>';
        echo '</div>';
    }
    */
}


// Función que maneja la solicitud AJAX
function wpbotwriter_dismiss_announcement() {
  check_ajax_referer('wpbotwriter_dismiss_nonce', 'security');
  
  if (isset($_POST['announcement_id'])) {
      $announcement_id = sanitize_text_field($_POST['announcement_id']);
      $announcements = get_option('wpbotwriter_announcements'); 

        if (isset($announcements[$announcement_id])) {
            $announcements[$announcement_id]['active'] = 0;
            update_option('wpbotwriter_announcements', $announcements);
        }
      
      wp_send_json_success();
  } else {
      wp_send_json_error();
  }
}
add_action('wp_ajax_wpbotwriter_dismiss_announcement', 'wpbotwriter_dismiss_announcement');




function wpbotwriter_announcements_add($title, $message) {    
    $announcements = get_option('wpbotwriter_announcements', []);
    // comprobar que no exista ya el anuncio
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
    update_option('wpbotwriter_announcements', $announcements);
}


// Función que se ejecuta al activar el plugin
/*
function wpbotwriter_announcements_activation() {
  if ( ! wp_next_scheduled( 'wpbotwriter_fetch_announcements' ) ) {
      wp_schedule_event( time(), 'ten_minutes', 'wpbotwriter_fetch_announcements' );
  }
}
register_activation_hook( __FILE__, 'wpbotwriter_announcements_activation' );

// Función que se ejecuta al desactivar el plugin
function wpbotwriter_announcements_deactivation() {
  wp_clear_scheduled_hook( 'wpbotwriter_fetch_announcements' );
}
register_deactivation_hook( __FILE__, 'wpbotwriter_announcements_deactivation' );

// Añadir eventos programados (intervalos)
function wpbotwriter_custom_intervals( $schedules ) {
  $schedules['ten_minutes'] = array(
      'interval' => 600, // 600 segundos = 10 minutos
      'display'  => __( 'Every 10 Minutes' ),
  );
  return $schedules;
}
add_filter( 'cron_schedules', 'wpbotwriter_custom_intervals' );

// Función para obtener anuncios de la API
function wpbotwriter_fetch_announcements() {
  // Obtener la opción wpbotwriter_settings y deserializar
  $settings_option = get_option( 'wpbotwriter_settings' );
  
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
      update_option( 'wpbotwriter_announcements', $announcements );
  }
}

add_action( 'wpbotwriter_fetch_announcements', 'wpbotwriter_fetch_announcements' );
*/

?>
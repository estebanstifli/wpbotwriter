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
                    echo '<button data-announcement-id="' . esc_attr($announcement_id) . '" class="button botwriter-dismiss-announcement">Dismiss</button>';
                }
            }
        }

        // Button to upgrade membership 
        echo '<p><a href="' . esc_url('https://wpbotwriter.com') . '" target="_blank" class="button button-primary">' . esc_html__('Upgrade Membership', 'botwriter') . '</a></p>';
        echo '</div>';

        
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



<?php
 
// Add an action to the 'admin_notices' hook to execute our function
add_action('admin_notices', 'botwriter_create_alert');

// Function to create admin alerts in the WordPress dashboard
function botwriter_create_alert() {
    // Get the alerts, settings, API key, and announcements
    $alerts = get_option('botwriter_alerts');
    $settings = get_option('botwriter_settings');
    $api_key = get_option('botwriter_openai_api_key');
    $announcements = get_option('botwriter_announcements', []);

    // Unserialize the settings if they are not an array
    $settings = $settings ? maybe_unserialize($settings) : [];

    // Check for active announcements
    $has_announcement = !empty($announcements) && array_filter($announcements, function($announcement) {
        return isset($announcement['active']) && $announcement['active'] == "1";
    });

    // Check if API key is missing
    $api_key_missing = empty($api_key);

    // Display notices if there are active announcements or API key is missing
    if ($has_announcement || $api_key_missing) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>' . esc_html__('BotWriter Announcement:', 'botwriter') . '</strong></p>';

        // Display API key missing notice
        if ($api_key_missing) {
            $settings_url = admin_url('admin.php?page=botwriter_settings');
            echo '<p>' . esc_html__('Please add your Open AI API Key in the settings to use BotWriter.', 'botwriter') . 
                 ' <a href="' . esc_url($settings_url) . '">' . esc_html__('Go to Settings', 'botwriter') . '</a></p>';
        }

        // Display general alerts
        if (!empty($alerts)) {
            echo '<p>' . esc_html($alerts) . '</p>';
        }

        // Display active announcements
        if (!empty($announcements)) {
            foreach ($announcements as $announcement_id => $announcement) {
                if (isset($announcement['active']) && $announcement['active'] == "1") {
                    echo '<p>' . esc_html($announcement['title']) . ': ' . wp_kses_post($announcement['message']) . '</p>';
                    echo '<button data-announcement-id="' . esc_attr($announcement_id) . '" class="button botwriter-dismiss-announcement">' . 
                         esc_html__('Dismiss', 'botwriter') . '</button>';
                }
            }
        }

       
        echo '</div>';
    }
}

// AJAX handler for dismissing announcements
function botwriter_dismiss_announcement() {
    check_ajax_referer('botwriter_dismiss_nonce', 'security');

    if (isset($_POST['announcement_id'])) {
        $announcement_id = sanitize_text_field(wp_unslash($_POST['announcement_id']));
        $announcements = get_option('botwriter_announcements', []);

        if (isset($announcements[$announcement_id])) {
            $announcements[$announcement_id]['active'] = 0;
            update_option('botwriter_announcements', $announcements);
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => 'Invalid announcement ID']);
        }
    } else {
        wp_send_json_error(['message' => 'Missing announcement ID']);
    }
}
add_action('wp_ajax_botwriter_dismiss_announcement', 'botwriter_dismiss_announcement');

// Function to add new announcements
function botwriter_announcements_add($title, $message) {
    $announcements = get_option('botwriter_announcements', []);
    $title = sanitize_text_field($title);
    $message = wp_kses_post($message);

    foreach ($announcements as $announcement) {
        if ($announcement['title'] === $title && $announcement['message'] === $message && $announcement['active'] == 1) {
            return;
        }
    }

    $announcements[] = [
        'id' => md5($title . $message . time()),
        'title' => $title,
        'message' => $message,
        'active' => 1
    ];
    update_option('botwriter_announcements', $announcements);
}

?>
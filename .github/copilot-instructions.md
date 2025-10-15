# BotWriter WordPress Plugin - AI Copilot Instructions

## Overview
BotWriter is a WordPress plugin that automates content creation using AI. It features a task-based system where users create "tasks" that automatically generate and publish WordPress posts at scheduled intervals using OpenAI GPT models.

## Core Architecture

### Plugin Structure
- **Main file**: `botwriter.php` - Plugin bootstrap, hooks, cron jobs, and AJAX handlers
- **Includes**: Modular functionality split across `/includes/` directory
- **Assets**: CSS, JS, and images in `/assets/` directory
- **Text Domain**: `botwriter` for internationalization

### Database Schema
Three custom tables manage the plugin functionality:
- `wp_botwriter_tasks` - Task configurations (what content to generate, when, how)
- `wp_botwriter_logs` - Execution history and status tracking
- `wp_botwriter_super` - Special AI-powered task results

Key task table fields: `task_name`, `writer`, `post_language`, `post_length`, `days`, `times_per_day`, `website_type`, `ai_keywords`

### Task Types & Content Sources
- **AI Generation** (`website_type: 'ai'`) - Pure AI content from keywords
- **WordPress Sites** - Rewrite content from other WP sites via REST API
- **RSS Feeds** - Transform RSS feed content
- **News API** - Generate content from news sources

## Development Patterns

### AJAX Implementation
Follow the established pattern for new AJAX endpoints:
```php
// In botwriter.php
add_action('wp_ajax_botwriter_your_action', 'your_callback_function');

// Nonce verification in callback
check_ajax_referer('botwriter_your_nonce', 'nonce');
```

JavaScript localization pattern:
```php
wp_localize_script('script-handle', 'botwriter_ajax', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('botwriter_nonce_name')
));
```

### Cron System
- **Schedule**: Every 30 minutes via `botwriter_scheduled_events_plugin_cron` hook
- **Execution**: `botwriter_scheduled_events_execute_tasks()` processes due tasks
- **Control**: Enable/disable via `botwriter_cron_active` option in settings

### API Integration Patterns
External API calls use WordPress HTTP API:
```php
$response = wp_remote_post($url, array(
    'timeout' => 30,
    'sslverify' => get_option('botwriter_sslverify') === 'yes',
    'body' => $data
));
```

Primary external service: `BOTWRITER_API_URL` (https://wpbotwriter.com/public/)

### Admin Interface Structure
- **Menu System**: Main menu with subpages for different functions
- **Page Handlers**: Functions named `{page}_page_handler()` in includes files
- **Script Loading**: Conditional loading based on `$screen->id` containing 'botwriter'
- **Bootstrap Integration**: Uses Bootstrap CSS/JS framework for UI consistency

## Key Functions & Workflow

### Task Execution Flow
1. Cron triggers `botwriter_scheduled_events_execute_tasks()`
2. Checks task schedules against `days`, `times_per_day`, `last_execution_date`
3. Sends task to external API queue via `botwriter_enviar_tarea_a_servidor()`
4. API processes task and returns generated content
5. `botwriter_actualizar_articulo_callback()` receives and publishes content

### Settings & Configuration
- **API Keys**: OpenAI key encrypted with `botwriter_encrypt_api_key()`
- **Validation**: OpenAI key validated via `/v1/models` endpoint
- **Options Pattern**: All settings prefixed with `botwriter_`

### Content Generation
Writers (AI personas): `orion`, `ai_cerebro`, etc. - each with distinct writing styles
Content types: Title, content, tags, images - all generated separately
SEO optimization built into content generation prompts

## File Organization

### Critical Files for Feature Development
- `includes/posts.php` - Task listing and management UI
- `includes/addnew.php` - Task creation forms
- `includes/settings.php` - Plugin configuration
- `includes/super.php` - Advanced AI task interface
- `includes/logs.php` - Execution history and debugging

### JavaScript Components
- `assets/js/botwriter.js` - Core functionality and AJAX handlers
- `assets/js/posts.js` - Task management interface
- `assets/js/super.js` - Advanced task creation
- `assets/js/admin-ajax-status.js` - Status updates and notifications

## Development Guidelines

### Adding New Task Types
1. Add new `website_type` option to form in `addnew.php`
2. Update task processing logic in main execution function
3. Handle new type in external API communication
4. Test cron execution and error handling

### Security Patterns
- Always use `wp_verify_nonce()` for forms and AJAX
- Sanitize inputs with `sanitize_text_field()`, `sanitize_email()`, etc.
- Use `current_user_can('manage_options')` for admin functions
- Escape outputs with `esc_html()`, `esc_attr()`, `esc_url()`

### Error Handling
- Log errors to `botwriter_logs` table with task context
- Use try-catch blocks for external API calls
- Provide user feedback via admin notices
- Implement retry mechanisms for failed tasks

Remember: This plugin relies heavily on external API communication, so always consider timeout scenarios and API rate limits when adding new features.
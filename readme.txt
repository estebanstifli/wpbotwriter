=== BotWriter ===
Contributors: estebandezafra
Donate link: https://wpbotwriter.com
Tags: automation, AI, robot, content generation
Requires at least: 4.0
Tested up to: 6.7
Stable tag: 1.3.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BotWriter is a plugin that automatically generates articles using artificial intelligence like chatgpt.

== Description ==

BotWriter allows you to automate the creation of blog posts on your website using AI. Customize parameters and let the bot write articles based on your settings.

== Installation ==

1. Upload the botwriter.zip file to your plugins folder (`/wp-content/plugins/`).
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Configure the settings under the "BotWriter" menu in the admin panel.

== Frequently Asked Questions ==

= What does BotWriter do? =
BotWriter uses AI to automatically generate content on your website.

= Can I customize the articles that are generated? =
Yes, you can configure certain parameters to guide the content creation process.

== Screenshots ==

1. Plugin settings page.

== Changelog ==

= 1.2.6 =
* Initial version of the plugin

= 1.2.9 =
* Various revisions to comply with WordPress.org guidelines

= 1.3.0 =
* Various bug fixes







== External Services ==

This plugin relies on third-party external services to generate text and images automatically, as well as to retrieve related content for your blog.

    Service’s Terms
     https://wpbotwriter.com/services-terms/

    Privacy Policy
    https://wpbotwriter.com/privacy-policy/


 Below is a summary of the external services used and the data they handle:

1. ** Endpoints on wpbotwriter.com**  
   These endpoints are part of the service provided by our platform and are used for various plugin functionalities:  
   - **Activation:**  
     - Endpoint: `https://wpbotwriter.com/public/activation.php`  
     - **Purpose:** Validates and activates the plugin.  

    **Generate Posts and Images**  
  - **Endpoints:**  
    - `https://wpbotwriter.com/public/redis_api_cola.php`  
    - `https://wpbotwriter.com/public/redis_api_finish.php`  
  - **Purpose:** Automatically generate posts and images.

   **Get User Data Subscription**  
  - **Endpoint:** `https://wpbotwriter.com/public/getUserData.php`  
  - **Purpose:** Receive user subscription data for users with the Pro version of the plugin, including version, subscription date, and number of posts per month.

  - **Email Confirmation:**  
    - Endpoint: `https://wpbotwriter.com/public/envio_email_confirmacion.php`  
    - **Purpose:** Sends email confirmations to register the plugin for email, optional.

2. **OpenAI API**  
   - **Purpose:** Generates text content automatically.  
   - **Data Transmitted:** The plugin sends user-supplied prompts and configuration parameters when requesting text generation.  
   - **When:** Each time the plugin generates content for related articles.  
   - **Terms and Privacy:**  
     [OpenAI Terms of Service](https://openai.com/terms/)  
     [OpenAI Privacy Policy](https://openai.com/privacy/)

3. **Fal.ai API**  
   - **Purpose:** Generates images automatically.  
   - **Data Transmitted:** The plugin sends keywords and style parameters when an image is required.  
   - **When:** Whenever an image is generated to accompany an article.  
   - **Terms and Privacy:**  
     [Fal.ai Terms of Service](https://fal.ai/terms)  
     [Fal.ai Privacy Policy](https://fal.ai/privacy)


By using this plugin, you acknowledge that it communicates with these third-party and external services, and that the data is transmitted as described above. Please review the respective Terms of Service and Privacy Policies for each service for further details.


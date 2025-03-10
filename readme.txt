=== BotWriter ===
Contributors: estebandezafra
Donate link: https://wpbotwriter.com
Tags: gpt, openai, AI, content generation, auto, chatgpt
Requires at least: 4.0
Tested up to: 6.7
Stable tag: 1.3.6
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

BotWriter allows you to automate the creation of blog posts on your website using AI, like Chat GPT. It uses the latest OpenAI models to generate articles.

== Description ==

Do you want to keep your WordPress blog updated automatically with fresh, SEO-optimized content? 📈 With Botwriter, artificial intelligence takes care of writing and publishing articles for you! 
You can go on vacation or stop worrying about your blog:

🔹 Automatically publish content every day / every week
🔹 Generate 100% original articles with HD images
🔹 Optimize your blog to rank higher on Google
🔹 Set it up in minutes and let AI do the rest

In this video, we’ll show you how to set up Botwriter in just a few steps so your blog stays active effortlessly. Take advantage of AI-powered automation and streamline your content strategy! 🚀
[youtube https://www.youtube.com/watch?v=PatljFLDNwI]

BotWriter has been designed to simplify content creation for blogs, websites, and online stores, allowing users to generate optimized articles without the need to manually write every word. Additionally, it can produce high-quality images automatically, enhancing the generated content with a more visually appealing experience. Its functionality is based on the integration with artificial intelligence models that analyze the topic and produce coherent and well-structured texts.

BotWriter is capable of creating entire blog content in minutes! Check out the example:
[youtube https://www.youtube.com/watch?v=MpJ0KHKRYi8]

How to Auto-Generate Articles from an RSS Feed | BotWriter + NASA RSS
[youtube https://youtu.be/7aq0496XwY0]


WP Bot Writer is a WordPress plugin that leverages artificial intelligence (AI) to rewrite existing content or generate completely new content. It integrates with sources such as WordPress, RSS, and Google News to provide unique and SEO-optimized content.

With BotWriter, you can automate the creation of blog posts on your website using AI. Customize parameters to suit your needs, and let the bot generate high-quality articles based on your settings, ensuring fresh and relevant content for your audience.


== Installation ==

1. Upload the botwriter.zip file to your plugins folder (`/wp-content/plugins/`).
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Configure the settings under the "BotWriter" menu in the admin panel.

== Frequently Asked Questions ==

= What is WP BotWriter? =
Botwriter is an AI-powered automation tool that writes and publishes articles on WordPress automatically. It helps bloggers and website owners keep their content updated effortlessly, improving SEO rankings and boosting organic traffic.

= How does AI content rewriting work? =
WP BotWriter can retrieve content from a WordPress site, RSS sources, or news feeds, then apply AI to rewrite it, creating fresh and unique posts. Additionally, WP BotWriter can generate attractive titles, relevant tags, and even thumbnails for each post.

= Can I generate original content from scratch? =
Yes, WP BotWriter allows you to create completely new content using AI, eliminating the need for manual writing. Each generated piece includes optimized titles, tags, and appealing thumbnails.

= Is the generated content SEO-friendly? =
Absolutely. WP BotWriter focuses on search engine optimization (SEO), ensuring that every title, tag, and piece of content is highly relevant.

= How does WP BotWriter prevent spam or duplicate content? =
WP BotWriter employs advanced AI technologies to detect and prevent the generation of irrelevant posts, spam, or content that is too similar to existing materials.

= Can the generated content be modified? =
Yes. WP BotWriter can be optionally configured to save posts as drafts, allowing you to review and edit them before publishing.

= Is the article image created automatically? =
Yes, WP BotWriter generates ultra-realistic, high-quality images based on the article’s content.

= Can content generation be automated? =
WP BotWriter can fully automate content creation on specified days of the week, with the ability to set the number of daily posts.

= Do I need technical knowledge to use WP BotWriter? =
No, WP BotWriter is designed to be intuitive and easy to use. You can choose to use predefined prompts or fully customize content generation to suit your needs.

= Does WP BotWriter use third-party services? =
Yes, WP BotWriter is linked to third-party API services to enhance the quality and efficiency of content generation and image editing. It connects to ChatGPT from OpenAI for text generation and Fal.ai for image creation.

= How can I start using WP BotWriter? =
You just need to install the plugin on your WordPress site, configure it according to your needs, and start generating content with the power of artificial intelligence.

= Is WP BotWriter compatible with all WordPress sites? =
WP BotWriter is compatible with most WordPress sites. It is recommended to check the plugin requirements before installation to ensure compatibility.

= Can I use WP BotWriter with Google AdSense? =
Yes, WP BotWriter generates high-quality, unique content that can be used with Google AdSense to monetize your website.

= How long does it take to generate articles? =
WP BotWriter typically takes between 30 seconds to 1 minute to generate an article, depending on the complexity and length of the content.


== Screenshots ==

1. Add New AI Task 1/3.
2. Add New AI Task 2/3.
3. Add New AI Task 3/3.
4. Add Tasks

== Changelog ==

= 1.2.6 =
* Initial version of the plugin

= 1.2.9 =
* Various revisions to comply with WordPress.org guidelines

= 1.3.0 =
* Various bug fixes

= 1.3.1 =
* New options for article packs. Super Tasks.

= 1.3.2 =

* bug fix

= 1.3.3 =

* Minor changes

= 1.3.4 =

* bug fix

= 1.3.5 =

* 2 video tutorials

= 1.3.6 =

* 1 new video tutorial



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


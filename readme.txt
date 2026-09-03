=== Repagify ===
Contributors: afriflare
Tags: content, repurposing, ai, seo, social media
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn posts you have already published into SEO blog posts, LinkedIn posts, X threads and newsletters with Repagify.

== Description ==

Repagify connects your WordPress site to [Repagify](https://repagify.afriflare.com/), an AI content repurposing platform.

Most sites are sitting on years of published writing that nobody reads any more. Repagify helps you get more out of it: point the plugin at your archive and turn a post you published two years ago into a LinkedIn post, an X thread, a newsletter issue, or a refreshed SEO blog post.

This release covers the connection between your site and your Repagify account. The archive scanner and the generation flow arrive in the next releases.

= What this version does =

* Stores your Repagify API key securely in your site options
* Lets you point the plugin at a development API instance if you are working on one
* Tests the connection and reads back your plan tier and remaining conversions

= Privacy and external services =

This plugin communicates with the Repagify API at `https://repagify.afriflare.com/api/v1` (or a different base URL, if you set one on the settings page).

Requests are only made when you explicitly ask for one, such as pressing the "Test connection" button. The plugin makes no external requests on normal page loads, and no post content leaves your site in this release.

When you press "Test connection", your API key is sent to Repagify in an `Authorization` header so that your account can be identified. See the Repagify [terms of service](https://repagify.afriflare.com/terms) and [privacy policy](https://repagify.afriflare.com/privacy).

== Installation ==

1. Upload the `repagify-plugin` folder to `/wp-content/plugins/`, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins screen.
3. Go to **Repagify → Settings** and paste the API key from your Repagify account.
4. Press **Test connection** to confirm the site can reach the service.

== Frequently Asked Questions ==

= Do I need a Repagify account? =

Yes. The plugin is a client for the Repagify service, and you need an API key from your account for it to do anything.

= Where is my API key stored? =

In your site's options table. It is never written to logs, never included in error messages, and never shown in full on the settings page once saved — only a mask of the last four characters is displayed.

= Can I point this at a development server? =

Yes. The API base URL is editable on the settings page.

= Will this change my published posts? =

No. Nothing in the plugin edits or publishes a post without you explicitly asking for it.

== Screenshots ==

1. The Repagify settings page, with the connection test result.

== Changelog ==

= 0.1.0 =
* Initial release.
* Settings page storing the Repagify API key and API base URL.
* API client with distinct, readable handling for invalid keys, missing endpoints, rate limits and server errors.
* Connection test that reports your plan tier and remaining conversions.

== Upgrade Notice ==

= 0.1.0 =
First release. Connects your site to your Repagify account.

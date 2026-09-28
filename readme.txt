=== Repagify ===
Contributors: afriflare
Tags: content, repurposing, seo, social media, ai
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Find the dormant posts in your archive worth reusing, then turn the best of them into blog posts, LinkedIn posts, X threads and newsletters.

== Description ==

Most sites are sitting on years of published writing nobody reads any more. Repagify finds the pieces still worth something and helps you give them a second life.

= The scanner works offline =

The archive scanner needs no account, no API key and no internet connection. It runs entirely on your own server:

* Scans every published post and scores it out of 100 for repurposing potential
* Ranks on word count, heading structure, how long the post has lain dormant, and whether you have reused it before
* Explains each score in plain language — "2,400 words, well structured, never repurposed"
* Sorts, filters and pages through the results, and tells you how many words of dormant content you are sitting on
* Batches the scan automatically on large archives so it never times out

This is most of what the plugin does, and it is available to everyone.

= Generating content needs an account =

Turning a post into something new happens on Repagify's servers, so that part needs a free [Repagify](https://repagify.afriflare.com/) account. You pick one post, choose a format and tone, and get back content you can copy and use wherever you like.

Available formats are a blog post, a LinkedIn post, an X thread and a newsletter, each in one of five tones.

Repagify plans include different numbers of generations. Free accounts include a small number, paid plans include more, and the dashboard always shows how many you have left before you spend one.

= What this plugin will not do =

* It will not edit, publish or change any of your posts. It writes one post meta value recording what you have already repurposed, and nothing else.
* It will not generate in bulk. One post at a time, so you can read each result before spending another generation.
* It will not make an external request on a normal page load, and it sends no telemetry or analytics of any kind.

= Open source =

Repagify is free software, licensed GPLv2 or later. Development happens in the open and patches are welcome — see CONTRIBUTING.md in the repository.

Source code: https://github.com/Gwatso/Repagify-AI-Plugin

== External services ==

This plugin connects to the Repagify API, a third-party service operated by Afriflare, to generate content from posts you choose. The plugin cannot generate anything without it.

**Service:** Repagify
**Endpoint:** `https://repagify.afriflare.com/api/v1`
**Provider:** Afriflare — https://repagify.afriflare.com/

= What is sent, and when =

The plugin contacts Repagify in exactly three situations, all of them triggered by you:

1. **When you press "Test connection"** on the settings screen. Your API key is sent so the service can identify your account. No post content is sent. The service replies with your plan tier and how many generations you have used and have left.

2. **When you press "Repurpose" and then "Generate"** on a post in the dashboard. The following is sent, and nothing else:
   * Your API key, to identify your account
   * The plain text of that single post — shortcodes, HTML tags and block markup stripped out first
   * The output format you chose (blog, linkedin, twitter or newsletter)
   * The tone you chose
   * The target keyword, only if you typed one and only for blog output

3. **When a Repagify admin screen is opened**, to read your plan tier and remaining generations so the dashboard can show them. Your API key is sent; no post content is. This result is cached for five minutes, so opening the screen repeatedly does not repeat the request. It does not happen at all if you have not saved an API key.

= What is never sent =

* No post content is transmitted during scanning, scoring, sorting, filtering or paging. All of that happens on your server.
* No content from any post other than the one you explicitly selected.
* No data on a normal page load, on plugin activation, or on any front-end request.
* No telemetry, analytics, usage statistics, site URL, administrator email or user information of any kind.

= Working without an account =

The archive scanner and the opportunity dashboard work fully with no Repagify account, no API key and no network access. If no API key is saved, the plugin makes no external request whatsoever, and the generation controls are disabled.

= Terms and privacy =

Using the generation feature means sending your content to Repagify, and is subject to their terms:

* Terms of service: https://repagify.afriflare.com/terms
* Privacy policy: https://repagify.afriflare.com/privacy

== Installation ==

1. Upload the `repagify-plugin` folder to `/wp-content/plugins/`, or install the plugin through the Plugins screen in WordPress.
2. Activate the plugin through the Plugins screen.
3. Go to **Repagify → Dashboard**. The scanner runs immediately — no account needed.
4. To generate content, go to **Repagify → Settings**, paste the API key from your Repagify account, and press **Test connection**.

== Frequently Asked Questions ==

= Do I need a Repagify account? =

Not to scan. The archive scanner and the opportunity dashboard work fully offline, with no account and no API key, and that is most of what the plugin does.

You need an account to generate content, because the generation runs on Repagify's servers.

= Does my content leave my site? =

Only when you ask it to. Scanning, scoring and filtering happen entirely on your server with no network access.

When you press Generate on a post, the plain text of that one post is sent to Repagify so it can be repurposed. No other post is sent, and nothing is sent in the background. See the External services section above for the full detail.

= How many generations do I get? =

That depends on your Repagify plan. Free accounts include a limited number, and paid plans include more. The dashboard shows how many you have left before you use one, and tells you when you have run out.

= Why can I not create an API key on my plan? =

API keys are currently issued on Repagify Pro and Agency plans. Free and Creator support is coming. Until then the scanner still works on any plan, or with no account at all.

= Can I repurpose my whole archive at once? =

No, and that is deliberate. Each generation takes the better part of a minute and uses one of your plan's generations. A bulk action would spend a small allowance in a single click with nothing to show for it. The plugin works one post at a time so you can read each result before deciding on the next.

= Will this change my published posts? =

No. The plugin never edits or publishes a post. The only thing it writes is a single post meta value recording which formats you have already generated, so the dashboard can show you what has been reused.

= Why is a post I know is good scoring badly? =

The score rewards length, heading structure and time spent dormant. A short post scores low however good it is, because there is not enough there to turn into a thread or a newsletter. Hover the reason text under any score to see what drove it.

= Can I point this at a development API instance? =

Yes. The API base URL is editable on the settings page.

= Where is my API key stored? =

In your site's options table. It is never written to logs, never included in error messages, never placed in a page attribute, and never shown in full on the settings page once saved — only a mask of the last four characters is displayed.

== Screenshots ==

1. The opportunity dashboard, ranking every published post by repurposing potential, with the plan and remaining generations shown at the top.
2. Choosing an output format and tone for a post, with the extracted word count shown before anything is sent.
3. A finished generation, ready to copy.
4. The settings screen, showing the saved API key as a mask and the result of a connection test.

== Changelog ==

= 0.6.0 =
* Settings and Dashboard links in the Plugins list, with a matching set on the network plugins screen for multisite.
* Documentation, Support and Report an issue links under the plugin's own row.
* View details now opens a real modal, populated from readme.txt rather than a second copy of the same text kept in code.
* Updates are served from GitHub releases until the plugin is hosted on WordPress.org, which is what makes the Enable auto-updates control appear. Auto-updates are never switched on for you.
* Release builds are produced by a workflow that verifies the tag, the Version header and the Stable tag all agree before publishing.

= 0.5.0 =
* Compliance pass for the WordPress.org directory: full GPL-2.0 licence text, a complete external services disclosure, a translation template, and repository hygiene files.
* Escaped two output statements that were safe but unescaped.
* Renamed an internal method whose name collided with a PHP function on security audit lists.
* No change to scanning, scoring, quota handling or generation.

= 0.4.0 =
* Plan and quota awareness: the dashboard shows which plan you are on and how many generations remain.
* Checks your remaining allowance before opening the generate dialog, rather than spending a request to be refused.
* Tier-appropriate guidance when an allowance runs out.
* Settings page now states which plans can currently issue an API key.

= 0.3.0 =
* Generation flow: turn a published post into a blog post, LinkedIn post, X thread or newsletter.
* Extracts clean text from post content, refusing posts too short to work with and trimming those past the conversion limit on a word boundary.
* Records each conversion against the post so the dashboard shows what has already been reused.
* Distinct, actionable handling for invalid keys, spent allowances, rate limits, server faults and network failures.

= 0.2.0 =
* Archive scanner and opportunity dashboard, scoring every published post out of 100.
* Sorting, filtering and paging, with batched scanning for large archives.
* Works entirely offline, with no account required.

= 0.1.0 =
* Initial release.
* Settings page storing the Repagify API key and API base URL.
* API client with distinct, readable handling for invalid keys, missing endpoints, rate limits and server errors.
* Connection test that reports your plan tier and remaining conversions.

== Upgrade Notice ==

= 0.6.0 =
Adds Plugins list links, a working View details modal, and updates served from GitHub releases so auto-updates can be enabled.

= 0.5.0 =
Licensing, disclosure and translation housekeeping. No functional change.

= 0.4.0 =
Shows your plan and how many generations you have left, and checks before opening the generate dialog.

= 0.3.0 =
Adds the generation flow. Turn a published post into a blog post, LinkedIn post, X thread or newsletter.

= 0.2.0 =
Adds the archive scanner and opportunity dashboard. Works with no account.

= 0.1.0 =
First release. Connects your site to your Repagify account.

=== WS Paste Cleaner ===
Contributors: webstrategy
Tags: editor, paste, word, gutenberg, cleaner
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically strip Microsoft Word formatting on paste in the WordPress editor. Compatible with Gutenberg and the Classic Editor.

== Description ==

WS Paste Cleaner intercepts paste events from Microsoft Word and removes the parasitic HTML — `MsoNormal` classes, inline styles, proprietary Office namespaces (`<o:p>`, `<w:*>`, `<v:*>`), and conditional comments — so your editor only sees clean, semantic markup.

**Features**

* Automatic cleanup on paste in Gutenberg and TinyMCE (Classic Editor)
* Three cleaning levels: Light, Moderate, Aggressive
* Built-in test zone to preview how content will be cleaned before pasting
* Local usage statistics (no remote tracking)
* No outbound API calls; cleaning happens entirely on your own server
* Fully translatable

**Cleaning levels**

* **Light** — Strips Word-only metadata (`Mso*` classes, Office namespaces, conditional comments). Keeps the rest of the structure intact.
* **Moderate** *(recommended)* — Strips all Word markup (classes, inline styles, span/font wrappers) while preserving semantic structure: headings, lists, links, strong/em.
* **Aggressive** — Plain text only. Removes all HTML and rebuilds the content as paragraphs.

**Source code**

The full source code is available at https://github.com/WordPress-freelance/ws-paste-cleaner — no build step is required, the plugin ships ready to run.

Continuous integration runs the full PHPUnit suite on PHP 7.4, 8.0, 8.1 and 8.2 on every push: https://github.com/WordPress-freelance/ws-paste-cleaner/actions

== Installation ==

1. Upload the `ws-paste-cleaner` folder to the `/wp-content/plugins/` directory, or install via Plugins → Add New.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → WS Paste Cleaner** to choose your cleaning level.
4. Paste content from Microsoft Word into any post or page — cleanup happens automatically.

== Frequently Asked Questions ==

= Does it work with Gutenberg? =

Yes, fully compatible with the block editor.

= Does it work with the Classic Editor? =

Yes, fully compatible with TinyMCE.

= Does it work with page builders like Elementor or Avada? =

Cleanup hooks into native WordPress paste events. Page builders that use their own iframe-based editor may not trigger the cleaner — this is on the roadmap for a future version.

= Will it remove formatting I want to keep? =

The Moderate level (default) preserves headings, lists, links, bold and italic. Only Word-specific noise is removed. Use Light if you want to be even more conservative, or Aggressive if you want plain text only.

= Does the plugin send my content to any external server? =

No. All HTML cleaning is performed locally on your WordPress installation by a PHP class. The pasted content never leaves your server. See the "External services" section below for the only exception (Google Fonts on the settings page UI).

= Where are the usage statistics stored? =

In the `wp_options` table on your own database, as a single integer counter. Nothing is sent anywhere.

== External services ==

This plugin uses one external service:

**Google Fonts**

The plugin's settings page (Settings → WS Paste Cleaner) loads two web fonts (Lora and Inter) from `fonts.googleapis.com` to render the WebStrategy admin design. This request is only made when an administrator opens the plugin's settings page; it never runs on the front end of the site or for visitors.

* What is sent: the standard request a browser makes to load a font (URL, user agent, IP address) — same as any site that uses Google Fonts.
* When: only when an administrator visits the plugin's settings page in `/wp-admin/`.
* Service provider: Google LLC.
* Terms of Service: https://policies.google.com/terms
* Privacy Policy: https://policies.google.com/privacy

No content typed into the WordPress editor is ever transmitted to Google or any other third party.

== Screenshots ==

1. Settings page with the three cleaning levels and the live test zone.
2. Before/after preview: raw Word HTML on the left, cleaned output on the right.
3. Statistics card showing the number of cleanups performed locally.

== Changelog ==

= 1.0.0 =
* Initial release.
* Automatic cleanup on paste in Gutenberg and TinyMCE.
* Three cleaning levels (Light, Moderate, Aggressive).
* Live test zone in the settings page.
* Local usage statistics.

== Upgrade Notice ==

= 1.0.0 =
First stable release.

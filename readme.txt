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
* No external API calls, no third-party services, no telemetry
* Fully translatable

**Cleaning levels**

* **Light** — Strips Word-only metadata (`Mso*` classes, Office namespaces, conditional comments). Keeps the rest of the structure intact.
* **Moderate** *(recommended)* — Strips all Word markup (classes, inline styles, span/font wrappers) while preserving semantic structure: headings, lists, links, strong/em.
* **Aggressive** — Plain text only. Removes all HTML and rebuilds the content as paragraphs.

**Privacy**

WS Paste Cleaner does not contact any external server. All cleaning is performed locally on your WordPress installation. Usage statistics are stored as a single counter in your own database and never leave your site.

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

= Does the plugin send my content anywhere? =

No. All cleaning is performed locally. The plugin makes no outbound HTTP requests.

== Screenshots ==

1. Settings page with the three cleaning levels and the live test zone.
2. Before/after comparison: raw Word HTML on the left, cleaned output on the right.
3. Usage statistics card showing the number of cleanups performed.

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

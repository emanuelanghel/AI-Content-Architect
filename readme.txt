=== AI Content Architect ===
Contributors: ai-content-architect
Tags: custom post types, taxonomies, AI, content modeling, admin
Requires at least: 6.2
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI Content Architect turns a plain-English description into reviewable WordPress content models.

== Description ==

This MVP lets administrators generate, review, edit, save, apply, disable, export, and import structured content model configurations. Applied models register custom post types, taxonomies, plugin-managed custom fields, and admin columns.

AI providers return JSON configuration only. The plugin validates generated/imported data before saving or applying it. No generated PHP is executed.

== Installation ==

1. Upload the ai-content-architect folder to wp-content/plugins.
2. Activate AI Content Architect.
3. Go to AI Content Architect > Settings.
4. Use the mock provider for local testing or configure an OpenAI-compatible provider.

== Changelog ==

= 0.1.0 =
Initial MVP scaffold.

=== AI Content Architect ===
Contributors: emanuelanghel
Tags: custom post types, taxonomies, content modeling, admin, artificial intelligence
Requires at least: 6.2
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create reviewable WordPress content models from plain-English prompts, then apply them as custom post types, taxonomies, and fields.

== Description ==

AI Content Architect helps administrators plan and create structured WordPress content models. Describe a content section in plain English, review the generated model, edit it, save it as a draft, and apply it only when it is ready.

The plugin can create:

* Custom post types.
* Taxonomies.
* Custom fields stored as post meta.
* Admin columns.
* Frontend field output.
* Optional fallback templates.
* Sample content for local testing.

AI Content Architect does not execute AI-generated PHP. AI providers return JSON configuration only. The plugin validates generated and imported configuration before saving or applying it.

The mock provider is available for local testing without an API key or external service request.

== External services ==

AI Content Architect does not contact external AI services while the mock provider is selected.

When an administrator selects OpenAI, OpenAI-compatible, or custom provider mode, the plugin sends the administrator's prompt, the selected model ID, and JSON-only generation instructions to the configured AI provider endpoint. If an API key is configured, the key is sent as an Authorization header. The plugin may also request the provider model list when an administrator clicks "Refresh models" or "Test connection" in Settings.

OpenAI service information:

* Service: OpenAI API
* Terms: https://openai.com/policies/terms-of-use
* Privacy policy: https://openai.com/policies/privacy-policy

OpenAI-compatible and custom providers use the base URL configured by the site administrator. Site owners are responsible for reviewing the terms and privacy policy of any configured custom provider.

AI Content Architect is not affiliated with or endorsed by OpenAI.

== Privacy and data storage ==

The plugin stores settings and saved content model configurations in the WordPress database.

API keys are stored in the WordPress options table and are not printed back into the Settings screen.

Generated custom field values are stored as WordPress post meta on the generated content items.

The plugin does not add tracking scripts and does not contact external services unless an administrator configures and uses a non-mock AI provider.

== Installation ==

1. Upload the `ai-content-architect` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins screen in WordPress.
3. Go to AI Content Architect > Settings.
4. Use the mock provider for local testing, or configure an OpenAI, OpenAI-compatible, or custom provider.

== Frequently Asked Questions ==

= Does this plugin execute AI-generated code? =

No. Providers return JSON configuration only. The plugin validates the configuration and uses WordPress APIs to register content structures.

= Can I test without an API key? =

Yes. Use the mock provider. It does not contact external services.

= What is sent to OpenAI or another provider? =

When a non-mock provider is used, the plugin sends the administrator's prompt, selected model ID, and JSON-only generation instructions. The API key is sent as an Authorization header when configured.

= Can themes override the frontend templates? =

Yes. Themes can add templates in `ai-content-architect/single-dynamic-content.php` and `ai-content-architect/archive-dynamic-content.php`.

= What happens when I delete a model? =

The model deletion confirmation lets administrators delete only the model configuration or delete the model plus generated posts and taxonomy terms. Media attachments are not deleted.

= What happens on uninstall? =

Uninstall removes the plugin settings, saved model configurations, rewrite flush flag, and provider model cache. Existing generated posts, terms, post meta, and media are not deleted by uninstall.

== Asset licensing ==

The bundled logo at `admin/images/ai-logo.png` is an original plugin asset and is licensed GPLv3 with the plugin.

== Changelog ==

= 0.1.0 =
Initial release.

== Upgrade Notice ==

= 0.1.0 =
Initial release.

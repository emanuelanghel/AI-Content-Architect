# AI Content Architect

AI Content Architect is an AI-assisted WordPress plugin that turns plain-English content ideas into reviewable, editable WordPress content models.

Instead of asking an AI model to generate PHP code, the plugin asks for structured JSON configuration. A WordPress admin can review that configuration, edit it, save it as a draft, and apply it only when it looks right.

## What It Builds

Given a prompt like:

```text
Create a job board with jobs, companies, locations, salary ranges, employment types, remote options, application deadlines, and application URLs.
```

AI Content Architect can propose:

- Custom post types
- Taxonomies
- Custom fields
- Admin columns
- Template suggestions
- Sample content
- Warnings and implementation notes

Applied models are stored as configuration and registered dynamically by the plugin. No generated PHP is executed.

## Current Status

This is an MVP/prototype plugin for local and development testing.

It currently supports:

- A mock provider for local testing
- An OpenAI-compatible provider abstraction
- Generated model validation and sanitization
- Editable model review before applying
- Draft, applied, and disabled model states
- Import/export as JSON
- Runtime CPT and taxonomy registration
- Generated field meta boxes
- Generated admin columns
- Frontend field rendering
- Fallback frontend templates
- Model cleanup options on delete

## Admin Screens

The plugin adds a WordPress admin menu:

```text
AI Content Architect
├── Architect
├── Content Models
└── Settings
```

### Architect

The Architect screen lets an administrator:

- Enter a natural-language content model prompt
- Use example prompt chips
- Generate a structured model
- Review and edit generated JSON-backed fields
- Save the model as a draft
- Apply the model to WordPress
- Optionally generate sample content

Generation never creates WordPress structures immediately. The admin must review and apply the model first.

### Content Models

The Content Models screen lets an administrator:

- View saved models
- See model status: `draft`, `applied`, or `disabled`
- Edit models
- Apply models
- Disable models
- Export model JSON
- Import model JSON as draft
- Delete models

When deleting a model, a custom confirmation popover lets the admin choose:

- Delete the model only
- Delete the model plus generated posts and taxonomy terms

Media attachments are intentionally preserved during cleanup.

### Settings

The Settings screen includes:

- Provider selection: mock or OpenAI-compatible
- API key storage without printing the key back to the browser
- Base URL
- Model name
- Strict validation
- Slug conflict prevention
- Review-before-apply enforcement
- Frontend field display toggle
- Fallback template toggle

## Frontend Rendering

Generated CPT single pages can be rendered in two ways.

### Theme Template + Plugin Injection

By default, the active theme renders the single post. AI Content Architect appends generated fields and taxonomies to `the_content` when:

- The view is singular
- The post type belongs to an applied AI Content Architect model
- Frontend field output is enabled
- The request is the main query and main loop

The output is scoped with classes such as:

```text
.aica-content
.aica-content-fields
.aica-content-field
.aica-content-field-label
.aica-content-field-value
.aica-content-taxonomies
.aica-content-term
```

### Plugin Fallback Templates

If fallback templates are enabled, the plugin can render generated CPT singles and archives using:

```text
templates/single-dynamic-content.php
templates/archive-dynamic-content.php
```

The single template displays:

- Featured image
- Post title
- Main content
- Generated custom fields
- Generated taxonomy terms

The archive template displays:

- A responsive card grid
- Featured image or placeholder
- Title
- Excerpt
- A small set of generated field values
- Taxonomy term pills
- View details link

### Theme Overrides

Themes can override plugin fallback templates by adding:

```text
your-theme/ai-content-architect/single-dynamic-content.php
your-theme/ai-content-architect/archive-dynamic-content.php
```

The plugin checks the active theme first, then falls back to its bundled templates.

## Field Formatting

Frontend generated fields are escaped and formatted by type.

Supported field types include:

- `text`
- `textarea`
- `number`
- `email`
- `url`
- `date`
- `checkbox`
- `select`
- `radio`
- `image`
- `gallery`
- `wysiwyg`

Frontend formatting behavior:

- Empty fields are skipped
- URLs are clickable links
- Email fields use `mailto:`
- Dates use the WordPress date format
- Numbers use localized number formatting
- Boolean fields render as Yes/No badges
- Images render as attachment images instead of raw IDs
- Galleries render as image grids
- WYSIWYG values use safe post HTML

## Validation and Slug Conflicts

The validator sanitizes and validates generated or imported configurations before they are saved or applied.

It checks:

- Required model name
- Valid CPT keys and slugs
- Valid taxonomy keys and slugs
- Field post type references
- Supported field types
- Admin column references
- Template references
- Sample content references
- Reserved WordPress slugs
- Conflicts with WordPress, themes, plugins, and other AI Content Architect models

Generation uses softer conflict handling so the user can still review and edit the model. Applying a model uses stricter validation so real conflicts are blocked before registration.

When editing or revalidating an existing model, the validator excludes the current model ID from conflict detection so a model does not conflict with itself.

## Model Lifecycle

Models are stored in the `aica_content_models` option.

Each model record contains:

- ID
- Name
- Status
- Config
- Created timestamp
- Updated timestamp
- Plugin version

Applied models register their generated CPTs and taxonomies on `init`.

When models are applied, disabled, or deleted, the plugin schedules rewrite flushing. This avoids stale 404s after deleting and recreating models with the same slug.

## Delete and Cleanup Behavior

Deleting a model opens a confirmation popover.

The admin can choose:

### Delete Model Only

This removes the model configuration but leaves existing posts, terms, media, and content in the database.

### Delete Model + Content

This removes:

- Posts belonging to the model's generated CPTs
- Terms belonging to the model's generated taxonomies
- The model configuration

It does not delete media attachments.

This makes it easier to remove a generated model completely and later reuse the same CPT or rewrite slug.

## Mock Provider

The mock provider is intended for local testing without an API key.

It can return different sample models based on prompt keywords, including:

- Job board
- Real estate listings
- Restaurant menu
- Movie database

This prevents every local test prompt from returning the same job-board structure.

## OpenAI-Compatible Provider

The OpenAI-compatible provider sends a chat-completions-style request to the configured endpoint and expects structured JSON back.

Settings include:

- API key
- Base URL
- Model name

The API key is saved but never printed back into the settings form.

The Settings screen includes provider connection testing and model discovery. For OpenAI and OpenAI-compatible providers, administrators can refresh the available model list from the provider API instead of typing model IDs manually. A curated fallback list is shown until a provider model list is refreshed, and advanced users can still enter a custom model ID.

Mock mode remains the recommended local testing path because it requires no API key and exercises the plugin workflow without external calls.

## Custom Providers

AI Content Architect includes a provider registry so integrations do not need to be hardcoded into the settings screen.

Built-in provider options include:

- Mock provider
- OpenAI
- OpenAI-compatible
- Custom provider

The custom provider option is intended for OpenAI-compatible local services, gateways, or third-party APIs. Developers can register additional providers with:

```php
add_filter( 'aica_ai_providers', function ( array $providers ) {
	$providers['my_provider'] = array(
		'label'           => 'My Provider',
		'description'     => 'Custom provider for AI Content Architect.',
		'class'           => My_AICA_Provider::class,
		'supports_models' => true,
		'requires_key'    => true,
		'default_base_url'=> 'https://example.com/v1',
	);

	return $providers;
} );
```

Provider classes must implement `AI_Provider_Interface`. Providers that support model refresh and connection testing can also implement `AI_Model_Provider_Interface`.

## Important Files

Bootstrap:

- `ai-content-architect.php`
- `includes/class-plugin.php`

Core:

- `includes/class-model-store.php`
- `includes/class-content-model.php`
- `includes/class-schema-validator.php`
- `includes/class-model-cleaner.php`
- `includes/class-capabilities.php`

AI providers:

- `includes/class-ai-provider-interface.php`
- `includes/class-mock-provider.php`
- `includes/class-openai-provider.php`

Registration:

- `includes/class-cpt-registrar.php`
- `includes/class-taxonomy-registrar.php`
- `includes/class-field-registrar.php`
- `includes/class-admin-columns.php`
- `includes/class-frontend-display.php`
- `includes/class-template-manager.php`

Admin:

- `admin/views/page-architect.php`
- `admin/views/page-models.php`
- `admin/views/page-settings.php`
- `admin/views/partial-model-review.php`
- `admin/js/admin.js`
- `admin/css/admin.css`

Frontend:

- `frontend/css/frontend.css`
- `templates/single-dynamic-content.php`
- `templates/archive-dynamic-content.php`

Import/export and samples:

- `includes/class-export-import.php`
- `includes/class-sample-content-generator.php`

## Installation

1. Copy the plugin folder into:

```text
wp-content/plugins/AI Content Architect
```

2. Activate the plugin in WordPress.
3. Go to:

```text
AI Content Architect > Settings
```

4. Use the mock provider for local testing, or configure an OpenAI-compatible provider.

## Local Testing Checklist

1. Go to `AI Content Architect > Architect`.
2. Generate a model from a prompt.
3. Review the generated CPTs, taxonomies, fields, columns, templates, and warnings.
4. Save as draft.
5. Apply the model.
6. Confirm the generated CPT appears in WordPress admin.
7. Add a generated CPT item and fill in custom fields.
8. View the single item on the frontend.
9. Confirm generated fields are styled and empty fields are hidden.
10. View the CPT archive.
11. Delete the model and choose whether to also delete generated content.
12. Recreate a model with the same slug and confirm frontend URLs do not 404.

Useful test prompts:

```text
Create a job board with jobs, companies, locations, salary ranges, employment types, remote options, application deadlines, and application URLs.
```

```text
Create a restaurant menu section with dishes, menu categories, ingredients, allergens, dietary labels, prices, spice levels, preparation time, dish images, featured dishes, and availability status.
```

```text
Create a movie database with movies, actors, genres, release dates, ratings, trailers, posters, runtime, directors, and featured movies.
```

## Security Notes

- Generated PHP is never executed.
- Generated configurations are validated before saving or applying.
- AJAX actions use nonces and capability checks.
- Field saving uses nonces and post editing permissions.
- Frontend output is escaped based on field type.
- API keys are stored but not printed back into admin forms.
- Cleanup actions are explicit and destructive content deletion requires a separate popover action.

## Known Limitations

This is still an MVP. Good future improvements include:

- Visual add/remove controls for CPTs, taxonomies, and fields
- WordPress media picker support for image and gallery fields
- OpenAI provider connection test
- Model revision flow, such as "add a field" or "change taxonomy"
- Blueprint/template library
- ACF or Meta Box export
- Relationship fields
- Search/filter generation
- Schema.org output
- Rollback support

## License

GPLv2 or later. See `LICENSE`.

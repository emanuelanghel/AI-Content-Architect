<?php
/**
 * Main plugin orchestrator.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	private static ?Plugin $instance = null;

	public Model_Store $store;
	public Schema_Validator $validator;
	public Template_Manager $templates;

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->store     = new Model_Store();
		$this->validator = new Schema_Validator( $this->store );
		$this->templates = new Template_Manager( $this->store );
	}

	public function run(): void {
		$cpts       = new CPT_Registrar( $this->store );
		$taxonomies = new Taxonomy_Registrar( $this->store );
		$fields     = new Field_Registrar( $this->store );
		$columns    = new Admin_Columns( $this->store );
		$assets     = new Assets( $this->store );
		$admin      = new Admin( $this->store, $this->validator );
		$frontend   = new Frontend_Display( $this->templates );

		add_action( 'init', array( $cpts, 'register' ), 8 );
		add_action( 'init', array( $taxonomies, 'register' ), 9 );
		add_action( 'init', array( $fields, 'register_meta' ), 20 );
		add_action( 'init', array( $this, 'flush_rewrite_rules_if_needed' ), 100 );
		add_action( 'admin_init', array( $fields, 'register_meta_box_hooks' ) );
		add_action( 'add_meta_boxes', array( $fields, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $fields, 'save_post' ), 10, 2 );
		add_action( 'admin_init', array( $columns, 'register_hooks' ) );
		add_action( 'admin_menu', array( $admin, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $assets, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( $assets, 'enqueue_frontend' ) );
		add_action( 'admin_post_aica_save_settings', array( $admin, 'save_settings' ) );
		add_action( 'admin_post_aica_import_model', array( $admin, 'import_model' ) );
		add_action( 'admin_post_aica_export_model', array( $admin, 'export_model' ) );
		add_filter( 'single_template', array( $this->templates, 'single_template' ) );
		add_filter( 'archive_template', array( $this->templates, 'archive_template' ) );
		add_filter( 'the_content', array( $frontend, 'append_fields_to_content' ), 20 );

		foreach ( array( 'generate_model', 'save_model', 'apply_model', 'disable_model', 'delete_model', 'refresh_models', 'test_provider_connection' ) as $action ) {
			add_action( 'wp_ajax_aica_' . $action, array( $admin, 'ajax_' . $action ) );
		}
	}

	public function flush_rewrite_rules_if_needed(): void {
		if ( ! get_option( AICA_OPTION_NEEDS_REWRITE_FLUSH, false ) ) {
			return;
		}

		delete_option( AICA_OPTION_NEEDS_REWRITE_FLUSH );
		flush_rewrite_rules();
	}
}

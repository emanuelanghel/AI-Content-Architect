<?php
/**
 * Admin screens and AJAX actions.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {
	private Model_Store $store;
	private Schema_Validator $validator;

	public function __construct( Model_Store $store, Schema_Validator $validator ) {
		$this->store     = $store;
		$this->validator = $validator;
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'AI Content Architect', 'ai-content-architect' ),
			__( 'AI Content Architect', 'ai-content-architect' ),
			Capabilities::MANAGE,
			'ai-content-architect',
			array( $this, 'render_architect_page' ),
			'dashicons-layout',
			58
		);

		add_submenu_page( 'ai-content-architect', __( 'Architect', 'ai-content-architect' ), __( 'Architect', 'ai-content-architect' ), Capabilities::MANAGE, 'ai-content-architect', array( $this, 'render_architect_page' ) );
		add_submenu_page( 'ai-content-architect', __( 'Content Models', 'ai-content-architect' ), __( 'Content Models', 'ai-content-architect' ), Capabilities::MANAGE, 'aica-models', array( $this, 'render_models_page' ) );
		add_submenu_page( 'ai-content-architect', __( 'Settings', 'ai-content-architect' ), __( 'Settings', 'ai-content-architect' ), Capabilities::MANAGE, 'aica-settings', array( $this, 'render_settings_page' ) );
	}

	public function render_architect_page(): void {
		$this->guard();
		$edit_model = isset( $_GET['model_id'] ) ? $this->store->get( sanitize_key( wp_unslash( $_GET['model_id'] ) ) ) : null;
		require AICA_PATH . 'admin/views/page-architect.php';
	}

	public function render_models_page(): void {
		$this->guard();
		$models = $this->store->all();
		require AICA_PATH . 'admin/views/page-models.php';
	}

	public function render_settings_page(): void {
		$this->guard();
		$settings = $this->settings();
		require AICA_PATH . 'admin/views/page-settings.php';
	}

	public function save_settings(): void {
		$this->verify_admin_post();
		$existing = $this->settings();
		$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

		$settings = array(
			'provider'               => isset( $_POST['provider'] ) && 'openai' === sanitize_key( wp_unslash( $_POST['provider'] ) ) ? 'openai' : 'mock',
			'api_key'                => '' !== $api_key ? $api_key : ( $existing['api_key'] ?? '' ),
			'base_url'               => esc_url_raw( isset( $_POST['base_url'] ) ? wp_unslash( $_POST['base_url'] ) : '' ),
			'model'                  => sanitize_text_field( isset( $_POST['model'] ) ? wp_unslash( $_POST['model'] ) : 'gpt-4.1-mini' ),
			'strict_validation'      => ! empty( $_POST['strict_validation'] ),
			'prevent_slug_conflicts' => ! empty( $_POST['prevent_slug_conflicts'] ),
			'require_review'         => true,
			'enable_templates'       => ! empty( $_POST['enable_templates'] ),
			'show_frontend_fields'   => ! empty( $_POST['show_frontend_fields'] ),
		);

		if ( ! empty( $_POST['clear_api_key'] ) ) {
			$settings['api_key'] = '';
		}

		update_option( AICA_OPTION_SETTINGS, $settings, false );
		wp_safe_redirect( add_query_arg( 'aica_notice', 'settings_saved', admin_url( 'admin.php?page=aica-settings' ) ) );
		exit;
	}

	public function import_model(): void {
		$this->verify_admin_post();
		$json     = isset( $_POST['import_json'] ) ? wp_unslash( $_POST['import_json'] ) : '';
		$importer = new Export_Import( $this->store, $this->validator );
		$result   = $importer->import_model( (string) $json );
		$notice   = ! empty( $result['valid'] ) ? 'imported' : 'import_failed';
		wp_safe_redirect( add_query_arg( 'aica_notice', $notice, admin_url( 'admin.php?page=aica-models' ) ) );
		exit;
	}

	public function export_model(): void {
		$this->verify_admin_post();
		$id       = isset( $_GET['model_id'] ) ? sanitize_key( wp_unslash( $_GET['model_id'] ) ) : '';
		$exporter = new Export_Import( $this->store, $this->validator );
		$config   = $exporter->export_model( $id );

		if ( ! $config ) {
			wp_die( esc_html__( 'Model not found.', 'ai-content-architect' ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="aica-model-' . sanitize_file_name( $id ) . '.json"' );
		echo wp_json_encode( $config, JSON_PRETTY_PRINT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public function ajax_generate_model(): void {
		$this->verify_ajax();
		$prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
		if ( '' === $prompt ) {
			aica_json_response_error( __( 'Please enter a prompt.', 'ai-content-architect' ) );
		}

		$provider = $this->provider();
		$config   = $provider->generate_content_model( $prompt );
		if ( ! empty( $config['error'] ) ) {
			aica_json_response_error( sanitize_text_field( $config['message'] ?? __( 'AI generation failed.', 'ai-content-architect' ) ) );
		}

		$model_id = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : null;
		$result   = $this->validator->validate( $config, ! empty( $this->settings()['prevent_slug_conflicts'] ), $model_id ?: null, 'warning' );
		if ( ! $result['valid'] ) {
			aica_json_response_error( __( 'Generated model failed validation.', 'ai-content-architect' ), array( 'errors' => $result['errors'] ) );
		}

		wp_send_json_success(
			array(
				'config'   => $result['config'],
				'warnings' => $result['warnings'],
				'html'     => $this->render_review_html( $result['config'], $model_id ?: null ),
			)
		);
	}

	public function ajax_save_model(): void {
		$this->verify_ajax();
		$result = $this->posted_config_result();
		if ( ! $result['valid'] ) {
			aica_json_response_error( __( 'Model failed validation.', 'ai-content-architect' ), array( 'errors' => $result['errors'] ) );
		}

		$id     = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : null;
		$model  = $this->store->save( $result['config'], 'draft', $id ?: null );
		wp_send_json_success( array( 'model' => $model, 'message' => __( 'Draft model saved.', 'ai-content-architect' ) ) );
	}

	public function ajax_apply_model(): void {
		$this->verify_ajax();
		$result = $this->posted_config_result();
		if ( ! $result['valid'] ) {
			aica_json_response_error( __( 'Model failed validation.', 'ai-content-architect' ), array( 'errors' => $result['errors'] ) );
		}

		$id    = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : null;
		$model = $this->store->save( $result['config'], 'applied', $id ?: null );
		$this->register_generated_structures();

		if ( ! empty( $_POST['generate_sample'] ) ) {
			$limit = isset( $_POST['sample_count'] ) ? absint( $_POST['sample_count'] ) : 3;
			( new Sample_Content_Generator() )->generate( $model, $limit );
		}

		$this->schedule_rewrite_flush();
		flush_rewrite_rules();
		wp_send_json_success( array( 'model' => $model, 'message' => __( 'Content model applied.', 'ai-content-architect' ) ) );
	}

	public function ajax_disable_model(): void {
		$this->verify_ajax();
		$id = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : '';
		$this->store->set_status( $id, 'disabled' );
		$this->schedule_rewrite_flush();
		wp_send_json_success( array( 'message' => __( 'Model disabled.', 'ai-content-architect' ) ) );
	}

	public function ajax_delete_model(): void {
		$this->verify_ajax();
		$id = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : '';
		$model = $this->store->get( $id );
		if ( ! $model ) {
			aica_json_response_error( __( 'Model not found.', 'ai-content-architect' ) );
		}

		$counts = array(
			'posts' => 0,
			'terms' => 0,
		);

		if ( ! empty( $_POST['delete_content'] ) ) {
			$counts = ( new Model_Cleaner() )->delete_model_content( $model );
		}

		$this->store->delete( $id );
		$this->schedule_rewrite_flush();

		$message = empty( $_POST['delete_content'] )
			? __( 'Model deleted. Existing content was not deleted.', 'ai-content-architect' )
			: sprintf(
				/* translators: 1: deleted post count, 2: deleted term count. */
				__( 'Model deleted. Removed %1$d generated posts and %2$d taxonomy terms. Media files were not deleted.', 'ai-content-architect' ),
				(int) $counts['posts'],
				(int) $counts['terms']
			);

		wp_send_json_success(
			array(
				'message' => $message,
				'counts'  => $counts,
			)
		);
	}

	private function render_review_html( array $config, ?string $model_id ): string {
		ob_start();
		require AICA_PATH . 'admin/views/partial-model-review.php';
		return (string) ob_get_clean();
	}

	private function posted_config_result(): array {
		$json   = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : '';
		$config = json_decode( (string) $json, true );
		if ( ! is_array( $config ) ) {
			return array( 'valid' => false, 'errors' => array( __( 'Invalid model JSON.', 'ai-content-architect' ) ) );
		}
		$id = isset( $_POST['model_id'] ) ? sanitize_key( wp_unslash( $_POST['model_id'] ) ) : null;
		return $this->validator->validate( $config, ! empty( $this->settings()['prevent_slug_conflicts'] ), $id ?: null, 'error' );
	}

	private function provider(): AI_Provider_Interface {
		$settings = $this->settings();
		return 'openai' === ( $settings['provider'] ?? 'mock' ) ? new OpenAI_Provider( $settings ) : new Mock_Provider();
	}

	private function register_generated_structures(): void {
		( new CPT_Registrar( $this->store ) )->register();
		( new Taxonomy_Registrar( $this->store ) )->register();
	}

	private function schedule_rewrite_flush(): void {
		update_option( AICA_OPTION_NEEDS_REWRITE_FLUSH, 1, false );
	}

	private function settings(): array {
		return wp_parse_args(
			get_option( AICA_OPTION_SETTINGS, array() ),
			array(
				'provider'               => 'mock',
				'api_key'                => '',
				'base_url'               => 'https://api.openai.com/v1/chat/completions',
				'model'                  => 'gpt-4.1-mini',
				'strict_validation'      => true,
				'prevent_slug_conflicts' => true,
				'require_review'         => true,
				'enable_templates'       => false,
				'show_frontend_fields'   => true,
			)
		);
	}

	private function verify_ajax(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			aica_json_response_error( __( 'Permission denied.', 'ai-content-architect' ) );
		}
		check_ajax_referer( 'aica_admin', 'nonce' );
	}

	private function verify_admin_post(): void {
		$this->guard();
		check_admin_referer( 'aica_admin' );
	}

	private function guard(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'Permission denied.', 'ai-content-architect' ) );
		}
	}
}

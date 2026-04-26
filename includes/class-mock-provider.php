<?php
/**
 * Development mock provider.
 *
 * @package AIContentArchitect
 */

namespace AIContentArchitect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mock_Provider implements AI_Provider_Interface {
	public function generate_content_model( string $user_prompt ): array {
		if ( false !== stripos( $user_prompt, 'real estate' ) || false !== stripos( $user_prompt, 'property' ) ) {
			return $this->real_estate();
		}

		return $this->job_board();
	}

	private function job_board(): array {
		return array(
			'model_name'        => 'Job Board',
			'description'       => 'A structured content model for job listings with employment details, locations, and application metadata.',
			'intended_use_case' => 'Publish searchable job openings and organize them by company, location, and employment type.',
			'warnings'          => array( 'Company may become a related custom post type in a later relationship-focused version.' ),
			'custom_post_types' => array(
				array(
					'key'            => 'job',
					'singular_label' => 'Job',
					'plural_label'   => 'Jobs',
					'slug'           => 'jobs',
					'menu_icon'      => 'dashicons-businessperson',
					'public'         => true,
					'has_archive'    => true,
					'show_in_rest'   => true,
					'hierarchical'   => false,
					'supports'       => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				),
			),
			'taxonomies'        => array(
				array(
					'key'            => 'company',
					'singular_label' => 'Company',
					'plural_label'   => 'Companies',
					'slug'           => 'company',
					'post_types'     => array( 'job' ),
					'hierarchical'   => false,
					'public'         => true,
					'show_in_rest'   => true,
				),
				array(
					'key'            => 'job_location',
					'singular_label' => 'Location',
					'plural_label'   => 'Locations',
					'slug'           => 'job-location',
					'post_types'     => array( 'job' ),
					'hierarchical'   => true,
					'public'         => true,
					'show_in_rest'   => true,
				),
				array(
					'key'            => 'employment_type',
					'singular_label' => 'Employment Type',
					'plural_label'   => 'Employment Types',
					'slug'           => 'employment-type',
					'post_types'     => array( 'job' ),
					'hierarchical'   => true,
					'public'         => true,
					'show_in_rest'   => true,
				),
			),
			'fields'            => array(
				array( 'key' => 'salary_range', 'label' => 'Salary Range', 'type' => 'text', 'post_type' => 'job', 'required' => false, 'placeholder' => '$80,000 - $110,000', 'help_text' => 'Enter a human-readable salary range.', 'default' => '' ),
				array( 'key' => 'remote_option', 'label' => 'Remote Option', 'type' => 'select', 'post_type' => 'job', 'required' => false, 'placeholder' => '', 'help_text' => 'Choose whether this role is remote, hybrid, or on-site.', 'default' => 'Hybrid', 'options' => array( 'Remote', 'Hybrid', 'On-site' ) ),
				array( 'key' => 'application_deadline', 'label' => 'Application Deadline', 'type' => 'date', 'post_type' => 'job', 'required' => false, 'placeholder' => 'YYYY-MM-DD', 'help_text' => 'Deadline for applications.', 'default' => '' ),
				array( 'key' => 'application_url', 'label' => 'Application URL', 'type' => 'url', 'post_type' => 'job', 'required' => true, 'placeholder' => 'https://example.com/apply', 'help_text' => 'External application link.', 'default' => '' ),
				array( 'key' => 'featured_job', 'label' => 'Featured Job', 'type' => 'checkbox', 'post_type' => 'job', 'required' => false, 'placeholder' => '', 'help_text' => 'Mark this job as featured.', 'default' => false ),
			),
			'admin_columns'     => array(
				array(
					'post_type' => 'job',
					'columns'   => array(
						array( 'key' => 'salary_range', 'label' => 'Salary', 'source' => 'field', 'source_key' => 'salary_range', 'sortable' => true ),
						array( 'key' => 'job_location', 'label' => 'Location', 'source' => 'taxonomy', 'source_key' => 'job_location', 'sortable' => false ),
						array( 'key' => 'employment_type', 'label' => 'Type', 'source' => 'taxonomy', 'source_key' => 'employment_type', 'sortable' => false ),
					),
				),
			),
			'templates'         => array(
				array(
					'post_type'      => 'job',
					'single_layout'  => 'Display job title, company, location, employment type, salary range, job description, application deadline, and application button.',
					'archive_layout' => 'Display a list of jobs with title, company, location, employment type, salary range, and application deadline.',
				),
			),
			'sample_content'    => array(
				array(
					'post_type'  => 'job',
					'title'      => 'Senior Product Designer',
					'content'    => 'Design polished product workflows for a growing software team.',
					'fields'     => array( 'salary_range' => '$100,000 - $130,000', 'remote_option' => 'Hybrid', 'application_deadline' => '2026-06-30', 'application_url' => 'https://example.com/apply', 'featured_job' => '1' ),
					'taxonomies' => array( 'company' => array( 'Acme Studio' ), 'job_location' => array( 'New York' ), 'employment_type' => array( 'Full Time' ) ),
				),
			),
		);
	}

	private function real_estate(): array {
		$model = $this->job_board();
		$model['model_name']  = 'Real Estate Listings';
		$model['description'] = 'A structured content model for property listings.';
		return $model;
	}
}

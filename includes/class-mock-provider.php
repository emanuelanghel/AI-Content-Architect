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
		if ( false !== stripos( $user_prompt, 'restaurant' ) || false !== stripos( $user_prompt, 'menu' ) || false !== stripos( $user_prompt, 'dish' ) ) {
			return $this->restaurant_menu();
		}

		if ( false !== stripos( $user_prompt, 'movie' ) || false !== stripos( $user_prompt, 'actor' ) || false !== stripos( $user_prompt, 'film' ) ) {
			return $this->movie_database();
		}

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
		return array(
			'model_name'        => 'Real Estate Listings',
			'description'       => 'A structured content model for property listings with locations, agents, amenities, and listing details.',
			'intended_use_case' => 'Publish searchable property listings and organize them by location, property type, and amenities.',
			'warnings'          => array( 'Gallery fields store attachment IDs; media-picker support can improve editing later.' ),
			'custom_post_types' => array(
				array(
					'key'            => 'property',
					'singular_label' => 'Property',
					'plural_label'   => 'Properties',
					'slug'           => 'properties',
					'menu_icon'      => 'dashicons-admin-home',
					'public'         => true,
					'has_archive'    => true,
					'show_in_rest'   => true,
					'hierarchical'   => false,
					'supports'       => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				),
			),
			'taxonomies'        => array(
				array( 'key' => 'property_location', 'singular_label' => 'Location', 'plural_label' => 'Locations', 'slug' => 'property-location', 'post_types' => array( 'property' ), 'hierarchical' => true, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'property_type', 'singular_label' => 'Property Type', 'plural_label' => 'Property Types', 'slug' => 'property-type', 'post_types' => array( 'property' ), 'hierarchical' => true, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'amenity', 'singular_label' => 'Amenity', 'plural_label' => 'Amenities', 'slug' => 'amenity', 'post_types' => array( 'property' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
			),
			'fields'            => array(
				array( 'key' => 'price', 'label' => 'Price', 'type' => 'number', 'post_type' => 'property', 'required' => true, 'placeholder' => '450000', 'help_text' => 'Listing price.', 'default' => '' ),
				array( 'key' => 'bedrooms', 'label' => 'Bedrooms', 'type' => 'number', 'post_type' => 'property', 'required' => false, 'placeholder' => '3', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'bathrooms', 'label' => 'Bathrooms', 'type' => 'number', 'post_type' => 'property', 'required' => false, 'placeholder' => '2', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'agent_name', 'label' => 'Agent Name', 'type' => 'text', 'post_type' => 'property', 'required' => false, 'placeholder' => 'Jane Smith', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'gallery_images', 'label' => 'Gallery Images', 'type' => 'gallery', 'post_type' => 'property', 'required' => false, 'placeholder' => '', 'help_text' => 'Store selected image IDs.', 'default' => '' ),
			),
			'admin_columns'     => array(
				array( 'post_type' => 'property', 'columns' => array(
					array( 'key' => 'price', 'label' => 'Price', 'source' => 'field', 'source_key' => 'price', 'sortable' => true ),
					array( 'key' => 'property_location', 'label' => 'Location', 'source' => 'taxonomy', 'source_key' => 'property_location', 'sortable' => false ),
					array( 'key' => 'property_type', 'label' => 'Type', 'source' => 'taxonomy', 'source_key' => 'property_type', 'sortable' => false ),
				) ),
			),
			'templates'         => array(
				array( 'post_type' => 'property', 'single_layout' => 'Display price, location, type, bedrooms, bathrooms, gallery, description, amenities, and agent details.', 'archive_layout' => 'Display property cards with image, price, location, bedrooms, bathrooms, and type.' ),
			),
			'sample_content'    => array(
				array( 'post_type' => 'property', 'title' => 'Modern Downtown Apartment', 'content' => 'Bright apartment near transit and shops.', 'fields' => array( 'price' => '450000', 'bedrooms' => '2', 'bathrooms' => '2', 'agent_name' => 'Jane Smith', 'gallery_images' => '' ), 'taxonomies' => array( 'property_location' => array( 'Downtown' ), 'property_type' => array( 'Apartment' ), 'amenity' => array( 'Parking', 'Balcony' ) ) ),
			),
		);
	}

	private function restaurant_menu(): array {
		return array(
			'model_name'        => 'Restaurant Menu',
			'description'       => 'A structured content model for dishes, menu organization, dietary labels, prices, and availability.',
			'intended_use_case' => 'Publish and manage a restaurant menu with searchable dishes and clear dietary metadata.',
			'warnings'          => array( 'Dish images use the featured image plus optional generated image fields.' ),
			'custom_post_types' => array(
				array( 'key' => 'dish', 'singular_label' => 'Dish', 'plural_label' => 'Dishes', 'slug' => 'dishes', 'menu_icon' => 'dashicons-food', 'public' => true, 'has_archive' => true, 'show_in_rest' => true, 'hierarchical' => false, 'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ) ),
			),
			'taxonomies'        => array(
				array( 'key' => 'menu_category', 'singular_label' => 'Menu Category', 'plural_label' => 'Menu Categories', 'slug' => 'menu-category', 'post_types' => array( 'dish' ), 'hierarchical' => true, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'ingredient', 'singular_label' => 'Ingredient', 'plural_label' => 'Ingredients', 'slug' => 'ingredient', 'post_types' => array( 'dish' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'allergen', 'singular_label' => 'Allergen', 'plural_label' => 'Allergens', 'slug' => 'allergen', 'post_types' => array( 'dish' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'dietary_label', 'singular_label' => 'Dietary Label', 'plural_label' => 'Dietary Labels', 'slug' => 'dietary-label', 'post_types' => array( 'dish' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
			),
			'fields'            => array(
				array( 'key' => 'price', 'label' => 'Price', 'type' => 'number', 'post_type' => 'dish', 'required' => true, 'placeholder' => '18', 'help_text' => 'Menu price.', 'default' => '' ),
				array( 'key' => 'spice_level', 'label' => 'Spice Level', 'type' => 'select', 'post_type' => 'dish', 'required' => false, 'placeholder' => '', 'help_text' => 'Choose the heat level.', 'default' => 'Mild', 'options' => array( 'None', 'Mild', 'Medium', 'Hot' ) ),
				array( 'key' => 'preparation_time', 'label' => 'Preparation Time', 'type' => 'text', 'post_type' => 'dish', 'required' => false, 'placeholder' => '15 minutes', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'dish_image', 'label' => 'Dish Image', 'type' => 'image', 'post_type' => 'dish', 'required' => false, 'placeholder' => '', 'help_text' => 'Store an image attachment ID.', 'default' => '' ),
				array( 'key' => 'featured_dish', 'label' => 'Featured Dish', 'type' => 'checkbox', 'post_type' => 'dish', 'required' => false, 'placeholder' => '', 'help_text' => 'Feature this dish.', 'default' => false ),
				array( 'key' => 'availability_status', 'label' => 'Availability Status', 'type' => 'select', 'post_type' => 'dish', 'required' => false, 'placeholder' => '', 'help_text' => '', 'default' => 'Available', 'options' => array( 'Available', 'Limited', 'Sold out', 'Seasonal' ) ),
			),
			'admin_columns'     => array(
				array( 'post_type' => 'dish', 'columns' => array(
					array( 'key' => 'price', 'label' => 'Price', 'source' => 'field', 'source_key' => 'price', 'sortable' => true ),
					array( 'key' => 'menu_category', 'label' => 'Category', 'source' => 'taxonomy', 'source_key' => 'menu_category', 'sortable' => false ),
					array( 'key' => 'availability_status', 'label' => 'Availability', 'source' => 'field', 'source_key' => 'availability_status', 'sortable' => false ),
				) ),
			),
			'templates'         => array(
				array( 'post_type' => 'dish', 'single_layout' => 'Display dish image, description, price, category, ingredients, allergens, dietary labels, spice level, prep time, and availability.', 'archive_layout' => 'Display a menu grouped by category with price, dietary labels, and availability.' ),
			),
			'sample_content'    => array(
				array( 'post_type' => 'dish', 'title' => 'Spicy Tomato Bucatini', 'content' => 'Fresh pasta with tomato, basil, and chili oil.', 'fields' => array( 'price' => '18', 'spice_level' => 'Medium', 'preparation_time' => '14 minutes', 'dish_image' => '', 'featured_dish' => '1', 'availability_status' => 'Available' ), 'taxonomies' => array( 'menu_category' => array( 'Pasta' ), 'ingredient' => array( 'Tomato', 'Basil', 'Chili' ), 'allergen' => array( 'Gluten' ), 'dietary_label' => array( 'Vegetarian' ) ) ),
			),
		);
	}

	private function movie_database(): array {
		return array(
			'model_name'        => 'Movie Database',
			'description'       => 'A structured content model for movies with actors, genres, ratings, media links, and production metadata.',
			'intended_use_case' => 'Publish a searchable movie catalog with featured movies, trailers, posters, and credits.',
			'warnings'          => array( 'Actors and directors are generated as taxonomies until relationship fields are available.' ),
			'custom_post_types' => array(
				array( 'key' => 'movie', 'singular_label' => 'Movie', 'plural_label' => 'Movies', 'slug' => 'movies', 'menu_icon' => 'dashicons-video-alt2', 'public' => true, 'has_archive' => true, 'show_in_rest' => true, 'hierarchical' => false, 'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ) ),
			),
			'taxonomies'        => array(
				array( 'key' => 'actor', 'singular_label' => 'Actor', 'plural_label' => 'Actors', 'slug' => 'actor', 'post_types' => array( 'movie' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'genre', 'singular_label' => 'Genre', 'plural_label' => 'Genres', 'slug' => 'genre', 'post_types' => array( 'movie' ), 'hierarchical' => true, 'public' => true, 'show_in_rest' => true ),
				array( 'key' => 'director', 'singular_label' => 'Director', 'plural_label' => 'Directors', 'slug' => 'director', 'post_types' => array( 'movie' ), 'hierarchical' => false, 'public' => true, 'show_in_rest' => true ),
			),
			'fields'            => array(
				array( 'key' => 'release_date', 'label' => 'Release Date', 'type' => 'date', 'post_type' => 'movie', 'required' => false, 'placeholder' => 'YYYY-MM-DD', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'rating', 'label' => 'Rating', 'type' => 'number', 'post_type' => 'movie', 'required' => false, 'placeholder' => '8.2', 'help_text' => 'Numeric rating.', 'default' => '' ),
				array( 'key' => 'trailer_url', 'label' => 'Trailer URL', 'type' => 'url', 'post_type' => 'movie', 'required' => false, 'placeholder' => 'https://example.com/trailer', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'poster_image', 'label' => 'Poster Image', 'type' => 'image', 'post_type' => 'movie', 'required' => false, 'placeholder' => '', 'help_text' => 'Store a poster attachment ID.', 'default' => '' ),
				array( 'key' => 'runtime', 'label' => 'Runtime', 'type' => 'text', 'post_type' => 'movie', 'required' => false, 'placeholder' => '132 minutes', 'help_text' => '', 'default' => '' ),
				array( 'key' => 'featured_movie', 'label' => 'Featured Movie', 'type' => 'checkbox', 'post_type' => 'movie', 'required' => false, 'placeholder' => '', 'help_text' => 'Feature this movie.', 'default' => false ),
			),
			'admin_columns'     => array(
				array( 'post_type' => 'movie', 'columns' => array(
					array( 'key' => 'rating', 'label' => 'Rating', 'source' => 'field', 'source_key' => 'rating', 'sortable' => true ),
					array( 'key' => 'genre', 'label' => 'Genre', 'source' => 'taxonomy', 'source_key' => 'genre', 'sortable' => false ),
					array( 'key' => 'release_date', 'label' => 'Release Date', 'source' => 'field', 'source_key' => 'release_date', 'sortable' => true ),
				) ),
			),
			'templates'         => array(
				array( 'post_type' => 'movie', 'single_layout' => 'Display poster, trailer, synopsis, actors, director, genres, release date, runtime, rating, and featured status.', 'archive_layout' => 'Display movie cards with poster, title, genre, rating, runtime, and release year.' ),
			),
			'sample_content'    => array(
				array( 'post_type' => 'movie', 'title' => 'The Last Signal', 'content' => 'A science fiction thriller about a missing deep-space broadcast.', 'fields' => array( 'release_date' => '2026-09-18', 'rating' => '8.1', 'trailer_url' => 'https://example.com/trailer', 'poster_image' => '', 'runtime' => '128 minutes', 'featured_movie' => '1' ), 'taxonomies' => array( 'actor' => array( 'Mara Chen', 'Jon Vale' ), 'genre' => array( 'Science Fiction' ), 'director' => array( 'Elena Hart' ) ) ),
			),
		);
	}
}

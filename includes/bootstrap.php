<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_bootstrap() {
	add_action( 'init', 'jr_content_core_register_post_types', 5 );
	add_action( 'init', 'jr_content_core_register_taxonomies', 6 );
	add_action( 'init', 'jr_content_core_register_meta', 7 );
	add_action( 'init', 'jr_content_core_taxonomy_rewrite_rules', 15 );
	add_action( 'init', 'jr_content_core_maybe_upgrade', 20 );
	add_action( 'rest_api_init', 'jr_content_core_register_rest_routes' );
	add_filter( 'term_link', 'jr_content_core_term_link', 10, 3 );

	if ( is_admin() ) {
		add_action( 'games_add_form_fields', 'jr_content_core_game_add_form_fields' );
		add_action( 'games_edit_form_fields', 'jr_content_core_game_edit_form_fields' );
		add_action( 'created_games', 'jr_content_core_save_game_type' );
		add_action( 'edited_games', 'jr_content_core_save_game_type' );
		add_filter( 'manage_edit-games_columns', 'jr_content_core_games_columns' );
		add_filter( 'manage_games_custom_column', 'jr_content_core_games_column_content', 10, 3 );

		add_action( 'platform_add_form_fields', 'jr_content_core_platform_add_form_fields' );
		add_action( 'platform_edit_form_fields', 'jr_content_core_platform_edit_form_fields' );
		add_action( 'created_platform', 'jr_content_core_save_platform_type' );
		add_action( 'edited_platform', 'jr_content_core_save_platform_type' );
		add_filter( 'manage_edit-platform_columns', 'jr_content_core_platform_columns' );
		add_filter( 'manage_platform_custom_column', 'jr_content_core_platform_column_content', 10, 3 );
	}
}

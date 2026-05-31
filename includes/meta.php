<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_meta_auth_callback( $allowed, $meta_key, $post_id, $user_id ) {
	if ( $post_id > 0 ) {
		return user_can( $user_id, 'edit_post', $post_id );
	}

	return user_can( $user_id, 'edit_posts' );
}

function jr_content_core_register_meta() {
	$simple_string_args = array(
		'single'            => true,
		'type'              => 'string',
		'show_in_rest'      => true,
		'sanitize_callback' => 'jr_content_core_sanitize_string',
		'auth_callback'     => 'jr_content_core_meta_auth_callback',
	);

	jr_content_core_register_single_meta(
		array( 'post', 'page' ),
		'jrblog_page_video_src',
		array(
			'single'            => true,
			'type'              => 'string',
			'show_in_rest'      => true,
			'sanitize_callback' => 'jr_content_core_sanitize_video_source',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);

	jr_content_core_register_single_meta( array( 'post', 'page' ), 'jrblog_page_video_id', $simple_string_args );
	jr_content_core_register_single_meta( array( 'post', 'page' ), 'jrblog_page_video_list', $simple_string_args );

	jr_content_core_register_single_meta(
		array( 'post', 'page' ),
		'jrblog_page_disable_excerpt',
		array(
			'single'            => true,
			'type'              => 'boolean',
			'show_in_rest'      => true,
			'sanitize_callback' => 'jr_content_core_sanitize_boolean',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);

	jr_content_core_register_single_meta(
		array( 'post', 'page' ),
		'jrblog_page_featured_position',
		array(
			'single'            => true,
			'type'              => 'string',
			'show_in_rest'      => true,
			'sanitize_callback' => 'jr_content_core_sanitize_feature_position',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);

	// Review-specific meta.
	// NOTE: This PR introduces a new, narrower set of review meta fields.
	// If there is existing content relying on old review meta keys, consider:
	// - Keeping deprecated keys registered (with show_in_rest => false) for back-compat
	// - Providing a migration script to convert old meta to new format
	// - Documenting the breaking change in a migration guide
	jr_content_core_register_single_meta(
		array( 'review' ),
		'jr_review_rating',
		array(
			'single'            => true,
			'type'              => array( 'number', 'null' ),
			'show_in_rest'      => true,
			'sanitize_callback' => 'jr_content_core_sanitize_rating',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);

	// Platform is handled via the platform taxonomy, not as individual meta.
	// This provides a single source of truth for platform filtering and querying.
	jr_content_core_register_single_meta( array( 'review' ), 'jr_review_genre', $simple_string_args );
	jr_content_core_register_single_meta( array( 'review' ), 'jr_review_playtime', $simple_string_args );

	$bool_args = array(
		'single'            => true,
		'type'              => 'boolean',
		'show_in_rest'      => true,
		'sanitize_callback' => 'jr_content_core_sanitize_boolean',
		'auth_callback'     => 'jr_content_core_meta_auth_callback',
	);

	jr_content_core_register_single_meta( array( 'social' ), 'icon_image', array(
		'single'            => true,
		'type'              => 'integer',
		'show_in_rest'      => true,
		'sanitize_callback' => 'absint',
		'auth_callback'     => 'jr_content_core_meta_auth_callback',
	) );

	$social_string_keys = array( 'jrblog_social_slug', 'jrblog_social_url', 'jrblog_social_name' );
	foreach ( $social_string_keys as $key ) {
		jr_content_core_register_single_meta( array( 'social' ), $key, $simple_string_args );
	}

	$social_bool_keys = array( 'jrblog_social_full', 'jrblog_social_sub', 'jrblog_social_share', 'jrblog_social_sharing', 'jrblog_social_follow' );
	foreach ( $social_bool_keys as $key ) {
		jr_content_core_register_single_meta( array( 'social' ), $key, $bool_args );
	}

	$video_string_keys = array( 'yt_video_id', 'yt_playlist_id', 'yt_published_at', 'yt_thumbnail_url', 'yt_duration', 'yt_broadcast_status', 'yt_scheduled_start', 'yt_import_source' );
	foreach ( $video_string_keys as $key ) {
		jr_content_core_register_single_meta( array( 'video' ), $key, $simple_string_args );
	}

	$video_count_args = array(
		'single'            => true,
		'type'              => 'string',
		'show_in_rest'      => true,
		'sanitize_callback' => 'jr_content_core_sanitize_count',
		'auth_callback'     => 'jr_content_core_meta_auth_callback',
	);
	jr_content_core_register_single_meta( array( 'video' ), 'yt_view_count', $video_count_args );
	jr_content_core_register_single_meta( array( 'video' ), 'yt_like_count', $video_count_args );

	jr_content_core_register_single_meta(
		array( 'video' ),
		'wp_playlist_id',
		array(
			'single'            => true,
			'type'              => 'integer',
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);

	$playlist_string_keys = array( 'yt_playlist_id', 'yt_thumbnail_url', 'yt_published_at', 'yt_import_source' );
	foreach ( $playlist_string_keys as $key ) {
		jr_content_core_register_single_meta( array( 'playlist' ), $key, $simple_string_args );
	}

	jr_content_core_register_single_meta(
		array( 'playlist' ),
		'yt_video_count',
		array(
			'single'            => true,
			'type'              => 'integer',
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'jr_content_core_meta_auth_callback',
		)
	);
}

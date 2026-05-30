<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_bootstrap() {
	add_action( 'init', 'jr_content_core_register_post_types', 5 );
	add_action( 'init', 'jr_content_core_register_taxonomies', 6 );
	add_action( 'init', 'jr_content_core_register_meta', 7 );
	add_action( 'init', 'jr_content_core_maybe_upgrade', 20 );
	add_action( 'rest_api_init', 'jr_content_core_register_rest_routes' );
}

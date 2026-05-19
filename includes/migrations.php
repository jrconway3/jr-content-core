<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_activate() {
	jr_content_core_register_post_types();
	jr_content_core_register_taxonomies();
	jr_content_core_register_meta();

	update_option( 'jr_content_core_version', JR_CONTENT_CORE_VERSION );

	flush_rewrite_rules();
}

function jr_content_core_maybe_upgrade() {
	$installed_version = get_option( 'jr_content_core_version' );

	if ( JR_CONTENT_CORE_VERSION === $installed_version ) {
		return;
	}

	update_option( 'jr_content_core_version', JR_CONTENT_CORE_VERSION );
}

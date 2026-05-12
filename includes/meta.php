<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_meta_auth_callback($allowed, $meta_key, $post_id, $user_id)
{
    if ($post_id > 0) {
        return user_can($user_id, 'edit_post', $post_id);
    }

    return user_can($user_id, 'edit_posts');
}

function jr_content_core_register_meta()
{
    $simple_string_args = array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'jr_content_core_sanitize_string',
        'auth_callback'     => 'jr_content_core_meta_auth_callback',
    );

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_src', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'jr_content_core_sanitize_video_source',
        'auth_callback'     => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_id', $simple_string_args);
    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_list', $simple_string_args);

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_disable_excerpt', array(
        'single'            => true,
        'type'              => 'boolean',
        'show_in_rest'      => true,
        'sanitize_callback' => 'jr_content_core_sanitize_boolean',
        'auth_callback'     => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_featured_position', array(
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'jr_content_core_sanitize_feature_position',
        'auth_callback'     => 'jr_content_core_meta_auth_callback',
    ));

    // Review-specific meta.
    // NOTE: This PR introduces a new, narrower set of review meta fields.
    // If there is existing content relying on old review meta keys, consider:
    // - Keeping deprecated keys registered (with show_in_rest => false) for back-compat
    // - Providing a migration script to convert old meta to new format
    // - Documenting the breaking change in a migration guide
    jr_content_core_register_single_meta(array('review'), 'jr_review_rating', array(
        'single'            => true,
        'type'              => array('number', 'null'),
        'show_in_rest'      => true,
        'sanitize_callback' => 'jr_content_core_sanitize_rating',
        'auth_callback'     => 'jr_content_core_meta_auth_callback',
    ));

    // Platform is handled via the platform taxonomy, not as individual meta.
    // This provides a single source of truth for platform filtering and querying.
    jr_content_core_register_single_meta(array('review'), 'jr_review_genre', $simple_string_args);
    jr_content_core_register_single_meta(array('review'), 'jr_review_playtime', $simple_string_args);
}

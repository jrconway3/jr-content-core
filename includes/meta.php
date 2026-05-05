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
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
        'sanitize_callback' => 'jr_content_core_sanitize_string',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    );

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_src', array(
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
        'sanitize_callback' => 'jr_content_core_sanitize_video_source',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_id', $simple_string_args);
    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_video_list', $simple_string_args);

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_disable_excerpt', array(
        'single' => true,
        'type' => 'boolean',
        'show_in_rest' => true,
        'sanitize_callback' => 'jr_content_core_sanitize_boolean',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(array('post', 'page'), 'jrblog_page_featured_position', array(
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
        'sanitize_callback' => 'jr_content_core_sanitize_feature_position',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(jr_content_core_post_types_with_gallery(), 'gallery_images', array(
        'single' => false,
        'type' => 'string',
        'show_in_rest' => false,
        'sanitize_callback' => 'jr_content_core_sanitize_legacy_repeatable',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(jr_content_core_post_types_with_gallery(), 'gallery_videos', array(
        'single' => false,
        'type' => 'string',
        'show_in_rest' => false,
        'sanitize_callback' => 'jr_content_core_sanitize_legacy_repeatable',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(jr_content_core_post_types_with_associated_games(), 'associated_games', array(
        'single' => false,
        'type' => 'string',
        'show_in_rest' => false,
        'sanitize_callback' => 'jr_content_core_sanitize_legacy_repeatable',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(jr_content_core_post_types_with_associated_characters(), 'associated_characters', array(
        'single' => false,
        'type' => 'string',
        'show_in_rest' => false,
        'sanitize_callback' => 'jr_content_core_sanitize_legacy_repeatable',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));

    jr_content_core_register_single_meta(array('review'), 'ratings', array(
        'single' => false,
        'type' => 'string',
        'show_in_rest' => false,
        'sanitize_callback' => 'jr_content_core_sanitize_legacy_repeatable',
        'auth_callback' => 'jr_content_core_meta_auth_callback',
    ));
}

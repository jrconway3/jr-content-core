<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_post_types()
{
    return array('portfolio', 'game', 'character', 'review');
}

function jr_content_core_taxonomies()
{
    return array('company', 'franchise', 'alliance', 'genre', 'platform', 'progression');
}

function jr_content_core_post_types_with_gallery()
{
    return array('post', 'page', 'character', 'game', 'review');
}

function jr_content_core_post_types_with_associated_games()
{
    return array('post', 'page', 'review');
}

function jr_content_core_post_types_with_associated_characters()
{
    return array('post', 'game', 'review');
}

function jr_content_core_sanitize_string($value)
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    return sanitize_text_field((string) $value);
}

function jr_content_core_sanitize_boolean($value)
{
    return empty($value) ? false : true;
}

function jr_content_core_sanitize_video_source($value)
{
    $value = jr_content_core_sanitize_string($value);
    $allowed = array('youtube', 'vimeo');

    if ($value === '') {
        return '';
    }

    return in_array($value, $allowed, true) ? $value : '';
}

function jr_content_core_sanitize_feature_position($value)
{
    $value = jr_content_core_sanitize_string($value);
    $allowed = array('full', 'left', 'right', 'gantry');

    if ($value === '') {
        return '';
    }

    return in_array($value, $allowed, true) ? $value : 'full';
}

function jr_content_core_sanitize_legacy_repeatable($value)
{
    if (is_array($value) || is_object($value)) {
        $encoded = wp_json_encode($value);
        return is_string($encoded) ? $encoded : '';
    }

    return trim((string) $value);
}

function jr_content_core_register_single_meta(array $post_types, $meta_key, array $args)
{
    foreach ($post_types as $post_type) {
        register_post_meta($post_type, $meta_key, $args);
    }
}

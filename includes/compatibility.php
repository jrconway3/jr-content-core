<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_supports_post_type($post_type)
{
    return in_array($post_type, jr_content_core_post_types(), true);
}

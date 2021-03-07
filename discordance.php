<?php
/*
Plugin Name: Discordance
Plugin URI: https://github.com/duscci/discordance
Description: An WordPress plugin to send your posts to Discord using Webhooks.
Version: 0.1.3
Author: Valdir Ronis
Author URI: https://github.com/duscci
Donate link: https://ko-fi.com/duscci
License: GPLv2 or later
*/

defined('ABSPATH') or die;
$discordance_version = '0.1.3';
$discordance_opts = get_option('discordance');
function discordance_init()
{
    global $discordance_opts;
    if (!is_array($discordance_opts)) {
        $discordance_opts = array(
            'webhooks' => '',
            'format' => '
{
    "content": "📢 **New post on the blog!**",
    "embeds": [
        {
            "author": {
                "name": "%author%",
                "url": "%author_url%",
                "icon_url": "%gravatar%"
            },
            "title": "%title%",
            "description": "%excerpt%",
            "url": "%link%",
            "thumbnail": {
                "url": "%thumbnail%"
            }
        }
    ]
}
'
        );
        function discordance_warning()
        {
            global $hook_suffix;
            if (!current_user_can('manage_options') || $hook_suffix === 'toplevel_page_discordance') {
                return;
            }
            echo '<div class="updated notice is-dismissible" id="discordance-setup-prompt"><p><a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=discordance">Please configure <strong>Discordance</strong> plugin</a></p></div>';
        }
        add_action('admin_notices', 'discordance_warning');
    }
}
function discordance_config()
{
    global $discordance_opts;
    if (!current_user_can('manage_options')) {
        return;
    }
    include('config.php');
}
function discordance_menu()
{
    if (function_exists('add_menu_page')) {
        add_menu_page('Discordance &lsaquo; Settings', 'Discordance', 'manage_options', 'discordance', 'discordance_config', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgdmlld0JveD0iMCAwIDUwMCA1MDAiCiAgIHZlcnNpb249IjEuMSIKICAgaWQ9InN2ZzM0IgogICBzb2RpcG9kaTpkb2NuYW1lPSJkaXNjb3JkYW5jZS5zdmciCiAgIGlua3NjYXBlOnZlcnNpb249IjEuMC4yLTIgKGU4NmM4NzA4NzksIDIwMjEtMDEtMTUpIj4KICA8bWV0YWRhdGEKICAgICBpZD0ibWV0YWRhdGE0MCI+CiAgICA8cmRmOlJERj4KICAgICAgPGNjOldvcmsKICAgICAgICAgcmRmOmFib3V0PSIiPgogICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PgogICAgICAgIDxkYzp0eXBlCiAgICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz4KICAgICAgPC9jYzpXb3JrPgogICAgPC9yZGY6UkRGPgogIDwvbWV0YWRhdGE+CiAgPGRlZnMKICAgICBpZD0iZGVmczM4IiAvPgogIDxzb2RpcG9kaTpuYW1lZHZpZXcKICAgICBwYWdlY29sb3I9IiNmZmZmZmYiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgYm9yZGVyb3BhY2l0eT0iMSIKICAgICBvYmplY3R0b2xlcmFuY2U9IjEwIgogICAgIGdyaWR0b2xlcmFuY2U9IjEwIgogICAgIGd1aWRldG9sZXJhbmNlPSIxMCIKICAgICBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMCIKICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIgogICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iMTkyMCIKICAgICBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSIxMDE3IgogICAgIGlkPSJuYW1lZHZpZXczNiIKICAgICBzaG93Z3JpZD0iZmFsc2UiCiAgICAgaW5rc2NhcGU6em9vbT0iMS43IgogICAgIGlua3NjYXBlOmN4PSIyNTAiCiAgICAgaW5rc2NhcGU6Y3k9IjE0OC45NzE3NyIKICAgICBpbmtzY2FwZTp3aW5kb3cteD0iMTkxMiIKICAgICBpbmtzY2FwZTp3aW5kb3cteT0iLTgiCiAgICAgaW5rc2NhcGU6d2luZG93LW1heGltaXplZD0iMSIKICAgICBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJzdmczNCIgLz4KICA8cGF0aAogICAgIGlkPSJyZWN0MjYiCiAgICAgc3R5bGU9ImZpbGw6IHJnYigxMTQsIDEzNywgMjE4KTsiCiAgICAgZD0iTSAxNi4yNzM0MzggMTYuMjczNDM4IEwgMTYuMjczNDM4IDQ4My43MjY1NiBMIDQ4My43MjY1NiA0ODMuNzI2NTYgTCA0ODMuNzI2NTYgMTYuMjczNDM4IEwgMTYuMjczNDM4IDE2LjI3MzQzOCB6IE0gNzcuMTUyMzQ0IDcyLjUgTCA0MjcuNSA3Mi41IEwgNDI3LjUgNDI2LjM3MzA1IEwgNzQuMjQ4MDQ3IDQyNi4zNzMwNSBMIDcyLjUgNDI3LjUgTCA3My4wNDg4MjggNDI2Ljk2Mjg5IEwgNzMuMDQ4ODI4IDM3NS42NjAxNiBMIDExNy4zOTY0OCAzNzUuNjYwMTYgTCAxMTcuMzk2NDggMTIzLjQxOTkyIEwgNzcuMTUyMzQ0IDEyMy40MTk5MiBMIDc3LjE1MjM0NCA3Mi41IHogTSAyNzUgMTI0IEwgMjc1IDI1OSBMIDMwOCAyNTkgTCAzMDggMTI0IEwgMjc1IDEyNCB6IE0gMzUwIDEyNCBMIDM1MCAyNTkgTCAzODMgMjU5IEwgMzgzIDEyNCBMIDM1MCAxMjQgeiAiIC8+Cjwvc3ZnPgo=');
    }
}
function discordance_js()
{
    global $discordance_version;
    wp_enqueue_script('discordance', plugins_url('/js/main.js', __FILE__), array(), $discordance_version, true);
}
function discordance_css()
{
    global $discordance_version;
    wp_enqueue_style('discordance', plugins_url('/css/style.css', __FILE__), array(), $discordance_version);
}
function discordance($postID)
{
    global $discordance_opts;
    $status = get_post_status($postID);
    if (get_post_meta($postID, '_discordance', true) !== 'publish' && $status === 'publish') {
        $post = get_post($postID);
        $title = sanitize_text_field($post->post_title);
        $excerpt = sanitize_text_field(
            html_entity_decode(
                (has_excerpt($postID) ? $post->post_excerpt : $post->post_content)
            )
        );
        $variables = array(
            '%author%' => get_the_author_meta('display_name', $post->post_author),
            '%author_url%' => get_the_author_meta('user_url', $post->post_author),
            '%gravatar%' => get_avatar_url($post->post_author, 96, 'retro'),
            '%title%' => trim(substr($title, 0, 248)) . (strlen($title) > 248 ? '[...]' : ''),
            '%excerpt%' => trim(substr($excerpt, 0, 512)) . (strlen($excerpt) > 512 ? '[...]' : ''),
            '%thumbnail%' => get_the_post_thumbnail_url($postID),
            '%link%' => get_permalink($postID)
        );
        $embed = $discordance_opts['format'];
        foreach ($variables as $search => $replace) {
            $embed = str_replace($search, $replace, $embed);
        }
        $hooks = preg_split('/\r\n|\r|\n/', $discordance_opts['webhooks']);
        foreach ($hooks as $hook) {
            $url = trim($hook);
            if (preg_match('/^(https\:\/\/(www\.)?discord\.com\/api\/webhooks\/([0-9]+)\/([a-zA-Z0-9_-]+))/', $url)) {
                $data = wp_remote_post($url, array(
                    'data_format' => 'body',
                    'headers' => array('Content-Type' => 'application/json'),
                    'body' => stripslashes($embed)
                ));
                if ($data['response']['code'] >= 300) {
                    error_log($data['body']);
                }
            }
        }
    }
    update_post_meta($postID, '_discordance', $status);
}
add_action('admin_init', 'discordance_init');
add_action('admin_menu', 'discordance_menu');
add_action('admin_print_scripts-toplevel_page_discordance', 'discordance_js');
add_action('admin_print_styles-toplevel_page_discordance', 'discordance_css');
add_action('publish_post', 'discordance');
add_action('publish_future_post', 'discordance');

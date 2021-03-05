<?php
/*
Plugin Name: Discordance
Plugin URI: https://github.com/duscci/discordance
Description: An WordPress plugin to send your posts to Discord using Webhooks.
Version: 0.1.1
Author: Valdir Ronis
Author URI: https://github.com/duscci
Donate link: https://ko-fi.com/duscci
License: GPLv2 or later
*/

defined('ABSPATH') or die;
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
        add_menu_page('Discordance &lsaquo; Settings', 'Discordance', 'manage_options', 'discordance', 'discordance_config');
    }
}
function discordance_js()
{
    wp_enqueue_script('discordance', WP_PLUGIN_URL . '/discordance/js/main.js', array(), '0.1.1', true);
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
add_action('publish_post', 'discordance');
add_action('publish_future_post', 'discordance');

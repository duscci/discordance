<?php
/*
Plugin Name: Discordance
Plugin URI: https://github.com/duscci/discordance
Description: Send your posts to Discord using Webhooks
Version: 0.1.0
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
    "content":"📢 **New post on the blog!**",
    "embeds": [{
        "title": "%title%",
        "description": "%excerpt%",
        "url": "%link%",
        "thumbnail": {
            "url": "%thumbnail%"
        }
    }]
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
    wp_enqueue_script('discordance', WP_PLUGIN_URL . '/discordance/js/main.js', array(), '0.1.0', true);
}
function discordance($postID)
{
    global $discordance_opts;
    $status = get_post_status($postID);
    if (get_post_meta($postID, '_discordance', true) !== 'publish' && $status === 'publish') {
        $post = get_post($postID);
        $gettitle = sanitize_text_field($post->post_title);
        $getexcerpt = sanitize_text_field(html_entity_decode(get_the_excerpt($postID)));
        $title = trim(substr($gettitle, 0, 248)) . (strlen($gettitle) > 248 ? '[...]' : '');
        $excerpt = trim(substr($getexcerpt, 0, 512)) . (strlen($getexcerpt) > 512 ? '[...]' : '');
        $search = array('%title%', '%excerpt%', '%thumbnail%', '%link%');
        $replace = array($title, $excerpt, get_the_post_thumbnail_url($postID), get_permalink($postID));
        $embed = str_replace($search, $replace, $discordance_opts['format']);
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

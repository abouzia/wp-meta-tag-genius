<?php

/*
Plugin Name: Meta Tags Genius
Description: A powerful WordPress plugin for managing meta tags with ease.
Version: 0.0.1
Author: Yassine Abouzia
License: GPL v2 or later
*/

class MetaTagGenius
{

    public function __construct()
    {
        // Register activation, deactivation, and uninstall hooks
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Add admin actions
        add_action('admin_enqueue_scripts', array($this, 'mgt_admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'mtg_admin_init'));
        add_action('admin_menu', array($this, 'mtg_admin_menu'));
    }

    public function activate()
    {
        get_option('mtg_meta_tags_options') || add_option('mtg_meta_tags_options', []);
    }

    function mgt_admin_enqueue_scripts()
    {
        wp_enqueue_style('mtg_admin_style', plugins_url('assets/css/admin.css', __FILE__));
    }

    function mtg_admin_init()
    {
        add_action('admin_post_mtg_save_action', array($this, 'mtg_save_action'));
    }

    function mtg_admin_menu()
    {
        add_menu_page(
            'Meta Tags Genius',
            'Meta Tags Genius',
            'manage_options',
            'mtg',
            array($this, 'mtg_options_page'),
            'dashicons-admin-site',
            2
        );
    }

    function mtg_options_page()
    {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $html = '<div class="mtg-container">';
        $html .= '<h1 class="mtg-title">Meta Tags Genius</h1>';
        $html .= "<h2>Welcome</h2>";
        $html .= '<form method="post" action="admin-post.php">';
        $html .= '<input type="hidden" name="action" value="mtg_save_action">';
        $html .= wp_nonce_field('mtg_save_action', 'mtg_nonce');
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_author_input">';
        $html .= 'Author: ';
        $html .= '</label>';
        $html .=  "<input type='text' id='mtg_author_input' name='mtg_author_input''>";
        $html .= '</div>';
        $html .=  '<input type="submit" id="mtg_save_button" name="mtg_save_button" value="Save">';
        $html .= '</form>';
        $html .= '</div>';


        echo $html;
    }
}

new MetaTagGenius();

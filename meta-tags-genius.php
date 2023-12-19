<?php

/*
Plugin Name: Meta Tags Genius
Description: A powerful WordPress plugin for managing meta tags with ease.
Version: 0.0.1-beta
Author: Yassine Abouzia
License: GPL v2 or later
*/

class MetaTagsGenius
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
        $this->mtg_get_options();
    }

    function mtg_get_options()
    {
        $mtg_options = get_option('mtg_meta_tags_options', []);

        if (empty($mtg_options)) {
            $defaults = [
                'author' => 'dummy author tag.',
                'content' => 'dummy content tag.'
            ];
            update_option('mtg_meta_tags_options', $defaults);
        }

        return $mtg_options;
    }

    function mgt_admin_enqueue_scripts()
    {
        wp_enqueue_style('mtg_admin_style', plugins_url('assets/css/admin.css', __FILE__));
    }

    function mtg_admin_init()
    {
        add_action('admin_post_mtg_save_action', array($this, 'mtg_admin_save'));
    }

    function mtg_admin_save()
    {
        // Check if user can access this page
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Check if nonce is valid
        check_admin_referer('mtg_save_action', 'mtg_nonce');

        // check if all post values not empty
        if (empty($_POST['mtg_author_input']) || empty($_POST['mtg_content_input'])) {
            wp_die(__('Please fill all the fields.'));
        }

        // convert post values to variables
        extract($_POST);

        // sanitize post values and save them to database
        $updated_options = [
            'author' => sanitize_text_field($mtg_author_input),
            'content' => sanitize_text_field($mtg_content_input)
        ];
        update_option('mtg_meta_tags_options', $updated_options);

        // redirect to options page
        wp_redirect(
            add_query_arg(
                [
                    'page' => 'mtg',
                    'saved' => 'true',
                ],
                admin_url('admin.php')
            )
        );
        exit;
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

    function mtg_meta_tags_options_html($meta_tags_options)
    {
        // meta tags array to variables
        extract($meta_tags_options);

        // html
        $html = '<div class="mtg-container">';
        $html .= '<h1 class="mtg-title">Meta Tags Genius</h1>';

        // form
        $html .= '<form method="post" action="admin-post.php">';
        $html .= '<input type="hidden" name="action" value="mtg_save_action">';
        $html .= wp_nonce_field('mtg_save_action', 'mtg_nonce');

        // author form group
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_author_input">';
        $html .= 'Meta Author: ';
        $html .= '</label>';
        $html .=  "<input type='text' id='mtg_author_input' name='mtg_author_input' value='$author'>";
        $html .= '</div>';

        // content form group
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_content_input">';
        $html .= 'Meta Conent: ';
        $html .= '</label>';
        $html .= "<textarea id='mtg_content_input' name='mtg_content_input' rows='4' cols='50'>";
        $html .= $content;
        $html .= '</textarea>';
        $html .= '</div>';

        // save button
        $html .=  '<input type="submit" id="mtg_save_button" name="mtg_save_button" value="Save">';
        $html .= '</form>';
        $html .= '</div>';


        echo $html;
    }

    function mtg_dismissible_message($message) {

        $html = '<div id="message" class="updated notice is-dismissible">';
        $html .= "<p>$message</p>";
        $html .= '</div>';

        echo $html;
    }

    function mtg_options_page()
    {

        // check if user can access this page
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }


        // check if saved 
        if (isset($_GET['saved'])) {
            $this->mtg_dismissible_message('Settings saved.');
        } else {
            $this->mtg_dismissible_message('Setting not saved.');
        }

        // get meta tags options
        $meta_tags_options = get_option('mtg_meta_tags_options');

        // show meta tags options page
        $this->mtg_meta_tags_options_html($meta_tags_options);
    }
}


new MetaTagsGenius();

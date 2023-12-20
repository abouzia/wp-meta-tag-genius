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

        // Add meta tags to the head
        add_action('wp_head', array($this, 'mtg_meta_tags_to_head'), 1);
    }



    public function activate()
    {
        $this->mtg_get_meta_tags();
    }

    function mtg_get_meta_tags()
    {
        $mtg_meta_tags = get_option('mtg_meta_tags_options', []);

        if (empty($mtg_options)) {
            $defaults = [
                'author' => 'dummy author tag.',
                'description' => 'dummy content tag.',
                'keywords' => 'dummy keywords tag.',
            ];
            update_option('mtg_meta_tags_options', $defaults);
        }

        return $mtg_meta_tags;
    }

    function mgt_admin_enqueue_scripts()
    {
        wp_enqueue_style('mtg_admin_style', plugins_url('assets/css/admin.css', __FILE__));
    }

    function mtg_meta_tags_to_head()
    {
        $meta_tags = $this->mtg_get_meta_tags();
        extract($meta_tags);

        foreach ($meta_tags as $key => $value) {
            if (empty($value)) {
                continue;
            }
            echo "<meta name='$key' content='$value'>\n";
        }
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
        if (
            empty($_POST['mtg_author_input']) ||
            empty($_POST['mtg_description_input']) ||
            empty($_POST['mtg_keywords_input'])
        ) {
            wp_die(__('Please fill all the fields.'));
        }

        // convert post values to variables
        extract($_POST);

        // sanitize post values and save them to database
        $updated_options = [
            'author' => sanitize_text_field($mtg_author_input),
            'description' => sanitize_text_field($mtg_description_input),
            'keywords' => sanitize_text_field($mtg_keywords_input),
        ];
        $is_updated = update_option('mtg_meta_tags_options', $updated_options);

        // redirect to options page
        wp_redirect(
            add_query_arg(
                [
                    'page' => 'mtg',
                    'updated' => $is_updated ? 'true' : 'false',
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

    function mtg_meta_tags_options_html($meta_tags)
    {
        // meta tags array to variables
        extract($meta_tags);

        // html
        $html = '<div class="mtg-container">';
        $html .= '<h1 class="mtg-title">Meta Tags Genius</h1>';

        // form
        $html .= '<form method="post" action="admin-post.php">';
        $html .= '<input type="hidden" name="action" value="mtg_save_action">';
        $html .= wp_nonce_field('mtg_save_action', 'mtg_nonce');

        // form groups
        foreach ($meta_tags as $key => $value) {
            $html .= '<div class="mtg-form-group">';
            $html .= '<label for="mtg_' . $key . '_input">';
            $html .= ucfirst($key) . ': ';
            $html .= '</label>';
            $html .=  "<input type='text' id='mtg_" . $key . "_input' name='mtg_" . $key . "_input' value='$value'>";
            $html .= '</div>';
        }

        /*
        // author form group
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_author_input">';
        $html .= 'Meta Author: ';
        $html .= '</label>';
        $html .=  "<input type='text' id='mtg_author_input' name='mtg_author_input' value='$author'>";
        $html .= '</div>';

        

        // description form group
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_description_input">';
        $html .= 'Meta Description: ';
        $html .= '</label>';
        $html .= "<textarea id='mtg_description_input' name='mtg_description_input' rows='4' cols='50'>";
        $html .= $description;
        $html .= '</textarea>';
        $html .= '</div>';

        // keywords form group
        $html .= '<div class="mtg-form-group">';
        $html .= '<label for="mtg_keywords_input">';
        $html .= 'Meta Keywords: ';
        $html .= '</label>';
        $html .=  "<input type='text' id='mtg_keywords_input' name='mtg_keywords_input' value='$keywords'>";
        $html .= '</div>';

        */

        // save button
        $html .=  '<input type="submit" id="mtg_save_button" name="mtg_save_button" value="Save">';
        $html .= '</form>';
        $html .= '</div>';


        echo $html;
    }

    function mtg_dismissible_message($message)
    {

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
        if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
            $this->mtg_dismissible_message('Settings saved.');
        }

        // get meta tags options
        $meta_tags = $this->mtg_get_meta_tags();

        // show meta tags options page
        $this->mtg_meta_tags_options_html($meta_tags);
    }
}


new MetaTagsGenius();

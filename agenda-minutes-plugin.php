<?php
/**
 * Plugin Name: Agenda & Minutes by OnPoint Insights
 * Description: The "Agenda Minutes Plugin" simplifies meeting management in WordPress, allowing you to create and document agendas and minutes, collaborate with your team, and use customizable templates for efficient meeting organization.
 * Version: 1.0.0
 * Author: OnPoint Insights LLC
 * Author URI: https://www.onpointinsights.us/
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly    

// Register custom post types and taxonomy
function create_custom_post_types()
{
    // Custom post type for Agenda
    register_post_type(
        'agenda',
        array(
            'labels' => array(
                'name' => __('Agenda/Minutes'),
                'singular_name' => __('Agenda'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        )
    );

    // Add custom taxonomy 'Years' for both 'Agenda' and 'Minutes'
    register_taxonomy(
        'years',
        array('agenda', 'minutes'),
        // Post types to which the taxonomy will be applied
        array(
            'label' => __('Years', 'agenda_minutes_plugin'),
            'rewrite' => array('slug' => 'year'),
            'hierarchical' => true,
        )
    );

    // Add meta fields to Agenda and Minutes
    add_action('add_meta_boxes', 'add_agenda_minutes_meta_boxes');
}

add_action('init', 'create_custom_post_types');

// Callback function to add meta boxes for Agenda and Minutes
function add_agenda_minutes_meta_boxes()
{
    // Add meta box for Calendar field
    add_meta_box(
        'agenda_minutes_calendar',
        __('Calendar', 'agenda_minutes_plugin'),
        'render_agenda_minutes_calendar_meta_box',
        array('agenda', 'minutes'),
        'normal',
        'default'
    );

    // Add meta box for Upload Option field
    add_meta_box(
        'agenda_minutes_upload_option',
        __('Upload Option', 'agenda_minutes_plugin'),
        'render_agenda_minutes_upload_option_meta_box',
        array('agenda', 'minutes'),
        'normal',
        'default'
    );

    // Add meta box for Select Type field
    add_meta_box(
        'agenda_minutes_select_type',
        __('Select Type', 'agenda_minutes_plugin'),
        'render_agenda_minutes_select_type_meta_box',
        array('agenda', 'minutes'),
        'side',
        'default'
    );
}

// Callback function to render the Calendar meta box content
function render_agenda_minutes_calendar_meta_box($post)
{
    $calendar_date = get_post_meta($post->ID, 'calendar', true);
    echo '<label for="agenda_minutes_calendar">';
    echo '<input type="date" id="agenda_minutes_calendar" name="agenda_minutes_calendar" value="' . esc_attr($calendar_date) . '" />';
    echo '</label>';
}

// Callback function to render the Select Type meta box content
function render_agenda_minutes_select_type_meta_box($post)
{
    error_log('save_agenda_minutes_meta_boxes called for post ID: ' . $post->ID);
    wp_nonce_field('save_agenda_minutes_meta_boxes', 'agenda_minutes_meta_box_nonce');
    $select_type = get_post_meta($post->ID, 'select_type', true);

    echo '<label for="agenda_minutes_select_type_agenda">';
    echo '<input type="radio" id="agenda_minutes_select_type_agenda" name="agenda_minutes_select_type" value="agenda"' . checked('agenda', $select_type, false) . ' />';
    echo ' Agenda</label><br>';

    echo '<label for="agenda_minutes_select_type_minute">';
    echo '<input type="radio" id="agenda_minutes_select_type_minute" name="agenda_minutes_select_type" value="minute"' . checked('minute', $select_type, false) . ' />';
    echo ' Minutes</label>';
}

// Callback function to render the Upload Option meta box content
function render_agenda_minutes_upload_option_meta_box($post)
{
    $upload_option = get_post_meta($post->ID, 'upload_option', true);
    echo '<div>';
    echo '<input type="text" id="agenda_minutes_upload_option_media" name="agenda_minutes_upload_option" value="' . esc_attr($upload_option) . '" />';
    echo '<input type="button" class="button" id="agenda_minutes_upload_option_media_button" value="Choose from Media Library" />';
    echo '<div class="submitbox"><b class="submitdelete deletion">* allowed file - pdf</b></div>';
    echo '</div>';
}

// Enqueue a custom script to handle the media library functionality
function agenda_enqueue_custom_scripts()
{
    wp_enqueue_script(
        'agenda_custom_media',
        plugin_dir_url(__FILE__) . 'assets/js/agenda_custom_media.js',
        array('jquery', 'media-upload', 'thickbox'),
        '1.0.0',
        true
    );
}

add_action('admin_enqueue_scripts', 'agenda_enqueue_custom_scripts');

// Save meta box data
function agenda_save_meta_boxes($post_id)
{
    if (!isset($_POST['agenda_minutes_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['agenda_minutes_meta_box_nonce']), 'save_agenda_minutes_meta_boxes')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['agenda_minutes_calendar'])) {
        update_post_meta($post_id, 'calendar', sanitize_text_field($_POST['agenda_minutes_calendar']));
    }

    if (isset($_POST['agenda_minutes_upload_option'])) {
        update_post_meta($post_id, 'upload_option', esc_url_raw($_POST['agenda_minutes_upload_option']));
    }

    if (isset($_POST['agenda_minutes_select_type'])) {
        update_post_meta($post_id, 'select_type', sanitize_text_field($_POST['agenda_minutes_select_type']));
    }
}

add_action('save_post', 'agenda_save_meta_boxes');

function custom_post_type_admin_notice()
{
    global $post;
    if (isset($post) && $post->post_type == 'agenda') {
        echo '<div class="notice notice-info">
            <h2>Agenda/Minutes Shortcode:</h2>
            <p>Use the following shortcode to customize the style and color of your agenda or minutes table: <strong>[agenda_table style="" color=""]</strong></p>
            <p>For the "style" parameter, you can choose from options such as <b>"style_one"</b> and <b>"style_two"</b>. The "color" parameter allows you to specify a hexadecimal/rgba color code.</p>
        </div>';
    }
}

add_action('admin_notices', 'custom_post_type_admin_notice');

include "agenda_shortcode.php";

// Enqueue the required CSS styles for the "Download PDF" link
function agenda_enqueue_custom_styles()
{
    wp_enqueue_style(
        'agenda_custom_styles',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(),
        '1.0.1'
    );
    wp_enqueue_style(
        'frontend_custom_styles',
        plugin_dir_url(__FILE__) . 'assets/bootstrap-5.0.2/css/bootstrap.min.css',
        array(),
        '1.0.1'
    );
}

add_action('wp_enqueue_scripts', 'agenda_enqueue_custom_styles', 1);

// Enqueue the required JavaScript for dropdown, tabs, and accordion
function agenda_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'agenda_dropdown',
        plugin_dir_url(__FILE__) . 'assets/js/agenda_dropdown.js',
        array('jquery'),
        '1.0.1',
        true
    );
    wp_enqueue_script(
        'agenda_tabs',
        plugin_dir_url(__FILE__) . 'assets/js/agenda_tabs.js',
        array('jquery'),
        '1.0.1',
        true
    );
    wp_enqueue_script(
        'frontend_custom_js',
        plugin_dir_url(__FILE__) . 'assets/bootstrap-5.0.2/js/bootstrap.min.js',
        array('jquery'),
        '1.0.1',
        true
    );
}

add_action('wp_enqueue_scripts', 'agenda_enqueue_scripts', 1);
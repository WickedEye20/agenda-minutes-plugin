<?php

/**
 * Plugin Name: Agenda and Minutes
 * Description: Creates custom post types for Agenda and Minutes.
 * Version: 1.0.0
 * Author: OnPoint
 * Author URI: https://www.onpointinsights.us/
 */

// Register custom post types and taxonomy


function create_custom_post_types()
{
    // Custom post type for Agenda
    register_post_type(
        'agenda',
        array(
            'labels' => array(
                'name' => __('Agendas'),
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
        // Unique ID for this meta box
        __('Calendar', 'agenda_minutes_plugin'),
        'render_agenda_minutes_calendar_meta_box',
        array('agenda', 'minutes'),
        // Custom post types where the meta box should appear
        'normal',
        'default'
    );

    // Add meta box for Upload Option field
    add_meta_box(
        'agenda_minutes_upload_option',
        // Unique ID for this meta box
        __('Upload Option', 'agenda_minutes_plugin'),
        'render_agenda_minutes_upload_option_meta_box',
        array('agenda', 'minutes'),
        // Custom post types where the meta box should appear
        'normal',
        'default'
    );

    // Add meta box for Select Type field
    add_meta_box(
        'agenda_minutes_select_type',
        // Unique ID for this meta box
        __('Select Type', 'agenda_minutes_plugin'),
        'render_agenda_minutes_select_type_meta_box',
        array('agenda', 'minutes'),
        // Custom post types where the meta box should appear
        'side',
        'default'
    );
}


// Callback function to render the Calendar meta box content
function render_agenda_minutes_calendar_meta_box($post)
{
    // Retrieve the saved value, if available
    $calendar_date = get_post_meta($post->ID, 'calendar', true);

    // Output the HTML input
    echo '<label for="agenda_minutes_calendar">';
    echo '<input type="date" id="agenda_minutes_calendar" name="agenda_minutes_calendar" value="' . esc_attr($calendar_date) . '" />';
    echo '</label>';
}

// Callback function to render the Select Type meta box content
function render_agenda_minutes_select_type_meta_box($post)
{
    error_log('save_agenda_minutes_meta_boxes called for post ID: ' . $post->ID);

    // Add nonce for security
    wp_nonce_field('save_agenda_minutes_meta_boxes', 'agenda_minutes_meta_box_nonce');

    // Retrieve the saved value, if available
    $select_type = get_post_meta($post->ID, 'select_type', true);

    // Output the HTML input
    echo '<label for="agenda_minutes_select_type_agenda">';
    echo '<input type="radio" id="agenda_minutes_select_type_agenda" name="agenda_minutes_select_type" value="agenda"' . checked('agenda', $select_type, false) . ' />';
    echo ' Agenda</label><br>';

    echo '<label for="agenda_minutes_select_type_minute">';
    echo '<input type="radio" id="agenda_minutes_select_type_minute" name="agenda_minutes_select_type" value="minute"' . checked('minute', $select_type, false) . ' />';
    echo ' Minute</label>';
}


// Callback function to render the Upload Option meta box content
function render_agenda_minutes_upload_option_meta_box($post)
{
    // Retrieve the saved value, if available
    $upload_option = get_post_meta($post->ID, 'upload_option', true);

    // Output the HTML input
    echo '<div>';
    echo '<input type="text" id="agenda_minutes_upload_option_media" name="agenda_minutes_upload_option" value="' . esc_attr($upload_option) . '" />';
    echo '<input type="button" class="button" id="agenda_minutes_upload_option_media_button" value="Choose from Media Library" onclick="openMediaLibrary(event);" />';
    echo '</div>';
}

// Enqueue a custom script to handle the media library functionality
function agenda_enqueue_custom_scripts()
{
    wp_enqueue_script(
        'agenda_custom_media',
        plugin_dir_url(__FILE__) . 'js/agenda_custom_media.js',
        // Use the correct path to the js folder
        array('jquery', 'media-upload', 'thickbox'),
        '1.0.0',
        true
    );
}

add_action('admin_enqueue_scripts', 'agenda_enqueue_custom_scripts');



// Save meta box data
function agenda_save_meta_boxes($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['agenda_minutes_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['agenda_minutes_meta_box_nonce'], 'save_agenda_minutes_meta_boxes')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Check the user's permissions.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Sanitize and save the Calendar field
    if (isset($_POST['agenda_minutes_calendar'])) {
        update_post_meta($post_id, 'calendar', sanitize_text_field($_POST['agenda_minutes_calendar']));
    }

    // Sanitize and save the Upload Option field
    if (isset($_POST['agenda_minutes_upload_option'])) {
        update_post_meta($post_id, 'upload_option', esc_url_raw($_POST['agenda_minutes_upload_option'])); // Use esc_url_raw to save the media URL
    }

    // Sanitize and save the Select Type field
    if (isset($_POST['agenda_minutes_select_type'])) {
        update_post_meta($post_id, 'select_type', sanitize_text_field($_POST['agenda_minutes_select_type']));
    }
}
add_action('save_post', 'agenda_save_meta_boxes');

include "agenda_shortcode.php";

// Enqueue the custom JavaScript file
// function agenda_enqueue_custom_script() {
//     wp_enqueue_script(
//         'agenda_custom_script',
//         plugin_dir_url(__FILE__) . 'js/custom.js',
//         array('jquery'),
//         '1.0.0',
//         true
//     );
// }

// add_action('wp_enqueue_scripts', 'agenda_enqueue_custom_script');

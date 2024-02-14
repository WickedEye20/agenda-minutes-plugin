<?php
// Security check to prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function agenda_table_shortcode($atts)
{
    ob_start();
    $args = shortcode_atts(
        array(
            'style' => 'style_one',
            'color' => '#0d6efd',
            // Define your arguments and default values here
        ),
        $atts
    );
    $style = sanitize_text_field($args['style']);
    $color = sanitize_hex_color($args['color']);

    // Global Style
    echo '<style>
            :root{
                --bg_color: ' . esc_attr($color) . ';
            }
        </style>';

    echo '<div class="agenda-main-container container-fluid p-0 p-md-5">';
    $taxonomy = 'years';
    $terms = get_terms($taxonomy, array('hide_empty' => true));
    $first_term = reset($terms);

    if ($terms) {
        echo '<div class="select_year_main d-flex align-items-center flex-wrap"><span class="fw-bold text-secondary">Select Year</span>';
        echo '<select class="agenda-years-dropdown form-select w-auto mx-2 cursor-pointer" name="year">';
        echo '<option value="select_year" disabled>' . esc_html__('Select Year', 'agenda_plugin') . '</option>';
        foreach ($terms as $key => $term) {
            $selected = ($key === 0) ? 'selected' : '';
            echo '<option value="' . esc_attr($term->slug) . '" ' . esc_attr($selected) . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
        echo '<div class="result_year fs-4 mt-md-0 mt-2">Showing result for <span id="selected-result-year" class="fw-bold text-primary">' . esc_html($first_term->name) . '</span></div>';
        echo '</div>';
    }

    echo '<div class="agenda-tabs nav nav-pills nav-fill mt-4 justify-content-center">';
    echo '<a class="_bg_color agenda-tab nav-link text-decoration-none active me-3 flex-grow-0 shadow fw-bold" data-tab="agenda" data-bs-toggle="tab" href="#agenda_minutes-tab-1" role="tab"
    aria-controls="agenda_minutes-tab-1" aria-selected="true">Agenda</a>';
    echo '<a class="_bg_color agenda-tab nav-link text-decoration-none ms-3 flex-grow-0 shadow fw-bold" data-tab="minute" data-bs-toggle="tab" href="#agenda_minutes-tab-2" role="tab"
    aria-controls="agenda_minutes-tab-2" aria-selected="false">Minute</a>';
    echo '</div>';

    echo '<div class="tab-content mt-4">';
    // Output the container div for Agenda content
    echo '<div class="agenda-content tab-pane fade show active" id="agenda_minutes-tab-1">';
    if ($style == 'style_one') {
        echo '<div class="accordion-content" id="#agenda_minutes-content">';
        echo '<div class="agenda-table row">';
        agenda_display_posts_by_type('agenda', $terms, $style);
        echo '</div>';
        echo '</div>';
    } elseif ($style == 'style_two') {
        echo '<div class="accordion table-style_two" id="agenda-style_two">
        <div class="accordion-item border-0">';
        agenda_display_posts_by_type('agenda', $terms, $style);
        echo '</div>
        </div>';
    }
    echo '</div>';

    // Output the container div for Minute content
    echo '<div class="minutes-content tab-pane fade" id="agenda_minutes-tab-2">';
    if ($style == 'style_one') {
        echo '<div class="accordion-content">';
        echo '<div class="minute-table row">';
        agenda_display_posts_by_type('minute', $terms, $style);
        echo '</div>';
        echo '</div>';
    } elseif ($style == 'style_two') {
        echo '<div class="accordion table-style_two" id="minute-style_two">
        <div class="accordion-item border-0">';
        agenda_display_posts_by_type('minute', $terms, $style);
        echo '</div>
        </div>';
    }
    echo '</div>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('agenda_table', 'agenda_table_shortcode');

function agenda_display_posts_by_type($type, $terms, $style)
{
    $groupedPosts = array();

    foreach ($terms as $term) {
        $args = array(
            'post_type' => 'agenda',
            'tax_query' => array(
                array(
                    'taxonomy' => 'years',
                    'field' => 'slug',
                    'terms' => $term->slug,
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'select_type',
                    'value' => $type,
                ),
            ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $calendarDate = get_post_meta(get_the_ID(), 'calendar', true);
                $uploadOption = esc_url(get_post_meta(get_the_ID(), 'upload_option', true));
                $formattedDate = date('F Y', strtotime($calendarDate));
                $key = sanitize_title($formattedDate);

                if (!isset($groupedPosts[$key])) {
                    $groupedPosts[$key] = array(
                        'date' => esc_html($formattedDate),
                        'date_term' => esc_html($term->name),
                        'posts' => array(),
                        'id' => $key,
                    );
                }

                $groupedPosts[$key]['posts'][] = array(
                    'title' => esc_html(get_the_title()),
                    'permalink' => esc_url(get_permalink()),
                    'date' => esc_html($calendarDate),
                    'uploads' => esc_url($uploadOption),
                );
            }
            wp_reset_postdata();
        } else {
            echo '<div class="agenda_main col-xxl-4 col-xl-4 col-md-6 agenda_main-' . sanitize_title($term->name) . '" id="agenda_main-' . sanitize_title($term->name) . '">';
            echo '<h6>' . esc_html__('No Posts Available for ', 'agenda_plugin') . esc_html($term->name) . '</h6>';
            echo '</div>';
        }
    }

    if ($style == 'style_one') {
        foreach ($groupedPosts as $group) {
            echo '<div class="agenda_main col-xxl-4 col-xl-4 col-md-6 agenda_main-' . sanitize_title($group['date_term']) . '">';
            echo '<h6>' . esc_html($group['date']) . '</h6>';
            echo '<table class="table border-0">';
            echo '<thead>
                    <tr>
                        <th scope="col" class="bg-primary text-white">DESCRIPTION</th>
                        <th scope="col" class="bg-primary text-white">MEETING DATE</th>
                        <th scope="col" class="bg-primary text-white">ACTION</th>
                    </tr>
                </thead>';

            foreach ($group['posts'] as $post) {
                $uploads = esc_url($post['uploads']);
                $date = esc_html($post['date']);
                echo '<tr>';
                echo '<td>' . esc_html($post['title']) . '</td>';
                echo '<td><span class="post-date">' . $date . '</span></td>';
                if ($uploads == null) {
                    echo '<td>No PDF Available</td>';
                } else {
                    echo '<td><a href="' . $uploads . '" data-pdf-url="' . $uploads . '" download="' . esc_attr(get_the_title()) . '.pdf">Download PDF</a></td>';
                }
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
        }
    }

    if ($style == 'style_two') {
        $i = 0;
        foreach ($groupedPosts as $group) {
            echo '<div class="mb-3 agenda_main col-md-12 agenda_main-' . sanitize_title($group['date_term']) . '">';
            echo '<h2 class="accordion-header m-0" id="style_two_accordion_' . $i . '">
        <button class="d-flex _bg_color accordion-button border w-100 shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $type . "-" . esc_html($group['id']) . '" aria-expanded="false" aria-controls="' . $type . "-" . esc_html($group['id']) . '">
        ' . esc_html($group['date']) . '
        </button>
        </h2>';
            echo '<div id="' . $type . "-" . esc_html($group['id']) . '" class="accordion-collapse collapse" aria-labelledby="style_two_accordion_' . $i . '" data-bs-parent="#' . $type . '-' . 'style_two">';
            echo '<div class="accordion-body p-3 px-2">';
            echo '<table class="table border-0">';
            echo '<thead>
                    <tr>
                        <th scope="col" class="_bg_color text-white border-0">DESCRIPTION</th>
                        <th scope="col" class="_bg_color text-white border-0">MEETING DATE</th>
                        <th scope="col" class="_bg_color text-white border-0">ACTION</th>
                    </tr>
                </thead>';

            foreach ($group['posts'] as $post) {
                echo '<tr>';
                echo '<td class="border-0">' . esc_html($post['title']) . '</td>';
                echo '<td class="border-0"><span class="post-date">' . esc_html($calendarDate) . '</span></td>';
                if ($uploadOption == null) {
                    echo '<td class="border-0">No PDF Available</td>';
                } else {
                    echo '<td class="border-0"><a href="' . esc_url($uploadOption) . '" data-pdf-url="' . esc_url($uploadOption) . '" download="' . esc_attr(get_the_title()) . '.pdf">Download PDF</a></td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        $i++;
    }
}

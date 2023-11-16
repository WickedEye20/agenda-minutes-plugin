<?php
// Shortcode to display Agenda in a table with category dropdown and tabs
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
    $style = $args['style'];
    $color = $args['color'];

    // Global Style
    echo '<style>
            :root{
                --bg_color: ' . $color . ';
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
            if ($key === 0) { // Set the first option as selected
                echo '<option value="' . $term->slug . '" selected>' . $term->name . '</option>';
            } else {
                echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
            }
        }
        echo '</select>';
        echo '<div class="result_year fs-4 mt-md-0 mt-2">Showing result for <span id="selected-result-year" class="fw-bold text-primary">' . $first_term->name . '</span></div>';
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
    // echo '<h3 class="accordion-title d-none">Agenda</h3>';
    if ($style == 'style_one') {
        // Style One
        echo '<div class="accordion-content" id="#agenda_minutes-content">';
        echo '<div class="agenda-table row">';
        // Call the function to fetch Agenda posts
        agenda_display_posts_by_type('agenda', $terms, $style);
        echo '</div>';
        echo '</div>';
    } else if ($style == 'style_two') {
        // Style Two
        echo '<div class="accordion table-style_two" id="agenda-style_two">
        <div class="accordion-item border-0">';
        // Call the function to fetch Minute posts
        agenda_display_posts_by_type('agenda', $terms, $style);
        echo '</div>
        </div>';
    }

    echo '</div>';

    // Output the container div for Minute content
    echo '<div class="minutes-content tab-pane fade" id="agenda_minutes-tab-2">';
    // echo '<h3 class="accordion-title d-none">Minutes</h3>';
    if ($style == 'style_one') {
        // Style One
        echo '<div class="accordion-content">';
        echo '<div class="minute-table row">';

        // Call the function to fetch Minute posts
        agenda_display_posts_by_type('minute', $terms, $style);

        echo '</div>';
        echo '</div>';
    } else if ($style == 'style_two') {
        // Style Two
        echo '<div class="accordion table-style_two" id="minute-style_two">
        <div class="accordion-item border-0">';
        // Call the function to fetch Minute posts
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
    // Initialize an empty array to store grouped posts
    $groupedPosts = array();

    foreach ($terms as $term) {
        // WP_Query code
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

                // Get the 'calendar' post meta value
                $calendarDate = get_post_meta(get_the_ID(), 'calendar', true);
                $uploadOption = get_post_meta(get_the_ID(), 'upload_option', true);

                // Format the date
                $formattedDate = date('F Y', strtotime($calendarDate));

                // Create a key based on the formatted date
                $key = sanitize_title($formattedDate);

                // Add the post to the corresponding group
                if (!isset($groupedPosts[$key])) {
                    $groupedPosts[$key] = array(
                        'date' => $formattedDate,
                        'date_term' => $term->name,
                        'posts' => array(),
                        'id' => $key,
                    );
                }

                // Add the post to the group
                $groupedPosts[$key]['posts'][] = array(
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    // Add other post data as needed...
                );
            }
            wp_reset_postdata();
        } else {
            // If no posts are found for this year, display a message
            echo '<div class="agenda_main col-xxl-4 col-xl-4 col-md-6 agenda_main-' . sanitize_title($term->name) . '" id="agenda_main-' . sanitize_title($term->name) . '">';
            echo '<h6>' . esc_html__('No Posts Available for ', 'agenda_plugin') . esc_html($term->name) . '</h6>';
            echo '</div>';
        }
    }

    // Loop through the grouped posts and display them
    // Style one loop
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
                echo '<tr>';
                echo '<td>' . esc_html($post['title']) . '</td>';
                echo '<td><span class="post-date">' . esc_html($calendarDate) . '</span></td>';
                if ($uploadOption == null) {
                    echo '<td>No PDF Available</td>';
                } else {
                    // Add PDF download links or other post data here...
                    echo '<td><a href="' . $uploadOption . '" data-pdf-url="' . esc_url($uploadOption) . '" download="' . get_the_title() . '.pdf">Download PDF</a></td>';
                }
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
        }
    }

    // Style two loop

    if ($style == 'style_two') {
        foreach ($groupedPosts as $group) {
            echo '<div class="mb-3 agenda_main col-md-12 agenda_main-' . sanitize_title($group['date_term']) . '">';
            echo '<h2 class="accordion-header m-0" id="headingOne">
        <button class="d-flex _bg_color accordion-button border w-100 shadow-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $type . "-" . esc_html($group['id']) . '" aria-expanded="false" aria-controls="' . $type . "-" . esc_html($group['id']) . '">
        ' . esc_html($group['date']) . '
        </button>
        </h2>';
            echo '<div id="' . $type . "-" . esc_html($group['id']) . '" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#' . $type . '-' . 'style_two">';
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
                    echo '<td class="border-0"><a href="' . $uploadOption . '" data-pdf-url="' . esc_url($uploadOption) . '" download="' . get_the_title() . '.pdf">Download PDF</a></td>';
                }
                // Add PDF download links or other post data here...
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

}

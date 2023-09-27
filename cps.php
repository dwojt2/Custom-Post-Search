<?php
/**
 * Plugin Name: Custom Post Search
 * Description: A plugin with a custom search form. It also displays a post based on search results.
 * Version: 1.0.0
 * Author: Dominik Wojtysiak
 * Author URI: https://wojtysiak.one
 */


// Register a settings page
function custom_post_search_settings() {
    add_options_page(
        'Post Search Settings',
        'Post Search Settings',
        'manage_options',
        'post_search_settings',
        'custom_post_search_settings_page'
    );
}
add_action('admin_menu', 'custom_post_search_settings');

// Callback function to display the settings page
function custom_post_search_settings_page() {
    ?>
    <div class="wrap">
        <h2>Post Search Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('custom_post_search_options'); ?>
            <?php do_settings_sections('custom_post_search_settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register and define plugin settings
function custom_post_search_register_settings() {
    register_setting('custom_post_search_options', 'custom_post_search_default_post_type', 'sanitize_text_field');

    add_settings_section('custom_post_search_section', 'Default Post Type', 'custom_post_search_section_callback', 'custom_post_search_settings');

    // This settings field displays the list of available post types
    add_settings_field('custom_post_search_available_post_types', 'Available Post Types', 'custom_post_search_available_post_types_callback', 'custom_post_search_settings', 'custom_post_search_section');
}
add_action('admin_init', 'custom_post_search_register_settings');

// Section callback function
function custom_post_search_section_callback() {
    echo '<p>Select the default post type for the post search form.</p>';
}

// Available post types callback function
function custom_post_search_available_post_types_callback() {
    // Get all registered post types
    $post_types = get_post_types(array('public' => true), 'names');
    ?>
    <ul>
        <?php
        foreach ($post_types as $type) {
            echo '<li>' . ucfirst($type) . '</li>';
        }
        ?>
    </ul>
    <?php
}

function custom_post_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_type' => 'post',
    ), $atts);

    ob_start();

    // Display the search form
    ?>
    <form method="post" action="">
        <input type="text" name="post_title" placeholder="Enter post title">
        <input type="hidden" name="post_type" value="<?php echo esc_attr($atts['post_type']); ?>">
        <input type="submit" value="Search">
    </form>

    <?php

    // Check if the form is submitted
    if (isset($_POST['post_title'])) {
        $post_title = sanitize_text_field($_POST['post_title']);
        $selected_post_type = sanitize_text_field($_POST['post_type']);

        // Query for posts with the given title and post type
        $query_args = array(
            'post_type' => $selected_post_type,
            'post_title_like' => $post_title, // Search for post titles
            'post_status' => 'publish', // Only search for published posts
        );

        $query = new WP_Query($query_args);

        // Display search results
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo '<h2>' . get_the_title() . '</h2>';
                echo '<div>' . get_the_content() . '</div>';
            }
            wp_reset_postdata();
        } else {
            echo '<p>No results found.</p>';
        }
    }

    return ob_get_clean();
}
add_shortcode('post_search', 'custom_post_search_shortcode');


$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/dwojt2/Custom-Post-Search',
	__FILE__,
	'custom_breakdance_post_loop_builder'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');
?>
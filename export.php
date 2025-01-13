<?php
/*
Plugin Name: ThirstyAffiliates Exporter
Description: Exports affiliate links from ThirstyAffiliates to a CSV file.
Version: 1.01
Author: Patrick Mahinge
*/

add_action('admin_menu', 'ta_exporter_menu');

function ta_exporter_menu()
{
    add_menu_page('TA Exporter', 'TA Exporter', 'manage_options', 'ta-exporter', 'ta_exporter_page');
}

function ta_exporter_page()
{
?>
    <div class="wrap">
        <h2>Export ThirstyAffiliates Links</h2>
        <form method="post" action="">
            <input type="submit" name="export_csv" class="button button-primary" value="Export Links to CSV">
        </form>
    </div>
<?php

    if (isset($_POST['export_csv'])) {
        ta_export_csv();
    }
}

function ta_export_csv()
{
    // Fetching all affiliate links
    $args = array(
        'post_type' => 'thirstylink',
        'posts_per_page' => -1,
    );
    $links = get_posts($args);

    // Check if there are links to export
    if (empty($links)) {
        wp_die('No affiliate links found to export.');
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="affiliate_links.csv"');

    $output = fopen('php://output', 'w');

    // Adding column headers
    fputcsv($output, array('Link Name', 'Destination URL', 'Slug', 'Categories'));

    foreach ($links as $link) {
        $link_name = get_the_title($link->ID);
        $destination_url = get_post_meta($link->ID, '_ta_destination_url', true);
        $slug = get_post_meta($link->ID, '_ta_slug', true);
        $categories = wp_get_post_terms($link->ID, 'thirstylink_category', array('fields' => 'names'));

        // Ensure categories is an array before imploding
        if (is_wp_error($categories) || !is_array($categories)) {
            $categories = [];
        }

        fputcsv($output, array($link_name, $destination_url, $slug, implode('; ', $categories)));
    }

    fclose($output);
    exit();
}
?>

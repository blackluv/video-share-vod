<?php
get_header();

$tax = $wp_query->get_queried_object();

echo do_shortcode('[videowhisper_playlist name="'.$tax->name.'"]');

get_footer();
?>
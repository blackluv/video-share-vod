<?php
get_header();

$tax = $wp_query->get_queried_object();
echo '<h3>'.$tax->name.'</h3>';
echo do_shortcode('[videowhisper_playlist name="'.$tax->name.'"]');

get_footer();
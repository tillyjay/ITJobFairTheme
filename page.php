<?php
// Get header
get_header();

while (have_posts()) : 
    the_post(); 
    
    $post = get_post();
    
    // Check if it's the front page
    if (is_front_page()) {
        // Get the page featured image url
        $featured_image_url = get_the_post_thumbnail_url($post->ID);

        if($featured_image_url) {
            // Get featured img and alt text
            $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
            
            // Display the featured image with overlay elements
            echo '<div class="featured-image-container">';
            echo '<img src="'. $featured_image_url.'" class="img-fluid mb-4" alt="'. $alt.'">';
            echo '<div class="overlay-content">';
            echo '<p class="overlay-text">Foster your recruiting!</p>'; 
            echo '<button class="overlay-button" onclick="navigateToIndustry()">Find out more</button>'; 
            echo '</div>';
            echo '</div>';
        }
    } else {
        // For all other pages, display featured image without overlay
        $featured_image_url = get_the_post_thumbnail_url($post->ID);

        if($featured_image_url) {
            // Get featured img id and alt text
            $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
            echo '<img src="'. $featured_image_url.'" class="img-fluid mb-4" alt="'. $alt.'">';
        }
    }
    
    the_title("<h1>", "</h1>");
    the_content(); 
endwhile;

// Get footer
get_footer();
?>

<!-- Find out more button JavaScript -->
<script>
function navigateToIndustry() {
    window.location.href = "https://bbird.org/itjobfair_wp/industry-essential-information/";
}
</script>
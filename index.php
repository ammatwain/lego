<?php
// Fallback for non-block-aware WordPress installations.
// It simply loads the block template if possible, otherwise falls back to default.

if ( file_exists( get_template_directory() . '/templates/index.html' ) ) {
    // Use the block template directly (WP will parse it internally).
    include get_template_directory() . '/templates/index.html';
} else {
    // Basic loop fallback
    get_header();
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            the_content();
        endwhile;
    endif;
    get_footer();
}
```
<?php
// Functions for the Lego Blocks Theme

// Enqueue theme stylesheet
function lego_blocks_enqueue_styles() {
    wp_enqueue_style('lego-blocks-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'lego_blocks_enqueue_styles');

// Support for block alignments and editor styles
function lego_blocks_setup() {
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('style.css');
}
function lego_register_sidebars() {
    unregister_sidebar('sidebar-left-1'); // Unregister default sidebar if it exists
    unregister_sidebar('sidebar-left-2'); // Unregister default sidebar if it exists
    unregister_sidebar('sidebar-right-1'); // Unregister default sidebar if it exists
    unregister_sidebar('sidebar-right-2'); // Unregister default sidebar if it exists

    register_sidebar([
        'name'          => __('Lego Left Sidebar', 'lego-blocks'),
        'id'            => 'lego-sidebar-left',
        'description'   => __('Widgets in the left sidebar.', 'lego-blocks'),

        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',

        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',

        'before_sidebar' => '<aside id="%1$s" class="sidebar %2$s">',
        'after_sidebar'  => '</aside>',
    ]);
    register_sidebar([
        'name'          => __('Lego Right Sidebar 1', 'lego-blocks'),
        'id'            => 'lego-sidebar-right-1',
        'description'   => __('Widgets in the first right sidebar.', 'lego-blocks'),

        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',

        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',

        'before_sidebar' => '<aside id="%1$s" class="sidebar %2$s">',
        'after_sidebar'  => '</aside>',
    ]);
    register_sidebar([
        'name'          => __('Lego Right Sidebar 2', 'lego-blocks'),
        'id'            => 'lego-sidebar-right-2',
        'description'   => __('Widgets in the second right sidebar.', 'lego-blocks'),

        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',

        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',

        'before_sidebar' => '<aside id="%1$s" class="sidebar %2$s">',
        'after_sidebar'  => '</aside>',
    ]);

}
add_action('widgets_init', 'lego_register_sidebars');

add_action('after_setup_theme', 'lego_blocks_setup');

/**
 * Make core/widget-group blocks render the associated dynamic sidebar
 * when placed in the FSE template with an "id" attribute matching a
 * registered sidebar.  Without this filter the block is self-closing
 * (no inner blocks) and produces empty markup.
 */
add_filter('render_block_core/widget-group', function ($block_content, $parsed_block) {
    $sidebar_id = $parsed_block['attrs']['id'] ?? '';

    if ($sidebar_id && is_active_sidebar($sidebar_id)) {
        ob_start();
        dynamic_sidebar($sidebar_id);
        return ob_get_clean();
    }

    return $block_content;
}, 10, 2);

function lego_sync_block_templates() {
    // limitare all'area amministrativa/CLI per ridurre il carico
    if ( ! is_admin() && ! defined( 'WP_CLI' ) ) {
        return;
    }

    $dir = get_template_directory() . '/templates';
    if ( ! is_dir( $dir ) ) {
        return;
    }

    $files = scandir( $dir );
    foreach ( $files as $file ) {
        if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'html' ) {
            continue;
        }

        $slug    = pathinfo( $file, PATHINFO_FILENAME );
        $content = file_get_contents( $dir . '/' . $file );

        $existing = get_posts( [
            'name'        => $slug,
            'post_type'   => 'wp_template',
            'post_status' => 'any',
            'numberposts' => 1,
        ] );

        if ( $existing ) {
            $post = $existing[0];
            if ( $post->post_content !== $content ) {
                wp_update_post( [
                    'ID'           => $post->ID,
                    'post_content' => $content,
                ] );
            }
        } else {
            wp_insert_post( [
                'post_type'    => 'wp_template',
                'post_title'   => ucwords( str_replace( '-', ' ', $slug ) ),
                'post_name'    => $slug,
                'post_content' => $content,
                'post_status'  => 'publish',
            ] );
        }
    }
}


//add_action( 'after_switch_theme', 'lego_sync_block_templates' );
// e – opzionale – anche in admin/cli per sicurezza

/**
 * Sincronizza i template a blocchi del tema dalla directory
 * /templates al post_type wp_template. Utile nei deploy.
 */
add_action( 'after_switch_theme', 'lego_sync_block_templates' );
// e – opzionale – anche in admin/cli per sicurezza
add_action( 'init', function() {
    if ( is_admin() || defined( 'WP_CLI' ) ) {
        lego_sync_block_templates();
    }
} );

function custom_viewport_meta() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">';
}

remove_action( 'wp_head', 'wp_viewport_meta');
add_action('wp_head', 'custom_viewport_meta', 0);

function lego_cambia_testo_paginazione( $translated_text, $text, $domain ) {
    // blocco Next
    if ( $text === 'Next page' || $text === 'Pagina successiva' ) {
        $translated_text = 'Carica altri articoli';
    }

    // blocco Previous
    if ( $text === 'Previous page' || $text === 'Pagina precedente' ) {
        $translated_text = 'Pagina precedente';
    }

    return $translated_text;
}
add_filter( 'gettext', 'lego_cambia_testo_paginazione', 20, 3 );

// functions.php o un include
function lego_register_permalink_block() {
    register_block_type( 'lego/link-only', [
        'render_callback' => function() {
            return esc_url( get_permalink() );
        },
    ]);
}
add_action( 'init', 'lego_register_permalink_block' );

// wrapper block that wraps inner HTML in a link to the current post
function lego_register_link_wrapper_block() {
    register_block_type( 'lego/link-wrapper', [
        'render_callback' => 'lego_render_link_wrapper',
        // allow inner blocks so the editor can nest content
        'supports'         => [
            'html' => false, // we will print our own HTML
        ],
        'attributes'       => [],
    ]);
}
add_action( 'init', 'lego_register_link_wrapper_block' );

function lego_render_link_wrapper( $attributes, $content ) {
    // try to get permalink for the current post in the loop
    $url = '';
    if ( function_exists( 'get_permalink' ) ) {
        global $post;
        if ( $post instanceof WP_Post ) {
            $url = get_permalink( $post );
        }
    }

    // even if url is empty we still output children
    return '<a href="' . esc_url( $url ) . '">' . $content . '</a>';
}

function custom_excerpt_length( $length ) {
    return 200; // Change this number to your desired word count
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

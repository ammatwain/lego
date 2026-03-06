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

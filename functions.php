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
add_action('after_setup_theme', 'lego_blocks_setup');

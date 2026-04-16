<?php

require_once get_template_directory() . '/includes/enqueue.php';
require_once get_template_directory() . '/includes/panel-empresa.php';
require_once get_template_directory() . '/includes/panel-empresa-ajax.php';


// Enqueue scripts
add_action('wp_enqueue_scripts', function() {

    if (is_page('panel-empresa')) {

        wp_enqueue_script(
            'panel-empresa-js',
            get_template_directory_uri() . '/assets/js/panel-empresa.js',
            [],
            '1.0',
            true
        );

        wp_localize_script('panel-empresa-js', 'pe_ajax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pe_nonce')
        ]);
    }
});
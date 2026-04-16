<?php

function panel_empresa_assets() {

    wp_enqueue_style(
        'panel-empresa-css',
        get_template_directory_uri() . '/assets/css/panel-empresa.css',
        array(),
        '2.0'
    );

    wp_enqueue_script(
        'panel-empresa-js',
        get_template_directory_uri() . '/assets/js/panel-empresa.js',
        array(),
        '1.0',
        true
    );

    // 🔥 ESTO reemplaza tu script PHP del nonce
    wp_localize_script('panel-empresa-js', 'pe_ajax', [
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pe_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'panel_empresa_assets');


<?php

add_action('wp_ajax_pe_agregar_usuario', function() {
    check_ajax_referer('pe_nonce', 'nonce');

    $user_id = wp_create_user($_POST['login'], $_POST['password'], $_POST['email']);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['mensaje' => $user_id->get_error_message()]);
    }

    wp_update_user([
        'ID' => $user_id,
        'display_name' => $_POST['nombre']
    ]);

    wp_send_json_success(['mensaje' => 'Usuario creado']);
});


add_action('wp_ajax_pe_modificar_usuario', function() {
    check_ajax_referer('pe_nonce', 'nonce');

    wp_update_user([
        'ID' => $_POST['user_id'],
        'display_name' => $_POST['nombre']
    ]);

    wp_send_json_success(['mensaje' => 'Actualizado']);
});


add_action('wp_ajax_pe_eliminar_usuario', function() {
    check_ajax_referer('pe_nonce', 'nonce');

    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user($_POST['user_id']);

    wp_send_json_success(['mensaje' => 'Eliminado']);
});
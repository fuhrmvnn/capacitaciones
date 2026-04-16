 <?php

function render_panel_empresa() {

if (!is_user_logged_in()) return;

$current_user = wp_get_current_user();
$nombre       = $current_user->display_name;
$rol_display  = in_array('administrator', $current_user->roles) ? 'Administrador' : 'Supervisor';

// Iniciales
$partes    = explode(' ', trim($nombre));
$iniciales = '';
if (isset($partes[0])) $iniciales .= strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) $iniciales .= strtoupper(substr($partes[1], 0, 1));

// ACF
$empresa = get_field('nombre_empresa', 'user_' . $current_user->ID);
$sede    = get_field('sede', 'user_' . $current_user->ID);

// Usuarios
$usuarios = get_users([
    'role'       => 'subscriber',
    'meta_key'   => 'nombre_empresa',
    'meta_value' => $empresa,
    'number'     => -1,
]);

$total = $activos = $certificados = 0;

foreach ($usuarios as $t) {
    $total++;
    $est = strtolower(trim(get_field('estado', 'user_' . $t->ID)));
    if ($est === 'activo') $activos++;
    if (!empty(get_user_meta($t->ID, 'certificado_aprobado', true))) $certificados++;
}
?>

<!-- ================= TU BLOQUE ORIGINAL ================= -->

<div class="pe-wrap">

    <!-- HEADER -->
    <div class="pe-header">

        <div class="pe-perfil">
            <div class="pe-avatar"><?php echo esc_html($iniciales); ?></div>
            <div class="pe-perfil-info">
                <span class="pe-rol"><?php echo esc_html($rol_display); ?></span>
                <p class="pe-nombre"><?php echo esc_html($nombre); ?></p>
                <?php if ($empresa): ?>
                <p class="pe-empresa"><?php echo esc_html($empresa); ?></p>
                <?php endif; ?>
                <?php if ($sede): ?>
                <p class="pe-sede"><?php echo esc_html($sede); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="pe-metricas">
            <div class="pe-metrica-card">
                <span class="pe-metrica-num"><?php echo $total; ?></span>
                <span class="pe-metrica-label">Total</span>
            </div>
            <div class="pe-metrica-card">
                <span class="pe-metrica-num"><?php echo $activos; ?></span>
                <span class="pe-metrica-label">Activos</span>
            </div>
            <div class="pe-metrica-card">
                <span class="pe-metrica-num"><?php echo $certificados; ?></span>
                <span class="pe-metrica-label">Certificados</span>
            </div>
        </div>

    </div>

    <!-- LISTADO -->
    <div class="pe-lista">

        <h2 class="pe-titulo">Listado de trabajadores</h2>

        <div class="pe-top">
            <div class="pe-filtros">
                <button class="pe-filtro-btn activo" onclick="peF('todos',this)">Todos</button>
                <button class="pe-filtro-btn" onclick="peF('activo',this)">Activos</button>
                <button class="pe-filtro-btn" onclick="peF('inactivo',this)">Fuera de servicio</button>
                <button class="pe-filtro-btn" onclick="peAZ()">A - Z</button>
            </div>
            <button class="pe-btn-agregar" onclick="peAgregar()">Agregar trabajador</button>
        </div>

        <?php if (empty($usuarios)): ?>
        <p style="color:#00246F;">No hay trabajadores registrados.</p>
        <?php else: ?>

        <table class="pe-table" id="peTabla">
            <thead>
                <tr>
                    <th>Trabajador</th>
                    <th>Cargo</th>
                    <th>Estado</th>
                    <th>Progreso</th>
                    <th>Certificación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $user):

                $estado = strtolower(trim(get_field('estado', 'user_' . $user->ID)));
                if (empty($estado)) $estado = 'activo';

                $cargo = get_field('rol', 'user_' . $user->ID);
                $rut   = get_field('rut', 'user_' . $user->ID);

                $progress  = 0;
                $cert_html = '<span class="pe-sin-cert">Sin certificado</span>';

                if (function_exists('tutor_utils')) {
                    $courses = tutor_utils()->get_enrolled_courses_by_user($user->ID);
                    if (!empty($courses->posts)) {
                        $course_id = $courses->posts[0]->ID;
                        $progress  = (int) tutor_utils()->get_course_completed_percent($course_id, $user->ID);
                        if (tutor_utils()->is_completed_course($course_id, $user->ID)) {
                            $cert_url  = get_user_meta($user->ID, 'certificado_url', true);
                            $cert_href = !empty($cert_url) ? esc_url($cert_url) : '#';
                            $cert_html = '<a class="pe-cert-link" href="' . $cert_href . '" target="_blank">Descargar</a>';
                        }
                    }
                }

                if ($progress >= 70)     $bar_color = '#00D118';
                elseif ($progress >= 30) $bar_color = '#FFA500';
                else                     $bar_color = '#FF3B3B';

                $badge_class = ($estado === 'activo') ? 'pe-badge-activo' : 'pe-badge-inactivo';
                $badge_label = ($estado === 'activo') ? 'Activo' : 'Fuera de servicio';
            ?>
            <tr data-estado="<?php echo esc_attr($estado); ?>"
                data-nombre="<?php echo esc_attr($user->display_name); ?>"
                data-id="<?php echo $user->ID; ?>"
                data-email="<?php echo esc_attr($user->user_email); ?>"
                data-login="<?php echo esc_attr($user->user_login); ?>"
                data-rut="<?php echo esc_attr($rut); ?>"
                data-cargo="<?php echo esc_attr($cargo); ?>"
                data-estado-val="<?php echo esc_attr($estado); ?>">

                <td>
                    <span class="pe-nombre-trabajador"><?php echo esc_html($user->display_name); ?></span>
                    <?php if ($rut): ?>
                    <span class="pe-rut"><?php echo esc_html($rut); ?></span>
                    <?php endif; ?>
                </td>

                <td><span class="pe-cargo"><?php echo esc_html($cargo); ?></span></td>

                <td>
                    <span class="pe-badge <?php echo $badge_class; ?>"><?php echo $badge_label; ?></span>
                </td>

                <td>
                    <div class="pe-progress-wrap">
                        <div class="pe-progress-bar">
                            <div class="pe-progress-fill"
                                 style="width:<?php echo $progress; ?>%;background:<?php echo $bar_color; ?>;"></div>
                        </div>
                        <span class="pe-progress-pct"><?php echo $progress; ?>%</span>
                    </div>
                </td>

                <td><?php echo $cert_html; ?></td>

                <td class="pe-acciones">
                    <a href="#" class="pe-modificar"
                       onclick="peMod(<?php echo $user->ID; ?>);return false;">Modificar</a>
                    <a href="#" class="pe-eliminar"
                       onclick="peDel(<?php echo $user->ID; ?>,'<?php echo esc_js($user->display_name); ?>');return false;">Eliminar</a>
                </td>

            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>

    </div>

</div>

<!-- MODAL -->
<div id="peModal" style="display:none;">
    <input type="text" id="pe_nombre" placeholder="Nombre">
    <input type="email" id="pe_email" placeholder="Email">
    <input type="text" id="pe_login" placeholder="Usuario">
    <input type="password" id="pe_password" placeholder="Password">

    <button onclick="peGuardar()">Guardar</button>
    <button onclick="peModalClose()">Cerrar</button>
</div>

<?php
}
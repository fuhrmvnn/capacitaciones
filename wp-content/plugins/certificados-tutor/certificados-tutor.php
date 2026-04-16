<?php
/*
Plugin Name: Certificados Tutor LMS v2
Description: Genera certificados PDF estilo formal (fondo blanco, líneas azules, logo y firma) al completar cursos en Tutor LMS usando TCPDF.
Version: 1.0
Author: coni
*/

// ──────────────────────────────────────────────────────────────────────────────
//  OPCIONES CONFIGURABLES  (Ajustes › Certificados v2)
//
//  cert2_logo_img   → ruta absoluta al logo (PNG/JPG) — esquina superior izquierda
//  cert2_firma_img  → ruta absoluta a la imagen de firma
//  cert2_firmante   → nombre que aparece bajo la línea de firma
//  cert2_cargo      → cargo del firmante
// ──────────────────────────────────────────────────────────────────────────────

require_once plugin_dir_path(__FILE__) . 'tcpdf/tcpdf.php';

// ── Hook principal ────────────────────────────────────────────────────────────
add_action('tutor_course_complete_after', 'generar_certificado_tutor_v2', 10, 2);

function generar_certificado_tutor_v2($course_id, $user_id) {

    $user_info    = get_userdata($user_id);
    $course_title = get_the_title($course_id);
    $fecha        = date('d \d\e F \d\e Y');

    // Leer opciones
    $logo_path  = get_option('cert2_logo_img',  '');
    $firma_path = get_option('cert2_firma_img', '');
    $firmante   = get_option('cert2_firmante',  get_bloginfo('name'));
    $cargo      = get_option('cert2_cargo',     'Director Académico');

    // ── PDF landscape A4 ──────────────────────────────────────────────────────
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage();

    $W = 297;
    $H = 210;

    // ══════════════════════════════════════════════════════════════════════════
    //  1. FONDO BLANCO
    // ══════════════════════════════════════════════════════════════════════════
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(0, 0, $W, $H, 'F');

    // ══════════════════════════════════════════════════════════════════════════
    //  2. LÍNEAS HORIZONTALES AZUL MARINO  (arriba y abajo, igual que la imagen)
    //     Línea gruesa + línea delgada en cada borde
    // ══════════════════════════════════════════════════════════════════════════
    $azul = [15, 40, 110];   // azul marino oscuro

    // — Borde SUPERIOR —
    $pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
    $pdf->Rect(0, 0,       $W,        3.5, 'F');   // banda gruesa — ancho completo
    $pdf->Rect(0, 5,       $W * 0.35, 1.0, 'F');   // línea fina — solo lado izquierdo (~35% del ancho)

    // — Borde INFERIOR —
    $pdf->Rect(0, $H - 3.5, $W, 3.5, 'F');            // banda gruesa — ancho completo
    $pdf->Rect($W * 0.65, $H - 5.5, $W * 0.35, 1.0, 'F'); // línea fina — solo lado derecho (~35% del ancho)

    // ══════════════════════════════════════════════════════════════════════════
    //  3. LOGO  (esquina superior izquierda)
    // ══════════════════════════════════════════════════════════════════════════
    $logo_ok = cert2_imagen_valida($logo_path);

    if ($logo_ok) {
        $ext_logo  = cert2_tipo_img($logo_path);
        $logoW     = 22;   // ancho en mm
        $logoH     = 22;   // alto en mm
        $logoX     = 12;
        $logoY     = 10;
        try {
            $pdf->Image($logo_path, $logoX, $logoY, $logoW, $logoH, $ext_logo,
                        '', '', true, 96, '', false, false, 0, 'C', false, false);
        } catch (Exception $e) {
            error_log('[Cert2] Error logo: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  4. TEXTOS
    //     Estilo de la imagen: espaciado amplio, texto con letter-spacing
    //     simulado con espacios entre letras usando writeHTMLCell + CSS,
    //     o con Cell y el texto ya espaciado manualmente.
    // ══════════════════════════════════════════════════════════════════════════

    // Función helper para espaciar letras manualmente
    // (TCPDF no soporta letter-spacing nativo, se inserta un espacio entre caracteres)
    $espaciar = function(string $texto, int $espacios = 1): string {
        return implode(str_repeat(' ', $espacios), mb_str_split($texto));
    };

    // ── "Certificado de reconocimiento" ──────────────────────────────────────
    // En la imagen está centrado pero desplazado a la derecha del logo
    // Usamos margen izquierdo de 40mm para no pisar el logo
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetXY(40, 18);
    $pdf->Cell($W - 40, 10, $espaciar('Certificado de reconocimiento'), 0, 1, 'C');

    // ── Línea separadora — ancho completo, debajo del título ─────────────────
    $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, 30, $W - 15, 30);

    // ── Nombre del alumno — grande, cursiva, azul, centrado ──────────────────
    $pdf->SetFont('times', 'I', 44);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetXY(0, 35);
    $pdf->Cell($W, 22, $user_info->display_name, 0, 1, 'C');

    // ── Línea bajo el nombre — ancho completo ────────────────────────────────
    $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, 59, $W - 15, 59);

    // ── "Por haber completado exitosamente el curso de" ───────────────────────
    // En la imagen: texto pequeño, espaciado, centrado
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetXY(0, 66);
    $pdf->Cell($W, 7, $espaciar('Por haber completado exitosamente el curso de'), 0, 1, 'C');

    // ── Nombre del curso — negrita, espaciado, centrado ──────────────────────
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetXY(40, 74);
    $pdf->MultiCell($W - 80, 8, $espaciar($course_title), 0, 'C');

    // ── Fecha — pequeña, gris, centrada, en mayúsculas ───────────────────────
    // Posición dinámica: siempre aparece 10mm después del curso
    $yFecha = max($pdf->GetY() + 10, 98);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(130, 130, 150);
    $pdf->SetXY(0, $yFecha);
    $pdf->Cell($W, 6, $espaciar(strtoupper('Lugar, a ' . $fecha)), 0, 1, 'C');

    // ══════════════════════════════════════════════════════════════════════════
    //  5. SECCIÓN DE FIRMA
    //     Posición: centrada, en la parte inferior (igual que la imagen)
    // ══════════════════════════════════════════════════════════════════════════
    $firmaW = 70;
    $firmaX = ($W - $firmaW) / 2;

    // — Imagen de firma —
    $firma_ok = cert2_imagen_valida($firma_path);
    error_log('[Cert2] firma_path=' . $firma_path . ' | ok=' . ($firma_ok ? 'SI' : 'NO'));

    $imgFirmaW = 50;
    $imgFirmaH = 18;
    $imgFirmaX = ($W - $imgFirmaW) / 2;
    $imgFirmaY = 128;

    if ($firma_ok) {
        try {
            $pdf->Image(
                $firma_path,
                $imgFirmaX, $imgFirmaY,
                $imgFirmaW, $imgFirmaH,
                cert2_tipo_img($firma_path),
                '', '', true, 96, '', false, false, 0, 'C', false, false
            );
            $lineY = $imgFirmaY + $imgFirmaH + 3;
        } catch (Exception $e) {
            error_log('[Cert2] Error firma: ' . $e->getMessage());
            $lineY = 150;
        }
    } else {
        $lineY = 150;
    }

    // — Línea de firma —
    $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetLineWidth(0.35);
    $pdf->Line($firmaX, $lineY, $firmaX + $firmaW, $lineY);

    // — Nombre del firmante —
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetXY($firmaX, $lineY + 3);
    $pdf->Cell($firmaW, 6, $espaciar($firmante), 0, 1, 'C');

    // — Cargo —
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(80, 90, 120);
    $pdf->SetXY($firmaX, $lineY + 10);
    $pdf->Cell($firmaW, 5, $cargo, 0, 1, 'C');

    // ══════════════════════════════════════════════════════════════════════════
    //  6. GUARDAR Y ENVIAR
    // ══════════════════════════════════════════════════════════════════════════
    $upload_dir = wp_upload_dir();
    $cert_dir   = $upload_dir['basedir'] . '/certificados/';
    if (!file_exists($cert_dir)) {
        mkdir($cert_dir, 0755, true);
    }

    $file_path = $cert_dir . 'certificado2_' . $user_id . '_' . $course_id . '.pdf';
    $pdf->Output($file_path, 'F');

    wp_mail(
        $user_info->user_email,
        '🎓 Tu certificado del curso: ' . $course_title,
        '<p>Felicitaciones, <strong>' . esc_html($user_info->display_name) . '</strong>!</p>'
        . '<p>Has completado exitosamente el curso <strong>' . esc_html($course_title) . '</strong>.</p>'
        . '<p>Adjuntamos tu certificado en PDF.</p>',
        ['Content-Type: text/html; charset=UTF-8'],
        [$file_path]
    );
}

// ─────────────────────────────────────────
// DESCARGA SEGURA DE CERTIFICADO
// URL: ?descargar_certificado=1&user_id=XX&course_id=YY
// ─────────────────────────────────────────

add_action('init', function () {

    if (!isset($_GET['descargar_certificado'])) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_die('Debes iniciar sesión.');
    }

    $current_user = wp_get_current_user();

    if (
        !in_array('administrator', $current_user->roles) &&
        !in_array('supervisor_empresa', $current_user->roles) &&
        !in_array('tutor_instructor', $current_user->roles)
    ) {
        wp_die('No tienes permisos.');
    }

    $user_id   = intval($_GET['user_id']);
    $course_id = intval($_GET['course_id']);

    $upload_dir = wp_upload_dir();
    $file_path  = $upload_dir['basedir'] . '/certificados/certificado2_' . $user_id . '_' . $course_id . '.pdf';

    if (!file_exists($file_path)) {
        wp_die('El certificado aún no ha sido generado.');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="certificado.pdf"');
    readfile($file_path);
    exit;
});

// ══════════════════════════════════════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════════════════════════════════════

/** Verifica que la ruta exista, sea legible y tenga extensión de imagen válida */
function cert2_imagen_valida(string $path): bool {
    if (empty($path)) return false;
    if (!file_exists($path) || !is_readable($path)) return false;
    return !empty(cert2_tipo_img($path));
}

/** Devuelve el tipo TCPDF según extensión: PNG, JPEG o GIF */
function cert2_tipo_img(string $path): string {
    $map = ['png' => 'PNG', 'jpg' => 'JPEG', 'jpeg' => 'JPEG', 'gif' => 'GIF'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return $map[$ext] ?? '';
}


// ══════════════════════════════════════════════════════════════════════════════
//  PANEL DE CONFIGURACIÓN  (Ajustes › Certificados v2)
// ══════════════════════════════════════════════════════════════════════════════
add_action('admin_menu', function () {
    add_options_page(
        'Certificados v2',
        'Certificados v2',
        'manage_options',
        'cert2-config',
        'cert2_render_settings'
    );
});

add_action('admin_init', function () {
    register_setting('cert2_group', 'cert2_logo_img');
    register_setting('cert2_group', 'cert2_firma_img');
    register_setting('cert2_group', 'cert2_firmante');
    register_setting('cert2_group', 'cert2_cargo');
});

function cert2_render_settings(): void { ?>
<div class="wrap">
    <h1>⚙️ Configuración de Certificados v2</h1>
    <p>Las rutas deben ser <strong>rutas absolutas del servidor</strong>, no URLs.<br>
       Ejemplo: <code>/home/josoyaneder/public_html/capa/wp-content/uploads/logo.png</code></p>
    <form method="post" action="options.php">
        <?php settings_fields('cert2_group'); ?>
        <table class="form-table">

            <tr>
                <th><label for="cert2_logo_img">🖼️ Logo (ruta absoluta)</label></th>
                <td>
                    <input type="text" id="cert2_logo_img" name="cert2_logo_img"
                           value="<?php echo esc_attr(get_option('cert2_logo_img', '')); ?>"
                           class="large-text" />
                    <p class="description">
                        Imagen PNG/JPG del logo. Se muestra en la esquina superior izquierda.<br>
                        Tamaño recomendado: cuadrado, mínimo 200×200 px, fondo transparente (PNG).
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="cert2_firma_img">✍️ Firma (ruta absoluta)</label></th>
                <td>
                    <input type="text" id="cert2_firma_img" name="cert2_firma_img"
                           value="<?php echo esc_attr(get_option('cert2_firma_img', '')); ?>"
                           class="large-text" />
                    <p class="description">
                        Imagen PNG/JPG de la firma manuscrita.<br>
                        Recomendado: fondo blanco o transparente, formato horizontal.
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="cert2_firmante">👤 Nombre del firmante</label></th>
                <td>
                    <input type="text" id="cert2_firmante" name="cert2_firmante"
                           value="<?php echo esc_attr(get_option('cert2_firmante', '')); ?>"
                           class="regular-text" />
                </td>
            </tr>

            <tr>
                <th><label for="cert2_cargo">🏷️ Cargo del firmante</label></th>
                <td>
                    <input type="text" id="cert2_cargo" name="cert2_cargo"
                           value="<?php echo esc_attr(get_option('cert2_cargo', 'Director Académico')); ?>"
                           class="regular-text" />
                </td>
            </tr>

        </table>
        <?php submit_button('Guardar cambios'); ?>
    </form>
</div>

<?php
} // fin cert2_render_settings()
let peUsuarioActual = null;

function peAgregar() {
    peUsuarioActual = null;
    document.getElementById('peModal').style.display = 'block';
}

function peMod(id) {
    peUsuarioActual = id;
    document.getElementById('peModal').style.display = 'block';
}

function peModalClose() {
    document.getElementById('peModal').style.display = 'none';
}

function peGuardar() {

    const data = new FormData();
    data.append('action', peUsuarioActual ? 'pe_modificar_usuario' : 'pe_agregar_usuario');
    data.append('nonce', pe_ajax.nonce);
    data.append('user_id', peUsuarioActual || '');
    data.append('nombre', document.getElementById('pe_nombre').value);
    data.append('email', document.getElementById('pe_email').value);
    data.append('login', document.getElementById('pe_login').value);
    data.append('password', document.getElementById('pe_password').value);

    fetch(pe_ajax.url, {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.data.mensaje);
    });
}

function peDel(id) {
    if (!confirm('Eliminar usuario?')) return;

    const data = new FormData();
    data.append('action', 'pe_eliminar_usuario');
    data.append('nonce', pe_ajax.nonce);
    data.append('user_id', id);

    fetch(pe_ajax.url, {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else alert(res.data.mensaje);
    });
}
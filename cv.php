<?php
// -----------------------------------------------------------
// mailer.php — Envío de formulario de RRHH con adjunto PDF
// -----------------------------------------------------------
// Configuración
$destinatario = "cv@grupoup.com.ar";  // 📩 Cambiá este correo
$asunto       = "Nueva postulación desde Grupo UP";
$max_size     = 5 * 1024 * 1024; // 5 MB

// Validar datos básicos
$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$localidad = trim($_POST['localidad'] ?? '');
$area      = trim($_POST['area'] ?? '');
$mensaje   = trim($_POST['mensaje'] ?? '');
$consent   = isset($_POST['consent']);

if (!$nombre || !$apellido || !$email || !$consent) {
    die("Por favor, completá los campos obligatorios.");
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("El correo electrónico no es válido.");
}

// Validar archivo PDF (si existe)
$archivo = $_FILES['cv_pdf'] ?? null;
$tieneArchivo = $archivo && $archivo['error'] === UPLOAD_ERR_OK;

if ($tieneArchivo) {
    $tipo = mime_content_type($archivo['tmp_name']);
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $tam = filesize($archivo['tmp_name']);

    if ($ext !== 'pdf' || $tipo !== 'application/pdf') {
        die("Solo se permiten archivos en formato PDF.");
    }
    if ($tam > $max_size) {
        die("El archivo supera el tamaño máximo de 5 MB.");
    }
}

// Cuerpo del mensaje (texto plano + HTML alternativo)
$body = "
Nueva postulación desde la web de Grupo UP:

Nombre: $nombre $apellido
Email: $email
Teléfono: $telefono
Localidad: $localidad
Área de interés: $area

Mensaje:
$mensaje

Consentimiento: " . ($consent ? 'Sí' : 'No') . "
Enviado desde: " . $_SERVER['HTTP_HOST'] . "
";

// Si hay archivo adjunto, armamos correo multipart
if ($tieneArchivo) {
    $file_tmp  = $archivo['tmp_name'];
    $file_name = basename($archivo['name']);
    $file_data = chunk_split(base64_encode(file_get_contents($file_tmp)));
    $boundary  = md5(time());

    $headers  = "From: Grupo UP <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: application/pdf; name=\"{$file_name}\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"{$file_name}\"\r\n\r\n";
    $message .= $file_data . "\r\n\r\n";
    $message .= "--{$boundary}--";

} else {
    // Sin archivo adjunto
    $headers  = "From: Grupo UP <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message  = $body;
}

// Enviar correo
if (mail($destinatario, $asunto, $message, $headers)) {
    echo "<h2>✅ Gracias, tu postulación fue enviada correctamente.</h2>
          <p>Podés volver al sitio o enviar otro formulario.</p>";
} else {
    echo "<h2>❌ Error: no se pudo enviar el correo.</h2>
          <p>Intentá nuevamente más tarde.</p>";
}
?>

<?php
// Verificamos que el formulario haya sido enviado por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Función para limpiar los datos
    function limpiar($dato) {
        return htmlspecialchars(strip_tags(trim($dato)));
    }

    // Recoger datos del formulario
    $nombre = limpiar($_POST['name'] ?? '');
    $email  = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['phone'] ?? '');
    $mensaje = limpiar($_POST['message'] ?? '');
    $servicio = limpiar($_POST['subject'] ?? '');

    // Validaciones mínimas
    if (!$nombre || !$email || !$telefono || !$servicio) {
        echo "Por favor, completa todos los campos obligatorios.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Correo inválido.";
        exit;
    }

    // Definir a qué correo enviar según el servicio
    $destinatarios = [
        'Limpieza Profesional' => 'Cleaningupsas@gmail.com',
        'Final de Obra' => 'Cleaningupsas@gmail.com',
        'Seguridad Privada' => 'Safe.upseguridad@gmail.com',
        'Pintura' => 'Grupoup.servicios@gmail.com',
        'Asesoramiento Jurídico' => 'Estudiojmgf@gmail.com',
        'Otro' => 'Grupoup.servicios@gmail.com'
    ];

    $para = $destinatarios[$servicio] ?? 'Grupoup.servicios@gmail.com';

    // Asunto y contenido del mensaje
    $asunto = "Nuevo mensaje desde el sitio web - Servicio: $servicio";
    $contenido = "
    Has recibido un nuevo mensaje desde el formulario del sitio web:\n\n
    Nombre: $nombre\n
    Correo: $email\n
    Teléfono: $telefono\n
    Servicio: $servicio\n
    Mensaje:\n$mensaje
    ";

    $cabeceras = "From: $nombre <$email>\r\n";
    $cabeceras .= "Reply-To: $email\r\n";
    $cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Enviar el correo
    if (mail($para, $asunto, $contenido, $cabeceras)) {
        echo "OK"; // Puedes usar esto para manejar respuestas AJAX
    } else {
        echo "Error al enviar el correo. Intente más tarde.";
    }

} else {
    echo "Acceso no permitido.";
}

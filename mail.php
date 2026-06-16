<?php

// Solo permite solicitudes enviadas desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Acceso no permitido.');
}

// Limpia los datos recibidos
function limpiar(string $dato): string
{
    $dato = trim($dato);
    $dato = strip_tags($dato);

    return str_replace(["\r", "\n"], ' ', $dato);
}

// Datos del formulario
$nombre   = limpiar($_POST['name'] ?? '');
$email    = limpiar($_POST['email'] ?? '');
$telefono = limpiar($_POST['phone'] ?? '');
$servicio = limpiar($_POST['subject'] ?? '');
$mensaje  = trim(strip_tags($_POST['message'] ?? ''));

// Validar campos obligatorios
if (
    $nombre === '' ||
    $email === '' ||
    $telefono === '' ||
    $servicio === ''
) {
    exit('Por favor, completá todos los campos obligatorios.');
}

// Validar correo del visitante
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('El correo ingresado no es válido.');
}

// Lista permitida de servicios y sus destinatarios
$destinatarios = [
    'Limpieza Profesional'   => 'comercial@cleaningup.com.ar',
    'Final de Obra'          => 'comercial@cleaningup.com.ar',
    'Seguridad Privada'      => 'safe.upseguridad@gmail.com',
    'Pintura'                => 'grupoup.servicios@gmail.com',
    'Asesoramiento Jurídico' => 'estudiojmgf@gmail.com',
    'Otro'                   => 'grupoup.servicios@gmail.com'
];

// Verificar que el servicio enviado exista
if (!isset($destinatarios[$servicio])) {
    exit('El servicio seleccionado no es válido.');
}

// Destinatario según el servicio elegido
$para = $destinatarios[$servicio];

// Casilla creada en HostGator/Titan
$remitente = 'formularios@grupoup.com.ar';

// Asunto del correo
$asunto = "Nueva consulta web - {$servicio}";

// Contenido del correo
$contenido  = "Has recibido una nueva consulta desde el sitio web.\n\n";
$contenido .= "Nombre: {$nombre}\n";
$contenido .= "Correo: {$email}\n";
$contenido .= "Teléfono: {$telefono}\n";
$contenido .= "Servicio: {$servicio}\n\n";
$contenido .= "Mensaje:\n";

if ($mensaje !== '') {
    $contenido .= "{$mensaje}\n";
} else {
    $contenido .= "El usuario no escribió un mensaje.\n";
}

// Cabeceras
$cabeceras  = "From: Sitio web Grupo UP <{$remitente}>\r\n";
$cabeceras .= "Reply-To: {$email}\r\n";
$cabeceras .= "MIME-Version: 1.0\r\n";
$cabeceras .= "Content-Type: text/plain; charset=UTF-8\r\n";
$cabeceras .= "Content-Transfer-Encoding: 8bit\r\n";
$cabeceras .= "X-Mailer: PHP/" . phpversion();

// Enviar correo
$enviado = mail(
    $para,
    $asunto,
    $contenido,
    $cabeceras,
    "-f{$remitente}"
);

// Resultado
if ($enviado) {
    header('Location: gracias.html');
    exit;
}

http_response_code(500);
exit('Error al enviar el correo. Intentá nuevamente más tarde.');
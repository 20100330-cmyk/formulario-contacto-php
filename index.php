<?php
session_start();
require 'conexion.php';

$errores = [];
$enviado = false;

// Verificar si viene de una redirección exitosa
if (isset($_SESSION['enviado'])) {
    $enviado = true;
    unset($_SESSION['enviado']);
}

$nombre   = '';
$correo   = '';
$telefono = '';
$asunto   = '';
$mensaje  = '';

if (isset($_POST['submit'])) {
    $nombre   = $_POST['nombre'] ?? '';
    $correo   = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $asunto   = $_POST['asunto'] ?? '';
    $mensaje  = $_POST['mensaje'] ?? '';

    // 1. Validar Nombre
    if (!empty(trim($nombre))) {
        $nombre = htmlspecialchars(strip_tags(trim($nombre)), ENT_QUOTES, 'UTF-8');
        if (strlen($nombre) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres.';
        }
    } else {
        $errores[] = 'Por favor ingresa tu nombre completo.';
    }

    // 2. Validar Correo
    if (!empty(trim($correo))) {
        $correo = filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        }
    } else {
        $errores[] = 'Por favor ingresa un correo electrónico.';
    }

    // 3. Validar Teléfono (Opcional, pero si lo llena debe ser válido)
    if (!empty(trim($telefono))) {
        $telefono = htmlspecialchars(strip_tags(trim($telefono)), ENT_QUOTES, 'UTF-8');
        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $telefono)) {
            $errores[] = 'El teléfono solo puede contener números y guiones (mínimo 7 dígitos).';
        }
    }

    // 4. Validar Asunto
    $asuntosPermitidos = ['Consulta General', 'Soporte Técnico', 'Cotización / Ventas', 'Otro'];
    if (empty($asunto) || !in_array($asunto, $asuntosPermitidos)) {
        $errores[] = 'Por favor selecciona un asunto de la lista.';
    }

    // 5. Validar Mensaje
    if (!empty(trim($mensaje))) {
        $mensaje = htmlspecialchars(strip_tags(trim($mensaje)), ENT_QUOTES, 'UTF-8');
        if (strlen($mensaje) < 10) {
            $errores[] = 'El mensaje es muy corto (mínimo 10 caracteres).';
        }
    } else {
        $errores[] = 'Por favor escribe tu mensaje.';
    }

    // 6. Guardar en Base de Datos si no hay errores
    if (empty($errores)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO mensajes (nombre, correo, telefono, asunto, mensaje) VALUES (:nombre, :correo, :telefono, :asunto, :mensaje)");
            $stmt->execute([
                ':nombre'   => $nombre,
                ':correo'   => $correo,
                ':telefono' => !empty($telefono) ? $telefono : null,
                ':asunto'   => $asunto,
                ':mensaje'  => $mensaje
            ]);

            $_SESSION['enviado'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $errores[] = 'Error interno al guardar el mensaje. Intenta más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctanos | Formulario Profesional</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor">
        <div class="tarjeta-formulario">
            <div class="encabezado">
                <h2>Envíanos un mensaje</h2>
                <p>Completa el formulario y te responderemos a la brevedad.</p>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                
                <div class="grupo-input">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej. Carlos Mendoza" value="<?php echo $nombre; ?>">
                </div>

                <div class="fila-doble">
                    <div class="grupo-input">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" value="<?php echo $correo; ?>">
                    </div>

                    <div class="grupo-input">
                        <label for="telefono">Teléfono / WhatsApp</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+51 999 888 777" value="<?php echo $telefono; ?>">
                    </div>
                </div>

                <div class="grupo-input">
                    <label for="asunto">Motivo de contacto *</label>
                    <select name="asunto" id="asunto" class="form-control">
                        <option value="" disabled <?php echo empty($asunto) ? 'selected' : ''; ?>>-- Selecciona un asunto --</option>
                        <option value="Consulta General" <?php echo ($asunto === 'Consulta General') ? 'selected' : ''; ?>>Consulta General</option>
                        <option value="Soporte Técnico" <?php echo ($asunto === 'Soporte Técnico') ? 'selected' : ''; ?>>Soporte Técnico</option>
                        <option value="Cotización / Ventas" <?php echo ($asunto === 'Cotización / Ventas') ? 'selected' : ''; ?>>Cotización / Ventas</option>
                        <option value="Otro" <?php echo ($asunto === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>

                <div class="grupo-input">
                    <div class="label-con-contador">
                        <label for="mensaje">Mensaje *</label>
                        <span id="contador">0 / 500</span>
                    </div>
                    <textarea name="mensaje" class="form-control" id="mensaje" maxlength="500" placeholder="Escribe aquí los detalles de tu consulta..."><?php echo $mensaje; ?></textarea>
                </div>

                <!-- Alerta de Errores -->
                <?php if (!empty($errores)): ?>
                    <div class="alert error">
                        <strong>Por favor corrige lo siguiente:</strong>
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif ($enviado): ?>
                    <!-- Alerta de Éxito -->
                    <div class="alert success">
                        ✓ ¡Tu mensaje ha sido enviado y guardado correctamente!
                    </div>
                <?php endif; ?>

                <div class="boton-contenedor">
                    <button type="submit" name="submit" class="btn">
                        Enviar Mensaje
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script ligero para el contador de caracteres en tiempo real -->
    <script>
        const textarea = document.getElementById('mensaje');
        const contador = document.getElementById('contador');
        
        function actualizarContador() {
            const actual = textarea.value.length;
            contador.textContent = `${actual} / 500`;
            contador.style.color = actual >= 450 ? '#dc2626' : '#64748b';
        }
        
        textarea.addEventListener('input', actualizarContador);
        actualizarContador();
    </script>
</body>
</html>
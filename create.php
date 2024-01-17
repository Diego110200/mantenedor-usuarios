<?php
include_once "includes/db.php";

$rut_error_message = "El RUT ya está registrado en la base de datos.";
$correo_error_message = "El correo ya está registrado en la base de datos.";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $rut = $_POST["rut"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $telefono = $_POST["telefono"];
    $comentarios = $_POST["comentarios"];

    // Validación de correo y RUT
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El correo ingresado no es válido.";
    } elseif (!preg_match('/^[0-9]{7,8}-[0-9Kk]$/', $rut)) {
        $error_message = "El RUT ingresado no es válido.";
    } else {
        // Validar unicidad de correo y RUT en la base de datos
        $stmt = $conn->prepare("SELECT correo, rut FROM usuarios WHERE correo = ? OR rut = ?");
        $stmt->execute([$correo, $rut]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($correo === $rut) {
                $error_message = "El RUT y el correo ya están registrados en la base de datos.";
            } elseif ($result['correo'] === $correo) {
                $error_message = $correo_error_message;
            } elseif ($result['rut'] === $rut) {
                $error_message = $rut_error_message;
            }
        } else {
            // Validación y carga de la imagen
            if ($_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
                $foto_temp = $_FILES["foto"]["tmp_name"];
                $foto_nombre = $_FILES["foto"]["name"];
                $foto_extension = pathinfo($foto_nombre, PATHINFO_EXTENSION);
        
                // Verificar que la extensión sea .jpg
                if ($foto_extension !== "jpg") {
                    $error_message = "La foto debe ser en formato JPG.";
                } else {
                    // Guardar la imagen en una carpeta y obtener la ruta
                    $ruta_destino =  "/" . $foto_nombre;
                    move_uploaded_file($foto_temp, $ruta_destino);
                    
                    // Inserción en la base de datos
                    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, comentarios, rut, apellido, correo, telefono, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nombre, $comentarios, $rut, $apellido, $correo, $telefono, $ruta_destino]);
                    
                    header("Location: index.php");
                    exit;
                }
            } elseif ($_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
                $error_message = "Error al cargar la foto.";
            } else {
                // Inserción en la base de datos sin foto
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, comentarios, rut, apellido, correo, telefono) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $comentarios, $rut, $apellido, $correo, $telefono]);
                
                header("Location: index.php");
                exit;
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Mantenedor de Usuarios</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="create.php">Agregar Usuario</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="text-center">Agregar Usuario</h1>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="create.php" enctype="multipart/form-data">
                                <center>
            <div class="mb-3">
                <img id="imagen-previa" src="" alt="Vista previa de la imagen" class="img-thumbnail" style="max-width: 100px;">
            </div>
        </center>
                                                <div class="mb-3">
                            <label for="rut" class="form-label">RUT:</label>
                            <input type="text" name="rut" class="form-control" placeholder="12345687-1" required value="<?php echo isset($_POST['rut']) ? $_POST['rut'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre:</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Marcos" required value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido:</label>
                            <input type="text" name="apellido" class="form-control" placeholder="Perez" required value="<?php echo isset($_POST['apellido']) ? $_POST['apellido'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo:</label>
                            <input type="email" name="correo" class="form-control" placeholder="example@gmail.com" required value="<?php echo isset($_POST['correo']) ? $_POST['correo'] : ''; ?>"> 
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <input type="text" name="telefono" class="form-control" placeholder="+56962857425" required value="<?php echo isset($_POST['telefono']) ? $_POST['telefono'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto de Perfil (JPG):</label>
                            <input type="file" name="foto" accept=".jpg" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="comentarios" class="form-label">Comentarios:</label>
                            <textarea name="comentarios" class="form-control" placeholder="Escribe tu comentario" required><?php echo isset($_POST['comentarios']) ? $_POST['comentarios'] : ''; ?></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Agregar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para mostrar la vista previa de la imagen seleccionada -->
<script>
    document.querySelector('input[type="file"]').addEventListener('change', function() {
        const imagenPrev = document.querySelector('#imagen-previa');
        const archivo = this.files[0];

        if (archivo) {
            const reader = new FileReader();

            reader.onload = function(e) {
                imagenPrev.src = e.target.result;
            };

            reader.readAsDataURL(archivo);
        } else {
            imagenPrev.src = ''; // Si el usuario deselecciona la imagen
        }
    });
</script>

</body>
</html>



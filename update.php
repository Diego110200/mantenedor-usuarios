<?php
include_once "includes/db.php";

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $nombre = $_POST["nombre"];
    $rut = $_POST["rut"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $telefono = $_POST["telefono"];
    $comentarios = $_POST["comentarios"];

    // Obtener el nombre de la foto actual
    $foto_actual = $_POST["foto_actual"];

    // Verificar si se cargó una nueva foto
    if ($_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
        $foto_temp = $_FILES["foto"]["tmp_name"];
        $foto_nombre = $_FILES["foto"]["name"];
        $foto_extension = pathinfo($foto_nombre, PATHINFO_EXTENSION);

        // Verificar que la extensión sea .jpg
        if ($foto_extension !== "jpg") {
            $error_message = "La foto debe ser en formato JPG.";
        } else {
            // Guardar la nueva imagen en la carpeta "uploads"
            $ruta_destino = __DIR__ . "/uploads/" . $foto_nombre;
            move_uploaded_file($foto_temp, $ruta_destino);
            
            try {
                // Actualizar los datos en la base de datos incluyendo la nueva foto
                $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, comentarios = ?, rut = ?, apellido = ?, correo = ?, telefono = ?, foto_perfil = ? WHERE id = ?");
                $stmt->execute([$nombre, $comentarios, $rut, $apellido, $correo, $telefono, $foto_nombre, $id]);

                // Eliminar la foto anterior si se cargó una nueva
                if (!empty($foto_actual) && file_exists(__DIR__ . "/uploads/" . $foto_actual)) {
                    unlink(__DIR__ . "/uploads/" . $foto_actual);
                }

                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $error_message = "Error al actualizar los datos: " . $e->getMessage();
            }
        }
    } else {
        // Si no se cargó una nueva foto, utiliza el nombre de la foto actual
        $foto_nombre = $foto_actual;

        try {
            // Actualizar los datos en la base de datos sin cambiar la foto
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, comentarios = ?, rut = ?, apellido = ?, correo = ?, telefono = ?, foto_perfil = ? WHERE id = ?");
            $stmt->execute([$nombre, $comentarios, $rut, $apellido, $correo, $telefono, $foto_nombre, $id]);

            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error_message = "Error al actualizar los datos: " . $e->getMessage();
        }
    }
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
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
    <h1>Editar Usuario</h1>
    
    <form method="POST" action="update.php" class="mt-3" enctype="multipart/form-data">
        <div class="row">
                    <center>
            <div class="mb-3">
                <img id="imagen-previa" src="<?php echo 'uploads/' . $registro["foto_perfil"]; ?>" alt="Vista previa de la imagen" class="img-thumbnail" style="max-width: 100px;">
            </div>
        </center>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" value="<?= $registro["nombre"] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="rut" class="form-label">RUT</label>
                    <input type="text" name="rut" value="<?= $registro["rut"] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" name="apellido" value="<?= $registro["apellido"] ?>" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" name="correo" value="<?= $registro["correo"] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" name="telefono" value="<?= $registro["telefono"] ?>" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Nueva Foto  (JPG)</label>
                    <input type="file" name="foto" accept=".jpg" class="form-control" onchange="updatePhotoName(this)">
                    <small class="form-text text-muted" id="foto_actual"><?= $registro["foto_perfil"] ?></small>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="comentarios" class="form-label">Comentarios</label>
            <textarea name="comentarios" class="form-control"><?= $registro["comentarios"] ?></textarea>
        </div>


        
        <input type="hidden" name="id" value="<?= $registro["id"] ?>">
        <!-- Agregamos un campo oculto para almacenar el nombre de la foto actual -->
        <input type="hidden" name="foto_actual" value="<?= $registro["foto_perfil"] ?>">

        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>

</body>
</html>


<script>
function updatePhotoName(inputFile) {
    const photoName = document.getElementById('foto_actual');
    
    if (inputFile.files.length > 0) {
        photoName.textContent = "Archivo seleccionado: " + inputFile.files[0].name;
    } else {
        photoName.textContent = "Seleccione un archivo JPG si desea actualizar la foto.";
    }
}
</script>
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
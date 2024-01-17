<?php
include_once "includes/db.php";

$stmt = $conn->query("SELECT * FROM usuarios");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
include_once "includes/db.php";

// Número de resultados por página
$resultados_por_pagina = 10;

// Página actual (por defecto, la primera página)
$pagina_actual = 1;

// Comprobar si se ha especificado una página en la URL
if (isset($_GET["page"]) && is_numeric($_GET["page"])) {
    $pagina_actual = $_GET["page"];
}

// Calcular el desplazamiento (offset) en la consulta SQL
$offset = ($pagina_actual - 1) * $resultados_por_pagina;

// Consulta SQL con LIMIT y OFFSET
$stmt = $conn->prepare("SELECT * FROM usuarios LIMIT :resultados_por_pagina OFFSET :offset");
$stmt->bindParam(":resultados_por_pagina", $resultados_por_pagina, PDO::PARAM_INT);
$stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Mantenedor de Usuarios</a>
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
    <h1>Mantenedor de Usuarios</h1>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">RUT</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Apellido</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Foto</th>
                    <th scope="col">Comentario</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <th scope="row"><?= $registro["rut"] ?></th>
                        <td><?= $registro["nombre"] ?></td>
                        <td><?= $registro["apellido"] ?></td>
                        <td><?= $registro["correo"] ?></td>
                        <td><?= $registro["telefono"] ?></td>
                        <td>
                        <img src="<?= 'uploads/' . $registro["foto_perfil"] ?>" alt="Foto de perfil" class="img-thumbnail" style="max-width: 100px;">
                        </td>
                        <td><?= $registro["comentarios"] ?></td>
                        <td><?= $registro["fecha_registro"] ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="update.php?id=<?= $registro["id"] ?>" class="btn btn-sm btn-warning me-2">Editar</a>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $registro["id"] ?>">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="confirmDeleteModal<?= $registro["id"] ?>" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que deseas eliminar el usuario <?= $registro["nombre"] ?>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <a href="delete.php?id=<?= $registro["id"] ?>" class="btn btn-danger">Eliminar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php
            // Calcular el número total de páginas
            $stmt = $conn->query("SELECT COUNT(*) FROM usuarios");
            $total_registros = $stmt->fetchColumn();
            $total_paginas = ceil($total_registros / $resultados_por_pagina);

            // Enlaces a páginas anteriores
            if ($pagina_actual > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($pagina_actual - 1) . '">Anterior</a></li>';
            }

            // Enlaces a páginas
            for ($i = 1; $i <= $total_paginas; $i++) {
                echo '<li class="page-item ';
                if ($i == $pagina_actual) {
                    echo 'active';
                }
                echo '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
            }

            // Enlaces a páginas siguientes
            if ($pagina_actual < $total_paginas) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($pagina_actual + 1) . '">Siguiente</a></li>';
            }
            ?>
        </ul>
    </nav>
</div>

</body>
</html>

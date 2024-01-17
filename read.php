<?php
include_once "includes/db.php";

$stmt = $conn->query("SELECT * FROM usuarios");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $registro): ?>
            <tr>
                    <td><?= $registro["nombre"] ?></td>
                    <td><?= $registro["apellido"] ?></td>
                    <td><?= $registro["correo"] ?></td>
                    <td><?= $registro["telefono"] ?></td>
                    <td><?= $registro["foto_perfil"] ?></td>
                    <td><?= $registro["comentarios"] ?></td>
                <td>
                    <a href="update.php?id=<?= $registro["id"] ?>">Editar</a>
                    <a href="delete.php?id=<?= $registro["id"] ?>">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

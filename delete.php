<?php
include_once "includes/db.php";

if (isset($_GET["id"])) {
    $id = $_GET["id"];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php");
}
?>

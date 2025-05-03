<?php
    include "../../includes/db.php";
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_ids'])) {
        $ids = $_POST['delete_ids'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        try {
            $stmt = $conn->prepare("DELETE FROM Users WHERE ID IN ($placeholders) AND Tipo = 'user'");
            $stmt->execute($ids);
            header("Location: superadmin.php?deleted=1");
            exit;
        } catch (PDOException $e) {
            die("Errore durante l'eliminazione: " . $e->getMessage());
        }
    } else {
        header("Location: ../home.php");
        exit;
    }
?>
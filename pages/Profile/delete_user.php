<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        // Eventualmente elimina anche dati collegati (es. note, allegati, ecc.)
        // Ad esempio, se hai una tabella Notes:
        // $stmt = $conn->prepare("DELETE FROM Notes WHERE user_id = :user_id");
        // $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        // $stmt->execute();

        // Elimina l'utente
        $stmt = $conn->prepare("DELETE FROM Users WHERE ID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Distruggi la sessione
        session_unset();
        session_destroy();

        // Reindirizza alla homepage con messaggio
        header("Location: ../../index.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Errore durante l'eliminazione dell'account: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
?>

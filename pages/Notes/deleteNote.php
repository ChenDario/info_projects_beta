<?php
    include "../../includes/db.php";
    session_start();

    if(isset($_GET['id'])) {
        $note_id = (int)$_GET['id'];

        try {
            $stmt = $conn->prepare("DELETE FROM Notes WHERE ID = :note_id");
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            
            if($stmt->execute()) {
                $_SESSION['popup_message'] = "Delete SUCCESSFUL";
            } else {
                $_SESSION['popup_message'] = "Delete FAILED";
            }
        } catch(PDOException $e) {
            $_SESSION['popup_message'] = "Errore durante l'eliminazione: " . $e->getMessage();
        }
        
        header("Location: ../home.php");
        exit();
    }
?>
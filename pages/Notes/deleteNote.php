<?php
    //Per eliminare i note
    include "../../includes/db.php";
    session_start();

    if(isset($_GET['id'])) {
        $note_id = (int)$_GET['id'];

        // Query per eliminare la nota
        $stmt = $conn->prepare("DELETE FROM Notes WHERE ID = ?");
        $stmt->bind_param("i", $note_id);
        if($stmt->execute()){
            $_SESSION['popup_message'] = "Delete SUCCESSFUL";
        } else {
            $_SESSION['popup_message'] = "Delete FAILED";
        }
        header("Location: ../home.php");
        exit();
    }
?>
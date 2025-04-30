<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['file_ids'])) {
        $file_ids = $_POST['file_ids'];
        $user_id = $_SESSION['user_id'];

        $placeholders = implode(',', array_fill(0, count($file_ids), '?'));
        $types = str_repeat('i', count($file_ids));

        // Recupera i file per controllare l'ownership e i nomi da eliminare dal disco
        $stmt = $conn->prepare("SELECT ID, Stored_filename FROM Files WHERE ID IN ($placeholders) AND User_id = ?");
        $types_with_user = $types . 'i';
        $params = [...$file_ids, $user_id];
        $stmt->bind_param($types_with_user, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $files_to_delete = [];
        while ($row = $result->fetch_assoc()) {
            $files_to_delete[] = $row;
        }

        if (!empty($files_to_delete)) {
            // Cancella dal database
            $stmt = $conn->prepare("DELETE FROM Files WHERE ID IN ($placeholders) AND User_id = ?");
            $stmt->bind_param($types_with_user, ...$params);
            $stmt->execute();

            // Cancella fisicamente i file
            foreach ($files_to_delete as $file) {
                $file_path = "../../uploads/" . $file['Stored_filename'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
    }

    header("Location: fileOnlyView.php");
    exit();
?>

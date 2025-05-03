<?php
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['file_ids'])) {
        try {
            $file_ids = $_POST['file_ids'];
            $user_id = $_SESSION['user_id'];
            
            // Crea una stringa di placeholder per la query IN
            $placeholders = implode(',', array_fill(0, count($file_ids), '?'));
            
            // Recupera i file per verificare l'ownership e i nomi da eliminare
            $query = "SELECT ID, Stored_filename FROM Files WHERE ID IN ($placeholders) AND User_id = ?";
            $stmt = $conn->prepare($query);
            
            // Combina i parametri (file_ids + user_id)
            $params = array_merge($file_ids, [$user_id]);
            
            // Esegui la query con i parametri
            $stmt->execute($params);
            $files_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($files_to_delete)) {
                // Inizia transazione
                $conn->beginTransaction();
                
                try {
                    // Cancella dal database
                    $delete_query = "DELETE FROM Files WHERE ID IN ($placeholders) AND User_id = ?";
                    $stmt = $conn->prepare($delete_query);
                    $stmt->execute($params);
                    
                    // Cancella fisicamente i file
                    foreach ($files_to_delete as $file) {
                        $file_path = "../../uploads/" . $file['Stored_filename'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollBack();
                    throw $e;
                }
            }
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore durante l'eliminazione dei file: " . $e->getMessage());
        }
    }

    header("Location: fileOnlyView.php");
    exit();
?>
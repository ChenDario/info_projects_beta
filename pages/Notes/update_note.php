<?php 
    include "../../includes/db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $note_id = (int)$_POST['note_id'];
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $materia_id = (int)$_POST['materia'];
            $argomento = trim($_POST['argomento']);
            $user_id = $_SESSION['user_id'];

            // Validazione
            if (empty($title) || empty($content) || empty($materia_id) || empty($argomento)) {
                throw new Exception("Tutti i campi sono obbligatori.");
            }

            // Inizia transazione
            $conn->beginTransaction();

            // 1. Verifica/Crea argomento
            $stmt = $conn->prepare("SELECT ID FROM Argomento WHERE Nome = :argomento");
            $stmt->bindParam(':argomento', $argomento);
            $stmt->execute();
            
            $id_argomento = $stmt->fetchColumn();
            
            if (!$id_argomento) {
                $stmt = $conn->prepare("INSERT INTO Argomento (Nome) VALUES (:argomento)");
                $stmt->bindParam(':argomento', $argomento);
                $stmt->execute();
                $id_argomento = $conn->lastInsertId();
            }

            // 2. Aggiorna nota
            $stmt = $conn->prepare("UPDATE Notes SET Title = :title, Materia_ID = :materia_id, Content = :content WHERE ID = :note_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':materia_id', $materia_id, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();

            // 3. Verifica e associa argomento
            $stmt = $conn->prepare("SELECT 1 FROM appunti_argomento WHERE IDNote = :note_id AND IDArgomento = :argomento_id");
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->bindParam(':argomento_id', $id_argomento, PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetchColumn()) {
                $stmt = $conn->prepare("INSERT INTO appunti_argomento (IDNote, IDArgomento) VALUES (:note_id, :argomento_id)");
                $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
                $stmt->bindParam(':argomento_id', $id_argomento, PDO::PARAM_INT);
                $stmt->execute();
            }

            // 4. Gestione nuovi file
            if (!empty($_FILES['file_upload']['name'][0])) {
                $upload_dir = '../../uploads/';

                foreach ($_FILES['file_upload']['name'] as $index => $original_name) {
                    if ($_FILES['file_upload']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['file_upload']['tmp_name'][$index];
                        $mime_type = $_FILES['file_upload']['type'][$index];
                        $file_size = $_FILES['file_upload']['size'][$index];
                        $stored_name = uniqid() . "_" . basename($original_name);
                        $destination = $upload_dir . $stored_name;

                        // Verifica se il file esiste già
                        $stmt = $conn->prepare("SELECT 1 FROM Files WHERE Note_id = :note_id AND Original_filename = :original_name");
                        $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
                        $stmt->bindParam(':original_name', $original_name);
                        $stmt->execute();
                        
                        if (!$stmt->fetchColumn()) {
                            if (move_uploaded_file($tmp_name, $destination)) {
                                $stmt = $conn->prepare("INSERT INTO Files (Note_id, User_id, Original_filename, Stored_filename, Mime_type, File_size) 
                                                      VALUES (:note_id, :user_id, :original_name, :stored_name, :mime_type, :file_size)");
                                $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
                                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                                $stmt->bindParam(':original_name', $original_name);
                                $stmt->bindParam(':stored_name', $stored_name);
                                $stmt->bindParam(':mime_type', $mime_type);
                                $stmt->bindParam(':file_size', $file_size, PDO::PARAM_INT);
                                $stmt->execute();
                            }
                        }
                    }
                }
            }

            // Commit transazione
            $conn->commit();
            $_SESSION['popup_message'] = "Nota aggiornata con successo!";

        } catch(Exception $e) {
            $conn->rollBack();
            $_SESSION['popup_message'] = "Errore: " . $e->getMessage();
        }

        header("Location: ../home.php");
        exit();
    }
?>
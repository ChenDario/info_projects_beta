<?php 
    include "../../includes/db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $user_id = $_SESSION['user_id'];
            $materia_id = (int)$_POST['materia'];
            $argomento = trim($_POST['argomento']);
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

            // 2. Inserisci nota
            $stmt = $conn->prepare("INSERT INTO Notes (User_id, Title, Materia_ID, Content) VALUES (:user_id, :title, :materia_id, :content)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':materia_id', $materia_id, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content);
            $stmt->execute();
            
            $note_id = $conn->lastInsertId();

            // 3. Associa argomento
            $stmt = $conn->prepare("INSERT INTO appunti_argomento (IDNote, IDArgomento) VALUES (:note_id, :argomento_id)");
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->bindParam(':argomento_id', $id_argomento, PDO::PARAM_INT);
            $stmt->execute();

            // 4. Gestione file
            if (!empty($_FILES['file_upload']['name'][0])) {
                $upload_dir = "../../uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                foreach ($_FILES['file_upload']['name'] as $index => $filename) {
                    if ($_FILES['file_upload']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['file_upload']['tmp_name'][$index];
                        $original_name = basename($filename);
                        $mime_type = mime_content_type($tmp_name);
                        $file_size = filesize($tmp_name);
                        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                        $unique_name = uniqid("file_", true) . "." . $ext;
                        $destination = $upload_dir . $unique_name;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $stmt = $conn->prepare("INSERT INTO Files (Note_id, User_id, Original_filename, Stored_filename, Mime_type, File_size) 
                                                  VALUES (:note_id, :user_id, :original_name, :unique_name, :mime_type, :file_size)");
                            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
                            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                            $stmt->bindParam(':original_name', $original_name);
                            $stmt->bindParam(':unique_name', $unique_name);
                            $stmt->bindParam(':mime_type', $mime_type);
                            $stmt->bindParam(':file_size', $file_size, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                }
            }

            // Commit transazione
            $conn->commit();
            $_SESSION['popup_message'] = "Nota salvata con successo!";

        } catch(Exception $e) {
            $conn->rollBack();
            $_SESSION['popup_message'] = "Errore: " . $e->getMessage();
        }

        header("Location: ../home.php");
        exit();
    }
?>
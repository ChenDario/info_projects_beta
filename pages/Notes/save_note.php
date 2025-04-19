<?php 
    include "../../includes/db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $user_id = $_SESSION['user_id'];
        $materia_id = $_POST['materia'];
        $argomento = trim($_POST['argomento']);

        // Validazione
        if (empty($title) || empty($content) || empty($materia_id) || empty($argomento)) {
            die("Errore: Tutti i campi sono obbligatori.");
        }

        // 1. Verifica se l'argomento esiste
        $stmt = $conn->prepare("SELECT ID FROM Argomento WHERE Nome = ?");
        $stmt->bind_param("s", $argomento);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Argomento già esistente
            $row = $result->fetch_assoc();
            $id_argomento = $row['ID'];
        } else {
            // Argomento non esiste, quindi lo creiamo
            $stmt_insert_arg = $conn->prepare("INSERT INTO Argomento (Nome) VALUES (?)");
            $stmt_insert_arg->bind_param("s", $argomento);
            if ($stmt_insert_arg->execute()) {
                $id_argomento = $stmt_insert_arg->insert_id;
            } else {
                $_SESSION['popup_message'] = "Errore durante l'inserimento dell'argomento.";
                header("Location: ../home.php");
                exit();
            }
            $stmt_insert_arg->close();
        }

        // 2. Inserisci la nota
        $stmt = $conn->prepare("INSERT INTO Notes (User_id, Title, Materia_ID, Content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $title, $materia_id, $content);

        if ($stmt->execute()) {
            $note_id = $stmt->insert_id;

            // 3. Inserisci nella tabella appunti_argomento
            $stmt_associa = $conn->prepare("INSERT INTO appunti_argomento (IDNote, IDArgomento) VALUES (?, ?)");
            $stmt_associa->bind_param("ii", $note_id, $id_argomento);

            if (!$stmt_associa->execute()) {
                $_SESSION['popup_message'] = "Nota salvata, ma errore nell'associare l'argomento.";
            } else {
                $_SESSION['popup_message'] = "Nota salvata con successo!";
            }

            $stmt_associa->close();

            // 4. Gestione dei file (opzionale)
            if (isset($_FILES['file_upload']) && count($_FILES['file_upload']['name']) > 0) {
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

                        // Crea un nome univoco
                        $unique_name = uniqid("file_", true) . "." . $ext;
                        $destination = $upload_dir . $unique_name;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            // Inserisci info file nel DB
                            $stmt_file = $conn->prepare("INSERT INTO Files (Note_id, User_id, Original_filename, Stored_filename, Mime_type, File_size) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt_file->bind_param("iisssi", $note_id, $user_id, $original_name, $unique_name, $mime_type, $file_size);
                            $stmt_file->execute();
                            $stmt_file->close();
                        }
                    }
                }
            }

        } else {
            $_SESSION['popup_message'] = "Errore durante il salvataggio della nota.";
        }

        $stmt->close();

        // Reindirizzamento finale
        header("Location: ../home.php");
        exit();
    }
?>

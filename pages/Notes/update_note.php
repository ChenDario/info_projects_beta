<?php 
    include "../../includes/db.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $note_id = (int)$_POST['note_id']; 
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
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
            $row = $result->fetch_assoc();
            $id_argomento = $row['ID'];
        } else {
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

        // 2. Aggiorna la nota
        $stmt = $conn->prepare("UPDATE Notes SET Title = ?, Materia_ID = ?, Content = ? WHERE ID = ?");
        $stmt->bind_param("sisi", $title, $materia_id, $content, $note_id);

        if ($stmt->execute()) {
            // 3. Associa l'argomento se non è già associato
            $stmt_check_assoc = $conn->prepare("SELECT 1 FROM appunti_argomento WHERE IDNote = ? AND IDArgomento = ?");
            $stmt_check_assoc->bind_param("ii", $note_id, $id_argomento);
            $stmt_check_assoc->execute();
            $assoc_result = $stmt_check_assoc->get_result();

            if ($assoc_result->num_rows === 0) {
                $stmt_associa = $conn->prepare("INSERT INTO appunti_argomento (IDNote, IDArgomento) VALUES (?, ?)");
                $stmt_associa->bind_param("ii", $note_id, $id_argomento);
                if (!$stmt_associa->execute()) {
                    $_SESSION['popup_message'] = "Nota salvata, ma errore nell'associare l'argomento.";
                } else {
                    $_SESSION['popup_message'] = "Nota aggiornata con successo!";
                }
                $stmt_associa->close();
            } else {
                $_SESSION['popup_message'] = "Nota aggiornata con successo!";
            }

            $stmt_check_assoc->close();
        } else {
            $_SESSION['popup_message'] = "Errore nell'aggiornamento della nota.";
        }

        $stmt->close();

        // 4. Gestione upload nuovi file
        if (!empty($_FILES['file_upload']['name'][0])) {
            $upload_dir = '../../uploads/';
            $user_id = $_SESSION['user_id']; // assicurati che l'utente sia loggato

            foreach ($_FILES['file_upload']['name'] as $index => $original_name) {
                $tmp_name = $_FILES['file_upload']['tmp_name'][$index];
                $mime_type = $_FILES['file_upload']['type'][$index];
                $file_size = $_FILES['file_upload']['size'][$index];

                if ($file_size > 0) {
                    $stored_name = uniqid() . "_" . basename($original_name);
                    $destination = $upload_dir . $stored_name;

                    // Evita duplicati per nome file e nota
                    $check_file = $conn->prepare("SELECT 1 FROM Files WHERE Note_id = ? AND Original_filename = ?");
                    $check_file->bind_param("is", $note_id, $original_name);
                    $check_file->execute();
                    $result = $check_file->get_result();

                    if ($result->num_rows === 0) {
                        if (move_uploaded_file($tmp_name, $destination)) {
                            $stmt_insert_file = $conn->prepare("INSERT INTO Files (Note_id, User_id, Original_filename, Stored_filename, Mime_type, File_size) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt_insert_file->bind_param("iisssi", $note_id, $user_id, $original_name, $stored_name, $mime_type, $file_size);
                            $stmt_insert_file->execute();
                            $stmt_insert_file->close();
                        }
                    }

                    $check_file->close();
                }
            }
        }

        // 5. Reindirizzamento finale
        header("Location: ../home.php");
        exit();
    }
?>

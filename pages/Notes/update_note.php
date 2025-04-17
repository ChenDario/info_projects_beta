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
        $stmt = $conn->prepare("UPDATE Notes SET Title = ?, Materia_ID = ?, Content = ? WHERE ID = ?");
        $stmt->bind_param("sisi", $title, $materia_id, $content, $note_id);

        if ($stmt->execute()) {
            // 3. Controlla se esiste già l'associazione(evito duplicati)
            $stmt_check_assoc = $conn->prepare("SELECT 1 FROM appunti_argomento WHERE IDNote = ? AND IDArgomento = ?");
            $stmt_check_assoc->bind_param("ii", $note_id, $id_argomento);
            $stmt_check_assoc->execute();
            $assoc_result = $stmt_check_assoc->get_result();

            if ($assoc_result->num_rows === 0) {
                // Se non esiste, la inseriamo
                $stmt_associa = $conn->prepare("INSERT INTO appunti_argomento (IDNote, IDArgomento) VALUES (?, ?)");
                $stmt_associa->bind_param("ii", $note_id, $id_argomento);

                if (!$stmt_associa->execute()) {
                    $_SESSION['popup_message'] = "Nota salvata, ma errore nell'associare l'argomento.";
                } else {
                    $_SESSION['popup_message'] = "Note Update SUCCESSFUL!";
                }

                $stmt_associa->close();
            } else {
                $_SESSION['popup_message'] = "Note Update SUCCESSFUL!";
            }

            $stmt_check_assoc->close();

        } else {
            $_SESSION['popup_message'] = "ERROR IN THE NOTE UPDATE";
        }

        $stmt->close();
        
        // Reindirizzamento finale
        header("Location: ../home.php");
        exit();
    }
?>

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
    } else {
        $_SESSION['popup_message'] = "Errore durante il salvataggio della nota.";
    }

    $stmt->close();

    // Reindirizzamento finale
    header("Location: ../home.php");
    exit();
}
?>

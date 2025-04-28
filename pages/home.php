<?php  
    include "../includes/db.php";
    session_start();

    $popup_message = "";
    if (isset($_SESSION['popup_message'])) {
        $popup_message = $_SESSION['popup_message'];
        unset($_SESSION['popup_message']); // Rimuove il messaggio dopo averlo mostrato
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
        // Rimozione degli spazi bianchi dai dati ricevuti
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Preparazione della query per ottenere l'utente in base al nome utente
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Se l'utente esiste
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifica se la password è corretta
            if (!password_verify($password, $user['Password_hash'])) {
                $_SESSION['message'] = "Password errata";
                header("Location: ../index.php");
                exit();
            }

            // Salvataggio dell'ID utente nella sessione
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['Username'];
        } else {
            // Username non trovato
            $_SESSION['message'] = "Username errato";
            header("Location: ../index.php");
            exit();
        }
    }

    // Controllo che l'utente sia loggato
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Recupero dei dati dell'utente loggato
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT Username FROM Users WHERE ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <!-- CSS For General Structure -->
    <link rel="stylesheet" href="../css/home.css">
    <!-- CSS Notes Visualization -->
    <link rel="stylesheet" href="../css/noteVisual.css">
    <!-- Framework Flatpickr per le date-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <!-- Messaggio di Errore per l'inserimento della nota-->
    <?php if (!empty($popup_message)) : ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            alert("<?= addslashes($popup_message) ?>");
        });
    </script>
    <?php endif; ?>

    <div>
        <!-- Sidebar laterale -->
        <div class="sidebar">
            <!-- Profilo utente con iniziale -->
            <div class="sidebar-profile">
                <?php echo strtoupper(substr($userData['Username'], 0, 1)); ?>
            </div>
            <!-- Link al profilo -->
            <h3 class="username"><a href="Profile/profile.php"><?= htmlspecialchars($userData['Username']) ?></a></h3>

            <!-- Sezione filtri -->
            <div class="sidebar-sections">
                <p class="item-content">Filtri</p>
                <div class="sidebar-item">
                    <!-- Filtro per materia -->
                    <select name="materia" id="materia" class="filter-input" form="filtersForm">
                        <option value="">Tutte</option>
                        <?php
                            // Recupera tutte le materie dal DB per il filtro
                            $stmt_m = $conn->prepare("SELECT * FROM Materia");
                            $stmt_m->execute();
                            $result = $stmt_m->get_result();
                            while($row = $result->fetch_assoc()) {
                                $selected = ($_GET['materia'] ?? '') == $row['ID'] ? "selected" : "";
                                echo "<option value='{$row['ID']}' $selected>" . htmlspecialchars($row['Nome']) . "</option>";
                            }
                        ?>
                    </select>
                    <!-- Filtro per autore -->
                    <input type="text" name="autore" id="autore" class="filter-input" placeholder="Autore..." value="<?= htmlspecialchars($_GET['autore'] ?? '') ?>" form="filtersForm">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="onlyFile" id="files" onchange="handleFileCheckbox(this)">
                        <label for="files">Only show file/s</label>
                    </div>
                </div>

                <!-- Filtro per date -->
                <div class="sidebar-item">
                    <label class="item-content">From Date (create)</label>
                    <input type="text" id="from_date" name="from_date" class="filter-input" value="<?= $_GET['from_date'] ?? '' ?>" form="filtersForm" placeholder="Select Date...">

                    <label class="item-content">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="filter-input" value="<?= $_GET['to_date'] ?? '' ?>" form="filtersForm" placeholder="Select Date...">
                </div>

                <!-- Filtro per ordinamento -->
                <p class="item-content">Order</p>
                <div class="sidebar-item">
                    <select name="sort_by" class="filter-input" form="filtersForm">
                        <option value="date" <?= ($_GET['sort_by'] ?? '') == 'date' ? 'selected' : '' ?>>Date</option>
                        <option value="title" <?= ($_GET['sort_by'] ?? '') == 'title' ? 'selected' : '' ?>>Title</option>
                    </select>
                    <select name="order" class="filter-input" form="filtersForm">
                        <option value="asc" <?= ($_GET['order'] ?? '') == 'asc' ? 'selected' : '' ?>>ASC</option>
                        <option value="desc" <?= ($_GET['order'] ?? '') == 'desc' ? 'selected' : '' ?>>DESC</option>
                    </select>
                </div>

                <!-- Form per applicare i filtri -->
                <form method="GET" id="filtersForm">
                    <button type="submit" class="filter-button">Applica</button>
                </form>
            </div>

            <!-- Logout -->
            <div class="logout-container">
                <a href="logout.php" class="logout-link">
                    <img src="../images/logout.png" alt="Logout" class="logout-icon">
                </a>
            </div>
        </div>

        <!-- Barra di ricerca -->
        <div class="navbar">
            <form method="GET" action="">
                <input type="text" name="search" class="search-bar" placeholder="Cerca..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>

        <!-- Contenuto principale -->
        <div class="container">
            <?php
                // Recupero parametri dai filtri e ricerca
                $searchTerm = $_GET['search'] ?? '';
                $materia = $_GET['materia'] ?? '';
                $autore = $_GET['autore'] ?? '';
                $from_date = $_GET['from_date'] ?? '';
                $to_date = $_GET['to_date'] ?? '';
                $sort_by = $_GET['sort_by'] ?? 'date';
                $order = $_GET['order'] ?? 'desc';

                // Query base per ottenere le note
                $query = "
                    SELECT DISTINCT N.*, U.Username, M.Nome AS MateriaNome 
                    FROM Notes N
                    INNER JOIN Users U ON N.User_id = U.ID
                    INNER JOIN Materia M ON N.Materia_ID = M.ID
                    LEFT JOIN appunti_argomento AA ON N.ID = AA.IDNote
                    LEFT JOIN Argomento A ON AA.IDArgomento = A.ID
                    WHERE 1=1
                ";

                // Parametri per bind_param
                $params = [];
                $types = "";

                // Aggiunta dei filtri alla query
                if (!empty($searchTerm)) {
                    $query .= " AND (
                        N.Title LIKE CONCAT('%', ?, '%')
                        OR N.Content LIKE CONCAT('%', ?, '%')
                        OR A.Nome LIKE CONCAT('%', ?, '%')
                    )";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $types .= "sss";
                }                
                if (!empty($materia)) {
                    $query .= " AND Materia_ID = ?";
                    $params[] = $materia;
                    $types .= "i";
                }
                if (!empty($autore)) {
                    $query .= " AND U.Username LIKE CONCAT('%', ?, '%')";
                    $params[] = $autore;
                    $types .= "s";
                }
                if (!empty($from_date)) {
                    $query .= " AND N.Created_at >= ?";
                    $params[] = $from_date;
                    $types .= "s";
                }
                if (!empty($to_date)) {
                    $query .= " AND N.Created_at <= ?";
                    $params[] = $to_date;
                    $types .= "s";
                }
                // Ordinamento dei risultati
                $order_by_column = $sort_by === 'title' ? 'N.Title' : 'N.Updated_at';
                $order_dir = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
                $query .= " ORDER BY $order_by_column $order_dir";
                // Preparazione e esecuzione della query
                $stmt = $conn->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                // Visualizzazione dei risultati
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "
                            <div class='card'>
                                <h2 class='card-title'>" . htmlspecialchars($row['Title']) . "</h2>
                                <div class='card-content-wrapper'>
                                    <p class='card-content'>" . nl2br($row['Content']) . "</p>
                                </div>
                                <div class='note-actions'>
                                    <a href='Notes/noteDetail.php?id={$row['ID']}' class='read-more'>More</a>
                        ";
                        // Controlla se l'utente loggato è l'autore della nota
                        if ($row['User_id'] == $user_id) {
                            echo "
                                <a href='Notes/editNote.php?id={$row['ID']}' class='read-more'>Edit</a>
                                <a href='Notes/deleteNote.php?id={$row['ID']}' class='read-more' onclick='return confirm(\"Sei sicuro di voler cancellare questa nota?\")'>Delete</a>
                            ";
                        }
                        echo "</div></div>";
                    }
                }
            ?>
        </div>
        <!-- Pulsante per aggiungere una nuova nota -->
        <div class="btn">
            <button class="floating-button" onclick="location.href='Notes/addNote.php'">+</button>
        </div>
    </div>
    <!-- Inizializzazione di Flatpickr per i campi data -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/it.js"></script>
    <script src="../js/Flatpickr.js"></script>
    <script>
        function handleFileCheckbox(checkbox) {
            if (checkbox.checked) {
                // Reindirizza alla pagina desiderata
                window.location.href = 'Files/fileOnlyView.php';
            }
        }
    </script>
</body>
</html>
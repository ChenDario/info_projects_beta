<?php  
    include "../includes/db.php";
    session_start();

    // Gestione dei messaggi popup (se presenti in sessione)
    $popup_message = "";
    if (isset($_SESSION['popup_message'])) {
        $popup_message = $_SESSION['popup_message'];
        unset($_SESSION['popup_message']);
    }

    // Processo di login quando viene inviato il form
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        try {
            // Verifica credenziali utente nel database
            $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Controllo password hashata
                if (!password_verify($password, $user['Password_hash'])) {
                    $_SESSION['message'] = "Password errata";
                    header("Location: ../index.php");
                    exit();
                }

                // Impostazione variabili di sessione per l'utente loggato
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['user_type'] = $user['Tipo']; 
            } else {
                $_SESSION['message'] = "Username errato";
                header("Location: ../index.php");
                exit();
            }
        } catch(PDOException $e) {
            $_SESSION['message'] = "Errore durante il login";
            header("Location: ../index.php");
            exit();
        }
    }

    // Reindirizzamento se l'utente non è loggato
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Recupero dati utente per visualizzazione nella sidebar
    try {
        $stmt = $conn->prepare("SELECT Username, Tipo FROM Users WHERE ID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_type = $userData['Tipo'];
    } catch(PDOException $e) {
        die("Errore nel recupero dei dati utente: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <!-- CSS General Structure -->
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
    <!-- Visualizzazione popup message se presente -->
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
                            try {
                                // Popolamento dropdown materie dal database
                                $stmt_m = $conn->query("SELECT * FROM Materia");
                                while($row = $stmt_m->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($_GET['materia'] ?? '') == $row['ID'] ? "selected" : "";
                                    echo "<option value='{$row['ID']}' $selected>" . htmlspecialchars($row['Nome']) . "</option>";
                                }
                            } catch(PDOException $e) {
                                die("Errore nel recupero delle materie: " . $e->getMessage());
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
            <?php if ($user_type === 'admin'): ?>
                <div id="superadmin">
                    <a href="Admin/superadmin.php"> SuperAdmin </a>
                </div>
            <?php endif; ?>
            <form method="GET" action="">
                <input type="text" name="search" class="search-bar" placeholder="Cerca..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
        </div>
        <!-- Contenuto principale - Visualizzazione note -->
        <div class="container">
            <?php
                // Recupero parametri di filtraggio dalla query string
                $searchTerm = $_GET['search'] ?? '';
                $materia = $_GET['materia'] ?? '';
                $autore = $_GET['autore'] ?? '';
                $from_date = $_GET['from_date'] ?? '';
                $to_date = $_GET['to_date'] ?? '';
                $sort_by = $_GET['sort_by'] ?? 'date';
                $order = $_GET['order'] ?? 'desc';
                // Costruzione query dinamica in base ai filtri
                $query = "
                    SELECT DISTINCT N.*, U.Username, M.Nome AS MateriaNome 
                    FROM Notes N
                    INNER JOIN Users U ON N.User_id = U.ID
                    INNER JOIN Materia M ON N.Materia_ID = M.ID
                    LEFT JOIN appunti_argomento AA ON N.ID = AA.IDNote
                    LEFT JOIN Argomento A ON AA.IDArgomento = A.ID
                    WHERE 1=1
                ";

                $params = [];
                
                // Aggiunta condizioni WHERE in base ai filtri selezionati
                if (!empty($searchTerm)) {
                    $query .= " AND (
                        LOWER(N.Title) LIKE LOWER(CONCAT('%', :searchTerm, '%'))
                        OR LOWER(N.Content) LIKE LOWER(CONCAT('%', :searchTerm, '%'))
                        OR LOWER(A.Nome) LIKE LOWER(CONCAT('%', :searchTerm, '%'))
                    )";
                    $params[':searchTerm'] = $searchTerm;
                }                
                if (!empty($materia)) {
                    $query .= " AND Materia_ID = :materia";
                    $params[':materia'] = $materia;
                }
                if (!empty($autore)) {
                    $query .= " AND LOWER(U.Username) LIKE LOWER(CONCAT('%', :autore, '%'))";
                    $params[':autore'] = $autore;
                }
                if (!empty($from_date)) {
                    $query .= " AND N.Created_at >= :from_date";
                    $params[':from_date'] = $from_date;
                }
                if (!empty($to_date)) {
                    $query .= " AND N.Created_at <= :to_date";
                    $params[':to_date'] = $to_date;
                }

                // Impostazione ordinamento risultati
                $order_by_column = $sort_by === 'title' ? 'N.Title' : 'N.Updated_at';
                $order_dir = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
                $query .= " ORDER BY $order_by_column $order_dir";

                try {
                    // Esecuzione query e visualizzazione risultati
                    $stmt = $conn->prepare($query);
                    foreach ($params as $key => &$val) {
                        $stmt->bindParam($key, $val);
                    }
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "
                                <div class='card'>
                                    <h2 class='card-title'>" . htmlspecialchars($row['Title']) . "</h2>
                                    <div class='card-content-wrapper'>
                                        <p class='card-content'>" . nl2br($row['Content']) . "</p>
                                    </div>
                                    <div class='note-actions'>
                                        <a href='Notes/noteDetail.php?id={$row['ID']}' class='read-more'>More</a>
                            ";
                            // Mostra pulsanti modifica/cancella solo per il proprietario o admin
                            if ($row['User_id'] == $_SESSION['user_id'] || $user_type === 'admin') {
                                echo "
                                    <a href='Notes/editNote.php?id={$row['ID']}' class='read-more'>Edit</a>
                                    <a href='Notes/deleteNote.php?id={$row['ID']}' class='read-more' onclick='return confirm(\"Sei sicuro di voler cancellare questa nota?\")'>Delete</a>
                                ";
                            }
                            echo "</div></div>";
                        }
                    }
                } catch(PDOException $e) {
                    die("Errore nel recupero delle note: " . $e->getMessage());
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
        // Gestione checkbox per visualizzazione solo file
        function handleFileCheckbox(checkbox) {
            if (checkbox.checked) {
                window.location.href = 'Files/fileOnlyView.php';
            }
        }
    </script>
</body>
</html>
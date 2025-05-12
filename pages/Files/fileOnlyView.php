<?php  
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Recupero dati utente
    try {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT Username, Tipo FROM Users WHERE ID = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_type = $userData['Tipo'];
    } catch (PDOException $e) {
        die("Errore nel recupero dei dati utente: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files</title>
    <!-- CSS General Structure -->
    <link rel="stylesheet" href="../../css/home.css">
    <link rel="stylesheet" href="../../css/files.css">
    <link rel="stylesheet" href="../../css/fileVisualization.css">
    <!-- CSS Date -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-profile">
                <?php echo strtoupper(substr($userData['Username'], 0, 1)); ?>
            </div>
            <h3 class="username">
                <a href="../Profile/profile.php"><?= htmlspecialchars($userData['Username']) ?></a>
            </h3>

            <div class="sidebar-sections">
                <p class="item-content">Filtri</p>
                <div class="sidebar-item">
                    <select name="materia" id="materia" class="filter-input" form="filtersForm">
                        <option value="">Tutte</option>
                        <?php
                        try {
                            $stmt_m = $conn->query("SELECT * FROM Materia");
                            while($row = $stmt_m->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($_GET['materia'] ?? '') == $row['ID'] ? "selected" : "";
                                echo "<option value='".htmlspecialchars($row['ID'])."' $selected>".htmlspecialchars($row['Nome'])."</option>";
                            }
                        } catch (PDOException $e) {
                            error_log("Errore nel recupero delle materie: " . $e->getMessage());
                        }
                        ?>
                    </select>

                    <input type="text" name="autore" id="autore" class="filter-input" placeholder="Autore..." 
                           value="<?= htmlspecialchars($_GET['autore'] ?? '') ?>" form="filtersForm">
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="onlyFile" id="files" onchange="handleFileCheckbox(this)">
                        <label for="files">Show All</label>
                    </div>
                </div>

                <div class="sidebar-item">
                    <label class="item-content">From Date (create)</label>
                    <input type="text" id="from_date" name="from_date" class="filter-input" 
                           value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>" form="filtersForm" placeholder="Select Date...">

                    <label class="item-content">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="filter-input" 
                           value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>" form="filtersForm" placeholder="Select Date...">
                </div>

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

                <form method="GET" id="filtersForm">
                    <button type="submit" class="filter-button">Applica</button>
                </form>
            </div>

            <div class="logout-container">
                <a href="../logout.php" class="logout-link">
                    <img src="../../images/logout.png" alt="Logout" class="logout-icon">
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
            try {
                $searchTerm = $_GET['search'] ?? '';
                $materia = $_GET['materia'] ?? '';
                $autore = $_GET['autore'] ?? '';
                $from_date = $_GET['from_date'] ?? '';
                $to_date = $_GET['to_date'] ?? '';
                $sort_by = $_GET['sort_by'] ?? 'date';
                $order = $_GET['order'] ?? 'desc';

                $query = "
                SELECT F.*, U.Username, M.ID AS Materia_ID
                FROM Files F
                LEFT JOIN Users U ON F.User_id = U.id
                LEFT JOIN Notes N ON N.id = F.Note_id
                LEFT JOIN Materia M ON M.ID = N.Materia_ID
                WHERE 1=1
            ";


                $params = [];

                // Filtro per utente se non è admin
                if ($user_type !== 'admin') {
                    $query .= " AND F.User_id = :user_id";
                    $params[':user_id'] = $user_id;
                }

                if (!empty($searchTerm)) {
                    $query .= " AND (F.Original_filename LIKE :searchTerm OR U.Username LIKE :searchTerm2)";
                    $params[':searchTerm'] = "%$searchTerm%";
                    $params[':searchTerm2'] = "%$searchTerm%";
                }

                if (!empty($materia)) {
                    $query .= " AND Materia_ID = :materia";
                    $params[':materia'] = $materia;
                }

                if (!empty($autore)) {
                    $query .= " AND U.Username LIKE :autore";
                    $params[':autore'] = "%$autore%";
                }

                if (!empty($from_date)) {
                    $query .= " AND F.Created_at >= :from_date";
                    $params[':from_date'] = $from_date;
                }

                if (!empty($to_date)) {
                    $query .= " AND F.Created_at <= :to_date";
                    $params[':to_date'] = $to_date;
                }

                $order_by_column = $sort_by === 'title' ? 'Original_filename' : 'Created_at';
                $order_dir = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
                $query .= " ORDER BY $order_by_column $order_dir";

                $stmt = $conn->prepare($query);
                $stmt->execute($params);
                $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($files)): ?>
                    <form method="POST" action="delete_files.php" id="delete-form" onsubmit="return confirm('Sei sicuro di voler eliminare i file selezionati?');">
                        <div class="file-header">
                            <h2 class="file-title">Files</h2>
                            <button type="submit" class="delete-button"> Delete Selected </button>
                        </div>    
                        <div id="file-preview" class="file-preview">
                            <?php foreach ($files as $file):
                                $filePath = "../../uploads/" . htmlspecialchars($file['Stored_filename']);
                                $fileName = htmlspecialchars($file['Original_filename']);
                                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                                $isPDF = strtolower($ext) === 'pdf';
                                $isTxt = strtolower($ext) === 'txt';
                            ?>
                            <div class="file-container" onclick="toggleCheckbox(this)">
                                <?php if ($isImage): ?>
                                    <div class="img-div">
                                        <img class="preview-image" src="<?= $filePath ?>" onclick="event.stopPropagation(); openModal('<?= $filePath ?>', 'image')">
                                    </div>
                                <?php elseif ($isPDF): ?>
                                    <div id="pdf-div">
                                        <p><?= $fileName ?></p>
                                        <div id="canvas-pdf" onclick="event.stopPropagation(); openModal('<?= $filePath ?>', 'pdf')">
                                            <canvas class="pdf-preview" data-pdf="<?= $filePath ?>"></canvas>
                                        </div>
                                    </div>
                                <?php elseif ($isTxt): ?>
                                    <div class="file-icon" onclick="event.stopPropagation(); openText('<?= $filePath ?>')"><?= $fileName ?></div>
                                <?php else: ?>
                                    <div class="file-icon"><?= $fileName ?></div>
                                <?php endif; ?>
                                <?php if ($file['User_id'] == $user_id || $user_type === 'admin'): ?>
                                    <input type="checkbox" class="file-checkbox" name="file_ids[]" value="<?= $file['ID'] ?>">
                                <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php endif;
            } catch (PDOException $e) {
                error_log("Errore nel recupero dei file: " . $e->getMessage());
                echo "<p>Si è verificato un errore nel caricamento dei file.</p>";
            }
            ?>

            <!-- Modal -->
            <div id="modal-viewer" class="modal-viewer" style="display:none;" onclick="closeModal()">
                <div class="modal-content" onclick="event.stopPropagation();">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <div id="modal-body"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script File Visualization -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../js/FileVisualization.js"></script>
    <!-- Script Flatpickr per le date -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/it.js"></script>
    <script src="../js/Flatpickr.js"></script>

    <script>
        function handleFileCheckbox(checkbox) {
            if (checkbox.checked) {
                window.location.href = '../home.php';
            }
        }
        document.querySelectorAll('.file-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                cb.closest('.file-container').classList.toggle('selected', cb.checked);
            });
        });
        function toggleCheckbox(container) {
            const checkbox = container.querySelector('.file-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                container.classList.toggle('selected', checkbox.checked);
            }
        }
    </script>
</body>
</html>
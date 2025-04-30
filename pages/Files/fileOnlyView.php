<?php  
    include "../../includes/db.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }

    // Recupero dati utente
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files</title>

    <!-- CSS For General Structure -->
    <link rel="stylesheet" href="../../css/home.css">
    <!-- CSS Files Container -->
    <link rel="stylesheet" href="../../css/files.css">
    <!--Link CSS File Visualization-->
    <link rel="stylesheet" href="../../css/fileVisualization.css">
    <!-- Framework Flatpickr per le date-->
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
                <a href="Profile/profile.php"><?= htmlspecialchars($userData['Username']) ?></a>
            </h3>

            <div class="sidebar-sections">
                <p class="item-content">Filtri</p>
                <div class="sidebar-item">
                    <select name="materia" id="materia" class="filter-input" form="filtersForm">
                        <option value="">Tutte</option>
                        <?php
                        $stmt_m = $conn->prepare("SELECT * FROM Materia");
                        $stmt_m->execute();
                        $result_m = $stmt_m->get_result();
                        while($row = $result_m->fetch_assoc()) {
                            $selected = ($_GET['materia'] ?? '') == $row['ID'] ? "selected" : "";
                            echo "<option value='{$row['ID']}' $selected>" . htmlspecialchars($row['Nome']) . "</option>";
                        }
                        ?>
                    </select>

                    <input type="text" name="autore" id="autore" class="filter-input" placeholder="Autore..." value="<?= htmlspecialchars($_GET['autore'] ?? '') ?>" form="filtersForm">
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="onlyFile" id="files" onchange="handleFileCheckbox(this)">
                        <label for="files">Show All</label>
                    </div>
                </div>

                <div class="sidebar-item">
                    <label class="item-content">From Date (create)</label>
                    <input type="text" id="from_date" name="from_date" class="filter-input" value="<?= $_GET['from_date'] ?? '' ?>" form="filtersForm" placeholder="Select Date...">

                    <label class="item-content">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="filter-input" value="<?= $_GET['to_date'] ?? '' ?>" form="filtersForm" placeholder="Select Date...">
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
                <a href="logout.php" class="logout-link">
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
            $types = "";

            if (!empty($searchTerm)) {
                $query .= " AND (F.Original_filename LIKE CONCAT('%', ?, '%') OR U.Username LIKE CONCAT('%', ?, '%'))";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= "ss";
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
                $query .= " AND F.Created_at >= ?";
                $params[] = $from_date;
                $types .= "s";
            }

            if (!empty($to_date)) {
                $query .= " AND F.Created_at <= ?";
                $params[] = $to_date;
                $types .= "s";
            }

            $order_by_column = $sort_by === 'title' ? 'Original_filename' : 'Created_at';
            $order_dir = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
            $query .= " ORDER BY $order_by_column $order_dir";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $files = [];
            while ($file = $result->fetch_assoc()) {
                $files[] = $file;
            }

            if (!empty($files)): ?>
                <h2>Files</h2>
                <div id="file-preview" class="file-preview">
                    <?php foreach ($files as $file):
                        $filePath = "../../uploads/" . $file['Stored_filename'];
                        $fileName = htmlspecialchars($file['Original_filename']);
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                        $isPDF = strtolower($ext) === 'pdf';
                        $isTxt = strtolower($ext) === 'txt';
                    ?>
                    <div class="file-container">
                        <?php if ($isImage): ?>
                            <img class="preview-image" src="<?php echo $filePath; ?>" onclick="openModal('<?php echo $filePath; ?>', 'image')">
                        <?php elseif ($isPDF): ?>
                            <p><?php echo $fileName; ?></p>
                            <canvas class="pdf-preview" data-pdf="<?php echo $filePath; ?>"></canvas>
                        <?php elseif ($isTxt): ?>
                            <div class="file-icon" onclick="openText('<?php echo $filePath; ?>')"><?php echo $fileName; ?></div>
                        <?php else: ?>
                            <div class="file-icon"><?php echo $fileName; ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Modal -->
            <div id="modal-viewer" class="modal-viewer" style="display:none;" onclick="closeModal()">
                <div class="modal-content" onclick="event.stopPropagation();">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <div id="modal-body"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="../../js/FileVisualization.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/it.js"></script>
    <script src="../js/Flatpickr.js"></script>

    <script>
        function handleFileCheckbox(checkbox) {
            if (checkbox.checked) {
                window.location.href = '../home.php';
            }
        }
    </script>
</body>
</html>

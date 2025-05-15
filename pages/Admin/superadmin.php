<?php
    include "../../includes/db.php";
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../css/admin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator</title>
</head>
<body>
    <div class="header">
        <button class="floating-button" onclick="location.href='../home.php'">Home</button>
        <h1>Users</h1>
        <button class="delete-button" id="deleteBtn">Delete User/s</button>
            <!-- Error/Success Message-->
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="success-message">Utente/i eliminati con successo.</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="error-message">Error</div>
            <?php endif; ?>
    </div>

    <form id="deleteForm" action="delete_users.php" method="POST">
        <table id="usersTable">
            <thead>
                <tr>
                    <!-- La colonna checkbox sarà aggiunta dinamicamente -->
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Creation Date</th>
                    <th>Last Update</th>
                </tr>
            </thead>
            <tbody>
            <?php
                try {
                    $stmt = $conn->query("SELECT * FROM Users");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr data-user-type='{$row['Tipo']}' data-user-id='{$row['ID']}'>
                            <td>{$row['ID']}</td>
                            <td>{$row['Username']}</td>
                            <td>{$row['Nome']}</td>
                            <td>{$row['Cognome']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['Tipo']}</td>
                            <td>{$row['Created_At']}</td>
                            <td>{$row['Updated_At']}</td>
                        </tr>";
                    }
                } catch (PDOException $e) {
                    die("Errore nel recupero degli utenti: " . $e->getMessage());
                }
            ?>
            </tbody>
        </table>
    </form>

    <!-- Script Delete Btn (checkbox visual & double click to delete)-->
    <script src="../../js/DeleteBtn.js"></script>
</body>
</html>

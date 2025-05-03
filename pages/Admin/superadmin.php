<?php
    include "../../includes/db.php";
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link CSS General Structure --->
    <link rel="stylesheet" href="../../css/admin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Administrator </title>
</head>
<body>
    <div class="header">
        <button class="floating-button" onclick="location.href='../home.php'">Home</button>
        <h1> Users </h1>
    </div>
    <table>
        <tr>
            <th> ID </th>
            <th> Username </th>
            <th> Name </th>
            <th> Surname </th>
            <th> Email </th>
            <th> Type </th>
            <th> Creation Date </th>
            <th> Last Update </th>
        </tr>
        <?php
            try{
                $stmt = $conn->query("SELECT * FROM Users");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    echo "
                        <tr>
                            <td>{$row['ID']}</td>
                            <td>{$row['Username']}</td>
                            <td>{$row['Nome']}</td>
                            <td>{$row['Cognome']}</td>
                            <td>{$row['Email']}</td>
                            <td>{$row['Tipo']}</td>
                            <td>{$row['Created_at']}</td>
                            <td>{$row['Updated_at']}</td>
                        </tr>
                    ";
                }
            } catch(PDOException $e) {
                die("Errore nel recupero degli utenti: " . $e->getMessage());
            }
        ?>
    </table>
</body>
</html>
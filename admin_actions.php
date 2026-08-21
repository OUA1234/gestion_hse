<?php
session_start();
require_once("connexion.php");

// حماية الصفحة (Admin فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Admin") {
    header("Location: login.php");
    exit();
}

// جلب جميع الإجراءات مع عنوان التصريح
$sql = "SELECT ac.*, d.titre
        FROM actions_correctives ac
        INNER JOIN declarations d
        ON ac.declaration_id = d.id
        ORDER BY ac.created_at DESC";

$stmt = $pdo->query($sql);
$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Actions Correctives</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Suivi des actions correctives</h3>

</div>

<div class="card-body">

<table class="table table-bordered table-hover">

<thead class="table-success">

<tr>

<th>ID</th>
<th>Déclaration</th>
<th>Action corrective</th>
<th>Responsable</th>
<th>Date action</th>
<th>Statut</th>

</tr>

</thead>

<tbody>

<?php foreach($actions as $a){ ?>

<tr>

<td><?= $a["id"] ?></td>

<td><?= htmlspecialchars($a["titre"]) ?></td>

<td><?= htmlspecialchars($a["action"]) ?></td>

<td><?= htmlspecialchars($a["responsable"]) ?></td>

<td><?= $a["date_action"] ?></td>

<td>

<?php

if($a["statut"]=="En cours"){
    echo "<span class='badge bg-warning text-dark'>En cours</span>";
}
elseif($a["statut"]=="Terminé"){
    echo "<span class='badge bg-success'>Terminé</span>";
}
else{
    echo "<span class='badge bg-secondary'>".$a["statut"]."</span>";
}

?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<a href="admin_dashboard.php" class="btn btn-secondary">
Retour au Dashboard
</a>

</div>

</div>

</div>

</body>
</html>
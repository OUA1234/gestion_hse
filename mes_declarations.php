<?php
session_start();
require_once("connexion.php");

// حماية الصفحة
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Employe") {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["id"];

// جلب تصريحات الموظف فقط
$sql = "SELECT *
        FROM declarations
        WHERE utilisateur_id = ?
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Mes Déclarations</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Mes Déclarations HSE</h3>

</div>

<div class="card-body">

<?php if(count($declarations)==0){ ?>

<div class="alert alert-info">
Vous n'avez envoyé aucune déclaration.
</div>

<?php } else { ?>

<table class="table table-bordered table-hover">

<thead class="table-success">

<tr>

<th>ID</th>
<th>Titre</th>
<th>Type</th>
<th>Gravité</th>
<th>Lieu</th>
<th>Statut</th>
<th>Date</th>
<th>Photo</th>

</tr>

</thead>

<tbody>

<?php foreach($declarations as $d){ ?>

<tr>

<td><?= $d["id"] ?></td>

<td><?= htmlspecialchars($d["titre"]) ?></td>

<td><?= htmlspecialchars($d["type"]) ?></td>

<td><?= htmlspecialchars($d["gravite"]) ?></td>

<td><?= htmlspecialchars($d["lieu"]) ?></td>

<td>

<?php

switch($d["statut"]){

case "Nouvelle":
echo "<span class='badge bg-danger'>Nouvelle</span>";
break;

case "En cours":
echo "<span class='badge bg-warning text-dark'>En cours</span>";
break;

case "Clôturée":
echo "<span class='badge bg-success'>Clôturée</span>";
break;

default:
echo "<span class='badge bg-secondary'>".$d["statut"]."</span>";

}

?>

</td>

<td><?= $d["created_at"] ?></td>

<td>

<?php if(!empty($d["photo"])){ ?>

<a href="uploads/<?= htmlspecialchars($d["photo"]) ?>"
target="_blank"
class="btn btn-sm btn-primary">

Voir

</a>

<?php } else { ?>

-

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<?php } ?>

<a href="employee_dashboard.php" class="btn btn-secondary">
Retour Dashboard
</a>

<a href="declaration_add.php" class="btn btn-success">
Nouvelle Déclaration
</a>

</div>

</div>

</div>

</body>

</html>
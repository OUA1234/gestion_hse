<?php
session_start();
require_once("connexion.php");

// حماية الصفحة (Responsable فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Responsable") {
    header("Location: login.php");
    exit();
}

$message = "";

/*====================================
=        Ajouter une action
====================================*/

if (isset($_POST["ajouter"])) {

    $declaration_id = $_POST["declaration_id"];
    $action = trim($_POST["action"]);
    $assigned_to = $_POST["assigned_to"];
    $date_action = $_POST["date_action"];

    // اسم مسؤول HSE الذي أنشأ المهمة
    $responsable = $_SESSION["nom"] . " " . $_SESSION["prenom"];

    // حفظ الإجراء
    $sql = "INSERT INTO actions_correctives
    (declaration_id, action, assigned_to, responsable, date_action, statut)
    VALUES (?, ?, ?, ?, ?, 'En cours')";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $declaration_id,
        $action,
        $assigned_to,
        $responsable,
        $date_action
    ]);

    // إنشاء إشعار للموظف
    $notification = "Vous avez une nouvelle action corrective à réaliser.";

    $sql = "INSERT INTO notifications
(utilisateur_id, declaration_id, message, lu)
VALUES (?, ?, ?, 0)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $assigned_to,
        $declaration_id,
        $notification
    ]);

    $message = "Action corrective ajoutée avec succès.";
}

/*====================================
=      Liste des déclarations
====================================*/

$sql = "SELECT id, titre
        FROM declarations
        ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*====================================
=      Liste des employés
====================================*/

$sql = "SELECT id, nom, prenom
        FROM utilisateurs
        WHERE role='Employe'
        ORDER BY nom";

$stmt = $pdo->query($sql);
$employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*====================================
=      Liste des actions
====================================*/

$sql = "SELECT
a.*,
d.titre,
u.nom,
u.prenom

FROM actions_correctives a

INNER JOIN declarations d
ON a.declaration_id = d.id

INNER JOIN utilisateurs u
ON a.assigned_to = u.id

ORDER BY a.created_at DESC";

$stmt = $pdo->query($sql);

$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Actions Correctives HSE</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>
<i class="fa-solid fa-screwdriver-wrench"></i>
Gestion des actions correctives
</h3>

</div>

<div class="card-body">

<?php if($message!=""){ ?>

<div class="alert alert-success">

<?= $message ?>

</div>

<?php } ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Déclaration

</label>

<select
name="declaration_id"
class="form-select"
required>

<?php foreach($declarations as $d){ ?>

<option value="<?= $d["id"] ?>">

<?= $d["id"] ?> - <?= htmlspecialchars($d["titre"]) ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Employé chargé

</label>

<select
name="assigned_to"
class="form-select"
required>

<?php foreach($employes as $e){ ?>

<option value="<?= $e["id"] ?>">

<?= htmlspecialchars($e["nom"]." ".$e["prenom"]) ?>

</option>

<?php } ?>

</select>

</div>

</div>

<div class="mb-3">

<label class="form-label">

Action corrective

</label>

<textarea
name="action"
class="form-control"
rows="4"
required></textarea>

</div>

<div class="mb-3">

<label class="form-label">

Date prévue

</label>

<input
type="date"
name="date_action"
class="form-control"
required>

</div>

<button
type="submit"
name="ajouter"
class="btn btn-success">

<i class="fa-solid fa-plus"></i>

Ajouter

</button>

<a href="responsable_dashboard.php"
class="btn btn-secondary">

Retour

</a>

</form>

<hr class="my-4">

<h4>

Liste des actions correctives

</h4>
<table class="table table-bordered table-hover mt-3">

<thead class="table-primary">

<tr>

<th>ID</th>
<th>Déclaration</th>
<th>Action corrective</th>
<th>Employé chargé</th>
<th>Responsable HSE</th>
<th>Date</th>
<th>Statut</th>

</tr>

</thead>

<tbody>

<?php foreach($actions as $a){ ?>

<tr>

<td><?= $a["id"] ?></td>

<td><?= htmlspecialchars($a["titre"]) ?></td>

<td><?= htmlspecialchars($a["action"]) ?></td>

<td><?= htmlspecialchars($a["nom"]." ".$a["prenom"]) ?></td>

<td><?= htmlspecialchars($a["responsable"]) ?></td>

<td><?= htmlspecialchars($a["date_action"]) ?></td>

<td>

<?php

if($a["statut"]=="En cours"){
    echo "<span class='badge bg-warning text-dark'>En cours</span>";
}
elseif($a["statut"]=="Terminé"){
    echo "<span class='badge bg-success'>Terminé</span>";
}
else{
    echo "<span class='badge bg-secondary'>".htmlspecialchars($a["statut"])."</span>";
}

?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>
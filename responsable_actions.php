<?php

session_start();

require_once("connexion.php");


// حماية Responsable

if(!isset($_SESSION["id"]) || $_SESSION["role"]!="Responsable"){

    header("Location: login.php");
    exit();

}


// جلب Actions

$sql = "

SELECT 
actions_correctives.*,
utilisateurs.nom,
utilisateurs.prenom

FROM actions_correctives

JOIN utilisateurs

ON actions_correctives.assigned_to = utilisateurs.id

ORDER BY actions_correctives.created_at DESC

";


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


<h2 class="mb-4">

Actions Correctives

</h2>



<div class="card p-4">


<table class="table table-bordered table-striped">


<thead class="table-primary">


<tr>

<th>Action</th>

<th>Responsable</th>

<th>Employé</th>

<th>Statut</th>

<th>Commentaire</th>

<th>Photo</th>

<th>Rapport</th>

</tr>


</thead>



<tbody>


<?php foreach($actions as $a){ ?>


<tr>


<td>

<?= htmlspecialchars($a["action"]) ?>

</td>



<td>

<?= htmlspecialchars($a["responsable"]) ?>

</td>



<td>

<?= $a["nom"]." ".$a["prenom"] ?>

</td>



<td>

<?php if($a["statut"]=="Terminé"){ ?>

<span class="badge bg-success">
Terminé
</span>

<?php }else{ ?>

<span class="badge bg-warning">
<?= $a["statut"] ?>
</span>

<?php } ?>

</td>



<td>

<?= htmlspecialchars($a["commentaire_fin"]) ?>

</td>



<td>


<?php if($a["photo_fin"]){ ?>


<a href="uploads/<?= $a["photo_fin"] ?>" target="_blank">

Voir image

</a>


<?php }else{ ?>

-

<?php } ?>


</td>




<td>


<?php if($a["rapport_fin"]){ ?>


<a href="uploads/<?= $a["rapport_fin"] ?>" download>

Télécharger

</a>


<?php }else{ ?>

-

<?php } ?>


</td>



</tr>


<?php } ?>


</tbody>


</table>


</div>



<a href="responsable_dashboard.php"

class="btn btn-secondary mt-3">

Retour Dashboard

</a>



</div>



</body>

</html>
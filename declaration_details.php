<?php

session_start();
require_once("connexion.php");


// حماية Responsable فقط
if(!isset($_SESSION["id"]) || $_SESSION["role"] != "Responsable"){
    header("Location: login.php");
    exit();
}


// vérifier id
if(!isset($_GET["id"])){
    die("ID manquant");
}


$id = intval($_GET["id"]);


// récupérer déclaration + utilisateur

$sql = "
SELECT 
declarations.*,
utilisateurs.nom,
utilisateurs.prenom

FROM declarations

JOIN utilisateurs

ON declarations.utilisateur_id = utilisateurs.id

WHERE declarations.id = ?
";


$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$declaration = $stmt->fetch(PDO::FETCH_ASSOC);


if(!$declaration){

    die("Déclaration introuvable");

}

?>


<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Détails déclaration</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<style>

body{
background:#f4f6f9;
}

.card{

border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.1);

}


.photo{

max-width:400px;
border-radius:10px;

}

</style>


</head>


<body>


<div class="container mt-5">


<div class="card p-4">


<h2 class="mb-4">
📄 Détails de la déclaration
</h2>



<table class="table table-bordered">


<tr>

<th>ID</th>

<td>
<?= $declaration["id"] ?>
</td>

</tr>



<tr>

<th>Titre</th>

<td>
<?= htmlspecialchars($declaration["titre"]) ?>
</td>

</tr>



<tr>

<th>Description</th>

<td>
<?= nl2br(htmlspecialchars($declaration["description"])) ?>
</td>

</tr>



<tr>

<th>Type</th>

<td>
<?= htmlspecialchars($declaration["type"]) ?>
</td>

</tr>



<tr>

<th>Gravité</th>

<td>
<?= htmlspecialchars($declaration["gravite"]) ?>
</td>

</tr>



<tr>

<th>Lieu</th>

<td>
<?= htmlspecialchars($declaration["lieu"]) ?>
</td>

</tr>



<tr>

<th>Déclarant</th>

<td>
<?= $declaration["nom"]." ".$declaration["prenom"] ?>
</td>

</tr>



<tr>

<th>Statut actuel</th>

<td>

<span class="badge bg-warning">

<?= $declaration["statut"] ?>

</span>

</td>

</tr>



<tr>

<th>Date</th>

<td>
<?= $declaration["created_at"] ?>
</td>

</tr>



<tr>

<th>Photo</th>

<td>


<?php if(!empty($declaration["photo"])){ ?>


<img 
src="uploads/<?= $declaration["photo"] ?>"
class="photo">


<?php }else{ ?>

Aucune photo

<?php } ?>


</td>

</tr>



</table>

<form action="update_statut.php" method="POST">

<input type="hidden" name="id" value="<?= $declaration['id'] ?>">


<label class="form-label">
Changer le statut
</label>


<select name="statut" class="form-select">


<option value="Nouvelle">
Nouvelle
</option>


<option value="En cours">
En cours
</option>


<option value="Clôturée">
Clôturée
</option>


</select>


<button class="btn btn-success mt-3">

Mettre à jour

</button>


</form>

<a href="responsable_dashboard.php"
class="btn btn-secondary">

← Retour Dashboard

</a>


</div>


</div>


</body>

</html>
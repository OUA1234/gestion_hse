<?php

session_start();
require_once("connexion.php");


// حماية الصفحة (Admin فقط)
if(!isset($_SESSION["id"]) || 
($_SESSION["role"] != "Admin" && $_SESSION["role"] != "Responsable")){

    header("Location: login.php");
    exit();

}


// تحديث حالة التصريح
if(isset($_POST["update_statut"])){

    $id = $_POST["id"];
    $statut = $_POST["statut"];


    $sql = "UPDATE declarations 
            SET statut = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $statut,
        $id
    ]);

}


// جلب التصريحات

$sql = "
SELECT declarations.*, 
utilisateurs.nom,
utilisateurs.prenom

FROM declarations

JOIN utilisateurs

ON declarations.utilisateur_id = utilisateurs.id

ORDER BY declarations.created_at DESC
";


$stmt = $pdo->query($sql);

$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>


<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Déclarations HSE</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>


<body class="bg-light">


<div class="container mt-5">


<div class="card shadow p-4">


<h2 class="mb-4">
Gestion des déclarations HSE
</h2>



<table class="table table-bordered table-striped">


<thead class="table-success">

<tr>

<th>ID</th>

<th>Titre</th>

<th>Type</th>

<th>Gravité</th>

<th>Lieu</th>

<th>Déclarant</th>

<th>Photo</th>

<th>Statut</th>

<th>Action</th>


</tr>

</thead>


<tbody>


<?php foreach($declarations as $d){ ?>


<tr>


<td>
<?= $d["id"] ?>
</td>


<td>
<?= $d["titre"] ?>
</td>


<td>
<?= $d["type"] ?>
</td>


<td>
<?= $d["gravite"] ?>
</td>


<td>
<?= $d["lieu"] ?>
</td>


<td>
<?= $d["nom"]." ".$d["prenom"] ?>
</td>



<td>

<?php if($d["photo"]){ ?>

<a href="uploads/<?= $d["photo"] ?>" target="_blank">

Voir

</a>

<?php }else{ ?>

-

<?php } ?>

</td>




<td>

<form method="POST">


<input type="hidden" 
name="id"
value="<?= $d["id"] ?>">



<select name="statut" class="form-select">


<option <?= $d["statut"]=="Nouvelle"?"selected":"" ?>>
Nouvelle
</option>


<option <?= $d["statut"]=="En cours"?"selected":"" ?>>
En cours
</option>


<option <?= $d["statut"]=="Clôturée"?"selected":"" ?>>
Clôturée
</option>


</select>


</td>



<td>

<button 
name="update_statut"
class="btn btn-success">

Modifier

</button>


</form>


</td>


</tr>


<?php } ?>


</tbody>


</table>


<a href="admin_dashboard.php" class="btn btn-secondary">
Retour Dashboard
</a>


</div>


</div>


</body>

</html>
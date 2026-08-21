<?php

session_start();
require_once("connexion.php");


// حماية Admin فقط
if(!isset($_SESSION["id"]) || $_SESSION["role"] != "Admin"){
    header("Location: login.php");
    exit();
}



$message = "";


// إضافة مستخدم

if(isset($_POST["ajouter"])){

    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];


    // تشفير كلمة المرور
    $password_hash = password_hash($password, PASSWORD_DEFAULT);



    $sql = "INSERT INTO utilisateurs
    (nom, prenom, email, password, role)

    VALUES (?, ?, ?, ?, ?)";


    $stmt = $pdo->prepare($sql);


    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $password_hash,
        $role
    ]);


    $message = "Utilisateur ajouté avec succès.";

}



// جلب المستخدمين

$sql = "SELECT * FROM utilisateurs ORDER BY created_at DESC";

$stmt = $pdo->query($sql);

$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>


<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Gestion utilisateurs</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>



<body class="bg-light">


<div class="container mt-5">


<div class="card shadow p-4">


<h2>
Gestion des utilisateurs
</h2>



<?php if($message!=""){ ?>

<div class="alert alert-success">

<?= $message ?>

</div>

<?php } ?>



<h4 class="mt-4">
Ajouter un utilisateur
</h4>



<form method="POST">



<div class="row">


<div class="col-md-6 mb-3">

<label>
Nom
</label>

<input 
type="text"
name="nom"
class="form-control"
required>

</div>



<div class="col-md-6 mb-3">

<label>
Prénom
</label>

<input 
type="text"
name="prenom"
class="form-control"
required>

</div>



</div>




<div class="mb-3">

<label>
Email
</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>




<div class="mb-3">

<label>
Mot de passe
</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>




<div class="mb-3">

<label>
Role
</label>


<select name="role" class="form-select">


<option value="Admin">
Admin
</option>


<option value="Responsable">
Responsable
</option>


<option value="Employe">
Employe
</option>


</select>


</div>



<button 
class="btn btn-success"
name="ajouter">

Ajouter

</button>



</form>




<hr>



<h4>
Liste des utilisateurs
</h4>



<table class="table table-bordered table-striped">


<thead class="table-success">


<tr>

<th>ID</th>
<th>Nom</th>
<th>Prénom</th>
<th>Email</th>
<th>Role</th>
<th>Date création</th>

</tr>


</thead>



<tbody>



<?php foreach($utilisateurs as $u){ ?>


<tr>


<td>
<?= $u["id"] ?>
</td>


<td>
<?= $u["nom"] ?>
</td>


<td>
<?= $u["prenom"] ?>
</td>


<td>
<?= $u["email"] ?>
</td>


<td>
<?= $u["role"] ?>
</td>


<td>
<?= $u["created_at"] ?>
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
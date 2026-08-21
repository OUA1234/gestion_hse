<?php
session_start();
require_once("connexion.php");

// حماية الصفحة (Employe فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Employe") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titre = trim($_POST["titre"]);
    $description = trim($_POST["description"]);
    $type = $_POST["type"];
    $gravite = $_POST["gravite"];
    $lieu = trim($_POST["lieu"]);

    $photo = "";

    // رفع الصورة (اختياري)
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {

        $extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));

        $extensions_autorisees = ["jpg","jpeg","png","gif","webp"];

        if (in_array($extension, $extensions_autorisees)) {

            $photo = time() . "_" . basename($_FILES["photo"]["name"]);

            move_uploaded_file(
                $_FILES["photo"]["tmp_name"],
                "uploads/" . $photo
            );
        }
    }

    $sql = "INSERT INTO declarations
    (titre, description, type, gravite, lieu, photo, statut, utilisateur_id)

    VALUES (?, ?, ?, ?, ?, ?, 'Nouvelle', ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titre,
        $description,
        $type,
        $gravite,
        $lieu,
        $photo,
        $_SESSION["id"]
    ]);


    // récupérer l'id de la déclaration ajoutée
$declaration_id = $pdo->lastInsertId();

// chercher tous les responsables
$sql = "SELECT id FROM utilisateurs WHERE role = 'Responsable'";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// créer une notification pour chaque responsable
$message_notification = "Nouvelle déclaration : " . $titre;

foreach ($responsables as $responsable) {

    $sqlNotif = "INSERT INTO notifications
    (utilisateur_id, declaration_id, message, lu)
    VALUES (?, ?, ?, 0)";

    $stmtNotif = $pdo->prepare($sqlNotif);

    $stmtNotif->execute([
        $responsable['id'],
        $declaration_id,
        $message_notification
    ]);
}

    $message = "Déclaration envoyée avec succès.";

}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Nouvelle Déclaration</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>Nouvelle Déclaration HSE</h3>

</div>

<div class="card-body">

<?php if($message!=""){ ?>

<div class="alert alert-success">

<?= $message ?>

</div>

<?php } ?>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">

<label class="form-label">
Titre
</label>

<input
type="text"
name="titre"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">
Description
</label>

<textarea
name="description"
class="form-control"
rows="5"
required></textarea>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Type
</label>

<select
name="type"
class="form-select"
required>

<option value="Accident">Accident</option>

<option value="Incident">Incident</option>

<option value="Situation dangereuse">Situation dangereuse</option>

<option value="Presqu'accident">Presqu'accident</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Gravité
</label>

<select
name="gravite"
class="form-select"
required>

<option value="Faible">Faible</option>

<option value="Moyenne">Moyenne</option>

<option value="Grave">Grave</option>

</select>

</div>

</div>

<div class="mb-3">

<label class="form-label">
Lieu
</label>

<input
type="text"
name="lieu"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">
Photo (optionnelle)
</label>

<input
type="file"
name="photo"
class="form-control"
accept=".jpg,.jpeg,.png,.gif,.webp">

</div>

<button
class="btn btn-success"
type="submit">

Envoyer

</button>

<a href="employee_dashboard.php"
class="btn btn-secondary">

Retour

</a>

</form>

</div>

</div>

</div>

</body>

</html>
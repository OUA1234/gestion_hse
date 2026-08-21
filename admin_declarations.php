<?php
session_start();
require_once("connexion.php");

// حماية الصفحة (Admin فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Admin") {
    header("Location: login.php");
    exit();
}

// جلب جميع التصريحات مع اسم الموظف
$sql = "SELECT d.*, u.nom, u.prenom
        FROM declarations d
        INNER JOIN utilisateurs u ON d.utilisateur_id = u.id
        ORDER BY d.created_at DESC";

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

    <div class="card shadow">

        <div class="card-header bg-success text-white">
            <h3>Liste des déclarations HSE</h3>
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead class="table-success">

                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Gravité</th>
                    <th>Lieu</th>
                    <th>Statut</th>
                    <th>Déclarant</th>
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

                            if($d["statut"]=="Nouvelle"){
                                echo "<span class='badge bg-danger'>Nouvelle</span>";
                            }
                            elseif($d["statut"]=="En cours"){
                                echo "<span class='badge bg-warning text-dark'>En cours</span>";
                            }
                            else{
                                echo "<span class='badge bg-success'>Clôturée</span>";
                            }

                            ?>

                        </td>

                        <td><?= htmlspecialchars($d["nom"]." ".$d["prenom"]) ?></td>

                        <td><?= $d["created_at"] ?></td>

                        <td>

                            <?php if(!empty($d["photo"])) { ?>

                                <a href="uploads/<?= htmlspecialchars($d["photo"]) ?>" target="_blank" class="btn btn-primary btn-sm">
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

            <a href="admin_dashboard.php" class="btn btn-secondary">
                Retour au Dashboard
            </a>

        </div>

    </div>

</div>

</body>
</html>
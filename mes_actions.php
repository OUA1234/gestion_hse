<?php

session_start();

require_once("connexion.php");


// حماية Employé

if(!isset($_SESSION["id"]) || $_SESSION["role"] != "Employe"){

    header("Location: login.php");
    exit();

}


$id = $_SESSION["id"];

$message = "";


// إنشاء dossier uploads

if(!is_dir("uploads")){

    mkdir("uploads");

}



/*====================================
        Mise à jour Action
====================================*/

if(isset($_POST["modifier"])){


    $action_id = $_POST["action_id"];

    $statut = $_POST["statut"];

    $commentaire = $_POST["commentaire_fin"];



    $photo = null;

    $rapport = null;



    // Upload photo

    if(isset($_FILES["photo_fin"]) 
        && $_FILES["photo_fin"]["name"] != ""){


        $photo = time()."_".$_FILES["photo_fin"]["name"];


        move_uploaded_file(

            $_FILES["photo_fin"]["tmp_name"],

            "uploads/".$photo

        );

    }




    // Upload rapport

    if(isset($_FILES["rapport_fin"]) 
        && $_FILES["rapport_fin"]["name"] != ""){


        $rapport = time()."_".$_FILES["rapport_fin"]["name"];


        move_uploaded_file(

            $_FILES["rapport_fin"]["tmp_name"],

            "uploads/".$rapport

        );

    }




    // Mise à jour base de données

    $sql = "

    UPDATE actions_correctives SET

    statut=?,

    commentaire_fin=?,

    photo_fin=?,

    rapport_fin=?,

    date_fin=NOW()


    WHERE id=? AND assigned_to=?

    ";



    $stmt = $pdo->prepare($sql);



    $stmt->execute([

        $statut,

        $commentaire,

        $photo,

        $rapport,

        $action_id,

        $id

    ]);





    /*===============================
        Notification Responsable
    ===============================*/


    if($statut == "Terminé"){



        // Nom employé

        $sql = "
        SELECT nom, prenom
        FROM utilisateurs
        WHERE id=?
        ";


        $stmt = $pdo->prepare($sql);

        $stmt->execute([$id]);


        $employe = $stmt->fetch();





        // Responsable

        $sql = "
        SELECT id
        FROM utilisateurs
        WHERE role='Responsable'
        LIMIT 1
        ";


        $responsable = $pdo->query($sql)->fetch();




        if($responsable){


            $notif = 
            "L'employé "
            .$employe["nom"]." "
            .$employe["prenom"].
            " a terminé une action corrective.";





            $sql = "

            INSERT INTO notifications

            (
            utilisateur_id,
            declaration_id,
            message,
            lien,
            lu,
            created_at
            )


            VALUES (?,?,?,?,?,NOW())

            ";



            $stmt = $pdo->prepare($sql);



            $stmt->execute([

                $responsable["id"],

                $action_id,

                $notif,

                "responsable_actions.php",

                0

            ]);

        }


    }



    $message = "Action mise à jour avec succès.";

}







/*====================================
        Récupération Actions
====================================*/


$sql = "

SELECT *

FROM actions_correctives

WHERE assigned_to=?

ORDER BY created_at DESC

";



$stmt = $pdo->prepare($sql);


$stmt->execute([$id]);


$actions = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>



<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Mes Actions</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>



<body class="bg-light">



<div class="container mt-5">



<h2>
Mes Actions
</h2>




<?php if($message!=""){ ?>

<div class="alert alert-success">

<?= $message ?>

</div>

<?php } ?>




<table class="table table-bordered bg-white mt-4">


<thead class="table-primary">


<tr>

<th>Action</th>

<th>Responsable</th>

<th>Date</th>

<th>Statut</th>

<th>Mise à jour</th>


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

<?= $a["date_action"] ?>

</td>




<td>

<?= $a["statut"] ?>

</td>




<td>


<form method="POST" enctype="multipart/form-data">



<input type="hidden"

name="action_id"

value="<?= $a["id"] ?>">





<select name="statut" class="form-select mb-2">


<option value="En cours"

<?= $a["statut"]=="En cours"?"selected":"" ?>>

En cours

</option>



<option value="Terminé"

<?= $a["statut"]=="Terminé"?"selected":"" ?>>

Terminé

</option>


</select>






<textarea

name="commentaire_fin"

class="form-control mb-2"

placeholder="Décrivez l'intervention effectuée"></textarea>






<label>

Photo preuve

</label>


<input type="file"

name="photo_fin"

class="form-control mb-2"

accept="image/*">





<label>

Rapport

</label>


<input type="file"

name="rapport_fin"

class="form-control mb-2"

accept=".pdf,.doc,.docx">






<button

class="btn btn-success"

name="modifier">

Enregistrer

</button>



</form>



</td>



</tr>



<?php } ?>



</tbody>


</table>



<a href="employee_dashboard.php"

class="btn btn-secondary">

Retour Dashboard

</a>



</div>



</body>

</html>
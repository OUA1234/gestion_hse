<?php

session_start();
require_once("connexion.php");


// حماية Responsable
if(!isset($_SESSION["id"])){
    header("Location: login.php");
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST"){

    


    $id = intval($_POST["id"]);
    $statut = $_POST["statut"];



    // جلب صاحب التصريح
    $sql = "SELECT utilisateur_id, titre 
            FROM declarations 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $declaration = $stmt->fetch(PDO::FETCH_ASSOC);



    if($declaration){


        // تحديث الحالة
        $sql = "UPDATE declarations 
                SET statut = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $statut,
            $id
        ]);



        // إنشاء إشعار للموظف

        $message = "Votre déclaration '".$declaration["titre"]."' est maintenant : ".$statut;


        $sql = "
        INSERT INTO notifications
        (utilisateur_id, declaration_id, message, lu)

        VALUES (?, ?, ?, 0)
        ";


        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            $declaration["utilisateur_id"],
            $id,
            $message

        ]);



    }


}


header("Location: declaration_details.php?id=".$id);

exit();

?>
<?php
session_start();
require_once("connexion.php");


///if (isset($_SESSION["id"])) {
  //  header("Location: dashboard.php");
  //  exit();
//}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["reset_request"])) {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (!empty($email) && !empty($password)) {

        $sql = "SELECT * FROM utilisateurs WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 1) {

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user["password"])) {

                // إنشاء Session
                $_SESSION["id"] = $user["id"];
                $_SESSION["nom"] = $user["nom"];
                $_SESSION["prenom"] = $user["prenom"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                // توجيه حسب الدور
                if ($user["role"] == "Admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                } elseif ($user["role"] == "Responsable") {
                    header("Location: responsable_dashboard.php");
                    exit();
                } elseif ($user["role"] == "Employe") {
                    header("Location: employee_dashboard.php");
                    exit();
                } else {
                    // header("Location: dashboard.php");
                    exit();
                }

            } else {
                $message = "Mot de passe incorrect.";
            }

        } else {
            $message = "Email introuvable.";
        }

    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// معالجة طلب نسيت كلمة المرور
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_request"])) {
    $reset_email = trim($_POST["reset_email"]);
    if (!empty($reset_email)) {
        $message = "Si cet email existe, un lien de réinitialisation vous sera envoyé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Connexion - Portail HSE</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 20px;
}

.card {
    width: 100%;
    max-width: 420px;
    border: none;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #198754, #20c997);
}

.logo {
    font-size: 50px;
    color: #198754;
    background: rgba(25, 135, 84, 0.1);
    width: 80px;
    height: 80px;
    line-height: 80px;
    border-radius: 20px;
    margin: 0 auto;
}

.form-control {
    border-radius: 10px;
    padding: 12px 15px;
    border: 1px solid #cbd5e1;
}

.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15);
}

.btn-success {
    background-color: #198754;
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #146c43;
    transform: translateY(-2px);
}

.forgot-link {
    color: #198754;
    font-size: 0.85rem;
    text-decoration: none;
    font-weight: 600;
}

.forgot-link:hover {
    text-decoration: underline;
}

.modal-content {
    border-radius: 16px;
    border: none;
}
</style>

</head>

<body>

<div class="card p-4">

<div class="text-center mb-4">

    <div class="logo mb-3">
        <i class="fa-solid fa-shield-halved"></i>
    </div>

    <h3 class="fw-bold">Portail HSE</h3>

    <p class="text-muted small">
        Connectez-vous à votre compte
    </p>

</div>

<?php if($message!=""){ ?>

<div class="alert alert-info alert-dismissible fade show rounded-3 small">

<?php echo $message; ?>
<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label fw-semibold small text-secondary">

Email

</label>

<input
type="email"
name="email"
class="form-control"
placeholder="Entrer votre email"
required>

</div>

<div class="mb-3">

<div class="d-flex justify-content-between align-items-center mb-1">
    <label class="form-label fw-semibold small text-secondary mb-0">
    Mot de passe
    </label>

    <!-- زر نسيت كلمة المرور -->
    <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
        Mot de passe oublié ?
    </a>
</div>

<div class="input-group">

<input
type="password"
name="password"
id="password"
class="form-control"
placeholder="Entrer votre mot de passe"
required>

<button
class="btn btn-outline-secondary"
type="button"
onclick="togglePassword()"
style="border-radius: 0 10px 10px 0;">

<i class="fa-solid fa-eye" id="eyeIcon"></i>

</button>

</div>

</div>

<button
type="submit"
class="btn btn-success w-100 mt-2">

<i class="fa-solid fa-right-to-bracket me-1"></i>

Se connecter

</button>

</form>

</div>

<!-- نافذة نسيت كلمة المرور (Modal) -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-key me-2 text-success"></i>Réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body py-3">
                    <p class="text-muted small">Entrez votre adresse email pour recevoir les instructions de réinitialisation.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Votre Email</label>
                        <input type="email" name="reset_email" class="form-control" placeholder="exemple@domaine.com" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="reset_request" class="btn btn-success rounded-3 px-4">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

function togglePassword(){

let password=document.getElementById("password");

let eye=document.getElementById("eyeIcon");

if(password.type==="password"){

password.type="text";

eye.classList.remove("fa-eye");

eye.classList.add("fa-eye-slash");

}else{

password.type="password";

eye.classList.remove("fa-eye-slash");

eye.classList.add("fa-eye");

}

}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
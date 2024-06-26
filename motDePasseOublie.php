<?php
$titre = 'Récupération de mot de passe';
require_once("inc/header.inc.php");
require_once("php/user.php");
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
Use App\User;
Use App\UserRepository;
$courrielEstExistant = new UserRepository();
$user = new User();
$userRepository = new UserRepository();


if(isset($_POST['courriel'])) {
    $password = uniqid();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
}
$mail = new PHPMailer(true);

if (isset($_POST['buttsub'])) {
    if (!empty($_POST['courriel'])) {
        try {
            $courriel = htmlspecialchars($_POST['courriel']);

            //Vérifier si le courriel existe déjà dans la BDD
            $courrielEstExistant = $courrielEstExistant->verifyIfUserIsInDB($courriel);
            if($courrielEstExistant) {
                $user->setCourriel($courriel);
                $user->setMotpasse($hashedPassword);
                $userRepository->updatePasword($user);
                $mail->CharSet = 'UTF-8';
                $mail->setFrom("no-reply@nodebt.com");
                $mail->addAddress($courriel);  //placez VOTRE adresse courriel
                $mail->isHTML(false);
                $mail->Subject = "Réinitialisation de mot de passe ";
                $mail->Body = "Bonjour, \n\n "."Votre nouveau mot de passe est $password. \n\n Nous vous conseillons vivement de le modifier afin de garantir la sécurite de votre compte après authentification. \n\n Cordialement, \n\n NoDebt";
                $mail->send();
                header('Location: index.php');
            } else {
                $erreur = "Ce courriel n'est associé à aucun compte !";
            }

        }  catch (Exception $e) {
            $erreur = 'Erreur survenue lors de l\'envoi de l\'invitation<br>' . $mail->ErrorInfo;
        }

    } else {
        $erreur = "Merci de compléter votre courriel !";
    }
}
?>
<?php
require_once ("php/nav.php");
?>
<nav>
     <ul class="breadcrumb">
        <li><a href="index.php">Se connecter</a></li>
        <li>Récupération de mot de passe</li>
      </ul>
</nav>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
    <main class="formulaire">
      <section class="form">
        <h1>Récupération de mot de passe</h1>
      <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
        <section class="register" id="recuperationMotDePasse">
          <label for="courriel">Adresse e-mail:* </label><input id="courriel" name="courriel" type="email" autocomplete ="off" value="<?php if(isset($_POST['courriel'])) echo htmlspecialchars($_POST['courriel']) ?>" placeholder="Adresse e-mail" required>
          <input type="submit" name="buttsub" value="Envoyer">
        </section>
      </form>
        <a href="contact.php" title="Créer un compte"> Un problème technique ? Contactez-nous</a>
    </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
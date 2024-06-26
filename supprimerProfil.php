<?php
require_once("inc/db_link.inc.php");
$titre = 'Supprimer Profil';
include("inc/header.inc.php");
require_once ("php/session.php");
require_once("php/user.php");
require_once("php/participer.php");
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
$auth = new Session();
$auth->forceUserToLogin();
USE App\ParticiperRepository;
USE App\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$userRepo = new UserRepository();
$participerRepository = new ParticiperRepository();
$uidOfTheUser = $auth->getUid();
$nbParticipationParticipant = $participerRepository->NumberOfParticipantInGroupsForAnUser($uidOfTheUser);
if(isset($_POST['butsub'])) {
    if(!empty($_POST['nom']) AND !empty($_POST['prenom']) AND !empty($_POST['courriel'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $courriel = htmlspecialchars($_POST['courriel']);

        //Vérification qu'il ne participe plus à un groupe
        if($nbParticipationParticipant->nbparticipation == 0) {
            $userRepo->removeAnUser($uidOfTheUser);
            //Envoi du mail de confirmation
            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->setFrom("no-reply@nodebt.com");
                $mail->addAddress($courriel);  //placez VOTRE adresse courriel
                $mail->isHTML(false);
                $mail->Subject = "Confirmation de suprression de compte NoDebt !";
                $mail->Body = "Bonjour, \n\n " . "Ne nous quittez pas si vite !\n\n" . "Voici le lien vous permettant d'activer à nouveau votre compte sur notre plateforme NoDebt : http://192.168.128.13/~q210054/EVAL_V4/index.php?uid=$uidOfTheUser  \n\n Nous espérons vous revoir très vite. \n\n Cordialement, \n\n NoDebt";
                $mail->send();
                $danger = "Un courriel vous permettant de réactiver votre compte a été envoyé à votre adresse e-mail : $courriel ";
            } catch (Exception $e) {
                $erreur =  'Erreur survenue lors de l\'envoi de l\'email<br>' . $mail->ErrorInfo;
            }
            session_destroy();
            header('Location: index.php');
        } else {
            $erreur = "Impossible de supprimer votre compte. Vous participez toujours à des groupes";
        }

    }
}
if(isset($_POST['butsubDecline'])) {
    header('Location: index.php');
}
?>
<?php
require_once ("php/nav.php");
?>
    <nav>
        <ul class="breadcrumb">
            <li><a href="listeGroupes.php">Liste des groupes</a></li>
            <li>Supprimer profil</li>
        </ul>
    </nav>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
    <main class="formulaire">
        <section class="form" id="registerFormAndExpenseForm">
            <h1>Souhaitez-vous supprimer votre profil ?</h1>
            <form class="formulaireBase" action="supprimerProfil.php" method="post">
                <section class="register" id="register">
                    <label for="nom">Nom</label><input id="nom" name="nom" type="text" value="<?php echo $auth->getUserLastName()?>" readonly>
                    <label for="prenom">Prénom</label><input id="prenom" name="prenom" type="text" value="<?php echo $auth->getUserFirstName() ?>" readonly>
                    <label for="courriel">Adresse e-mail</label><input id="courriel" name="courriel" type="email" value="<?php echo $auth->getUserMail() ?>" readonly>
                    <button id="accept" class="boutonA" type="submit" name="butsub">Confirmer</button>
                    <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
                </section>
            </form>
        </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
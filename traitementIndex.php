<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require_once("php/user.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
USE App\UserRepository;
$courrielEstExistant = new UserRepository();
?>
<?php
session_start();
$bdd = \DB\DBLink::connect2db(MYDB, $message);
$userRepo = new UserRepository();
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    $oldUser = $userRepo->getUserById($uid);
    if($oldUser->isEstActif() == 0) {
        //Réactivation du compte
        $userRepo->updateAnOldUser($uid);
        $check = "Votre compte est de nouveau actif !";
    } else {
        $danger = "Ce compte est déjà actif";
    }
    header("Refresh: 5; url=index.php");
}
if(isset($_POST['buttsub'])) {
    if (!empty($_POST['courriel']) and !empty($_POST['motPasse'])) {
        $courriel = htmlspecialchars($_POST['courriel']);
        $motDePasse = htmlspecialchars($_POST['motPasse']);
        //Vérification que l'utilisateur se trouve dans la BDD
        $user = $userRepo->getUserByMail($courriel);
        $courrielEstExistant = $courrielEstExistant->verifyIfUserIsInDB($courriel);

        if ($courrielEstExistant) {
            if ($user->checkMotPasse($motDePasse) == true) {
                if ($user->isEstActif() == 1) {
                    $_SESSION['nom'] = $user->getNom();
                    $_SESSION['prenom'] = $user->getPrenom();
                    $_SESSION['courriel'] = $user->getCourriel();
                    $_SESSION['uid'] = $user->getId();
                    header("Location: listeGroupes.php");
                } elseif ($user->isEstActif() == 0) {
                    $mail = new PHPMailer(true);
                    $uidOfTheUser = $user->getId();
                    try {
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom("no-reply@nodebt.com");
                        $mail->addAddress($courriel);  //placez VOTRE adresse courriel
                        $mail->isHTML(false);
                        $mail->Subject = "Réactivation de votre compte NoDebt !";
                        $mail->Body = "Bonjour, \n\n " . "Voici le lien vous permettant d'activer à nouveau votre compte sur notre plateforme NoDebt : http://192.168.128.13/~q210054/EVAL_V4/index.php?uid=$uidOfTheUser  \n\n Nous vous souhaitons un agréable moment. \n\n Cordialement, \n\n NoDebt";
                        $mail->send();
                        $danger = "Un courriel vous permettant de réactiver votre compte a été envoyé à votre adresse e-mail : $courriel ";
                    } catch (Exception $e) {
                        echo 'Erreur survenue lors de l\'envoi de l\'email<br>' . $mail->ErrorInfo;
                    }
                    header("Refresh: 10; url=index.php");
                }
            } else {
                $erreur = "Votre mot de passe est incorrect !";
            }

        } else {
            $erreur = "Aucun compte n'est associé à ce courriel !";
        }
    } else {
        $erreur = "Veuillez compléter tous les champs !";
    }
}

<?php
$titre = 'Contact';
require_once("inc/header.inc.php");
require_once ("php/session.php");
require_once("php/user.php");



USE App\UserRepository;
$auth = new Session();

if(isset($_SESSION['uid'])) {
    $userRepo = new UserRepository();
    $user = $userRepo->getUserById($_SESSION['uid']);
}

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

    try {
        if (isset($_POST['butsub'])) {
            if (!empty($_POST['courriel']) && !empty($_POST['intitule']) && !empty($_POST['message'])) {
                $courriel = htmlspecialchars($_POST['courriel']);
                $subject = htmlspecialchars($_POST['intitule']);
                $message = htmlspecialchars($_POST['message']);

                $mail->CharSet = 'UTF-8';
                $mail->setFrom($courriel);
                $mail->addAddress('e.devlegelaer@student.helmo.be');  //placez VOTRE adresse courriel
                $mail->addCC($courriel);
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->send();
                $check =  "Courriel envoyé";
            }
        }
    } catch (Exception $e) {
        $erreur =  'Erreur survenue lors de l\'envoi de l\'email<br>' . $mail->ErrorInfo;
    }



?>
<?php
require_once("php/nav.php")
?>
<nav>
        <ul class="breadcrumb">
            <li><a href="index.php">Accueil</a></li>
            <li>Contact</li>
        </ul>
    </nav>
<?php if(isset($check)): ?>
    <span class="notification" id="notificationCheck"><?= $check ?></span>
<?php endif; ?>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
    <main class="formulaire">
      <section class="form" id="contactForm">
        <h1>Nous contacter !</h1>
        <form class="formulaireBase" action="contact.php" method="post">
            <section class="contactFormAndExpenseForm">
                <?php if($auth->isLogged()):
                ?>
            <label for="courriel">Adresse e-mail*</label><input id="courriel" name="courriel" type="email"  value="<?php echo $auth->getUserMail() ?>" autocomplete ="off" required>
            <?php else: ?>
            <label for="courriel">Adresse e-mail*</label><input id="courriel" name="courriel" type="email"  placeholder="John.doe@gmail.com" autocomplete ="off" required>
            <?php endif; ?>
            <label for="intitule">Intitulé*</label><input id="intitule" name="intitule" type="text"  autocomplete ="off" required>
            <label class="message" for="message">Message*</label><textarea id="message" name="message" rows="15" cols="60" placeholder="Ecrivez-ici..." required></textarea>
            <input id="declineContact" class="refuser" type="reset" name="reset" value="Réinitialiser">
            <input id="acceptContact" class="accepter" type="submit" name="butsub" value="Envoyer">
            </section>
        </form>
      </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
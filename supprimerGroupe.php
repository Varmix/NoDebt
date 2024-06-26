<?php
$titre = 'Supprimer groupe';
include("inc/header.inc.php");
require_once("php/groupe.php");
require_once ("php/session.php");
require_once("php/user.php");
require_once("php/participer.php");
USE App\User;
USE App\UserRepository;
USE App\Groupe;
USE App\GroupRepository;
USE App\Participer;
USE App\ParticiperRepository;
$auth = new Session();
$groupeRepository = new GroupRepository();

$participantInformation = new ParticiperRepository();
$participant = $participantInformation->VerifyIfUserIsAnParticipant($auth->getUid(), $_GET['id']);
$gid = $_GET['id'];
if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}
$idGroup = $groupeRepository->idOfCreator($gid);
if(($idGroup['idcreateur'] != $auth->getUid())) {
    header('Location: listeGroupes.php');
}

if($participant->numdugroupe != $_GET['id'] && $participant->numueroduparticipant != $auth->getUid()) {
    header('Location: listeGroupes.php');
}

$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);

$selected = $nameOfTheGroup['currency'];
$options = array('Euro (€)', 'Dollar américain ($)',  'Livre sterling(£)', 'Franc suisse (CHF)', 'Yen japonais (¥)', 'Dollar australien ($)', 'Dollar canadien ($)');
if(isset($_POST['butsub'])) {

    if(!empty($_POST['nomDuGroupe'])) {
        $nomDuGroupe = htmlspecialchars($_POST['nomDuGroupe']);

            //Supprimer les participants à ce groupe
            $participerRepository = new ParticiperRepository();
            $participerRepository->DeleteAllParticipantsOfAGroup($gid);

            //Supprime le groupe en BDD
            $deleteGroupe = new GroupRepository();
            $deleteGroupe->deleteAGroup($gid);
            header('Location: listeGroupes.php');

    } else {
        $erreur =  "Impossible de supprimer le groupe, la devise indiquée n'est pas prise en charge.";
    }

}

if(isset($_POST['butsubDecline'])) {
    header("Location: consulterGroupe.php?id=$gid");
}
?>
<?php
require_once ("php/nav.php");
?>
    <nav>
        <ul class="breadcrumb">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="listeGroupes.php">Liste des Groupes</a></li>
            <li><a href="consulterGroupe.php?id=<?= $gid ?>">Groupe : <?= $nameOfTheGroup['name'] ?></a></li>
            <li>Supprimer groupe</li>
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
        <section class="form" id="groupForm">
            <h1>Supprimer un groupe</h1>
            <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
                <section class="groupFormSection">
                    <label for="nomDuGroupe">Nom du groupe*</label><input id="nomDuGroupe" name="nomDuGroupe" type="text" value="<?php echo $nameOfTheGroup['name'] ?>" readonly required>
                    <label for="devise">Devise*</label>
                    <select id="devise" name="devise" disabled>
                        <?php
                        foreach($options as $option) {
                            if($selected == $option) {
                                echo "<option selected='selected' value='$option'>$option</option>";
                            } else {
                                echo "<option value='$option'>$option</option>";
                            }
                        }
                        ?>
                    </select>
                    <button id="accept" class="boutonA" type="submit" name="butsub">Confirmer</button>
                    <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
                </section>
            </form>
        </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
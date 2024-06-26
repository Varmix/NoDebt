<?php
$titre = 'Modifier groupe';
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

if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}

if($participant->numdugroupe != $_GET['id'] && $participant->numueroduparticipant != $auth->getUid()) {
    header('Location: listeGroupes.php');
}
$gid = $_GET['id'];
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);

$selected = $nameOfTheGroup['currency'];
$options = array('Euro (€)', 'Dollar américain ($)',  'Livre sterling(£)', 'Franc suisse (CHF)', 'Yen japonais (¥)', 'Dollar australien ($)', 'Dollar canadien ($)');
if(isset($_POST['butsub'])) {

    if(!empty($_POST['nomDuGroupe']) AND !empty($_POST['devise'])) {
        $nomDuGroupe = htmlspecialchars($_POST['nomDuGroupe']);
        $devise = htmlspecialchars($_POST['devise']);

        //Vérifier que la devise se trouve bien dans le tableau
        if(in_array($devise, $options)) {
            $groupe = new Groupe();
            $groupe->setNomGroupe($nomDuGroupe);
            $groupe->setDevise($devise);
            $groupe->setGid($gid);

            //Insertion du groupe en BDD
            $updateGroupe = new GroupRepository();
            $updateGroupe->updateAGroup($groupe);

            header("Location: consulterGroupe.php?id=$gid");

        }

    } else {
        $erreur = "Impossible de modifier le groupe, la devise indiquée n'est pas prise en charge.";
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
            <li>Modifier groupe</li>
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
            <h1>Modifier un groupe</h1>
            <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
                <section class="groupFormSection">
                    <label for="nomDuGroupe">Nom du groupe*</label><input id="nomDuGroupe" name="nomDuGroupe" type="text" value="<?php echo $nameOfTheGroup['name'] ?>" required>
                    <label for="devise">Devise*</label>
                    <select id="devise" name="devise">
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
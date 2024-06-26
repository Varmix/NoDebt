<?php
$titre = 'Modifier dépense';
require_once("inc/header.inc.php");
//Définir le nouveau fuseau horaire
date_default_timezone_set('Europe/Paris');
require_once("php/depense.php");
require_once ("php/session.php");
require_once("php/participer.php");
require_once("php/tag.php");
require_once("php/groupe.php");
USE App\Depense;
USE App\DepenseRepository;
USE App\Participer;
USE APP\ParticiperRepository;
USE App\Tag;
USE App\TagRepository;
USE App\GroupRepository;
$auth = new Session();
$depense = new Depense();
$depenseRepository = new DepenseRepository();
$tagObj = new Tag();
$tagRepository = new TagRepository();
$groupeRepository = new GroupRepository();
$gid = $_GET['gid'];
$did = $_GET['did'];
$participantInformation = new ParticiperRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);


//Vérification que l'utilisateur authentifié se trouve dans le groupe
$currentParticipant = $participantInformation->VerifyIfUserIsAnParticipant($auth->getUid(), $_GET['gid']);

//Récupérer le nom et prénom de l'utilisateur actuel
$currentUser = $auth->getUserFirstName() . ' ' . $auth->getUserLastName();

//Liste des participants
$listOfParticipants = $participantInformation->getUsersOfAGroups($gid);
if(!($currentParticipant->numdugroupe == $_GET['gid'] && $currentParticipant->numueroduparticipant == $auth->getUid())) {
    header('Location: listeGroupes.php');
}

//Vérifier si la dépense appartient bien au groupe
$depenseInformation = new DepenseRepository();
$depenseActuelle = $depenseInformation->VerifyIfAnExpenseBelongsTheGroup($gid, $did);

if(!($depenseActuelle->numdugroupedep == $gid && $depenseActuelle->numdepense == $did)) {
    header('Location: listeGroupes.php');
}


$expense = $depenseRepository->InformationAboutAnExpenese($did);
$nameTag = $depenseRepository->getNameOfATag($did);
$nbTag = $tagRepository->TagLinkedAnExpenseOrNot($did);
$date = $expense->date;
$date = date ('Y-m-d\TH:i:s', strtotime($date));
if(isset($_POST['butsub'])) {

    if(!empty($_POST['libelle']) AND !empty($_POST['tags']) AND !empty($_POST['montant'])
        AND !empty($_POST['date']) AND !empty($_POST['payePar']) ) {


        $date = htmlspecialchars($_POST['date']);
        $debutSubstrDate = substr($date, 0, 10);
        $finSubstrDate =substr($date, 11, 5);
        $date = $debutSubstrDate . ' ' . $finSubstrDate;
        $format = 'Y-m-d H:i';

        $DateTime = DateTime::createFromFormat($format, $date);
        //Récupération des valeurs
        $libelle = htmlspecialchars($_POST['libelle']);
        $tags = htmlspecialchars($_POST['tags']);
        $_POST['montant'] < 0 ? $montant = 0 : $montant = htmlspecialchars($_POST['montant']);
        $payeur = htmlspecialchars($_POST['payePar']);

        if(($DateTime && $date == $DateTime->format($format)) == true) {
            $dateHeure = $date;
            //Affectation des valeurs pour la dépense
            $depense->setLibelle($libelle);
            $depense->setMontant($montant);
            $depense->setDateheure($dateHeure);
            $depense->setUid($payeur);
            $depense->setDid($did);

            //Affectation des valeurs pour le tag
            $tagObj->setTag($tags);
            //Mise à jour de la dépense
            $depenseRepository->updateAnExpense($depense);

            //Mise à jour du tag
            if ($nbTag->nbtag == 1) {
                $tid = $nameTag->tid;
                $tagObj->setTag($tags);
                $tagRepository->updateATag($tagObj, $tid);
            } else if ($nbTag->nbtag == 0) {
                $tagObj->setGid($gid);
                $tagRepository->addTag($tagObj);
                //Tag lié à la dépense
                $lastTid = $tagRepository->getLatestTag($gid);
                $tagRepository->addTagCaracterisiter($lastTid->lasttid, $did);
            }
            $depense = "ok";
            header("Location: consulterGroupe.php?id=$gid&messaged=$depense");
        } else {
            $danger = "La date ne respecte pas le format attendu !";
        }


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
         <li>Modifier dépense</li>
     </ul>
</nav>
<?php if(isset($danger)): ?>
    <span class="notification" id="notificationDanger"><?= $danger ?></span>
<?php endif; ?>
    <main class="formulaire">
      <section class="form" id="registerFormAndExpenseForm">
          <h1>Souhaitez-vous modifier cette dépense ?</h1>
        <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
            <section class="contactFormAndExpenseForm">
            <label for="libelle">Libellé*</label><input id="libelle" name="libelle" type="text" value="<?php echo $expense->libelle ?>" required>
                <?php if(!empty($nameTag)):?>
                <label for="tags">Tags*</label><input id="tags" name="tags" type="text" value="<?php echo $nameTag->tag ?>" required>
                <?php else: ?>
                    <label for="tags">Tags*</label><input id="tags" name="tags" type="text" placeholder="Voyage, Course, ..." required>
                <?php endif; ?>
            <label for="montant">Montant*</label><input id="montant" name="montant" type="number" value="<?php echo $expense->montant ?>" required>
            <label for="date">Date*</label><input id="date" name="date" type="datetime-local"  value="<?php echo $date;?>" required>
            <label for="payePar">Payé par*</label>
                <select id="payePar" name="payePar">
                    <?php foreach ($listOfParticipants as $participant): ?>
                        <option value="<?= $participant->uid ?>"<?php if (strcasecmp($participant->participant, $expense->payeur) == 0) echo ' selected' ?>><?= $participant->participant ?></option>
                    <?php endforeach; ?>
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
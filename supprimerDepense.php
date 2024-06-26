<?php
$titre = 'Supprimer dépense';
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

if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}

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
$date = $expense->date;
$date = date ('Y-m-d\TH:i:s', strtotime($date));
if(isset($_POST['butsub'])) {

    if(!empty($_POST['libelle']) AND !empty($_POST['tags']) AND !empty($_POST['montant'])
        AND !empty($_POST['date'])) {


        //Suppression du tag lié à cette dépense
        $tid = $nameTag->tid;
        $tagRepository->deleteATagLinkedToAnExpense($did, $tid);
        $tagRepository->removeAnExpenseTag($tid);

        //Suppression de la dépense
        $depenseRepository->deleteAnExpense($did);
        header("Location: consulterGroupe.php?id=$gid");


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
         <li>Supprimer dépense</li>
     </ul>
</nav>
    <main class="formulaire">
        <section class="form" id="registerFormAndExpenseForm">
            <h1>Souhaitez-vous supprimer cette dépense ?</h1>
            <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
                <section class="contactFormAndExpenseForm">
                    <label for="libelle">Libellé*</label><input id="libelle" name="libelle" type="text" value="<?php echo $expense->libelle ?>" readonly required>
                    <label for="tags">Tags*</label><input id="tags" name="tags" type="text" value="<?php echo $nameTag->tag ?>" readonly required>
                    <label for="montant">Montant*</label><input id="montant" name="montant" type="number" value="<?php echo $expense->montant ?>" readonly required>
                    <label for="date">Date*</label><input id="date" name="date" type="datetime-local"  value="<?php echo $date;?>" readonly required>
                    <label for="payePar">Payé par*</label>
                    <select id="payePar" name="payePar" disabled>
                        <?php foreach ($listOfParticipants as $participant): ?>
                            <option value="<?= $participant->uid ?>"<?php if (strcasecmp($participant->participant, $expense->payeur) == 0) echo ' selected' ?>><?= $participant->participant ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="accept" class="boutonA"  type="submit" name="butsub">Confirmer</button>
                    <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
                </section>
            </form>
        </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
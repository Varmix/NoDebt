<?php
$titre = "Solder groupe";
require_once("inc/header.inc.php");
require_once("php/participer.php");
require_once ("php/depense.php");
require_once ("php/session.php");
require_once ("php/versement.php");
require_once("php/user.php");
require_once("php/tag.php");
require_once ("php/facture.php");
require_once("php/groupe.php");
Use App\DepenseRepository;
Use App\ParticiperRepository;
Use App\VersementRepository;
Use App\User;
Use App\TagRepository;
Use App\FactureRepository;
Use App\GroupRepository;
Use App\Versement;
//Récupération de l'uid + vérification qu'une session soit démarrée.
$auth = new Session();
$gid = $_GET['gid'];
//Déclaration de variables
$versementRepository = new VersementRepository();
$depenseRepository = new DepenseRepository();
$participerRepository = new ParticiperRepository();
$groupeRepository = new GroupRepository();
$allDepensesByUsers = $depenseRepository->getDepensesByParticipant($gid);
$montantTotal = $depenseRepository->getTotalAmount($gid);
$nbParticipant = $participerRepository->NumberOfParticipant($gid);
$averageExpense = round($montantTotal->montant / $nbParticipant->nbparticipants, 2);
$tagRepository = new TagRepository();
$factureRepository = new FactureRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);
$versement = new Versement();
$participantInformation = new ParticiperRepository();
$participant = $participantInformation->VerifyIfUserIsAnParticipant($auth->getUid(), $gid);


//Vérification que le participant soit dans le groupe via les paramètres reçus en url (sécurité)
if($participant->numdugroupe != $gid && $participant->numueroduparticipant != $auth->getUid()) {
    header('Location: listeGroupes.php');
}

//Définition du nouveau fuseau horaire
date_default_timezone_set('Europe/Paris');
$date = date ('Y-m-d H:i:s');

//Traitement du formulaire
$credit = 0;
$debit = 0;
$crediteur = new User();
$debiteur = new User();
$arrayDepenses = [];
$crediteurArray = [];
$debiteurArray =  [];;
foreach ($allDepensesByUsers as $depenses) {
    $ecartMoyenne = $depenses['montant'] - $averageExpense;
    if ($ecartMoyenne >= 0) {
        $credit = $ecartMoyenne;
        $crediteur->setUid($depenses['uid']);
    } else {
        $debit = $ecartMoyenne;
        $debiteur->setUid($depenses['uid']);
    }
}

if ($depenses['montant'] > 0) {
    array_push($arrayDepenses, $depenses['montant']);
}
if (!is_null($crediteur)) {
    array_push($crediteurArray, $crediteur->getId());
}
if (!is_null($debiteur)) {
    array_push($debiteurArray, $debiteur->getId());
}


if(isset($_POST['butsub'])) {

    foreach ($allDepensesByUsers as $creditDepense) {
        if ($creditDepense['montant'] - $ecartMoyenne == abs($debit)) {
            //Ajouter un versment
            $versement->setGid($gid);
            $versement->setUid($debiteurArray[0]);
            $versement->setUid1($crediteurArray[0]);
            $versement->setDateheure($date);
            $versement->setMontant(abs($debit));
            $versement->setEstconfirme(0);

            $versementRepository->addVersement($versement);
            $allDepenses = $depenseRepository->affichageDepenseAvecTag($gid);
            foreach ($allDepenses as $depensesGroup) {
                $did = $depensesGroup['numdepense'];
                $factureRepository->DeleteAnInvoiceLinkedToAnExpenseJustOnDidParam($did);
                $tagRepository->deleteATagLinkedToAnExpenseOnDidParam($did);
                $tagRepository->removeAnExpenseTagOnGidParam($gid);
                $depenseRepository->deleteAnExpense($did);
            }
            header("Refresh: 0.01; url=consulterGroupe.php?id=$gid");
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
        <li><a href="consulterGroupe.php?id=<?=$gid?>">Groupe : <?= $nameOfTheGroup['name'] ?></a></li>
        <li>Solder groupe</li>
    </ul>
</nav>
<main class="formulaire">
    <section class="form" id="groupForm">
        <h1>Souhaitez-vous solder le groupe : <span class="italic"><?= $nameOfTheGroup['name'] ?></span> ? </h1>
        <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
            <section class="groupFormSection" id="solderSection">
                <label for="nom">Nom du groupe</label><input id="nom" name="nom" type="text" value="<?= $nameOfTheGroup['name'] ?>" readonly>
                <label for="prenom">Total du groupe (<?=$montantTotal->symboledevise ?>): </label><input id="total" name="prenom" type="text" value="<?= $montantTotal->montant ?>" readonly>
                <button id="accept" class="boutonA" type="submit" name="butsub">Confirmer</button>
                <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
            </section>
        </form>
    </section>
</main>
<?php
include("inc/footer.inc.php");
?>

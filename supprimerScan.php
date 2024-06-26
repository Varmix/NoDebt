<?php
$titre = 'Supprimer scan';
require_once("inc/header.inc.php");
require_once("php/facture.php");
require_once ("php/session.php");
require_once("php/groupe.php");
$auth = new Session();
Use App\Facture;
Use App\FactureRepository;
Use App\GroupRepository;
$gid = $_GET['gid'];
$fid = $_GET['fid'];
$did = $_GET['did'];
$factureRepository = new FactureRepository();
$nomFacture = $factureRepository->recoverAnInvoiceLinkedToAnExpense($fid);
$groupeRepository = new GroupRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);
if(isset($_POST['butsub'])) {
    if(!empty($_POST['facture'])) {
        echo "tu es ici";
        $facture = htmlspecialchars($_POST['facture']);

        $supprimerFacture = $factureRepository->DeleteAnInvoiceLinkedToAnExpense($fid, $did);
    }

    header("Location: consulterGroupe.php?id=$gid");

}

if(isset($_POST['butsubDecline'])) {
    header("Location: consulterGroupe.php?id=$gid");
}
?>
<?php
require_once ("php/nav.php")
?>
<nav>
    <ul class="breadcrumb">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="listeGroupes.php">Liste des Groupes</a></li>
        <li><a href="consulterGroupe.php?id=<?= $gid ?>">Groupe : <?= $nameOfTheGroup['name'] ?></a></li>
        <li>Supprimer scan</li>
    </ul>
</nav>
    <main class="formulaire">
        <section class="form" id="groupForm">
      <h1>Supprimer scan</h1>
      <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
          <section class="groupFormSection">
              <p class="bold5">Souhaitez-vous supprimer cette facture ?</p><input type="text" name="facture" value="<?php echo $nomFacture->scan ?>" readonly>
              <button id="accept" class="boutonA" type="submit" name="butsub">Confirmer</button>
              <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
          </section>
      </form>
      </section>
    </main>
    <?php
    include("inc/footer.inc.php");
    ?>
    
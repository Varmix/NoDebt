<?php
$titre = 'Ajouter scan';
include("inc/header.inc.php");
require_once ("php/facture.php");
require_once ("php/groupe.php");
require_once ("php/depense.php");
Use App\Facture;
Use App\FactureRepository;
Use App\GroupRepository;
Use App\DepenseRepository;
$did = $_GET['did'];
$gid = $_GET['gid'];
$groupeRepository = new GroupRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);
$depenseRepository = new DepenseRepository();
$informationAbountAnExpense = $depenseRepository->nameOfAnExpense($did);
if(isset($_POST['butsub']))  {
    if($_FILES['scan']['error'] > 0) {
        echo "Une erreur est survenue lors du transfert de votre fichier";
    }

    $maxSize = 10485760; //valeur en octet --> équivaut à 20 Mo
    $filesSize = $_FILES['scan']['size'];
    $validExt = array('.jpg', '.jpeg', '.png', '.docx', '.pfd');
    //Vérification de la taille du fichier
    if($filesSize > $maxSize) {
        $danger = "Le fichier est trop volumineux";
    }
    //Vérification que l'extension du document/image respecte celle qu'on souhaite
    $fileName = $_FILES['scan']['name'];
    $fileExt = '.' . strtolower(substr(strchr($fileName, '.'), 1));
    if(!in_array($fileExt, $validExt)) {
        $danger = "L'extension du fichier n'est pas (.jpg, .jpeg, .png, .docx, .pdf).";
    }
    //Génération d'un nom unique pour l'image
    $tmpName = $_FILES['scan']['tmp_name'];
    $uniqueName = uniqid('', true);
    $fileName = $uniqueName . $fileExt;
    $result = move_uploaded_file($tmpName,"uploads/" . $fileName);

    $fileNameInUploads = "uploads/" . $fileName;
    //Préparation des données concernant la facture à fournir à la BDD.
    $facture = new Facture();
    $facture->setScan($fileName);
    $facture->setDid($did);
    //Ajout de la facture en BDD
    $creerFacture = new FactureRepository();
    $creerFacture->addScan($facture);
    if($result) {
        $check = "Transfert terminé !";
    }
    if(file_exists($fileNameInUploads)) {
        $check =  "Fichier existant";
    }

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
          <li>Ajouter un scan</li>
        </ul>
    </nav>
<?php if(isset($check)): ?>
    <span class="notification" id="notificationCheck"><?= $check ?></span>
<?php endif; ?>
<?php if(isset($danger)): ?>
    <span class="notification" id="notificationDanger"><?= $danger ?></span>
<?php endif; ?>
    <main class="formulaire">
      <section class="form" id="groupForm">
      <h1>Ajouter un scan à la dépense : <span class="italic"><?= $informationAbountAnExpense->libelle ?></span></h1>
          <!-- listeGroupes.php -->
      <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>"  method="post" enctype="multipart/form-data">
          <section class="groupFormSection">
              <label for="nom">Libellé dépense</label><input id="nom" name="nom" type="text" value="<?php echo $informationAbountAnExpense->libelle ?>" readonly>
          <span>Ajouter une image*</span>
              <input type="hidden" name="MAX_FILE_SIZE" value="10485760" >
              <input type="file" name="scan" accept="image/*, .pdf, .docx">
          <input type="reset" name="reset1" value="Réinitialiser">
          <input type="submit" name="butsub" value="Envoyer">
          </section>
      </form>
      </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
    
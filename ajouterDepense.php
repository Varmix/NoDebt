<?php
    $titre = 'Ajouter dépense';
    require_once("inc/header.inc.php");
// Définir le nouveau fuseau horaire
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
$gid = $_GET['id'];
$participantInformation = new ParticiperRepository();
$groupeRepository = new GroupRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);

if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}


//Récupérer le nom et prénom de l'utilisateur actuel
$currentUser = $auth->getUserFirstName() . ' ' . $auth->getUserLastName();

//Liste des participants
$listOfParticipants = $participantInformation->getUsersOfAGroups($gid);

if(isset($_POST['butsub'])) {

    if(!empty($_POST['libelle'])  AND !empty($_POST['montant'])
        AND !empty($_POST['date']) AND !empty($_POST['payePar']) ) {

        $date = htmlspecialchars($_POST['date']);
        $debutSubstrDate = substr($date, 0, 10);
        $finSubstrDate =substr($date, 11, 5);
        $date = $debutSubstrDate . ' ' . $finSubstrDate;
        $format = 'Y-m-d H:i';

        $DateTime = DateTime::createFromFormat($format, $date);



        //Récupération des valeurs
        empty($_POST['libelle']) ? $libelle = "" : $libelle = htmlspecialchars($_POST['libelle']);
        $_POST['montant'] < 0 ? $montant = 0 : $montant = htmlspecialchars($_POST['montant']);
        $payeur = htmlspecialchars($_POST['payePar']);


        if(($DateTime && $date == $DateTime->format($format)) == true) {
            $dateHeure = $date;
            //Affectation des valeurs pour la dépense
            $depense->setLibelle($libelle);
            $depense->setMontant($montant);
            $depense->setDateheure($dateHeure);
            $depense->setUid($payeur);
            $depense->setGid($gid);


            // Récupération + affectation des valeurs pour le tag
            if(!empty($_POST['tags'])) {
                $tags = htmlspecialchars($_POST['tags']);
                $tagObj->setTag($tags);
                $tagObj->setGid($gid);
            }

            //Insertion des valeurs concernant la dépense en BDD
            $depenseRepository->addDepense($depense);

            if(!empty($_POST['tags'])) {
                //Insertion du tag en BDD
                $tagRepository->addTag($tagObj);

                //Tag lié à la dépense
                $lastTid = $tagRepository->getLatestTag($gid);
                $lastDid = $depenseRepository->getLastIdExpense($gid);
                $tagRepository->addTagCaracterisiter($lastTid->lasttid, $lastDid->lastdid);
            }
            $depense = "dépense";
            header("Location: consulterGroupe.php?id=$gid&message=$depense");
        } else {
            $danger = "La date ne respecte pas le format attendu !";
        }
    }
}
?>
<?php require_once("php/nav.php") ?>
<nav>
        <ul class="breadcrumb">
          <li><a href="index.php">Accueil</a></li>
          <li><a href="listeGroupes.php">Liste des Groupes</a></li>
          <li><a href="consulterGroupe.php?id=<?= $gid ?>">Groupe : <?= $nameOfTheGroup['name'] ?></a></li>
          <li>Ajouter une dépense</li>
        </ul>
</nav>
    <main class="formulaire">
      <section class="form" id="registerFormAndExpenseForm">
        <h1>Ajouter une dépense</h1>
        <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
          <section class="contactFormAndExpenseForm">
          <label for="libelle">Libellé*</label><input id="libelle" name="libelle" type="text" value="<?php if(isset($_POST['libelle'])) echo htmlspecialchars($_POST['libelle']) ?>" placeholder="Montagne" required>
          <label for="tags">Tags</label><input id="tags" name="tags" type="text" value="<?php if(isset($_POST['tags'])) echo htmlspecialchars($_POST['tags']) ?>" placeholder="Voyage, Course, ...">
          <label for="montant">Montant*</label><input id="montant" name="montant" type="number" step="0.01" value="<?php if(isset($_POST['montant'])) echo htmlspecialchars($_POST['montant']) ?>" placeholder="10,52" required>
          <label for="date">Date*</label><input id="date" name="date" type="datetime-local" value="<?php echo date('Y-m-d').'T'.date('H:i'); ?>">
          <label for="payePar">Payé par*</label>
          <select id="payePar" name="payePar">
              <?php foreach ($listOfParticipants as $participant): ?>
                  <option value="<?= $participant->uid ?>"<?php if ($participant->uid == $auth->getUid()) echo ' selected' ?>><?= $participant->participant ?></option>
              <?php endforeach; ?>
          </select>
          <input type="submit" name="butsub" value="Envoyer">
          </section>
        </form>
      </section>
    </main>
<?php
    include("inc/footer.inc.php");
?>
<?php
$titre = 'Liste des groupes';
include("inc/header.inc.php");
require_once("php/session.php");
require_once("php/groupe.php");
require_once("php/user.php");
require_once("php/participer.php");
require_once ("php/depense.php");
$auth = new Session();
USE App\User;
USE App\UserRepository;
USE App\Groupe;
USE App\GroupRepository;
USE App\Participer;
USE App\ParticiperRepository;
USE App\Depense;
Use App\DepenseRepository;
$uid = $auth->getUid();
$participantRepository = new ParticiperRepository();
$userRepository = new UserRepository();
$nbParticipation = $userRepository->NumberOfParticipant($uid);
$nbInvitation = $userRepository->NumberOfInvitation($uid);

if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}
if(isset($_POST['accept'])) {
    $idGroupe = htmlspecialchars($_POST['idGroupe']);
    //Passage du estConfirme à 1 en BDD
    $participantRepository->participantConfirmation($uid, $idGroupe);
    header("Location: consulterGroupe.php?id=$idGroupe");
}
if(isset($_POST['decline'])) {
    $idGroupe = htmlspecialchars($_POST['idGroupe']);
    $participantRepository->declinationOfAParticipant($uid, $idGroupe);
}
?>
<?php
require_once ("php/nav.php");
?>
<nav>
  <ul class="breadcrumb">
    <li><a href="index.php">Accueil</a></li>
    <li>Liste des Groupes</li>
  </ul>
</nav>
<main>
  <header class="editerGroupe">
    <h1>Vos groupes :</h1>
    <a href="ajouterGroupe.php">Ajouter Groupe</a>
  </header>
  <section class="cartes">
      <?php if($nbInvitation->nbinvitation == 0): ?>
          <h2 class="bold6">Vous n'avez aucune invitation en attente.</h2>
      <?php endif; ?>
      <?php if($nbParticipation->nbparticipation == 0): ?>
      <h2 class="bold6">Vous ne participez à aucun groupe.</h2>
    <?php endif; ?>
      <?php
      $user = new User();
      $userId = $auth->getUid();
      $listeGroup = new GroupRepository();
      $groupeAffichage = $listeGroup->affichageGroupe($userId);
      $groupeAffichageInvite = $listeGroup->affichageGroupeInvitation($userId);
      $LastThreeDepenses = new DepenseRepository(); ?>
      <?php foreach ($groupeAffichageInvite as $articleGroupeInvite): ?>
      <article class="carte">
          <header>
              <img src="./images/idée2.png" alt="idée">
          </header>
          <ul>
              <li class="firstLiAccueil"><span class="bold"><?php echo $articleGroupeInvite['nomdugroupe'] ?></span></li>
              <li class="autresLiAccueil"><span class="bold">Créé par : </span> <?php echo $articleGroupeInvite['createur'] ; ?></li>
              <li class="autresLiAccueil"><span class="bold">Total des dépenses :</span><?php if($articleGroupeInvite['totaldepenses'] == 0) {
                      echo '0' . $articleGroupeInvite['symboledevise'];
                  } else {
                      echo $articleGroupeInvite['totaldepenses'] . ' ' . $articleGroupeInvite['symboledevise'];
                  } ?></li>
              <li><details open>
                      <summary>Historique des dépenses :</summary>
                      <ul>
                          <?php $result = $LastThreeDepenses->get3LatestDepenses($articleGroupeInvite["numdugroupe"]) ?>
                          <?php foreach ($result as $depenses): ?>
                              <li><?= $depenses['payeur'] . "a payé " . $depenses['montant'] . ' '. $depenses['symboledevise'] . " , le " . $depenses['dateheure'] . ' (' . $depenses['libelle'] . ')' ?></li>
                          <?php endforeach; ?>
                      </ul>
                  </details></li>
          </ul>
          <h4 class="phraseInvite">Vous êtes invité à rejoindre ce groupe !</h4>
          <form class="boutonInvitation" action="listeGroupes.php" method="post">
              <input id="idGroupe" name="idGroupe" type="hidden" value ="<?= $articleGroupeInvite['numdugroupe'] ?>" readonly>
              <input class ="accepter" type="submit" name="accept" value="Accepter">
              <a class="refuser" href="refuserInvitation.php?id=<?= $articleGroupeInvite['numdugroupe'] ?>">Refuser</a>
          </form>
      </article>
    <?php endforeach ?>
     <?php foreach ($groupeAffichage as $articleGroup): ?>
          <article class="carte">
      <header>
        <img src="./images/budget3.png" alt="budget">
      </header>
      <ul>
        <li class="firstLiAccueil"><span class="bold"><?php echo $articleGroup['nomdugroupe'] ?></span></li>
        <li class="autresLiAccueil"><span class="bold">Créé par : </span> <?php echo $articleGroup['createur'] ; ?></li>
        <li class="autresLiAccueil"><span class="bold">Total des dépenses :</span> <?php if($articleGroup['totaldepenses'] == 0) {
            echo '0 ' . $articleGroup['symboledevise'];
        } else {
              echo $articleGroup['totaldepenses'] . ' ' . $articleGroup['symboledevise'];
            } ?></li>
        <li><details open>
          <summary>Historique des dépenses :</summary>
          <ul>
              <?php $result = $LastThreeDepenses->get3LatestDepenses($articleGroup["numdugroupe"]) ?>
              <?php foreach ($result as $depenses): ?>
              <li><?= $depenses['payeur'] . "a payé " . $depenses['montant'] . ' '. $depenses['symboledevise'] . " , le " . $depenses['dateheure'] . ' (' . $depenses['libelle'] . ')' ?></li>
              <?php endforeach; ?>
          </ul>
        </details></li>
      </ul>
      <a class="boutonA" href="consulterGroupe.php?id=<?= $articleGroup['numdugroupe'] ?> ">Consulter</a>
    </article>
      <?php endforeach; ?>
  </section>
</main>
<?php
include("inc/footer.inc.php");
?>
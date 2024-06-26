<?php
$titre = 'Consulter versement';
include("inc/header.inc.php");
require_once ("php/session.php");
require_once("php/user.php");
require_once ("php/versement.php");
require_once("php/groupe.php");
require_once ("php/depense.php");
require_once("php/participer.php");
Use App\User;
Use App\UserRepository;
Use App\Versement;
Use App\VersementRepository;
Use App\GroupRepository;
Use App\DepenseRepository;
Use App\ParticiperRepository;
$auth = new Session();
$gid = $_GET['gid'];
$versementRepository = new VersementRepository();
$allVersements = $versementRepository->voirVersement($gid);
$userRepository = new UserRepository();
$groupeRepository = new GroupRepository();
$depenseRepository = new DepenseRepository();
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);
$symboleDevise = $depenseRepository->getTotalAmount($gid);
$participantInformation = new ParticiperRepository();
$participant = $participantInformation->VerifyIfUserIsAnParticipant($auth->getUid(), $gid);


//Vérification que le participant soit dans le groupe via les paramètres reçus en url (sécurité)
if($participant->numdugroupe != $gid && $participant->numueroduparticipant != $auth->getUid()) {
    header('Location: listeGroupes.php');
}
if(isset($_POST['accepter'])) {
    if(!empty($_POST['debiteur']) AND !empty($_POST['crediteur']) AND !empty($_POST['montant'])) {
        $debiteur = htmlspecialchars($_POST['debiteur']);
        $crediteur = htmlspecialchars($_POST['crediteur']);
        $montant = htmlspecialchars($_POST['montant']);

        //Mettre le versement en statut accepté
        $versementRepository->payementStatusAccept($debiteur, $crediteur, $montant, $gid);
        header("Refresh: 0.01; url=confirmerVersement.php?gid=$gid");


    }
}
if(isset($_POST['refuser'])) {
    if(!empty($_POST['debiteur']) AND !empty($_POST['crediteur']) AND !empty($_POST['montant'])) {
        $debiteur = htmlspecialchars($_POST['debiteur']);
        $crediteur = htmlspecialchars($_POST['crediteur']);
        $montant = htmlspecialchars($_POST['montant']);

        //Mettre le versement en statut accepté
        $versementRepository->payementStatusDecline($debiteur, $crediteur, $montant, $gid);
        header("Refresh: 0.01; url=confirmerVersement.php?gid=$gid");

    }
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
        <li>Consulter versement</li>
     </ul>
    </nav>
    <main class="consulterVersement">
    <section>
        <h1>Liste des versments :</h1>
        <?php
        foreach ($allVersements as $versement): ?>
            <article>
        <ul>
          <li><span class="bold2"><?php echo $versement->dateheure ?></span>
            <ol>
              <li><span class="bold"><?php $name = $userRepository->getNameOfAUser($versement->debiteur); echo $name->nomparticipant  ?> </span></li>
              <li><span class="italic">doit </span><?= $versement->montant . ' ' .$symboleDevise->symboledevise ?></li>
              <li><span class="bold"><?php $name = $userRepository->getNameOfAUser($versement->crediteur); echo $name->nomparticipant  ?></span></li>
            </ol>
          </li>
          <li><span class="bold">Statut :</span>
              <?php $etatVersement = $versement->statut;
              switch($etatVersement):
                  case 0: ?>en attente</li>
                  <?php break?>
                  <?php case 1: ?>
                  confirmé </li>
                  <?php break?>
                <?php case -1: ?>
                  refusé </li>
                  <?php break?>
              <?php endswitch; ?>

        </ul>
                <?php if($versement->crediteur == $auth->getUid() && $etatVersement == 0): ?>
        <form class="boutonInvitation" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
              <input id="debiteur" name="debiteur" type="hidden" value ="<?= $versement->debiteur ?>" readonly>
              <input id="crediteur" name="crediteur" type="hidden" value ="<?= $versement->crediteur ?>" readonly>
              <input id="montant" name="montant" type="hidden" value="<?= $versement->montant?>" readonly>
              <input class ="accepter" type="submit" name="accepter" value="Accepter">
              <input class = "refuser" type="submit" name="refuser" value="Refuser">
            </form>
            <?php endif ?>
        </article>
         <?php   endforeach; ?>
      </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
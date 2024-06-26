<?php
$titre = 'Groupe';
require_once("inc/header.inc.php");
require_once("php/groupe.php");
require_once ("php/session.php");
require_once("php/participer.php");
require_once ("php/depense.php");
require_once ("php/facture.php");
require_once("php/user.php");
require_once("php/tag.php");
require_once ("php/versement.php");
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
USE App\Participer;
USE App\ParticiperRepository;
Use App\Groupe;
Use App\GroupRepository;
Use App\Depense;
Use App\DepenseRepository;
Use App\Facture;
Use App\FactureRepository;
Use App\User;
Use App\UserRepository;
Use App\Versement;
USE App\VersementRepository;
Use App\Tag;
Use App\TagRepository;
//Déclaration des variables
$auth = new Session();
$groupe = new Groupe();
$depenseRepository = new DepenseRepository();
$groupeRepository = new GroupRepository();
$participantInformation = new ParticiperRepository();
//Vérification que le participant soit bien dans le groupe (sécurité)
$participant = $participantInformation->VerifyIfUserIsAnParticipant($auth->getUid(), $_GET['id']);
//Déclaration de variable
$factureRepository = new FactureRepository();
$userRepository = new UserRepository();
$userParticiant = new User();
$firstNameOfCurrentUser = $auth->getUserFirstName();
$lastNameOfCurrentUser = $auth->getUserLastName();
$participerRepository = new ParticiperRepository();
$versement = new Versement();
$versementRepository = new VersementRepository();
$tagRepository = new TagRepository();

//Définition du nouveau fuseau horaire
date_default_timezone_set('Europe/Paris');
$date = date ('Y-m-d H:i:s');

//Vérification que l'utilisateur soit authentifié
if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}

    //Vérification que le participant soit dans le groupe via les paramètres reçus en url (sécurité)
    if($participant->numdugroupe != $_GET['id'] && $participant->numueroduparticipant != $auth->getUid()) {
        header('Location: listeGroupes.php');
    }

    if(!empty($_GET['message'])) {
        $check =  "Dépense ajoutée";
    }

    if(!empty($_GET['messaged'])) {
        $check =  "Dépense modifiée";
}
//Déclaration de variables
$groupe->setGid($_GET['id']);
$gid = $groupe->getGid();
$creator = $groupeRepository->getNameOfCreator($auth->getUid(), $gid);
$allDepensesByUsers = $depenseRepository->getDepensesByParticipant($gid);
$currentUser = $auth->getUserFirstName() . ' ' . $auth->getUserLastName();
$montantTotal = $depenseRepository->getTotalAmount($gid);
$allDepensesDisplay = $depenseRepository->affichageDepenseAvecTag($gid);
$nameOfTheGroup = $groupeRepository->nameAndCurrencyOfTheGroup($gid);

$participantInformation = new ParticiperRepository();
$listOfParticipants = $participantInformation->getUsersOfAGroups($gid);

//Traitement de l'invitation
$mail = new PHPMailer(true);

try {
    if (isset($_POST['inviter'])) {
        if (!empty($_POST['mail'])) {
            $courriel = htmlspecialchars($_POST['mail']);

            $userParticiant->setCourriel($courriel);
            //Insertion de l'utilisateur en BDD
            $user = $userRepository->getUserByMail($courriel);
            if(empty($user)) {
                $userRepository->createUserParticipant($userParticiant);
            }
            $user = $userRepository->getUserByMail($courriel);
            $userUid = $user->getId();
            //Insertion de l'utilisateur dans la table "participer en bdd"
            $participantInvite = new Participer();
            $participantInvite->setUid($userUid);
            $participantInvite->setGid($gid);
            $participantInvite->setEstconfirme(0);
            //Créer le partcipant en bdd
            $creerParticipant = new ParticiperRepository();
            $creerParticipant->createParticipant($participantInvite);

            $mail->CharSet = 'UTF-8';
            $mail->setFrom("invitation@nodebt.com");
            $mail->addAddress($courriel);  //placez VOTRE adresse courriel
            $mail->isHTML(false);
            $mail->Subject = "$firstNameOfCurrentUser $lastNameOfCurrentUser vous invite à rejoindre un groupe sur NoDebt !";
            $mail->Body = "Bonjour, \n\n "."Vous avez été invité par $firstNameOfCurrentUser $lastNameOfCurrentUser à un rejoindre un groupe sur NoDebt. \n\n Voici le lien :  http://192.168.128.13/~q210054/EVAL_V4/inscription.php?gid=$gid&uid=$userUid" . "\n\n Cordialement, \n NoDebt";
            $mail->send();
            $check = "Courriel envoyé";
        }
    }
} catch (Exception $e) {
    $erreur = 'Erreur survenue lors de l\'envoi de l\'invitation<br>' . $mail->ErrorInfo;
}

//Traitement de recherche simple
/*if(isset($_GET['rechercheSimple']) AND !empty($_GET['rechercheSimple'])) {
        $rechercheSimple = htmlspecialchars($_GET['rechercheSimple']);
        $allDepensesDisplay = $depenseRepository->affichageDepenseRechercheAvecTag($gid, $rechercheSimple);
}*/
//Traitement de recherche simple
if (isset($_POST['effectuerRecherche'])) {
    if(!empty($_POST['rechercheSimple'])) {
        $rechercheSimple = htmlspecialchars($_POST['rechercheSimple']);
        $allDepensesDisplay = $depenseRepository->affichageDepenseRechercheAvecTag($gid, $rechercheSimple);
        if(empty($allDepensesDisplay)) {
            $erreur =   "Aucune dépense trouvée";
            header("Refresh: 5; url=consulterGroupe.php?id=$gid");
        }
        header("Refresh: 5; url=consulterGroupe.php?id=$gid");
    }
}

if(isset($_POST['effectuerRechercheAvancee'])) {
    if(!empty($_POST['libelle']) OR !empty($_POST['montantMinimum']) OR !empty($_POST['montantMaximum']) OR !empty($_POST['tags'])
    OR !empty($_POST['dateDebut']) OR !empty($_POST['dateFin'])) {
        //Suppression de cookies en fonction des recherches
        /*setcookie('libelle', time() - 3600);
        setcookie('montantMinimum', time() - 3600);
        setcookie('montantMaximum', time() - 3600);
        setcookie('tags', time() - 3600);
        setcookie('dateDebut', time() -3600);
        setcookie('dateFin', time() -3600);*/
        //Traitement + mise en place des cookies
        $libelle = htmlspecialchars($_POST['libelle']);
        !empty($_POST['libelle'])  ? $libelle = htmlspecialchars($_POST['libelle']) : $libelle = "";
        if(!empty($_POST['libelle'])) { // ou !is_null
            setcookie('libelle', "$libelle", time() + 3600 * 24 * 365, null, null, false, true);
        }
        $_POST['montantMinimum'] > 0 ? $montantMinimum = htmlspecialchars($_POST['montantMinimum']) : $montantMinimum = 0;
        if(!empty($_POST['montantMinimum'])) {
            setcookie('montantMinimum', "$montantMinimum", time() + 3600 * 24 * 365, null, null, false, true);
        }
        $_POST['montantMaximum'] < 0 ? $montantMaximum = 214789641 : $montantMaximum = htmlspecialchars($_POST['montantMaximum']);
        var_dump($_POST['montantMinimum']);
        if(!empty($_POST['montantMaximum'])) {
            setcookie('montantMaximum', "$montantMaximum", time() + 3600 * 24 * 365, null, null, false, true);
        }
        !empty($_POST['tags']) ? $tags = htmlspecialchars($_POST['tags']) : $tags = "";
        if(!empty($_POST['tags'])) {
            setcookie('tags', "$tags", time() + 3600 * 24 * 365, null, null, false, true);
        }
        $dateDeb = new DateTime('1970-01-01');
        empty($_POST['dateDebut']) ? $dateDebut = date_format($dateDeb, 'd/m/Y') : $dateDebut = htmlspecialchars($_POST['dateDebut']);
        if(!empty($_POST['dateDebut'])) {
            setcookie('dateDebut', "$dateDebut", time() + 3600 * 24 * 365, null, null, false, true);
        }
        $dateDebut = date ('%d/%m/%Y', strtotime($dateDebut));
        $dateFi = new DateTime('2030-12-31');
        empty($_POST['dateFin']) ? $dateFin = date_format($dateFi, 'd/m/y') : $dateFin = htmlspecialchars($_POST['dateFin']);
        if(!empty($_POST['dateFin'])) {
            setcookie('dateFin', "$dateFin", time() + 3600 * 24 * 365, null, null, false, true);
        }
        $dateFin = date ('%d/%m/%Y', strtotime($dateFin));

        $allDepensesDisplay = $depenseRepository->affichageDepenseRechercheAvanceeAvecTag($gid, $libelle, $tags, $montantMinimum, $montantMaximum, $dateDebut, $dateFin);
        header("Refresh: 20; url=consulterGroupe.php?id=$gid");

    }
}

//Calculer dépense moyenne (Données pour affichage et traitement)
$nbParticipant = $participerRepository->NumberOfParticipant($gid);
$averageExpense = round($montantTotal->montant / $nbParticipant->nbparticipants, 2);



?>
<?php
require_once ("php/nav.php");
?>
<nav>
  <ul class="breadcrumb">
    <li><a href="index.php">Accueil</a></li>
    <li><a href="listeGroupes.php">Liste des Groupes</a></li>
    <li><a href="consulterGroupe.php?id=<?=$gid?>">Groupe : <?= $nameOfTheGroup['name'] ?></a></li>
  </ul>
</nav>
<?php if(isset($check)): ?>
    <span class="notification" id="notificationCheck"><?= $check ?></span>
<?php endif; ?>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
<main class="groupe">
  <section class="statistiques">
    <header class="firstHeaderGroupe">
      <h1> <?php echo $nameOfTheGroup['name'] ?> : </h1>
      <p class="createur"><span class="bold"> Créé par :</span><?php echo $creator->createur ?></p>
    </header>
    <nav class="groupeHeader">
      <a class="boutonA" href="modifierGroupe.php?id=<?= $gid ?>"><i class="fas fa-edit"></i>Modifier Groupe</a>
      <a class="boutonSupprimer" href="supprimerGroupe.php?id=<?= $gid ?>"><i class="fas fa-trash-alt"></i>Supprimer Groupe</a>
    </nav>
    <article class="articleGroupe1">
      <header class="headerGroupeArticle">
        <h2>Statistiques :</h2>
        <p><span class="bold">Montant total :</span><?php if($montantTotal->montant == 0) echo '0 ' . $montantTotal->symboledevise; else echo $montantTotal->montant . ' ' . $montantTotal->symboledevise?></p>
        <form class="search" action="consulterGroupe.php?id=<?= $gid ?>" method="post">
          <input  id="mail" name="mail" type="email" placeholder="Inviter un participant">
          <button type="submit" name="inviter"><i class="fas fa-user-plus"></i></button>
        </form>
      </header>
      <table>
        <tr>
          <th>Participant</th>
          <th>Total dépensé</th>
        </tr>
          <?php foreach ($allDepensesByUsers as $depensesByUser): ?>
        <tr>
          <td><?php $userBuyer = $depensesByUser['payeur'];
              if(strcasecmp($userBuyer, $currentUser) == 0)  {
                 echo $depensesByUser['payeur'] . ' (moi)';

             } else {
                  echo $depensesByUser['payeur'];
             }
               ?>
          </td>
          <td><?php if(is_null($depensesByUser['montant'])) {
                echo '0 ' . $montantTotal->symboledevise;
              } else {
                echo $depensesByUser['montant'] . ' ' . $montantTotal->symboledevise;
              }
             ?>
            </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </article>
    <article class="articleGroupe2">
      <header class="headerGroupeArticle">
        <h2>Moyenne</h2>
        <p><span class="bold">Moyenne par participant :</span><?= $averageExpense . ' ' . $montantTotal->symboledevise ?></p>
          <a class="boutonAHeader" href="confirmerVersement.php?gid=<?=$gid?>"><i class="fas fa-hand-holding-usd"></i>Consulter versement</a>
          <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
              <a class="boutonAHeader" href="solderGroupe.php?gid=<?=$gid?>"><i class="fas fa-coins"></i>Solder</a>
                  <!--<button class="boutonAHeader" type="submit" name="solder"><i class="fas fa-coins"></i>Solder</button>-->
          </form>
      </header>
        <table>
            <tr>
                <th>Participant</th>
                <th>Ecart à la moyenne</th>
            </tr>
            <?php //Affichage solder groupe
                foreach ($allDepensesByUsers as $depenses):
                    $ecartMoyenne = $depenses['montant'] - $averageExpense;
                    if($ecartMoyenne >= 0): ?>
                    <tr>
                        <td><?= $depenses['payeur'] ?></td>
                        <td class="positif"><?php echo $credit = $ecartMoyenne; ?>  <?= $montantTotal->symboledevise ?></td>
                     </tr>
                    <?php else: ?>
                    <tr>
                        <td><?= $depenses['payeur'] ?></td>
                        <td class="negatif"><?php echo $debit = $ecartMoyenne ;?> <?= $montantTotal->symboledevise ?> </td>
                    </tr>
                    <?php endif;
                    //var_dump($arrayDepenses);
                    //var_dump($crediteurArray);
                    //var_dump($debiteurArray);
                    //var_dump($crediteur);
                    //var_dump($debiteur);
                    //var_dump($debiteurArray[0]);
                    //var_dump($crediteurArray[0]);
                    endforeach;
             ?>
        </table>
    </article>
    <article class="articleFinGroupe">
      <header id="headerHistorique" class="headerGroupeArticle">
        <h2>Historique des dépenses : </h2>
        <a class="boutonA" id="headerGroupeahref" href="ajouterDepense.php?id=<?= $gid ?>" title="Ajouter dépense"><i class="fas fa-plus"></i>Ajouter dépense</a>
        <form class="search" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
          <input id="rechercher" name="rechercheSimple" type="search" placeholder="Mot-clef">
          <button type="submit" name="effectuerRecherche"><i class="fas fa-search"></i></button>
        </form>
        <details class="rechercheAvanceeDetails">
          <summary class="rechercheAvancee"><i class="fas fa-search-plus"></i>Effectuer une recherche avancée</summary>
          <form class="formulaireRechercheAvancee" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
            <label for="libelle">Libellé(s) :</label><input id="libelle" name="libelle" type="text"  value="<?php if(isset($_COOKIE['libelle'])) echo  htmlspecialchars($_COOKIE['libelle']) ?>" placeholder="Libellé(s)">
            <label for ="montantMinimum">Montant minimum :</label><input id="montantMinimum" name="montantMinimum" type="number" value="<?php if(isset($_COOKIE['montantMinimum'])) echo  htmlspecialchars($_COOKIE['montantMinimum']) ?>" placeholder="Montant minmum">
            <label for="montantMaximum">Montant maximum :</label><input id ="montantMaximum" name="montantMaximum" type="number" value="<?php if(isset($_COOKIE['montantMaximum'])) echo  htmlspecialchars($_COOKIE['montantMaximum']) ?>" placeholder="Montant maximum">
            <label for="tags">Tags :</label>
            <input id="tags" name="tags" type="text" value="<?php if(isset($_COOKIE['tags'])) echo  htmlspecialchars($_COOKIE['tags']) ?>" placeholder="Tags">
            <label for="dateDebut">Date de début : </label><input id="dateDebut" name="dateDebut" type="date" value="<?php if(isset($_COOKIE['dateDebut'])) echo  htmlspecialchars($_COOKIE['dateDebut']) ?>">
            <label for="dateFin">Date de Fin : </label><input id="dateFin" name="dateFin" type="date" value="<?php if(isset($_COOKIE['dateFin'])) echo  htmlspecialchars($_COOKIE['dateFin']) ?>">
              <button class="boutonAHeader" type="submit" name="effectuerRechercheAvancee"><i class="fas fa-search-dollar"></i>Effectuer</button>
          </form>
        </details>
      </header>
      <section class="cadreHistorique">
          <?php
          if(isset($erreur)) {
              echo $erreur;
          }
          ?>
        <?php foreach ($allDepensesDisplay as $depenses): ?>
        <ul>
            <li><a class="bold3" href="modifierDepense.php?gid=<?=$gid ?>&did=<?= $depenses['numdepense'] ?> "><?php echo $depenses['libelle'] ?><i id="penIcon" class="fas fa-pen"></i></a>
                <a class="bold3" href="supprimerDepense.php?gid=<?=$gid ?>&did=<?= $depenses['numdepense'] ?> "><i id="crossIcon" class="fas fa-times-circle"></i></a>
            <ol>
              <li class="informationsPaiement"><span class="bold">Payé par : </span><?php echo $depenses['payeur'] ?></li>
              <li class="informationsPaiement"><span class="bold">Montant : </span><?php echo $depenses['montant'] . ' ' .  $montantTotal->symboledevise ?></li>
              <li class="informationsPaiement"><span class="bold">Date :</span><?php echo $depenses['date'] ?></li>
                <?php if(!empty($depenses['tag'])): ?>
                    <li class="informationsPaiement"><span class="bold">Tags : </span><?php echo $depenses['tag'] ?></li>
                <?php else: ?>
                    <li> </li>
                <?php endif; ?>

            </ol>
          </li>
            <li class="bold4">Scan(s) : </li>
            <?php $factureLinkDepense = $factureRepository->affichageFacture($depenses['numdepense']);
            $i = 0;
            foreach ($factureLinkDepense as $factureDepense): ?>
            <li><a href="./uploads/<?= $factureDepense['scan'] ?>" download>Facture <?php echo $i++ ?></a><a href="supprimerScan.php?gid=<?=$gid ?>&did=<?= $depenses['numdepense'] ?>&fid=<?= $factureDepense['numfacture'] ?> "><i id="crossIcon2" class="fas fa-times"></i></a></li>
            <?php endforeach; ?>
          <li class="boutonALi"><a class="boutonALittle" href="ajoutScan.php?gid=<?=$gid?>&did=<?= $depenses['numdepense'] ?> ">Ajout scan</a></li>
        </ul>
        <?php endforeach; ?>
      </section>
    </article>
  </section>
</main>
<?php
include("inc/footer.inc.php");
?>
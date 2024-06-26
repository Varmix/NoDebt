<?php
$titre = 'Ajouter groupe';
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
$selected = "Euro (€)";
$options = array('Euro (€)', 'Dollar américain ($)', 'Livre sterling(£)', 'Franc suisse (CHF)', 'Yen japonais (¥)', 'Dollar australien ($)', 'Dollar canadien ($)');
if(isset($_POST['butsub'])) {

    if(!empty($_POST['nomDuGroupe']) AND !empty($_POST['devise'])) {
        $nomDuGroupe = htmlspecialchars($_POST['nomDuGroupe']);
        $devise = htmlspecialchars($_POST['devise']);

        //Vérifier que la devise se trouve bien dans le tableau
        if(in_array($devise, $options)) {
            $groupe = new Groupe();
            $groupe->setNomGroupe($nomDuGroupe);
            $groupe->setDevise($devise);
            $groupe->setUid($auth->getUid()); // On définit l'UID du créateur en fonction de celui présent en session

            //Insertion du groupe en BDD
            $insertGroupe = new GroupRepository();
            $insertGroupe->createGroup($groupe);

            $dernierGroupe = new GroupRepository();
            //Récupérer le dernier groupe de l'utilisateur sur base de son ID
            $lastGroupe = $dernierGroupe->getLastIdGroup($auth->getUid());
            //Préparation aux données à fournir à la BDD pour la participant créateur
            $participantCreateur = new Participer();
            $participantCreateur->setUid($auth->getUid());
            $participantCreateur->setGid($lastGroupe->lastgid);
            $participantCreateur->setEstconfirme(1);
            //Créer le partcipant en bdd
            $creerParticipant = new ParticiperRepository();
            $creerParticipant->createParticipant($participantCreateur);
            header('Location: listeGroupes.php');

            }

        } else {
            $erreur =  "Impossible de créer le groupe, la devise indiquée n'est pas prise en charge.";
        }

}
?>
<?php
require_once ("php/nav.php");
?>
<nav>
        <ul class="breadcrumb">
          <li><a href="index.php">Accueil</a></li>
          <li><a href="listeGroupes.php">Liste des groupes</a></li>
          <li>Ajout d'un groupe</li>
        </ul>
      </nav>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
    <main class="formulaire">
      <section class="form" id="groupForm">
        <h1>Créer un groupe</h1>
      <form class="formulaireBase" action="ajouterGroupe.php" method="post">
          <section class="groupFormSection">
          <label for="nomDuGroupe">Nom du groupe*</label><input id="nomDuGroupe" name="nomDuGroupe" type="text" required>
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
          <input type="submit" name="butsub" value="Créer">
          </section>
      </form>
    </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
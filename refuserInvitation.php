<?php
$titre = 'Supprimer dépense';
require_once("inc/header.inc.php");
//Définir le nouveau fuseau horaire
date_default_timezone_set('Europe/Paris');
require_once("php/depense.php");
require_once ("php/session.php");
require_once("php/participer.php");
require_once("php/tag.php");
USE App\Depense;
USE App\DepenseRepository;
USE App\Participer;
USE APP\ParticiperRepository;
USE App\Tag;
USE App\TagRepository;
$auth = new Session();
$gid = $_GET['id'];
$participantInformation = new ParticiperRepository();
$participantRepository = new ParticiperRepository();

if(!$auth->isLogged()) {
    $auth->forceUserToLogin();
}

//Vérification que l'utilisateur authentifié se trouve dans le groupe
$currentParticipant = $participantInformation->VerifyIfUserIsAGuest($auth->getUid(), $_GET['id']);


//Liste des participants
$listOfParticipants = $participantInformation->getUsersOfAGroups($gid);
if(!($currentParticipant->numdugroupe == $_GET['id'] && $currentParticipant->numueroduparticipant == $auth->getUid())) {
    header('Location: listeGroupes.php');
}


if(isset($_POST['butsub'])) {
    $participantRepository->declinationOfAParticipant($auth->getUid(), $gid);
}

if(isset($_POST['butsubDecline'])) {
    header("Location: listeGroupes.php");
}




?>
<?php
require_once ("php/nav.php");
?>
<nav>
    <ul class="breadcrumb">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="listeGroupes.php">Liste des Groupes</a></li>
        <li>Nom du Groupe</li>
        <li>Supprimer dépense</li>
    </ul>
</nav>
<main class="formulaire">
    <section class="form" id="registerFormAndExpenseForm">
        <h1>Souhaitez-vous refuser l'invitation à ce groupe ?</h1>
        <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
            <section class="contactFormAndExpenseForm">
                <button id="accept" class="boutonA"  type="submit" name="butsub">Confirmer</button>
                <button id="decline" class="boutonSupprimer" type="submit" name="butsubDecline">Annuler</button>
            </section>
        </form>
    </section>
</main>
<?php
include("inc/footer.inc.php");
?>

<?php
require_once("inc/db_link.inc.php");
$titre = 'Editer profil';
include("inc/header.inc.php");
require_once ("php/session.php");
require_once("php/user.php");
USE App\User;
USE APP\UserRepository;
$auth = new Session();
$userRepo = new UserRepository();
$auth->forceUserToLogin();
$user = $userRepo->getUserByMail($auth->getUserMail());
$bdd = \DB\DBLink::connect2db(MYDB, $message);
if(isset($_POST['butssub'])){
    if(!empty($_POST['nom']) AND !empty($_POST['prenom']) AND !empty($_POST['courriel'])
        AND !empty($_POST['motPasse']) AND !empty($_POST['VerifMotPasse'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $courriel = htmlspecialchars($_POST['courriel']);
        $motPasse = htmlspecialchars($_POST['motPasse']);
        $verifMotDePasse = htmlspecialchars($_POST['VerifMotPasse']);

        if($motPasse != $verifMotDePasse) {
            $erreur = "Vos mots de passe ne correspondent pas... Veuillez réessayer";
        }

        $motPasse = password_hash($motPasse, PASSWORD_BCRYPT);
        /*
         * Si aucune modification du courriel n'est effectuée, l'utilisateur le conserve.
         * Si une modification est signaliée, vérification que le nouveau courriel ne soit
         * pas déjà dans la BDD.
         */
        $insertUser = new UserRepository();
        if($courriel == $user->getCourriel()) {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setMotpasse($motPasse);
            $_SESSION['nom'] = $user->getNom();
            $_SESSION['prenom'] = $user->getPrenom();
            $_SESSION['uid'] = $user->getId();
            $insertUser->update($user);
            $check = "Utilisateur mis à jour";
            header('Location: listeGroupes.php');
        } else {
            //Vérification que le courriel existe ou non dans la BDD
            $courrielEstExistant = new UserRepository();
            $courrielEstExistant = $courrielEstExistant->verifyIfUserIsInDB($courriel);
            if($courrielEstExistant) {
                $erreur = "Ce courriel est déjà utilisé";
            } else {
                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setCourriel($courriel);
                $user->setMotpasse($motPasse);

                $_SESSION['nom'] = $user->getNom();
                $_SESSION['prenom'] = $user->getPrenom();
                $_SESSION['courriel'] = $user->getCourriel();
                $_SESSION['uid'] = $user->getId();


                $insertUser->update($user);
                $check = "Utilisateur mis à jour";
                header('Location: listeGroupes.php');
            }
        }

    }
}
?>
<?php
require_once ("php/nav.php");
?>
<nav>
     <ul class="breadcrumb">
         <li><a href="listeGroupes.php">Liste des groupes</a></li>
         <li>Editer profil</li>
     </ul>
</nav>
<?php if(isset($check)): ?>
    <span class="notification" id="notificationCheck"><?= $check ?></span>
<?php endif; ?>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
<main class="formulaire">
      <section class="form" id="registerFormAndExpenseForm">
        <h1>Vos informations</h1>
      <form class="formulaireBase" action="editerProfil.php" method="post">
          <section class="register" id="register">
              <label for="nom">Nom</label><input id="nom" name="nom" type="text" value="<?php echo $auth->getUserLastName()?>">
              <label for="prenom">Prénom</label><input id="prenom" name="prenom" type="text" value="<?php echo $auth->getUserFirstName() ?>">
              <label for="courriel">Adresse e-mail</label><input id="courriel" name="courriel" type="email" value="<?php echo $auth->getUserMail() ?>" >
              <label for="motPasse">Mot de passe</label><input id="motPasse" name="motPasse" type="password" placeholder="•••••••••••••">
              <label for="VerifMotPasse">Confirmation le mot de passe</label><input id="VerifMotPasse" name="VerifMotPasse" type="password" placeholder="•••••••••••••">
              <input type="submit" name="butssub" value="Enregistrer">
          </section>
          </form>
    </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
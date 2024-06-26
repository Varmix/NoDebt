<?php
$titre = 'Inscription';
require_once("inc/header.inc.php");
require_once("php/user.php");
require_once ("php/session.php");
USE DB\DBLink;
USE APP\User;
USE App\UserRepository;
$courrielEstExistant = new UserRepository();
$userRepository = new UserRepository();
$auth = new Session();
$bdd = \DB\DBLink::connect2db(MYDB, $message);
if(!empty($_GET['gid']) && $_GET['uid']) {
    $gid = $_GET['gid'];
    $uid = $_GET['uid'];
}

if(!empty($gid) AND  !empty($uid)) {
    //Passage par un lien d'invitation
    $user = $userRepository->getUserById($uid);
    if(is_null($user->getPrenom()) && is_null($user->getNom()) && is_null($user->getMotPasse()) && ($user->isEstActif() == 0) && !empty($user->getCourriel())) {
        //Utilisateur n'ayant pas de compte mais a été ajouté dans un groupe

        if(isset($_POST['butssub'])) {
            //Vérification des champs envoyés par l'utilisateur

            if(!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['motPasse']) && !empty($_POST['VerifMotPasse']) &&!empty($_POST['courriel'])) {
                $nomInvite = htmlspecialchars($_POST['nom']);
                $prenomInvite = htmlspecialchars($_POST['prenom']);
                $courriel = htmlspecialchars($_POST['courriel']);
                $motPasseInvite = htmlspecialchars($_POST['motPasse']);
                $verifMotDePasseInvite = htmlspecialchars($_POST['VerifMotPasse']);

                if($motPasseInvite == $verifMotDePasseInvite) {

                    $motPasse = password_hash($motPasseInvite, PASSWORD_BCRYPT);

                    $userInvite = new User();
                    $userInvite->setUid($user->getId());
                    $userInvite->setNom($nomInvite);
                    $userInvite->setPrenom($prenomInvite);
                    $userInvite->setCourriel($courriel);
                    $userInvite->setMotpasse($motPasse);
                    $userInvite->setEstActif(1);

                    $insertUserInvite = new UserRepository();
                    $insertUserInvite->update($userInvite);

                    if (!empty($insertUserInvite)) {
                        $_SESSION['nom'] = $userInvite->getNom();
                        $_SESSION['prenom'] = $userInvite->getPrenom();
                        $_SESSION['courriel'] = $userInvite->getCourriel();
                        $_SESSION['uid'] = $userInvite->getId();
                    }

                    header('Location: listeGroupes.php');

                } else {
                    $erreur = "Vos mots de passe ne correspondent pas... Veuillez réessayer";
                }
            } else {
                $erreur = "Veuillez compléter tous les champs !";
            }
        }
    }
    if(!is_null($user->getPrenom()) && !is_null($user->getNom()) && !empty($user->getCourriel()) && !is_null($user->getMotPasse()) && ($user->isEstActif() == 1)) {
        if($auth->isLogged()) {
            header('Location: listeGroupes.php');
        } else {
           $auth->forceUserToLogin();
        }
    }

} else {
    //Inscription normale
    if(isset($_POST['butssub'])) {
        if(!empty($_POST['nom']) AND !empty($_POST['prenom'])
            AND !empty($_POST['motPasse']) AND !empty($_POST['VerifMotPasse'])) {
            $nom = htmlspecialchars($_POST['nom']);
            $prenom = htmlspecialchars($_POST['prenom']);
            $motPasse = htmlspecialchars($_POST['motPasse']);
            $courriel = htmlspecialchars($_POST['courriel']);
            $verifMotDePasse = htmlspecialchars($_POST['VerifMotPasse']);


            if($motPasse == $verifMotDePasse) {
                $motPasse = password_hash($motPasse, PASSWORD_BCRYPT);

                //Vérifier si le courriel existe déjà dans la BDD
                $courrielEstExistant = $courrielEstExistant->verifyIfUserIsInDB($courriel);
                if ($courrielEstExistant) {
                    $erreur =  "Ce courriel est déjà utilisé";
                } else {
                    $newUser = new User();
                    $newUser->setNom($nom);
                    $newUser->setPrenom($prenom);
                    $newUser->setCourriel($courriel);
                    $newUser->setMotpasse($motPasse);
                    $newUser->setEstActif(1);

                    $insertUser = new UserRepository();
                    $insertUser->createUser($newUser);


                    if (!empty($insertUser)) {
                        $_SESSION['nom'] = $newUser->getNom();
                        $_SESSION['prenom'] = $newUser->getPrenom();
                        $_SESSION['courriel'] = $newUser->getCourriel();
                        $_SESSION['uid'] = $newUser->getId();
                    }
                    header('Location: listeGroupes.php');
                }
            } else {
                $erreur = "Vos mots de passe ne correspondent pas... Veuillez réessayer";
            }

        } else {
            $erreur = "Veuillez compléter tous les champs !";
        }
    }

}
?>
<?php
require_once("php/nav.php");
?>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
<?php endif; ?>
<main class="formulaire" id="formWithoutBreadcrumb">
        <section class="form" id="registerFormAndExpenseForm">
          <h1>Création de votre compte</h1>
          <form class="formulaireBase" action="<?php echo htmlentities($_SERVER["PHP_SELF"]) . '?'.http_build_query($_GET); ?>" method="post">
              <section class="register">
              <label for="nom">Nom*</label><input id="nom" name="nom" type="text" value="<?php if(isset($_POST['nom'])) echo htmlspecialchars($_POST['nom']) ?>" placeholder="Doe" autofocus autocomplete ="off" required>
              <label for="prenom">Prénom*</label><input id="prenom" name="prenom" type="text" value="<?php if(isset($_POST['prenom'])) echo htmlspecialchars($_POST['prenom']) ?>" placeholder="John" autocomplete ="off" required>
              <?php
              if(!empty($_GET['gid']) && $_GET['uid']): {
                  $gid = $_GET['gid'];
                  $uid = $_GET['uid'];
                  $user = $userRepository->getUserById($uid);
                  $userCourrielParticipant = $user->getCourriel();
              }
              ?>
              <label for="courriel">Adresse e-mail*</label><input id="courriel" name="courriel" placeholder="Adresse e-mail" type="email" value="<?php echo $userCourrielParticipant ?>" readonly autocomplete ="off" >
              <?php else: ?>
              <label for="courriel">Adresse e-mail*</label><input id="courriel" name="courriel" placeholder="John.Doe@gmail.com" type="email" value="<?php if(isset($_POST['courriel'])) echo htmlspecialchars($_POST['courriel']) ?>" autocomplete ="off" required>
              <?php endif; ?>
              <label for="motPasse">Mot de passe*</label><input id="motPasse" name="motPasse" placeholder="•••••••••••••" type="password" autocomplete ="off" required>
              <label for="VerifMotPasse">Confirmer le mot de passe*</label><input id="VerifMotPasse" placeholder="•••••••••••••" name="VerifMotPasse" type="password" autocomplete ="off" required >
              <input type="submit" name="butssub" value="S'inscrire">
              </section>
          </form>
            <a href="index.php" title="Créer un compte"> Vous possédez déjà un compte ? Identifiez-vous.</a>
        </section>
    </main>
<?php
include("inc/footer.inc.php");
?>
<?php
require_once("inc/db_link.inc.php");
$titre = 'Connexion';
require_once("inc/header.inc.php");
require_once ("traitementIndex.php");
?>
<?php
require("php/nav.php")
?>
<?php
if(isset($erreur)): ?>
    <span class="notification" id="notificationErreur"><?= $erreur ?></span>
    <?php endif; ?>
<?php if(isset($danger)): ?>
    <span class="notification" id="notificationDanger"><?= $danger ?></span>
<?php endif; ?>
<?php if(isset($check)): ?>
    <span class="notification" id="notificationCheck"><?= $check ?></span>
<?php endif; ?>
<main class="formulaire" id="formWithoutBreadcrumb">
  <section class="form">
    <h1>Connexion</h1>
      <!--Listegroupes.php--> <!--index.php-->
    <form class="formulaireBase" action="index.php" method="post">
      <section class="login">
        <label for="courriel">Adresse e-mail*</label><input id="courriel" name="courriel" type="email" placeholder="John.doe@gmail.com" value="<?php if(isset($_POST['courriel'])) echo htmlspecialchars($_POST['courriel']) ?>" autocomplete ="off" required >
        <label for="motPasse">Mot de passe*</label><input id="motPasse" name="motPasse" type="password" placeholder="•••••••••••••" autocomplete ="off" required>
      </section>
      <section class="login">
        <input class="boutonConnexion" type="submit" name="buttsub" value="Se connecter">
      </section>
    </form>
    <a href="motDePasseOublie.php"> Mot de passe oublié ? </a>
    <a href="inscription.php"  title="Créer un compte"> Vous n'avez pas de compte ? Créez-en un maintenant.</a>
  </section>
</main>
<?php
include("inc/footer.inc.php");
?>
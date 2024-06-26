<?php
require_once ("php/session.php");
require_once("php/user.php");
$auth = new Session();
USE App\UserRepository;
?>
<header>
    <a class="logo" href="index.php"><img src="./images/logoNoDebtV2.png" alt="Logo"></a>
    <nav class="menuHeader">
      <?php
        if(isset($_SESSION['uid'])):
            // Une fois que l'utilisateur est connecté
            $userRepo = new UserRepository();
            $user = $userRepo->getUserById($_SESSION['uid']);
            ?>
            <ul class="left">
                <li><a href="listeGroupes.php"><i class="fas fa-users"></i>Groupes</a></li>
                <li><a href="contact.php"><i class="fas fa-comment-dots"></i>Contact</a></li>
            </ul>
            <ul class="menuProfil">
                <li><a href="#"><i class="fas fa-user-circle"></i><?php echo $auth->getUserFirstName() . " " . $auth->getUserLastName() ?></a>
                    <ul class="menuDeroulantProfil">
                        <li><a href="editerProfil.php">Editer profil</a></li>
                        <li><a href="supprimerProfil.php">Supprimer profil</a></li>
                        <li><a href="./php/deconnexion.php">Se déconnecter</a>
                        </li>
                    </ul>
                </li>
            </ul>
        <?php else: ?>
            <ul class="left">
                <li><a href="index.php"><i class="fas fa-home"></i>Accueil</a></li>
                <li><a href="contact.php"><i class="fas fa-comment-dots"></i>Contact</a></li>
            </ul>
            <ul class="right">
                <li class="menuRightLogin"><a href="index.php">Se connecter</a></li>
                <li class="menuRightRegister"><a href="inscription.php">S'inscrire</a></li>
            </ul>

        <?php
        endif;
        ?>
    </nav>
</header>

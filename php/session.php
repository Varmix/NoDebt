<?php
class Session {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) //On vérifie qu'aucune session n'existe
            session_start();
    }
    /**
     * Affiche l'uid d'un utilisateur
     */
    public function getUid() {
        return $_SESSION['uid'];
    }
    /*
     * Vérifie si un utilisateur est connecté
     */
    public function isLogged() {
       return !empty($_SESSION['uid']);
    }
    /*
     * Force l'utilisateur à se connecter
     */
    public function forceUserToLogin() {
        if(!$this->isLogged()) {
            header('Location: ./index.php');
            exit();
        }
    }
    /*
     * Récupère le prénom de l'utilisateur
     */
    public function getUserFirstName()
    {
        return $_SESSION['prenom'];
    }
    /*
     * Récupère le nom de l'utilisateur
     */
    public function getUserLastName()
    {
        return $_SESSION['nom'];
    }

    /**
     * @return mixed
     */
    public function getUserMail()
    {
        return $_SESSION['courriel'];
    }




    public function login(User $user)
    {
        $_SESSION['uid'] = $user->getId();
        $_SESSION['courriel'] = $user->getCourriel();
        $_SESSION['prenom'] = $user->getPrenom();
        $_SESSION['nom'] = $user->getNom();
    }
}
?>

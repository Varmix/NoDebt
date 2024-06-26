<?php
namespace App;
require_once("./inc/db_link.inc.php");
require_once("./inc/config.inc.php");
use DB\DBLink;
class User
{
    private $uid;
    private $nom;
    private $prenom;
    private $courriel;
    private $motpasse;
    private $estactif;

    /**
     * @param $uid
     * @param $nom
     * @param $prenom
     * @param $courriel
     * @param $estactif
     */


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }



    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return mixed
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @return mixed
     */
    public function getCourriel()
    {
        return $this->courriel;
    }

    public function getMotPasse()
    {
        return $this->motpasse;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @param mixed $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @param mixed $courriel
     */
    public function setCourriel($courriel)
    {
        $this->courriel = $courriel;
    }

    /**
     * @param mixed $motpasse
     */
    public function setMotpasse($motpasse)
    {
        $this->motpasse = $motpasse;
    }

    public function isEstActif()
    {
        return $this->estactif;
    }



    public function setEstActif($estactif) {
        $this->estactif = $estactif;
        return $this;
    }

    public function checkMotPasse($motPasse) {
        return password_verify($motPasse, $this->motpasse);
    }


    /**
     * Authentifie un utilisateur
     * @param User $user L'utilisateur à authentifier
     * @return void
     */
    public function login(User $user)
    {
        $_SESSION['uid'] = $user->getId();
        $_SESSION['courriel'] = $user->getCourriel();
        $_SESSION['prenom'] = $user->getPrenom();
        $_SESSION['nom'] = $user->getNom();
    }






}

class UserRepository {

    CONST TABLE_NAME = 'nodebt_utilisateur';
    /**
     * Retourne le membre correspondant à un identifiant unique
     * @param int $uid Identifiant unique du membre
     * @return User|null Le membre associé à l'identifiant unique ou null s'il n'existe pas
     */
    public function getUserById($uid, $message = null)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE uid = :uid");
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            if ($stmt->execute() && $stmt->rowCount() === 1) {
                $result = $stmt->fetchObject(User::class);
            }
            $stmt = null;
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * @param $courriel
     * @return User|null
     */
    public function getUserByMail($courriel)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE courriel = :courriel");
            $stmt->bindValue(':courriel', $courriel, \PDO::PARAM_INT);
            if ($stmt->execute() && $stmt->rowCount() === 1) {
                $result = $stmt->fetchObject(User::class);
            }
            $stmt = null;
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }
    /**
     * Enregistre l'utilisateur dans la base de données.
     * @param User $user Le membre à ajouter
     */
    public function createUser(User &$user, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (courriel, nom, prenom, motPasse, estActif) VALUES (:courriel, :nom, :prenom, :motPasse, :estActif)");
            $stmt->bindValue(':courriel', $user->getCourriel());
            $stmt->bindValue(':nom', $user->getNom());
            $stmt->bindValue(':prenom', $user->getPrenom());
            $stmt->bindValue(':motPasse', $user->getMotPasse());
            $stmt->bindValue(':estActif', $user->isEstActif(), \PDO::PARAM_BOOL);
            $stmt->execute();
            $user->setUid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }
    /**
     * @param PDO $bdd
     * @param $courriel
     * @return mixed
     */
   public function verifyIfUserIsInDB($courriel)
    {
        $bdd = DBLink::connect2db(MYDB, $message);
        $stmt = $bdd->prepare('SELECT * FROM nodebt_utilisateur WHERE courriel = ?');
        $stmt->execute([$courriel]);
        $courrielEstExistant = $stmt->fetch();
        return $courrielEstExistant;
    }

    /** Met à jour les informations de l'utilisateur
     * @param User $user
     * @param $errorMessage
     * @return void
     */
    public function update(User $user, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME .
                " SET courriel = :courriel, nom = :nom, prenom = :prenom, motPasse = :motPasse, estActif = :estActif WHERE uid = :uid");
            $stmt->bindValue(':uid', $user->getId(), \PDO::PARAM_INT);
            $this->bindUserValues($stmt, $user);

        } catch(\PDOException $e) {
            $errorMessage .= $e->getCode();
        }
        DBLink::disconnect($bdd);
    }

    private function bindUserValues(\PDOStatement &$stmt, User $user)
    {
        $stmt->bindValue(':courriel', $user->getCourriel());
        $stmt->bindValue(':nom', $user->getNom());
        $stmt->bindValue(':prenom', $user->getPrenom());
        $stmt->bindValue(':motPasse', $user->getMotPasse());
        $stmt->bindValue(':estActif', $user->isEstActif(), \PDO::PARAM_BOOL);
        $stmt->execute();
        $stmt = null;
    }

    /**
     * Enregistre un utilisateur n'ayant pas de compte uniquement sur base de son courriel
     * @param User $user Le membre à ajouter
     */
    public function createUserParticipant(User &$user, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (courriel) VALUES (:courriel)");
            $stmt->bindValue(':courriel', $user->getCourriel());
            $stmt->execute();
            $user->setUid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /** Met à jour les informations d'un utilisateur invité
     * @param User $user
     * @param $errorMessage
     * @return void
     */
    public function updateAnParticipant(User $user, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME .
                " SET nom = :nom, prenom = :prenom, motPasse = :motPasse, estActif = :estActif WHERE uid = :uid");
            $stmt->bindValue(':uid', $user->getId(), \PDO::PARAM_INT);
            $stmt->bindValue(':nom', $user->getNom());
            $stmt->bindValue(':prenom', $user->getPrenom());
            $stmt->bindValue(':motPasse', $user->getMotPasse());
            $stmt->bindValue(':estActif', $user->isEstActif(), \PDO::PARAM_BOOL);

        } catch(\PDOException $e) {
            $errorMessage .= $e->getCode();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Met à jour un mot de passe sur base du courriel
     * @param $courriel
     * @param $errorMessage
     * @return void
     */
    public function updatePasword(User &$user, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME .
                " SET motPasse = :motPasse WHERE courriel = :courriel");
            $stmt->bindValue(':motPasse', $user->getMotPasse());
            $stmt->bindValue(':courriel', $user->getCourriel());
            $stmt->execute();
        } catch(\PDOException $e) {
            $errorMessage .= $e->getCode();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Retourne le nombre de participation à un groupe sur base du uid
     * @param $uid
     * @return mixed|null
     */
    public function NumberOfParticipant($uid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT count(*) as nbParticipation FROM " . self::TABLE_NAME .
                " u JOIN nodebt_participer p ON p.uid = u.uid
                 WHERE p.uid = :uid AND p.estConfirme = 1 ");
            $stmt->bindValue(":uid", $uid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Retourne le nombre d'invitations à un groupe sur base du uid
     * @param $uid
     * @return mixed|null
     */
    public function NumberOfInvitation($uid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT count(*) as nbInvitation FROM " . self::TABLE_NAME .
                " u JOIN nodebt_participer p ON p.uid = u.uid
                 WHERE p.uid = :uid AND p.estConfirme = 0");
            $stmt->bindValue(":uid", $uid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Récupérer le nom d'un utilisateur
     * @param $uid
     * @return mixed|null
     */
    public function getNameOfAUser($uid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT concat(prenom, ' ', nom) as nomParticipant FROM " . self::TABLE_NAME .
                " WHERE uid = :uid ");
            $stmt->bindValue(":uid", $uid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Active le compte d'un ancien utilisateur
     * @param $uid
     * @return void
     */
    public function updateAnOldUser($uid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME .
                " SET estActif = 1 WHERE uid = :uid");
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Désactive le compte d'un utilisateur sur base de son id
     * @param $uid
     * @return void
     */
    public function removeAnUser($uid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("UPDATE " . self::TABLE_NAME .
                " SET estActif = 0 WHERE uid = :uid");
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }



}








<?php
namespace App;
use DB\DBLink;
require_once("user.php");
require_once("./inc/db_link.inc.php");
USE App\User;
class Groupe {

    private $gid;
    private $nom;
    private $devise;
    private $uid;


    /**
     * Retourne l'id du groupe
     * @return mixed
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * Modifie l'id du groupe
     * @param mixed $gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }

    /**
     * Récupère le nom du groupe
     * @return mixed
     */
    public function getNomGroupe()
    {
        return $this->nom;
    }

    /**
     * Modifie le nom du groupe
     * @param mixed $nom
     */
    public function setNomGroupe($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Récupère la devise d'un groupe
     * @return mixed
     */
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Modifie la devise d'un groupe
     * @param mixed $devise
     */
    public function setDevise($devise)
    {
        $this->devise = $devise;
    }

    /**
     * @return mixed
     */
    public function getUid()
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
}
class GroupRepository  {
    CONST TABLE_NAME = 'nodebt_groupe';

    /**
     * Enregistre le groupe dans la base de données.
     * @param Groupe $groupe Le groupe à ajouter
     * @param User $user Le membre créateur du groupe
     */
    public function createGroup(Groupe &$groupe, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (nom, devise, uid) VALUES (:nom, :devise, :uid)");
            $stmt->bindValue(':nom', $groupe->getNomGroupe());
            $stmt->bindValue(':devise', $groupe->getDevise());
            $stmt->bindValue(':uid', $groupe->getUid());
            $stmt->execute();
            $groupe->setGid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }


    public function getGroupe($userId)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT * FROM " . self::TABLE_NAME .
                " WHERE uid = :uid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function getLastIdGroup($userId)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT MAX(gid) as lastGid FROM " . self::TABLE_NAME .
                " WHERE uid = :uid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function getNameOfCreator($userId, $gid) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" SELECT concat(u.prenom, ' ', u.nom) as createur FROM " . self::TABLE_NAME .
                " g JOIN nodebt_utilisateur u ON u.uid = g.uid 
                JOIN nodebt_participer p ON g.gid = p.gid
                WHERE p.uid = :uid AND p.gid = :gid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result =  $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Cette méthode retourne les informations tels que le createur, le montant total dépensé, la devise du groupe
     * @param $gid
     * @return array|false|null
     */
    public function getInformationsGroup($userId)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT g.gid, g.nom, g.devise, g.uid, concat(u.prenom, ' ', u.nom) as createur, sum(d.montant) as totalDepenses FROM " . self::TABLE_NAME .
                " g JOIN nodebt_utilisateur u ON u.uid = g.uid 
                LEFT OUTER JOIN nodebt_depense d ON g.gid = d.gid
                WHERE g.uid = :uid AND p.uid = :uid;
                GROUP BY g.gid, g.nom, g.devise, g.uid, p;uid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result =  $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Affiche les groupes à l'utilisateur où il a confirmé sa présence.
     * @param $userId
     * @return array|false|null
     */
    public function affichageGroupe($userId) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT g.gid as numDuGroupe, g.nom as nomDuGroupe,concat(u.prenom, ' ', u.nom) as createur, ROUND(sum(d.montant), 2) as totalDepenses, p.uid as numParticipant, SUBSTRING(g.devise, -2, 1) as symboleDevise FROM " . self::TABLE_NAME .
                " g JOIN nodebt_utilisateur u ON u.uid = g.uid 
                JOIN nodebt_participer p ON g.gid = p.gid
                LEFT OUTER JOIN nodebt_depense d ON g.gid = d.gid
                WHERE p.uid = :uid AND p.estConfirme = 1
                GROUP BY p.uid, g.gid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result =  $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /*
     * Cette méthode retourne les groupes auxquels participe l'utilisateur
     */
    public function getGroupsOfUsers($userId) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT g.gid as numDuGroupe, g.uid as numeroDuCreateur, p.uid as numueroDuParticipant FROM " . self::TABLE_NAME .
                " g JOIN nodebt_participer p WHERE g.gid = p.gid AND p.uid = :uid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    public function affichageGroupeInvitation($userId) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT g.gid as numDuGroupe, g.nom as nomDuGroupe,concat(u.prenom, ' ', u.nom) as createur, ROUND(sum(d.montant), 2) as totalDepenses, p.uid as numParticipant, SUBSTRING(g.devise, -2, 1) as symboleDevise FROM " . self::TABLE_NAME .
                " g JOIN nodebt_utilisateur u ON u.uid = g.uid 
                JOIN nodebt_participer p ON g.gid = p.gid
                LEFT OUTER JOIN nodebt_depense d ON g.gid = d.gid
                WHERE p.uid = :uid AND p.estConfirme = 0
                GROUP BY p.uid, g.gid");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result =  $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }
    /*
     * Récupère le nom du groupe
     */
    public function nameAndCurrencyOfTheGroup($gid) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT nom as name, devise as currency FROM " . self::TABLE_NAME .
                " WHERE gid = :gid ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result =  $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Met à jour un groupe
     * @param Depense $depense
     * @return void
     */
    public function updateAGroup(Groupe &$groupe)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET nom = :nom, devise = :devise  WHERE gid = :gid");
            $stmt->bindValue(':nom', $groupe->getNomGroupe());
            $stmt->bindValue(':devise', $groupe->getDevise());
            $stmt->bindValue(':gid', $groupe->getGid());
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    public function deleteAGroup($gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" DELETE FROM " . self::TABLE_NAME . "  WHERE gid = :gid ");
            $stmt->bindValue(':gid', $gid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }


    /**
     * @param $gid
     * @return mixed|null
     * Récupère l'id du créateur
     */
    public function idOfCreator($gid) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT uid as idCreateur  FROM " . self::TABLE_NAME .
                " WHERE gid = :gid ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $result =  $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }










}
?>

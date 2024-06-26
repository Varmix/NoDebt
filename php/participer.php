<?php
namespace App;
require_once ("./inc/db_link.inc.php");
USE DB\DBLink;
class Participer {

    private $uid;
    private $gid;
    private $estconfirme;

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

    /**
     * @return mixed
     */
    public function getGid()
    {
        return $this->gid;
    }

    /**
     * @param mixed $gid
     */
    public function setGid($gid)
    {
        $this->gid = $gid;
    }

    /**
     * @return mixed
     */
    public function getEstconfirme()
    {
        return $this->estconfirme;
    }

    /**
     * @param mixed $estconfirme
     */
    public function setEstconfirme($estconfirme)
    {
        $this->estconfirme = $estconfirme;
    }



}

class ParticiperRepository {

    CONST TABLE_NAME = 'nodebt_participer';

    public function createParticipant(Participer &$participant, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (uid, gid, estConfirme) VALUES (:uid, :gid, :estConfirme)");
            $stmt->bindValue(':uid', $participant->getUid());
            $stmt->bindValue(':gid', $participant->getGid());
            $stmt->bindValue(':estConfirme', $participant->getEstconfirme());
            $stmt->execute();
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Cette méthode retourne la liste des participants à un groupe.
     * @param $gid
     * @return array|false|null
     */
    public function getUsersOfAGroups($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT u.uid as uid, concat(u.prenom, ' ', u.nom) as participant FROM " . self::TABLE_NAME .
                " p JOIN nodebt_groupe g ON p.gid = g.gid
                    JOIN nodebt_utilisateur u ON p.uid = u.uid
                 WHERE g.gid = :gid AND p.estConfirme = 1 ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Vérifie si un participant est bien dans le groupe
     * @param $userId
     * @param $gid
     * @return mixed|null
     *
     */
    public function VerifyIfUserIsAnParticipant($userId, $gid) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT p.gid as numDuGroupe, p.uid as numueroDuParticipant FROM " . self::TABLE_NAME .
                " p WHERE p.gid = :gid AND p.uid = :uid AND estConfirme = 1");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, \PDO::PARAM_INT);
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
     * Confirmer une participation à groupe
     * @param $uid
     * @param $gid
     * @return void
     */
    public function participantConfirmation($uid, $gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET estConfirme = 1 WHERE uid = :uid AND gid = :gid");
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /** Décliner une invitation
     * @param $uid
     * @param $gid
     * @return void
     */
    public function declinationOfAParticipant($uid, $gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . "  WHERE uid = :uid AND gid = :gid ");
            $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Supprimer tous les participants d'un groupe
     * @param $gid
     * @return void
     */
    public function DeleteAllParticipantsOfAGroup($gid)
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
     * Nombre de participants dans un groupe
     * @param $gid
     * @return mixed|null
     */
    public function NumberOfParticipant($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT count(p.uid) as nbParticipants FROM " . self::TABLE_NAME .
                " p JOIN nodebt_groupe g ON p.gid = g.gid
                    JOIN nodebt_utilisateur u ON p.uid = u.uid
                 WHERE g.gid = :gid AND p.estConfirme = 1 ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Vérifie si un utilisateur est un participant
     * @param $userId
     * @param $gid
     * @return mixed|null
     */
    public function VerifyIfUserIsAGuest($userId, $gid) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT p.gid as numDuGroupe, p.uid as numueroDuParticipant FROM " . self::TABLE_NAME .
                " p WHERE p.gid = :gid AND p.uid = :uid AND estConfirme = 0");
            $stmt->bindValue(":uid", $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':gid', $gid, \PDO::PARAM_INT);
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
     * Compte le nombre de groupes où de l'utilisateur actuel
     * @param $userId
     * @param $gid
     * @return mixed|null
     */
    public function NumberOfParticipantInGroupsForAnUser($userId) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT COUNT(*) as nbParticipation FROM " . self::TABLE_NAME .
                " p JOIN nodebt_utilisateur u on u.uid = p.uid 
                WHERE p.uid = :uid ");
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



}

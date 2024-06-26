<?php
namespace App;
use DB\DBLink;
require_once("user.php");
require_once("./inc/db_link.inc.php");
class Tag {
    private $tid;
    private $tag;
    private $gid;

    /**
     * @return mixed
     */
    public function getTid()
    {
        return $this->tid;
    }

    /**
     * @param mixed $tid
     */
    public function setTid($tid)
    {
        $this->tid = $tid;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
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


}

class TagRepository {

    CONST TABLE_NAME = 'nodebt_tag';

    /**
     * Ajoute un tag en BDD
     * @param Tag $tag
     * @param $errorMessage
     * @return void
     */
    public function addTag(Tag &$tag,  &$errorMessage = null) {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" INSERT INTO " . self::TABLE_NAME .
                " (tag, gid) VALUES (:tag, :gid)");
            $stmt->bindValue(':tag', $tag->getTag());
            $stmt->bindValue(':gid', $tag->getGid());
            $stmt->execute();
            $tag->setTid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Ajout du tag lié à une dépense dans la table caractériser
     * @param $tid
     * @param $did
     * @param $errorMessage
     * @return void
     */
    public function addTagCaracterisiter($tid, $did,  &$errorMessage = null) {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO  nodebt_caracteriser 
                (did, tid) VALUES (:did, :tid)");
            $stmt->bindValue(':did', $did, \PDO::PARAM_INT);
            $stmt->bindValue(':tid', $tid);
            $stmt->execute();
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Récupère le dernier tid sur base du gid
     * @param $gid
     * @return mixed|null
     */
    public function getLatestTag($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT MAX(tid) as lastTid FROM " . self::TABLE_NAME .
                " WHERE gid = :gid");
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
     * Met à jour le nom du tag sur base du tid
     * @param Tag $tag
     * @param $tid
     * @return void
     */
    public function updateATag(Tag &$tag, $tid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET tag = :tag WHERE tid = :tid");
            $stmt->bindValue(':tag', $tag->getTag());
            $stmt->bindValue(':tid', $tid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Supprime de la table nodebt_caracteriser le did et tid
     * @param $fid
     * @param $did
     * @return void
     */
    public function deleteATagLinkedToAnExpense($did, $tid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM nodebt_caracteriser  WHERE did = :did AND tid = :tid ");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->bindValue(":tid", $tid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }
    /*
     * Supprime le tag de la table nodebt_tag lié à la dépensé
     */
    public function removeAnExpenseTag($tid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . "  WHERE tid = :tid");
            $stmt->bindValue(":tid", $tid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /*
     * Supprime le tag de la table nodebt_tag lié à la dépensé
     */
    public function removeAnExpenseTagOnGidParam($gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . "  WHERE gid = :gid");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Supprime de la table nodebt_caracteriser le did et tid
     * @param $fid
     * @param $did
     * @return void
     */
    public function deleteATagLinkedToAnExpenseOnDidParam($did)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM nodebt_caracteriser  WHERE did = :did");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Détermine si un tag est lié à une dépense ou non
     * @param $did
     * @return mixed|null
     */
    public function TagLinkedAnExpenseOrNot($did)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT count(*) as nbTag
                FROM nodebt_caracteriser
                WHERE did = :did");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
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

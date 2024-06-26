<?php
namespace App;
use DB\DBLink;
require_once("user.php");
require_once("./inc/db_link.inc.php");
class Versement {

    private $gid;
    private $uid;
    private $uid1;
    private $dateheure;
    private $montant;
    private $estconfirme;

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
    public function getUid1()
    {
        return $this->uid1;
    }

    /**
     * @param mixed $uid1
     */
    public function setUid1($uid1)
    {
        $this->uid1 = $uid1;
    }

    /**
     * @return mixed
     */
    public function getDateheure()
    {
        return $this->dateheure;
    }

    /**
     * @param mixed $dateheure
     */
    public function setDateheure($dateheure)
    {
        $this->dateheure = $dateheure;
    }

    /**
     * @return mixed
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * @param mixed $montant
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;
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
class VersementRepository {

    CONST TABLE_NAME = 'nodebt_versement';

    /**
     * Ajoute un versement en BDD
     * @param Versement $versement
     * @param $errorMessage
     * @return void
     */
    public function addVersement(Versement &$versement, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (gid, uid, uid_1, dateHeure, montant, estConfirme) VALUES (:gid, :uid, :uid1, :dateHeure, :montant, :estConfirme)");
            $stmt->bindValue(':gid', $versement->getGid());
            $stmt->bindValue(':uid', $versement->getUid());
            $stmt->bindValue(':uid1', $versement->getUid1());
            $stmt->bindValue(':dateHeure', $versement->getDateheure());
            $stmt->bindValue(':montant', $versement->getMontant());
            $stmt->bindvalue(':estConfirme', $versement->getEstconfirme());
            $stmt->execute();
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Afficher les dépenes avec un tag
     * @param $gid
     * @return array|false|null
     */
    public function voirVersement($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT DATE_FORMAT(v.dateHeure, '%d-%m-%Y %H:%i') as dateHeure , v.uid as debiteur, v.uid_1 as crediteur, v.montant as montant, v.estConfirme as statut   FROM " . self::TABLE_NAME . " 
            v 
            JOIN nodebt_groupe g on g.gid = v.gid
            WHERE v.gid = :gid ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $stmt->execute();
            $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Met à jour le statut d'un versement accepté
     * @param Depense $depense
     * @return void
     */
    public function payementStatusAccept($debiteur, $crediteur, $montant, $gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET estConfirme = 1 WHERE uid = :debiteur AND uid_1 = :crediteur AND montant = :montant AND gid = :gid");
            $stmt->bindValue(':debiteur', $debiteur);
            $stmt->bindValue(':crediteur', $crediteur);
            $stmt->bindValue(':montant', $montant);
            $stmt->bindValue(':gid', $gid);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Met à jour le statut d'un versement refusé
     * @param $debiteur, $crediteur, $montant, $gid
     * @return void
     */
    public function payementStatusDecline($debiteur, $crediteur, $montant, $gid)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET estConfirme = -1 WHERE uid = :debiteur AND uid_1 = :crediteur AND  montant = :montant AND gid = :gid");
            $stmt->bindValue(':debiteur', $debiteur);
            $stmt->bindValue(':crediteur', $crediteur);
            $stmt->bindValue(':montant', $montant);
            $stmt->bindValue(':gid', $gid);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

}

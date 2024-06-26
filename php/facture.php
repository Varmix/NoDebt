<?php
namespace App;
require_once ("./inc/db_link.inc.php");
USE DB\DBLink;
class Facture {
    private $fid;
    private $scan;
    private $did;

    /**
     * @return mixed
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param mixed $fid
     */
    public function setFid($fid)
    {
        $this->fid = $fid;
    }

    /**
     * @return mixed
     */
    public function getScan()
    {
        return $this->scan;
    }

    /**
     * @param mixed $scan
     */
    public function setScan($scan)
    {
        $this->scan = $scan;
    }

    /**
     * @return mixed
     */
    public function getDid()
    {
        return $this->did;
    }

    /**
     * @param mixed $did
     */
    public function setDid($did)
    {
        $this->did = $did;
    }


}

class FactureRepository {

    CONST TABLE_NAME = 'nodebt_facture';
    /*
     * Ajoute une facture en BDD
     */
    public function addScan(Facture &$facture, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (scan, did) VALUES (:scan, :did)");
            $stmt->bindValue(':scan', $facture->getScan());
            $stmt->bindValue(':did', $facture->getDid(), \PDO::PARAM_INT);
            $stmt->execute();
            $facture->setFid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Affiche les factures relative à une dépense
     * @param $did
     * @return array|false|null
     */
    public function affichageFacture($did)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT f.fid as numFacture, f.scan as scan FROM " . self::TABLE_NAME . " 
            f JOIN nodebt_depense d ON f.did = d.did
            WHERE f.did = :did ");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute();
            $result = $stmt->fetchAll();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Récupérer le nom d'une facture
     * @param $fid
     * @return array|false|null
     */
    public function recoverAnInvoiceLinkedToAnExpense($fid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT f.scan as scan FROM " . self::TABLE_NAME . " f 
            WHERE f.fid = :fid ");
            $stmt->bindValue(":fid", $fid, \PDO::PARAM_INT);
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $stmt->execute();
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
        return $result;
    }

    /**
     * Supprimer une facture liée à une dépense
     * @param $fid
     * @return array|false|null
     */
    public function DeleteAnInvoiceLinkedToAnExpense($fid, $did)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . "  WHERE fid = :fid AND did = :did ");
            $stmt->bindValue(":fid", $fid, \PDO::PARAM_INT);
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Supprimer une facture liée à une dépense
     * @param $fid
     * @return array|false|null
     */
    public function DeleteAnInvoiceLinkedToAnExpenseJustOnDidParam($did)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("DELETE FROM " . self::TABLE_NAME . "  WHERE did = :did ");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }





}

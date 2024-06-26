<?php
namespace App;
require_once ("./inc/db_link.inc.php");
USE DB\DBLink;
class Depense {
    private $did;
    private $dateheure;
    private $montant;
    private $libelle;
    private $gid;
    private $uid;

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
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param mixed $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
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

class DepenseRepository {

    CONST TABLE_NAME = 'nodebt_depense';

    public function get3LatestDepenses($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT DATE_FORMAT(d.dateHeure, '%d-%m-%Y %H:%i') as dateHeure, ROUND(d.montant, 2) as montant, g.devise, concat(u.prenom, ' ', u.nom, ' ') as payeur, d.libelle as libelle, SUBSTRING(g.devise, -2, 1) as symboleDevise  FROM " . self::TABLE_NAME . " d JOIN nodebt_utilisateur u ON d.uid = u.uid JOIN nodebt_groupe g on g.gid = d.gid WHERE d.gid = :gid ORDER BY d.dateHeure desc limit 3");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Enregistre une dépense dans la base de données
     * @param Depense $depense Le membre à ajouter
     */
    public function addDepense(Depense &$depense, &$errorMessage = null)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("INSERT INTO " . self::TABLE_NAME .
                " (dateHeure, montant, libelle, gid, uid) VALUES (:dateHeure, :montant, :libelle, :gid, :uid)");
            $stmt->bindValue(':dateHeure', $depense->getDateheure());
            $stmt->bindValue(':montant', $depense->getMontant());
            $stmt->bindValue(':libelle', $depense->getLibelle());
            $stmt->bindValue(':gid', $depense->getGid());
            $stmt->bindValue(':uid', $depense->getUid(), \PDO::PARAM_INT);
            $stmt->execute();
            $depense->setDid($bdd->lastInsertId());
        } catch (\PDOException $e) {
            $errorMessage .= $e->getMessage();
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Récupère le(s) utilisateur(s) ainsi que leur(s) dépense(s) au sein d'un groupe
     * @param $gid
     */
    public function getDepensesByUsers($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT ROUND(sum(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom, ' ') as payeur  FROM " . self::TABLE_NAME . " 
            d LEFT OUTER  JOIN nodebt_utilisateur u ON d.uid = u.uid 
            LEFT OUTER  JOIN nodebt_groupe g on g.gid = d.gid 
            WHERE d.gid = :gid 
            GROUP BY u.prenom, u.nom");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Récupère le montant total dépensé par un groupe.
     * @param $gid
     */
    public function getTotalAmount($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT ROUND(sum(d.montant), 2) as montant, SUBSTRING(g.devise, -2, 1) as symboleDevise FROM " . self::TABLE_NAME . " 
            d 
            JOIN nodebt_groupe g on g.gid = d.gid 
            WHERE d.gid = :gid ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Affiche les informations telles que le montant, le nom et prénom du payeur, la date et le libelle d'une dépense sur base de l'id du groupe
     * @param $gid
     * @return array|false|null
     */
    public function affichageDepense($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.did as numDepense, ROUND(CONCAT(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom, ' ') as payeur, d.libelle as libelle, DATE_FORMAT(d.dateHeure, '%d/%m/%Y') as date   FROM " . self::TABLE_NAME . " 
            d JOIN nodebt_utilisateur u ON d.uid = u.uid 
            JOIN nodebt_groupe g on g.gid = d.gid 
            WHERE d.gid = :gid");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Vérifie si la dépense appartient au groupe
     * @param $userId
     * @param $gid
     * @return mixed|null
     */
    public function VerifyIfAnExpenseBelongsTheGroup($gid, $did) {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.gid as numDuGroupeDep, d.did as numDepense FROM " . self::TABLE_NAME .
                " d JOIN nodebt_groupe g ON d.gid = g.gid
                 WHERE g.gid = :gid AND d.did = :did");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->bindValue(':did', $did, \PDO::PARAM_INT);
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
     * Récupérer le dernier id d'une dépense sur base de gid
     * @param $userId
     * @return mixed|null
     */
    public function getLastIdExpense($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT MAX(did) as lastDid FROM " . self::TABLE_NAME .
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
     * Retourne les informations à propos d'une dépense
     * @param $did
     * @return array|false|null
     */
    public function InformationAboutAnExpenese($did)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT ROUND(CONCAT(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom) as payeur, d.libelle as libelle, DATE_FORMAT(d.dateHeure, '%Y-%m-%d %H:%i') as date   FROM " . self::TABLE_NAME . " 
            d JOIN nodebt_utilisateur u ON d.uid = u.uid 
            WHERE d.did = :did");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
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
     * Sélectionne un tag sur base du did
     * @param $gid
     * @return mixed|null
     */
    public function getNameOfATag($did)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT t.tid as tid, t.tag as tag FROM " . self::TABLE_NAME .
                " d JOIN nodebt_groupe g ON d.gid = g.gid
                JOIN nodebt_tag t ON g.gid = t.gid
                JOIN nodebt_caracteriser c ON d.did = c.did
                WHERE t.tid = c.tid AND d.did = :did");
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

    /**
     * Met à jour une dépense
     * @param Depense $depense
     * @return void
     */
    public function updateAnExpense(Depense &$depense)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" UPDATE " . self::TABLE_NAME .
                " SET dateHeure = :dateHeure, montant = :montant, libelle = :libelle, uid = :uid WHERE did = :did");
            $stmt->bindValue(':dateHeure', $depense->getDateheure());
            $stmt->bindValue(':montant', $depense->getMontant());
            $stmt->bindValue(':libelle', $depense->getLibelle());
            $stmt->bindValue(':uid', $depense->getUid());
            $stmt->bindValue(':did', $depense->getDid());
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Supprime une dépense
     * @param Depense $depense
     * @return void
     */
    public function deleteAnExpense($did)
    {
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" DELETE FROM " . self::TABLE_NAME . "  WHERE did = :did ");
            $stmt->bindValue(':did', $did, \PDO::PARAM_INT);
            $stmt->execute();
        } catch(\PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
        DBLink::disconnect($bdd);
    }

    /**
     * Récupère le(s) utilisateur(s) ainsi que leur(s) dépense(s) ou non au sein d'un groupe
     * @param $gid
     */
    public function getDepensesByParticipant($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare(" SELECT p.gid, p.uid as uid, concat(u.prenom, ' ', u.nom) as payeur, ROUND(sum(d.montant),2) as montant FROM " . self::TABLE_NAME . " 
            d right join nodebt_participer p on d.uid = p.uid and d.gid = p.gid
            join nodebt_utilisateur u ON p.uid = u.uid
            WHERE p.gid = :gid AND p.estConfirme = 1
            GROUP by p.gid, p.uid");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Afficher les dépenes avec un tag
     * @param $gid
     * @return array|false|null
     */
    public function affichageDepenseAvecTag($gid)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.did as numDepense, ROUND(CONCAT(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom, ' ') as payeur, d.libelle as libelle, DATE_FORMAT(d.dateHeure, '%d/%m/%Y') as date, t.tag  as tag, SUBSTRING(g.devise, -2, 1) as symboleDevise
            FROM nodebt_depense
            d JOIN nodebt_utilisateur u ON d.uid = u.uid
            JOIN nodebt_groupe g on g.gid = d.gid
            LEFT JOIN nodebt_caracteriser c ON d.did = c.did
            LEFT JOIN nodebt_tag t ON c.tid = t.tid
            WHERE d.gid = :gid ");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
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
     * Affiche les informations telles que le montant, le nom et prénom du payeur, la date et le libelle d'une dépense sur base de l'id du groupe
     * @param $gid
     * @return array|false|null
     */
    public function affichageDepenseRechercheAvecTag($gid, $recherche)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.did as numDepense, ROUND(CONCAT(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom) as payeur, d.libelle as libelle, DATE_FORMAT(d.dateHeure, '%d/%m/%Y') as date, t.tag as tag FROM " . self::TABLE_NAME . "
            d JOIN nodebt_utilisateur u ON d.uid = u.uid 
            JOIN nodebt_groupe g on g.gid = d.gid 
            JOIN nodebt_tag t ON g.gid = t.gid
            JOIN nodebt_caracteriser c ON d.did = c.did
            WHERE d.gid = :gid AND t.tid = c.tid AND (t.tag LIKE :recherche OR d.libelle LIKE :recherche )");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->bindValue(":recherche", '%'.$recherche.'%');
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
     * Affiche les informations telles que le montant, le nom et prénom du payeur, la date et le libelle d'une dépense sur base de l'id du groupe
     * @param $gid
     * @return array|false|null
     */
    public function affichageDepenseRechercheAvanceeAvecTag($gid, $libelle, $tags, $montantMinimum, $montantMaximum, $dateDebut, $dateFin)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.did as numDepense, ROUND(CONCAT(d.montant), 2) as montant, concat(u.prenom, ' ', u.nom) as payeur, d.libelle as libelle, DATE_FORMAT(d.dateHeure, '%d/%m/%Y') as date, t.tag as tag FROM " . self::TABLE_NAME . "
            d JOIN nodebt_utilisateur u ON d.uid = u.uid 
            JOIN nodebt_groupe g on g.gid = d.gid 
            JOIN nodebt_tag t ON g.gid = t.gid
            JOIN nodebt_caracteriser c ON d.did = c.did
            WHERE d.gid = :gid AND t.tid = c.tid AND (t.tag LIKE :tags AND d.libelle LIKE :libelle  AND DATE_FORMAT(d.dateHeure, '%d/%m/%Y') BETWEEN :dateDebut AND :dateFin OR d.montant BETWEEN :montantMinimum AND :montantMaximum)");
            $stmt->bindValue(":gid", $gid, \PDO::PARAM_INT);
            $stmt->bindValue(":tags", '%'.$tags.'%');
            $stmt->bindValue(":libelle", '%'.$libelle.'%');
            $stmt->bindValue(":montantMinimum", $montantMinimum);
            $stmt->bindValue(":montantMaximum", $montantMaximum);
            $stmt->bindValue(":dateDebut", $dateDebut);
            $stmt->bindValue(":dateFin", $dateFin);
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
     * Retourne le nom d'une dépense
     * @param $did
     */
    public function nameOfAnExpense($did)
    {
        $result = null;
        $bdd = null;
        try {
            $bdd = DBLink::connect2db(MYDB, $message);
            $stmt = $bdd->prepare("SELECT d.libelle as libelle FROM " . self::TABLE_NAME . " 
            d 
            WHERE d.did = :did");
            $stmt->bindValue(":did", $did, \PDO::PARAM_INT);
            $stmt->setFetchMode(\PDO::FETCH_OBJ);
            $stmt->execute();
            $result = $stmt->fetch();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
        DBLink::disconnect($bdd);
        return $result;
    }





}

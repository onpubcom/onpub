<?php

/**
 * Manage website-section maps in an Onpub database.
 *
 * This class contains the methods to manage the data contained in an
 * {@link http://onpub.com/pdfs/onpub_schema.pdf OnpubWSMaps table}. An
 * OnpubWSMaps table maps website IDs to section IDs. This allows Onpub to do
 * the following without any duplication of website/section data:
 * - Add a single section to multiple websites.
 * - Add multiple sections to a single website.
 * - Specify the order of sections in a website.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubWSMaps
{
  private $pdo;
  /**
   * @var bool
   */
  public $enableTransactions;

  /**
   * Connect to an Onpub database.
   *
   * All the methods in this class which query the database use the database
   * connection provided by the PDO object required by this constructor.
   * Currently, Onpub only supports MySQL as a database for storing content.
   * Therefore, when constructing the PDO object, only the
   * {@link PHP_MANUAL#ref.pdo-mysql PDO_MYSQL} driver is supported
   * as a PDO {@link PHP_MANUAL#ref.pdo-mysql.connection data source}.
   *
   * All the methods in this class require the
   * {@link http://onpub.com/pdfs/onpub_schema.pdf Onpub schema}
   * to be installed in the PDO-connected database. The {@link OnpubDatabase}
   * class contains methods to manage the Onpub schema. The Onpub
   * schema can also be installed by clicking the "Install the schema" link
   * once logged in to the Onpub web interface. The schema generally only has
   * to be installed once per database.
   *
   * @param PDO $pdo A {@link PHP_MANUAL#function.pdo-construct PHP Data Object}.
   * @param bool $enableTransactions
   */
  function __construct(PDO $pdo, $enableTransactions = TRUE)
  {
    $this->pdo = $pdo;
    $this->enableTransactions = $enableTransactions;
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
  }

  /**
   * @param int $websiteID Delete the map(s) with websiteID.
   * @param int $sectionID Delete the map(s) with sectionID.
   * @return int The number of maps deleted, 0 if no maps were deleted.
   */
  public function delete($websiteID, $sectionID)
  {
    if ($websiteID && $sectionID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubWSMaps WHERE websiteID = :websiteID AND sectionID = :sectionID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':websiteID', $websiteID);
      $stmt->bindParam(':sectionID', $sectionID);
    }

    if ($websiteID && !$sectionID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubWSMaps WHERE websiteID = :websiteID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':websiteID', $websiteID);
    }

    if (!$websiteID && $sectionID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubWSMaps WHERE sectionID = :sectionID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':sectionID', $sectionID);
    }

    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  /**
   * @param OnpubWSMap $wsmap Map whose ID you want to get.
   * @return int The ID of the map. NULL if the map does not exist in the database.
   */
  public function getID(OnpubWSMap $wsmap)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubWSMaps WHERE websiteID = :websiteID AND sectionID = :sectionID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $websiteID = $wsmap->websiteID;
    $sectionID = $wsmap->sectionID;

    $stmt->bindParam(':websiteID', $websiteID);
    $stmt->bindParam(':sectionID', $sectionID);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubWSMap} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL, $websiteID = NULL, $sectionID = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions, $websiteID, $sectionID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $wsmaps = array();

    if ($rows) {
      foreach ($rows as $row) {
        $wsmap = new OnpubWSMap();

        $wsmap->ID = $row["ID"];
        $wsmap->websiteID = $row["websiteID"];
        $wsmap->sectionID = $row["sectionID"];
        $wsmap->setCreated(new DateTime($row["created"]));
        $wsmap->setModified(new DateTime($row["modified"]));

        $wsmaps[] = $wsmap;
      }
    }

    $result->closeCursor();
    return $wsmaps;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL, $websiteID, $sectionID)
  {
    if ($websiteID) $websiteID = ctype_digit($websiteID) ? $websiteID : $this->pdo->quote($websiteID);
    if ($sectionID) $sectionID = ctype_digit($sectionID) ? $sectionID : $this->pdo->quote($sectionID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->dateLimit) {
      $where = "WHERE created <= " . $this->pdo->quote($queryOptions->dateLimit->format('Y-m-d H:i:s'));

      if ($websiteID) {
        $where .= " AND websiteID = $websiteID";
      }
      elseif ($sectionID) {
        $where .= " AND sectionID = $sectionID";
      }
    }
    else {
      if ($websiteID) {
        $where = "WHERE websiteID = $websiteID";
      }
      elseif ($sectionID) {
        $where = "WHERE sectionID = $sectionID";
      }
    }

    if ($queryOptions->orderBy) {
      $orderBy = "ORDER BY " . $queryOptions->orderBy;

      if ($queryOptions->order) {
        $orderBy .= " " . $queryOptions->order;
      }
    }

    if ($queryOptions->getPage() && $queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT " . (($queryOptions->getPage() - 1) * $queryOptions->rowLimit) .
               "," . $queryOptions->rowLimit;
    }
    elseif ($queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT 0," . $queryOptions->rowLimit;
    }

    return "SELECT ID, websiteID, sectionID, created, modified FROM OnpubWSMaps $where $orderBy $limit";
  }

  /**
   * @param mixed $wsmaps A single {@link OnpubWSMap} object or an array of {@link OnpubWSMap} objects (to insert multiple maps at a time).
   * @return mixed The ID(s) of the new map(s). An int will be returned if a single map was inserted. An array of ints will be returned if multiple maps were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($wsmaps)
  {
    $wsmapIDs = array();
    $isArray = TRUE;

    if (!is_array($wsmaps)) {
      $wsmaps = array ($wsmaps);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubWSMaps (ID, websiteID, sectionID, created, modified) VALUES (:ID, :websiteID, :sectionID, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($wsmaps as $wsmap) {
      try {
        $wsmapID = $this->getID($wsmap);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($wsmapID) {
        $wsmapIDs[] = $wsmapID;
        $wsmap->ID = $wsmapID;
      }
      else {
        $ID = $wsmap->ID;
        $websiteID = $wsmap->websiteID;
        $sectionID = $wsmap->sectionID;
        $created = $wsmap->getCreated()->format('Y-m-d H:i:s');
        $modified = $wsmap->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':websiteID', $websiteID);
        $stmt->bindParam(':sectionID', $sectionID);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $wsmapIDs[] = $this->pdo->lastInsertId();
        $wsmap->ID = $this->pdo->lastInsertId();
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    if ($isArray) {
      return $wsmapIDs;
    }
    else {
      return end($wsmapIDs);
    }
  }
}
?>
<?php

/**
 * Manage section-article maps in an Onpub database.
 *
 * This class contains the methods to manage the data contained in an
 * {@link http://onpub.com/pdfs/onpub_schema.pdf OnpubSAMaps table}. An
 * OnpubSAMaps table maps section IDs to article IDs. This allows Onpub to do
 * the following without any duplication of section/article data:
 * - Add a single article to multiple sections.
 * - Add multiple articles to a single section.
 * - Specify the order of articles in a section.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubSAMaps
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
   * @param int $sectionID Delete the map(s) with sectionID.
   * @param int $articleID Delete the map(s) with articleID.
   * @return int The number of maps deleted, 0 if no maps were deleted.
   */
  public function delete($sectionID, $articleID)
  {
    if ($sectionID && $articleID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubSAMaps WHERE sectionID = :sectionID AND articleID = :articleID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':sectionID', $sectionID);
      $stmt->bindParam(':articleID', $articleID);
    }

    if ($sectionID && !$articleID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubSAMaps WHERE sectionID = :sectionID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':sectionID', $sectionID);
    }

    if (!$sectionID && $articleID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubSAMaps WHERE articleID = :articleID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':articleID', $articleID);
    }

    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  /**
   * @param OnpubSAMap $samap Map whose ID you want to get.
   * @return int The ID of the map. NULL if the map does not exist in the database.
   */
  public function getID(OnpubSAMap $samap)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubSAMaps WHERE sectionID = :sectionID AND articleID = :articleID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $sectionID = $samap->sectionID;
    $articleID = $samap->articleID;

    $stmt->bindParam(':sectionID', $sectionID);
    $stmt->bindParam(':articleID', $articleID);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubSAMap} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL, $sectionID = NULL, $articleID = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions, $sectionID, $articleID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $samaps = array();

    if ($rows) {
      foreach ($rows as $row) {
        $samap = new OnpubSAMap();

        $samap->ID = $row["ID"];
        $samap->sectionID = $row["sectionID"];
        $samap->articleID = $row["articleID"];
        $samap->setCreated(new DateTime($row["created"]));
        $samap->setModified(new DateTime($row["modified"]));

        $samaps[] = $samap;
      }
    }

    $result->closeCursor();
    return $samaps;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL, $sectionID = NULL, $articleID = NULL)
  {
    if ($sectionID) $sectionID = ctype_digit($sectionID) ? $sectionID : $this->pdo->quote($sectionID);
    if ($articleID) $articleID = ctype_digit($articleID) ? $articleID : $this->pdo->quote($articleID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->dateLimit) {
      $where = "WHERE created <= "
        . $this->pdo->quote($queryOptions->dateLimit->format('Y-m-d H:i:s'));

      if ($sectionID) {
        $where .= " AND sectionID = $sectionID";
      }
      elseif ($articleID) {
        $where .= " AND articleID = $articleID";
      }
    }
    else {
      if ($sectionID) {
        $where = "WHERE sectionID = $sectionID";
      }
      elseif ($articleID) {
        $where = "WHERE articleID = $articleID";
      }
    }

    if ($queryOptions->orderBy) {
      $orderBy = "ORDER BY " . $queryOptions->orderBy;

      if ($queryOptions->order) {
        $orderBy .= " " . $queryOptions->order;
      }
    }

    if ($queryOptions->getPage() && $queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT "
        . (($queryOptions->getPage() - 1) * $queryOptions->rowLimit) . ","
        . $queryOptions->rowLimit;
    }
    elseif ($queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT 0," . $queryOptions->rowLimit;
    }

    return "SELECT ID, sectionID, articleID, created, modified FROM OnpubSAMaps $where $orderBy $limit";
  }

  /**
   * @param mixed $samaps A single {@link OnpubSAMap} object or an array of {@link OnpubSAMap} objects (to insert multiple maps at a time).
   * @return mixed The ID(s) of the new map(s). An int will be returned if a single map was inserted. An array of ints will be returned if multiple maps were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($samaps)
  {
    $samapIDs = array();
    $isArray = TRUE;

    if (!is_array($samaps)) {
      $samaps = array ($samaps);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubSAMaps (ID, sectionID, articleID, created, modified) VALUES (:ID, :sectionID, :articleID, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($samaps as $samap) {
      try {
        $samapID = $this->getID($samap);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($samapID) {
        $samapIDs[] = $samapID;
        $samap->ID = $samapID;
      }
      else {
        $ID = $samap->ID;
        $sectionID = $samap->sectionID;
        $articleID = $samap->articleID;
        $created = $samap->getCreated()->format('Y-m-d H:i:s');
        $modified = $samap->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':sectionID', $sectionID);
        $stmt->bindParam(':articleID', $articleID);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $samapIDs[] = $this->pdo->lastInsertId();
        $samap->ID = $this->pdo->lastInsertId();
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    if ($isArray) {
      return $samapIDs;
    }
    else {
      return end($samapIDs);
    }
  }

  /**
   * @param OnpubSAMap $samap The map to be updated.
   * @return int 1 if the map was updated. 0 if the map does not exist in the database.
   */
  public function update(OnpubSAMap $samap)
  {
    $stmt = $this->pdo->prepare("UPDATE OnpubSAMaps SET sectionID = :sectionID, articleID = :articleID, created = :created, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $ID = $samap->ID;
    $sectionID = $samap->sectionID;
    $articleID = $samap->articleID;
    $created = $samap->getCreated()->format('Y-m-d H:i:s');
    $modified = $samap->getModified()->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':sectionID', $sectionID);
    $stmt->bindParam(':articleID', $articleID);
    $stmt->bindParam(':created', $created);
    $stmt->bindParam(':modified', $modified);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    return $stmt->rowCount();
  }
}
?>
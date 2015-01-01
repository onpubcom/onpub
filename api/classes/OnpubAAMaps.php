<?php

/**
 * Manage article-author maps in an Onpub database.
 *
 * This class contains the methods to manage the data contained in an
 * {@link http://onpub.com/pdfs/onpub_schema.pdf OnpubAAMaps table}. An
 * OnpubAAMaps table maps article IDs to author IDs. This allows Onpub to do
 * the following without any duplication of article/author data:
 * - Use the same author for multiple articles.
 * - A single article can have multiple authors.
 * - Specify the order of an article's authors.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubAAMaps
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
   * @param int $articleID Delete the map(s) with articleID.
   * @param int $authorID Delete the map(s) with authorID.
   * @return int The number of maps deleted, 0 if no maps were deleted.
   */
  public function delete($articleID, $authorID)
  {
    if ($articleID && $authorID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubAAMaps WHERE articleID = :articleID AND authorID = :authorID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':articleID', $articleID);
      $stmt->bindParam(':authorID', $authorID);
    }

    if ($articleID && !$authorID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubAAMaps WHERE articleID = :articleID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':articleID', $articleID);
    }

    if (!$articleID && $authorID) {
      $stmt = $this->pdo->prepare("DELETE FROM OnpubAAMaps WHERE authorID = :authorID");
      OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);
      $stmt->bindParam(':authorID', $authorID);
    }

    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubAAMap} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $aamaps = array();

    if ($rows) {
      foreach ($rows as $row) {
        $aamap = new OnpubAAMap();

        $aamap->ID = $row["ID"];
        $aamap->articleID = $row["articleID"];
        $aamap->authorID = $row["authorID"];
        $aamap->setCreated(new DateTime($row["created"]));
        $aamap->setModified(new DateTime($row["modified"]));

        $aamaps[] = $aamap;
      }
    }

    $result->closeCursor();
    return $aamaps;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->dateLimit) {
      $where = "WHERE created <= "
        . $this->pdo->quote($queryOptions->dateLimit->format('Y-m-d H:i:s'));
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

    return "SELECT ID, articleID, authorID, created, modified FROM OnpubAAMaps $where $orderBy $limit";
  }

  /**
   * @param OnpubAAMap $aamap Map whose ID you want to get.
   * @return int The ID of the map. NULL if the map does not exist in the database.
   */
  public function getID(OnpubAAMap $aamap)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubAAMaps WHERE articleID = :articleID AND authorID = :authorID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $articleID = $aamap->articleID;
    $authorID = $aamap->authorID;

    $stmt->bindParam(':articleID', $articleID);
    $stmt->bindParam(':authorID', $authorID);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param mixed $aamaps A single {@link OnpubAAMap} object or an array of {@link OnpubAAMap} objects (to insert multiple maps at a time).
   * @return mixed The ID(s) of the new map(s). An int will be returned if a single map was inserted. An array of ints will be returned if multiple maps were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($aamaps)
  {
    $aamapIDs = array();
    $isArray = TRUE;

    if (!is_array($aamaps)) {
      $aamaps = array ($aamaps);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubAAMaps (ID, articleID, authorID, created, modified) VALUES (:ID, :articleID, :authorID, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($aamaps as $aamap) {
      try {
        $aamapID = $this->getID($aamap);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($aamapID) {
        $aamapIDs[] = $aamapID;
        $aamap->ID = $aamapID;
      }
      else {
        $ID = $aamap->ID;
        $articleID = $aamap->articleID;
        $authorID = $aamap->authorID;
        $created = $aamap->getCreated()->format('Y-m-d H:i:s');
        $modified = $aamap->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':articleID', $articleID);
        $stmt->bindParam(':authorID', $authorID);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $aamapIDs[] = $this->pdo->lastInsertId();
        $aamap->ID = $this->pdo->lastInsertId();
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    if ($isArray) {
      return $aamapIDs;
    }
    else {
      return end($aamapIDs);
    }
  }
}
?>
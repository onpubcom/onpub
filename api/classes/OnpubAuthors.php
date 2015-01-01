<?php

/**
 * Manage authors in an Onpub database.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubAuthors
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
   * @return int
   */
  public function count()
  {
    $query = $this->countQuery();
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($row = $result->fetch(PDO::FETCH_ASSOC))) {
      return 0;
    }

    $result->closeCursor();
    return $row["count"];
  }

  private function countQuery()
  {
    return "SELECT COUNT(ID) AS count FROM OnpubAuthors";
  }

  /**
   * @param int $ID ID of the author to get.
   * @return OnpubAuthor An {@link OnpubAuthor} object. NULL if the author does not exist in the database.
   */
  public function get($ID)
  {
    $query = $this->getQuery($ID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($row = $result->fetch(PDO::FETCH_ASSOC))) {
      return NULL;
    }

    $author = new OnpubAuthor();

    $author->ID = $row["ID"];
    $author->imageID = $row["imageID"];
    $author->givenNames = $row["givenNames"];
    $author->familyName = $row["familyName"];
    $author->displayAs = $row["displayAs"];
    $author->url = $row["url"];
    $author->setCreated(new DateTime($row["created"]));
    $author->setModified(new DateTime($row["modified"]));

    $result->closeCursor();
    return $author;
  }

  private function getQuery($ID)
  {
    if ($ID) $ID = ctype_digit($ID) ? $ID : $this->pdo->quote($ID);

    return "SELECT * FROM OnpubAuthors WHERE ID = $ID";
  }

  /**
   * @param OnpubAuthor $author Author whose ID you want to get.
   * @return int The ID of the author. NULL if the author does not exist in the database.
   */
  public function getID(OnpubAuthor $author)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubAuthors WHERE displayAs = :displayAs");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $displayAs = OnpubDatabase::utf8Decode(trim($author->displayAs));

    $stmt->bindParam(':displayAs', $displayAs);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubAuthor} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $authors = array();

    if ($rows) {
      foreach ($rows as $row) {
        $author = new OnpubAuthor();

        $author->ID = $row["ID"];
        $author->imageID = $row["imageID"];
        $author->givenNames = $row["givenNames"];
        $author->familyName = $row["familyName"];
        $author->displayAs = $row["displayAs"];
        $author->url = $row["url"];
        $author->setCreated(new DateTime($row["created"]));
        $author->setModified(new DateTime($row["modified"]));

        $authors[] = $author;
      }
    }

    $result->closeCursor();
    return $authors;
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

    return "SELECT ID, imageID, givenNames, familyName, displayAs, url, created, modified FROM OnpubAuthors $where $orderBy $limit";
  }

  /**
   * @param mixed $authors A single {@link OnpubAuthor} object or an array of {@link OnpubAuthor} objects (to insert multiple authors at a time).
   * @return mixed The ID(s) of the new author(s). An int will be returned if a single author was inserted. An array of ints will be returned if multiple authors were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($authors)
  {
    $oimages = new OnpubImages($this->pdo, FALSE);
    $IDs = array();
    $isArray = TRUE;

    if (!is_array($authors)) {
      $authors = array ($authors);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubAuthors (ID, imageID, givenNames, familyName, displayAs, url, created, modified) VALUES (:ID, :imageID, :givenNames, :familyName, :displayAs, :url, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($authors as $author) {
      if ($author->image) {
        try {
          $imageID = $oimages->insert($author->image);
          $author->imageID = $imageID;
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }

      try {
        $ID = $this->getID($author);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($ID) {
        $IDs[] = $ID;
        $author->ID = $ID;
      }
      else {
        $ID = $author->ID;
        $imageID = $author->imageID;
        $givenNames = OnpubDatabase::utf8Decode(trim($author->givenNames));
        $familyName = OnpubDatabase::utf8Decode(trim($author->familyName));
        $displayAs = OnpubDatabase::utf8Decode(trim($author->displayAs));
        $url = OnpubDatabase::utf8Decode(trim($author->url));
        $created = $author->getCreated()->format('Y-m-d H:i:s');
        $modified = $author->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':imageID', $imageID);
        $stmt->bindParam(':givenNames', $givenNames);
        $stmt->bindParam(':familyName', $familyName);
        $stmt->bindParam(':displayAs', $displayAs);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $IDs[] = $this->pdo->lastInsertId();
        $author->ID = $this->pdo->lastInsertId();
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    if ($isArray) {
      return $IDs;
    }
    else {
      return end($IDs);
    }
  }

  /**
   * @param OnpubAuthor $author The author to be updated.
   * @return int 1 if the author was updated. 0 if the author does not exist in the database.
   */
  public function update(OnpubAuthor $author)
  {
    $now = new DateTime();

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("UPDATE OnpubAuthors SET imageID = :imageID, givenNames = :givenNames, familyName = :familyName, displayAs = :displayAs, url = :url, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $ID = $author->ID;
    $imageID = $author->imageID;
    $givenNames = OnpubDatabase::utf8Decode(trim($author->givenNames));
    $familyName = OnpubDatabase::utf8Decode(trim($author->familyName));
    $displayAs = OnpubDatabase::utf8Decode(trim($author->displayAs));
    $url = OnpubDatabase::utf8Decode(trim($author->url));
    $modified = $now->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':imageID', $imageID);
    $stmt->bindParam(':givenNames', $givenNames);
    $stmt->bindParam(':familyName', $familyName);
    $stmt->bindParam(':displayAs', $displayAs);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':modified', $modified);

    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    return $stmt->rowCount();
  }
}
?>
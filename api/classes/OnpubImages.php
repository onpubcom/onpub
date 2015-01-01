<?php

/**
 * Manage images in an Onpub database.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubImages
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
    return "SELECT COUNT(ID) AS count FROM OnpubImages";
  }

  /**
   * @param mixed $images A single {@link OnpubImage} object or an array of {@link OnpubImage} objects (to insert multiple images at a time).
   * @return mixed The ID(s) of the new image(s). An int will be returned if a single image was inserted. An array of ints will be returned if multiple images were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($images)
  {
    $IDs = array();
    $isArray = TRUE;

    if (!is_array($images)) {
      $images = array ($images);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubImages (ID, websiteID, fileName, description, url, created, modified) VALUES (:ID, :websiteID, :fileName, :description, :url, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($images as $image) {
      try {
        $ID = $this->getID($image);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($ID) {
        $IDs[] = $ID;
        $image->ID = $ID;
      }
      else {
        $ID = $image->ID;
        $websiteID = $image->websiteID;
        $fileName = OnpubDatabase::utf8Decode(trim($image->fileName));
        $description = OnpubDatabase::utf8Decode(trim($image->description));
        $url = OnpubDatabase::utf8Decode(trim($image->url));
        $created = $image->getCreated()->format('Y-m-d H:i:s');
        $modified = $image->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':websiteID', $websiteID);
        $stmt->bindParam(':fileName', $fileName);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $IDs[] = $this->pdo->lastInsertId();
        $image->ID = $this->pdo->lastInsertId();
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
   * @param int $ID ID of the image to get.
   * @return OnpubImage An {@link OnpubImage} object. NULL if the image does not exist in the database.
   */
  public function get($ID)
  {
    $query = $this->getQuery($ID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($row = $result->fetch(PDO::FETCH_ASSOC))) {
      return NULL;
    }

    $website = new OnpubWebsite();

    $website->ID = $row["websiteID"];
    $website->imageID = $row["websiteImageID"];
    $website->name = $row["websiteName"];
    $website->url = $row["websiteURL"];
    $website->imagesURL = $row["websiteImagesURL"];
    $website->imagesDirectory = $row["websiteImagesDirectory"];
    $website->setCreated(new DateTime($row["websiteCreated"]));
    $website->setModified(new DateTime($row["websiteModified"]));

    $image = new OnpubImage();

    $image->ID = $row["ID"];
    $image->websiteID = $row["websiteID"];
    $image->fileName = $row["fileName"];
    $image->description = $row["description"];
    $image->url = $row["url"];
    $image->setCreated(new DateTime($row["created"]));
    $image->setModified(new DateTime($row["modified"]));
    $image->website = $website;

    $result->closeCursor();
    return $image;
  }

  private function getQuery($ID)
  {
    if ($ID) $ID = ctype_digit($ID) ? $ID : $this->pdo->quote($ID);

    return "SELECT images.ID, images.websiteID, images.fileName, " .
           "images.description, images.url, images.created, images.modified, " .
           "website.imageID as websiteImageID, website.name as websiteName, " .
           "website.url as websiteURL, website.imagesURL as websiteImagesURL, " .
           "website.imagesDirectory as websiteImagesDirectory, website.created " .
           "as websiteCreated, website.modified as websiteModified FROM " .
           "OnpubImages as images LEFT JOIN OnpubWebsites as website ON " .
           "images.websiteID = website.ID WHERE images.ID = $ID";
  }

  /**
   * @param OnpubImage $image Image whose ID you want to get.
   * @return int The ID of the image. NULL if the image does not exist in the database.
   */
  public function getID(OnpubImage $image)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubImages WHERE websiteID = :websiteID AND fileName = :fileName");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $websiteID = $image->websiteID;
    $fileName = OnpubDatabase::utf8Decode(trim($image->fileName));

    $stmt->bindParam(':websiteID', $websiteID);
    $stmt->bindParam(':fileName', $fileName);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubImage} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $images = array();

    if ($rows) {
      foreach ($rows as $row) {
        $website = new OnpubWebsite();

        $website->ID = $row["websiteID"];
        $website->imageID = $row["websiteImageID"];
        $website->name = $row["websiteName"];
        $website->url = $row["websiteURL"];
        $website->imagesURL = $row["websiteImagesURL"];
        $website->imagesDirectory = $row["websiteImagesDirectory"];
        $website->setCreated(new DateTime($row["websiteCreated"]));
        $website->setModified(new DateTime($row["websiteModified"]));

        $image = new OnpubImage();

        $image->ID = $row["ID"];
        $image->websiteID = $row["websiteID"];
        $image->fileName = $row["fileName"];
        $image->description = $row["description"];
        $image->url = $row["url"];
        $image->setCreated(new DateTime($row["created"]));
        $image->setModified(new DateTime($row["modified"]));
        $image->website = $website;

        $images[] = $image;
      }
    }

    $result->closeCursor();
    return $images;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->dateLimit) {
      $where = "WHERE images.created <= "
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

    return "SELECT images.ID, images.websiteID, images.fileName, " .
           "images.description, images.url, images.created, images.modified, " .
           "website.imageID as websiteImageID, website.name as websiteName, " .
           "website.url as websiteURL, website.imagesURL as websiteImagesURL, " .
           "website.imagesDirectory as websiteImagesDirectory, website.created " .
           "as websiteCreated, website.modified as websiteModified FROM " .
           "OnpubImages as images LEFT JOIN OnpubWebsites as website ON " .
           "images.websiteID = website.ID $where $orderBy $limit";
  }

  /**
   * @param string $keywords Keyword(s) to search for.
   * @return array All the images which were found as an array of {@link OnpubImage} objects.
   */
  public function search($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->searchQuery($keywords, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $images = array();

    if ($rows) {
      foreach ($rows as $row) {
        $image = new OnpubImage();

        $image->ID = $row["ID"];
        $image->websiteID = $row["websiteID"];
        $image->fileName = $row["fileName"];
        $image->description = $row["description"];
        $image->url = $row["url"];
        $image->setCreated(new DateTime($row["created"]));
        $image->setModified(new DateTime($row["modified"]));

        $images[] = $image;
      }
    }

    $result->closeCursor();
    return $images;
  }

  private function searchQuery($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $keywords = $this->pdo->quote(OnpubDatabase::utf8Decode(trim($keywords)));

    if ($queryOptions->orderBy) {
      $orderBy = $queryOptions->orderBy;
    }
    else {
      $orderBy = "";
    }

    if ($queryOptions->order) {
      $order = $queryOptions->order;
    }
    else {
      $order = "";
    }

    if ($orderBy) {
      if ($order) {
        return "SELECT ID, websiteID, fileName ,description, url, created, modified FROM OnpubImages WHERE ID RLIKE $keywords OR fileName RLIKE $keywords OR description RLIKE $keywords OR modified RLIKE $keywords ORDER BY $orderBy $order";
      }
      else {
        return "SELECT ID, websiteID, fileName ,description, url, created, modified FROM OnpubImages WHERE ID RLIKE $keywords OR fileName RLIKE $keywords OR description RLIKE $keywords OR modified RLIKE $keywords ORDER BY $orderBy";
      }
    }
    else {
      return "SELECT ID, websiteID, fileName ,description, url, created, modified FROM OnpubImages WHERE ID RLIKE $keywords OR fileName RLIKE $keywords OR description RLIKE $keywords OR modified RLIKE $keywords";
    }
  }

  /**
   * @param OnpubImage $image The image to be updated.
   * @return int 1 if the image was updated. 0 if the image does not exist in the database.
   */
  public function update(OnpubImage $image)
  {
    $now = new DateTime();

    $stmt = $this->pdo->prepare("UPDATE OnpubImages SET fileName = :fileName, description = :description, url = :url, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $ID = $image->ID;
    $fileName = OnpubDatabase::utf8Decode(trim($image->fileName));
    $description = OnpubDatabase::utf8Decode(trim($image->description));
    $url = OnpubDatabase::utf8Decode(trim($image->url));
    $modified = $now->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':fileName', $fileName);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':modified', $modified);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  /**
   * @param int $ID ID of the image to delete.
   * @return int 1 if the image was deleted, 0 if the image does not exist in the database.
   */
  public function delete($ID)
  {
    $stmt = $this->pdo->prepare("DELETE FROM OnpubImages WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $stmt->bindParam(':ID', $ID);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  public static function getThumbURL($phpThumbParams, $onpub_dir_phpthumb = '../api/phpThumb/')
  {
    global $PHPTHUMB_CONFIG;

    if ($PHPTHUMB_CONFIG['high_security_enabled']) {
      return $onpub_dir_phpthumb . 'phpThumb.php?' . $phpThumbParams . '&hash=' . md5($phpThumbParams . $PHPTHUMB_CONFIG['high_security_password']);
    }

    return $onpub_dir_phpthumb . 'phpThumb.php?' . $phpThumbParams;
  }
}
?>
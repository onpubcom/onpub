<?php

/**
 * Manage sections in an Onpub database.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubSections
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
    return "SELECT COUNT(ID) AS count FROM OnpubSections";
  }

  /**
   * @param int $ID ID of the section to delete.
   * @return int 1 if the section was deleted, 0 if the section does not exist in the database.
   */
  public function delete($ID)
  {
    $osamaps = new OnpubSAMaps($this->pdo, FALSE);
    $owsmaps = new OnpubWSMaps($this->pdo, FALSE);

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    try {
      $osamaps->delete($ID, NULL);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    try {
      $owsmaps->delete(NULL, $ID);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    $stmt = $this->pdo->prepare("DELETE FROM OnpubSections WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $stmt->bindParam(':ID', $ID);

    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    return $stmt->rowCount();
  }

  /**
   * @param string $keywords Keyword(s) to search for.
   * @return array All the sections which were found as an array of {@link OnpubSection} objects.
   */
  public function search($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->searchQuery($keywords, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $sections = array();

    if ($rows) {
      foreach ($rows as $row) {
        $section = new OnpubSection();

        $section->ID = $row["ID"];
        $section->imageID = $row["imageID"];
        $section->websiteID = $row["websiteID"];
        $section->parentID = $row["parentID"];
        $section->name = $row["name"];
        $section->url = $row["url"];
        $section->setCreated(new DateTime($row["created"]));
        $section->setModified(new DateTime($row["modified"]));

        $sections[] = $section;
      }
    }

    $result->closeCursor();
    return $sections;
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
        return "SELECT ID, imageID, websiteID, parentID, name, url, created, modified FROM OnpubSections WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords AND parentID IS NULL ORDER BY $orderBy $order";
      }
      else {
        return "SELECT ID, imageID, websiteID, parentID, name, url, created, modified FROM OnpubSections WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords AND parentID IS NULL ORDER BY $orderBy";
      }
    }
    else {
      return "SELECT ID, imageID, websiteID, parentID, name, url, created, modified FROM OnpubSections WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords AND parentID IS NULL";
    }
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubSection} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL, $websiteID = NULL, $flatSectionList = TRUE, $parentID = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $query = $this->selectQuery($queryOptions, $websiteID, $parentID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, $this->enableTransactions);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $sections = array();

    if ($rows) {
      foreach ($rows as $row) {
        $section = new OnpubSection();

        $section->ID = $row["ID"];
        $section->imageID = $row["imageID"];
        $section->websiteID = $row["websiteID"];
        $section->parentID = $row["parentID"];
        $section->name = $row["name"];
        $section->url = $row["url"];
        $section->setCreated(new DateTime($row["created"]));
        $section->setModified(new DateTime($row["modified"]));

        $sections[] = $section;
      }
    }

    if ($parentID) {
      if ($this->enableTransactions) {
        $status = $this->pdo->commit();
        OnpubDatabase::verifyTransaction($this->pdo, $status);
      }

      $result->closeCursor();
      return $sections;
    }

    $sectionsassoc = array();

    for ($i = 0; $i < sizeof($sections); $i++) {
      $sectionsassoc[$sections[$i]->ID] = $sections[$i];
    }

    for ($i = 0; $i < sizeof($sections); $i++) {
      $section = $sections[$i];
      $parent = NULL;

      while ($section->parentID) {
        if (isset($sectionsassoc[$section->parentID])) {
          $parent = $sectionsassoc[$section->parentID];
        }
        else {
          // Here's where we have to lookup section from DB.
          $query = $this->getQuery($section->parentID);
          $result = $this->pdo->query($query);
          OnpubDatabase::verifyQuery($this->pdo, $result, $this->enableTransactions);

          $row = $result->fetch(PDO::FETCH_ASSOC);

          $parent = new OnpubSection();

          if ($row) {
            $parent->ID = $row["ID"];
            $parent->imageID = $row["imageID"];
            $parent->websiteID = $row["websiteID"];
            $parent->parentID = $row["parentID"];
            $parent->name = $row["name"];
            $parent->url = $row["url"];
            $parent->setCreated(new DateTime($row["created"]));
            $parent->setModified(new DateTime($row["modified"]));

            $result->closeCursor();
          }
        }

        $section->parent = $parent;

        $found = FALSE;

        foreach ($parent->sections as $s) {
          if ($s->ID == $section->ID) {
            $found = TRUE;
          }
        }

        if (!$found) {
          $parent->sections[] = $section;
        }

        $section = $parent;
      }
    }

    $sections = array();

    foreach ($sectionsassoc as $s) {
      if ($flatSectionList) {
        $sections[] = $s;
      }
      else {
        if (!$s->parentID) {
          $sections[] = $s;
        }
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $result->closeCursor();
    return $sections;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL, $websiteID, $parentID)
  {
    if ($websiteID) $websiteID = ctype_digit($websiteID) ? $websiteID : $this->pdo->quote($websiteID);
    if ($parentID) $parentID = ctype_digit($parentID) ? $parentID : $this->pdo->quote($parentID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->dateLimit) {
      $where = "WHERE created <= "
        . $this->pdo->quote($queryOptions->dateLimit->format('Y-m-d H:i:s'));

      if ($websiteID) {
        $where .= " AND websiteID = $websiteID";
      }
      elseif ($parentID) {
        $where .= " AND parentID = $parentID";
      }
    }
    else {
      if ($websiteID) {
        $where = "WHERE websiteID = $websiteID";
      }
      elseif ($parentID) {
        $where = "WHERE parentID = $parentID";
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

    return "SELECT ID, imageID, websiteID, parentID, name, url, created, modified FROM OnpubSections $where $orderBy $limit";
  }

  /**
   * @param int $ID ID of the section to get.
   * @return OnpubSection An {@link OnpubSection} object. NULL if the section does not exist in the database.
   */
  public function get($ID, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->getQuery($ID, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($rows = $result->fetchAll(PDO::FETCH_ASSOC))) {
      return NULL;
    }

    $row = $rows[0];

    $section = new OnpubSection();

    $section->ID = $row["ID"];
    $section->imageID = $row["imageID"];
    $section->websiteID = $row["websiteID"];
    $section->parentID = $row["parentID"];
    $section->name = $row["name"];
    $section->url = $row["url"];
    $section->setCreated(new DateTime($row["created"]));
    $section->setModified(new DateTime($row["modified"]));

    if (!$queryOptions->includeArticles) {
      return $section;
    }

    if ($row["imageID"]) {
      $image = new OnpubImage();

      $image->ID = $row["imageID"];
      $image->websiteID = $row["websiteID"];
      $image->fileName = $row["fileName"];
      $image->description = $row["description"];
      $image->setCreated(new DateTime($row["imageCreated"]));
      $image->setModified(new DateTime($row["imageModified"]));

      $section->image = $image;
    }

    $articlesassoc = array();

    foreach ($rows as $row) {
      $articleID = $row["articleID"];

      if ($articleID) {
        if ($queryOptions->includeContent) {
          $content = $row["content"];
        }
        else {
          $content = "";
        }

        $article = new OnpubArticle();

        $article->ID = $articleID;
        $article->imageID = $row["articleImageID"];
        $article->title = $row["title"];
        $article->content = $content;
        $article->url = $row["articleURL"];
        $article->setCreated(new DateTime($row["articleCreated"]));
        $article->setModified(new DateTime($row["articleModified"]));

        if (isset($articlesassoc["$articleID"])) {
          $article = $articlesassoc["$articleID"];
        }
        else {
          $articlesassoc["$articleID"] = $article;
        }

        if ($row["articleImageID"]) {
          $image = new OnpubImage();

          $image->ID = $row["articleImageID"];
          $image->websiteID = $row["articleImageWebsiteID"];
          $image->fileName = $row["articleImageFileName"];
          $image->description = $row["articleImageDescription"];
          $image->url = $row["articleImageURL"];
          $image->setCreated(new DateTime($row["articleImageCreated"]));
          $image->setModified(new DateTime($row["articleImageModified"]));

          $article->image = $image;
        }

        $articles = $section->articles;
        $exists = FALSE;

        foreach ($articles as $a) {
          if ($a->ID == $article->ID) {
            $exists = TRUE;
          }
        }

        if ($exists == FALSE) {
          $articles[] = $article;
          $section->articles = $articles;
        }
      }
    }

    $authorsassoc = array();

    reset ($rows);

    foreach ($rows as $row) {
      $authorID = $row["authorID"];
      $articleID = $row["articleID"];

      if ($authorID && $articleID) {
        $author = new OnpubAuthor();

        $author->ID = $authorID;
        $author->imageID = $row["authorImageID"];
        $author->givenNames = $row["givenNames"];
        $author->familyName = $row["familyName"];
        $author->displayAs = $row["displayAs"];
        $author->setCreated(new DateTime($row["authorCreated"]));
        $author->setModified(new DateTime($row["authorModified"]));

        if (isset($authorsassoc["$authorID"])) {
          $author = $authorsassoc["$authorID"];
        }
        else {
          $authorsassoc["$authorID"] = $author;
        }

        if ($row["authorImageID"]) {
          $image = new OnpubImage();

          $image->ID = $row["authorImageID"];
          $image->websiteID = $row["authorImageWebsiteID"];
          $image->fileName = $row["authorImageFileName"];
          $image->description = $row["authorImageDescription"];
          $image->setCreated(new DateTime($row["authorImageCreated"]));
          $image->setModified(new DateTime($row["authorImageModified"]));

          $author->image = $image;
        }

        $article = $articlesassoc["$articleID"];
        $authors = $article->authors;
        $exists = FALSE;

        foreach ($authors as $a) {
          if ($a->ID == $author->ID) {
            $exists = TRUE;
          }
        }

        if ($exists == FALSE) {
          $authors[] = $author;
          $article->authors = $authors;
        }
      }
    }

    $result->closeCursor();
    return $section;
  }

  private function getQuery($ID, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($ID) $ID = ctype_digit($ID) ? $ID : $this->pdo->quote($ID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    if ($queryOptions->orderBy) {
      $orderBy = $queryOptions->orderBy;
      $orderBy = "articles.$orderBy";
    }
    else {
      $orderBy = "samaps.ID";
    }

    if ($queryOptions->order) {
      $order = $queryOptions->order;
    }
    else {
      $order = "";
    }

    $where = "WHERE sections.ID = $ID";

    if ($queryOptions->dateLimit) {
      $dateLimit = $queryOptions->dateLimit->format('Y-m-d H:i:s');
      $where .= " AND articles.created <= '$dateLimit'";
    }

    if ($queryOptions->includeContent) {
      $articleColumns = "articles.ID AS articleID, articles.imageID AS articleImageID, title, content, articles.url AS articleURL, articles.created AS articleCreated, articles.modified AS articleModified";
    }
    else {
      $articleColumns = "articles.ID AS articleID, articles.imageID AS articleImageID, title, articles.url AS articleURL, articles.created AS articleCreated, articles.modified AS articleModified";
    }

    if ($queryOptions->includeArticles) {
      return
        "SELECT sections.ID, sections.imageID, sections.websiteID, sections.parentID, sections.name, sections.url, sections.url, sections.created, sections.modified, sectionimages.fileName, sectionimages.description, sectionimages.created AS imageCreated, sectionimages.modified AS imageModified, $articleColumns, articleimages.websiteID AS articleImageWebsiteID, articleimages.fileName AS articleImageFileName, articleimages.description AS articleImageDescription, articleimages.url AS articleImageURL, articleimages.created AS articleImageCreated, articleimages.modified AS articleImageModified, authors.ID AS authorID, authors.imageID AS authorImageID, authors.givenNames, authors.familyName, authors.displayAs, authors.created AS authorCreated, authors.modified AS authorModified, authorimages.websiteID AS authorImageWebsiteID, authorimages.fileName AS authorImageFileName, authorimages.description AS authorImageDescription, authorimages.url AS authorImageURL, authorimages.created AS authorImageCreated, authorimages.modified AS authorImageModified FROM OnpubSections AS sections LEFT JOIN OnpubImages AS sectionimages ON sections.imageID = sectionimages.ID LEFT JOIN OnpubSAMaps AS samaps ON sections.ID = samaps.sectionID LEFT JOIN OnpubArticles AS articles ON samaps.articleID = articles.ID LEFT JOIN OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = aamaps.articleID LEFT JOIN OnpubAuthors AS authors ON aamaps.authorID = authors.ID LEFT JOIN OnpubImages AS authorimages ON authors.imageID = authorimages.ID $where ORDER BY $orderBy $order";
    }
    else {
      return "SELECT ID, imageID, websiteID, parentID, name, url, created, modified FROM OnpubSections WHERE ID = $ID";
    }
  }

  /**
   * @param OnpubSection $section Section whose ID you want to get.
   * @return int The ID of the section. NULL if the section does not exist in the database.
   */
  public function getID(OnpubSection $section)
  {
    if ($section->parentID) {
      $stmt = $this->pdo->prepare("SELECT ID FROM OnpubSections WHERE websiteID = :websiteID AND parentID = :parentID AND name = :name");
    }
    else {
      $stmt = $this->pdo->prepare("SELECT ID FROM OnpubSections WHERE websiteID = :websiteID AND parentID IS NULL AND name = :name");
    }

    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $websiteID = $section->websiteID;
    $parentID = $section->parentID;
    $name = OnpubDatabase::utf8Decode(trim($section->name));

    $stmt->bindParam(':websiteID', $websiteID);

    if ($parentID) {
      $stmt->bindParam(':parentID', $parentID);
    }

    $stmt->bindParam(':name', $name);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param mixed $sections A single {@link OnpubSection} object or an array of {@link OnpubSection} objects (to insert multiple sections at a time).
   * @return mixed The ID(s) of the new section(s). An int will be returned if a single section was inserted. An array of ints will be returned if multiple sections were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($sections, $insertWSMaps = FALSE)
  {
    $oimages = new OnpubImages($this->pdo, FALSE);
    $owsmaps = new OnpubWSMaps($this->pdo, FALSE);
    $IDs = array();
    $isArray = TRUE;
    $wsmaps = array();

    if (!is_array($sections)) {
      $sections = array ($sections);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubSections (ID, imageID, websiteID, parentID, name, url, created, modified) VALUES (:ID, :imageID, :websiteID, :parentID, :name, :url, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($sections as $section) {
      if ($section->image) {
        try {
          $imageID = $oimages->insert($section->image);
          $section->imageID = $imageID;
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }

      try {
        $ID = $this->getID($section);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($ID) {
        $IDs[] = $ID;
        $section->ID = $ID;
      }
      else {
        $ID = $section->ID;
        $imageID = $section->imageID;
        $websiteID = $section->websiteID;
        $parentID = $section->parentID;
        $name = OnpubDatabase::utf8Decode(trim($section->name));
        $url = OnpubDatabase::utf8Decode(trim($section->url));
        $created = $section->getCreated()->format('Y-m-d H:i:s');
        $modified = $section->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':imageID', $imageID);
        $stmt->bindParam(':websiteID', $websiteID);
        $stmt->bindParam(':parentID', $parentID);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $IDs[] = $this->pdo->lastInsertId();
        $section->ID = $this->pdo->lastInsertId();
      }

      $wsmap = new OnpubWSMap();

      $wsmap->websiteID = $section->websiteID;
      $wsmap->sectionID = $section->ID;
      $wsmap->setCreated($section->getCreated());
      $wsmap->setModified($section->getModified());

      $wsmaps[] = $wsmap;
    }

    if ($insertWSMaps) {
      try {
        $owsmaps->insert($wsmaps);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
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
   * @param OnpubSection $section The section to be updated.
   * @return int 1 if the section was updated. 0 if the section was not updated or does not exist.
   */
  public function update(OnpubSection $section)
  {
    $oarticles = new OnpubArticles($this->pdo, FALSE);
    $osamaps = new OnpubSAMaps($this->pdo, FALSE);
    $now = new DateTime();
    $inTransaction = FALSE;

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
      $inTransaction = TRUE;
    }

    if ($section->ID == $section->parentID) {
      $section->parentID = NULL;
    }

    if ($section->parentID) {
      $this->enableTransactions = FALSE;
      $parentID = $section->parentID;

      while ($parentID) {
        try {
          $parent = $this->get($parentID);
        }
        catch (PDOException $e) {
          if ($inTransaction)
            $this->pdo->rollBack();

          throw $e;
        }

        if (!$parent) {
          $section->parentID = NULL;
          break;
        }

        if ($section->ID == $parent->parentID) {
          $section->parentID = NULL;
          break;
        }

        $parentID = $parent->parentID;
      }

      $this->enableTransactions = TRUE;
    }

    $stmt = $this->pdo->prepare("UPDATE OnpubSections SET imageID = :imageID, parentID = :parentID, name = :name, url = :url, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $ID = $section->ID;
    $imageID = $section->imageID;
    $parentID = $section->parentID;
    $name = OnpubDatabase::utf8Decode(trim($section->name));
    $url = OnpubDatabase::utf8Decode(trim($section->url));
    $modified = $now->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':imageID', $imageID);
    $stmt->bindParam(':parentID', $parentID);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':modified', $modified);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    try {
      $osamaps->delete($section->ID, NULL);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    $articles = $section->articles;
    $samaps = array();

    foreach ($articles as $article) {
      if ($article->ID) {
        try {
          $article = $oarticles->get($article->ID, new OnpubQueryOptions());
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }

        $samap = new OnpubSAMap();

        $samap->sectionID = $section->ID;
        $samap->articleID = $article->ID;
        $samap->setCreated($article->getCreated());
        $samap->setModified($article->getModified());

        $samaps[] = $samap;
      }
      else {
        try {
          $articleID = $oarticles->insert($article);
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }

        $samap = new OnpubSAMap();

        $samap->sectionID = $section->ID;
        $samap->articleID = $articleID;

        $samaps[] = $samap;
      }
    }

    try {
      $osamaps->insert($samaps);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    return $stmt->rowCount();
  }
}
?>
<?php

/**
 * Manage articles in an Onpub database.
 *
 * This class contains the methods to manage the data contained in an
 * {@link http://onpub.com/pdfs/onpub_schema.pdf OnpubArticles table}.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubArticles
{
  private $pdo;
  /**
   * Controls whether or not the methods in this class use transactions when
   * they access the database.
   *
   * By default, this is set to TRUE and database transactions will be used
   * where necessary. Set this to FALSE if you want to manage transactions
   * yourself.
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
   * @param bool $enableTransactions If TRUE (the default), the methods in
   * this class that use database transactions will do so. If FALSE, methods
   * that by default use transactions, will not. Only pass FALSE to this
   * argument if you are managing transactions yourself.
   *
   */
  function __construct(PDO $pdo, $enableTransactions = TRUE)
  {
    $this->pdo = $pdo;
    $this->enableTransactions = $enableTransactions;
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
  }

  /**
   * Get the total number of articles in the database.
   *
   * @param int $sectionID Get the total number of articles in a section
   *                       identified by its ID.
   * @return int
   */
  public function count($sectionID = NULL)
  {
    $query = $this->countQuery($sectionID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    if (!($row = $result->fetch(PDO::FETCH_ASSOC))) {
      return 0;
    }

    $result->closeCursor();
    return $row["count"];
  }

  private function countQuery($sectionID = NULL)
  {
    if ($sectionID) $sectionID = ctype_digit($sectionID) ? $sectionID : $this->pdo->quote($sectionID);

    if ($sectionID) {
      return "SELECT COUNT(articles.ID) AS count FROM OnpubArticles AS articles LEFT JOIN OnpubSAMaps as samaps ON articles.ID = samaps.articleID WHERE sectionID = $sectionID";
    }
    else {
      return "SELECT COUNT(articles.ID) AS count FROM OnpubArticles AS articles";
    }
  }

  /**
   * Delete an article from the database.
   *
   * @param int $ID ID of the article to delete.
   * @return int 1 if the article was deleted, 0 if the article does not exist in the database.
   */
  public function delete($ID)
  {
    $oaamaps = new OnpubAAMaps($this->pdo, FALSE);
    $osamaps = new OnpubSAMaps($this->pdo, FALSE);

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    try {
      $oaamaps->delete($ID, NULL);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    try {
      $osamaps->delete(NULL, $ID);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    $stmt = $this->pdo->prepare("DELETE FROM OnpubArticles WHERE ID = :ID");
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
   * Search for articles in the database.
   *
   * @param string $keywords Keyword(s) to search for.
   * @return array All the articles which were found as an array of {@link OnpubArticle} objects.
   */
  public function search($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->searchQuery($keywords, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $articles = array();
    $authors = array();
    $lastAID = 0;
    $lastArticle = NULL;
    $content = "";

    if ($rows) {
      foreach ($rows as $row) {
        $currAID = $row["ID"];
        $currIID = $row["imageID"];
        $currAIID = $row["authorImageID"];

        if ($lastAID != $currAID) {
          $authors = array();
        }

        $author = new OnpubAuthor();

        $author->ID = $row["authorID"];
        $author->imageID = $currAIID;
        $author->givenNames = $row["givenNames"];
        $author->familyName = $row["familyName"];
        $author->displayAs = $row["displayAs"];
        $author->setCreated(new DateTime($row["authorCreated"]));
        $author->setModified(new DateTime($row["authorModified"]));

        $authors[] = $author;

        if ($currAIID) {
          $s = sizeof($authors);
          $author = $authors[($s - 1)];

          $image = new OnpubImage();

          $image->ID = $currAIID;
          $image->websiteID = $row["authorImageWebsiteID"];
          $image->fileName = $row["authorImageFileName"];
          $image->description = $row["authorImageDescription"];
          $image->setCreated(new DateTime($row["authorImageCreated"]));
          $image->setModified(new DateTime($row["authorImageModified"]));

          $author->image = $image;
        }

        if ($queryOptions->includeContent) {
          $content = $row["content"];
        }
        else {
          $content = "";
        }

        $currArticle = new OnpubArticle();

        $currArticle->ID = $currAID;
        $currArticle->imageID = $currIID;
        $currArticle->title = $row["title"];
        $currArticle->content = $content;
        $currArticle->url = $row["url"];
        $currArticle->setCreated(new DateTime($row["created"]));
        $currArticle->setModified(new DateTime($row["modified"]));

        if ($currIID) {
          $image = new OnpubImage();

          $image->ID = $currIID;
          $image->websiteID = $row["websiteID"];
          $image->fileName = $row["fileName"];
          $image->description = $row["description"];
          $image->url = $row["articleImageURL"];
          $image->setCreated(new DateTime($row["imageCreated"]));
          $image->setModified(new DateTime($row["imageModified"]));

          $currArticle->image = $image;
        }

        if ($lastArticle != NULL) {
          if ($lastArticle->ID == $currArticle->ID) {
            $lastArticle->authors = $authors;
          }
          else {
            $articles[] = $currArticle;
            $currArticle->authors = $authors;
          }
        }
        else {
          $articles[] = $currArticle;
          $currArticle->authors = $authors;
        }

        $lastAID = $currAID;

        if ($lastArticle != NULL) {
          if ($lastArticle->ID != $currArticle->ID) {
            $lastArticle = $currArticle;
          }
        }
        else {
          $lastArticle = $currArticle;
        }
      }
    }

    $result->closeCursor();
    return $articles;
  }

  private function searchQuery($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $keywords = $this->pdo->quote('%' . OnpubDatabase::utf8Decode(trim($keywords)) . '%');
    $where = "";

    if ($keywords) {
      if ($queryOptions->fullTextSearch) {
        $where = "WHERE articles.title LIKE $keywords OR authors.displayAS LIKE $keywords OR articles.created LIKE $keywords OR articles.modified LIKE $keywords OR articles.ID LIKE $keywords OR articles.content LIKE $keywords OR articles.url LIKE $keywords";
      }
      else {
        $where = "WHERE articles.title LIKE $keywords OR authors.displayAS LIKE $keywords OR articles.created LIKE $keywords OR articles.modified LIKE $keywords OR articles.ID LIKE $keywords OR articles.url LIKE $keywords";
      }
    }

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

    if ($queryOptions->includeContent) {
      $articleColumns = "articles.ID, articles.imageID, title, content, articles.url, articles.created, articles.modified";
    }
    else {
      $articleColumns = "articles.ID, articles.imageID, title, articles.url,  articles.created, articles.modified";
    }

    if ($queryOptions->dateLimit) {
      $dateLimit = $queryOptions->dateLimit->format('Y-m-d H:i:s');

      if ($keywords) {
        $where .= " AND articles.created <= '$dateLimit'";
      }
      else {
        $where = "WHERE articles.created <= '$dateLimit'";
      }
    }

    if ($orderBy) {
      if ($order) {
        return
          "SELECT $articleColumns, articleimages.websiteID, articleimages.fileName, articleimages.description, articleimages.url AS articleImageURL, articleimages.created AS imageCreated, articleimages.modified AS imageModified, authors.ID AS authorID, authors.imageID AS authorImageID, givenNames, familyName, displayAs, authors.created AS authorCreated, authors.modified AS authorModified, authorimages.websiteID AS authorImageWebsiteID, authorimages.fileName AS authorImageFileName, authorimages.description AS authorImageDescription, authorimages.url AS authorImageURL, authorimages.created AS authorImageCreated, authorimages.modified AS authorImageModified FROM OnpubArticles AS articles LEFT JOIN OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = aamaps.articleID LEFT JOIN OnpubAuthors AS authors ON aamaps.authorID = authors.ID LEFT JOIN OnpubImages AS authorimages ON authors.imageID = authorimages.ID $where ORDER BY articles.$orderBy $order";
      }
      else {
        return "SELECT $articleColumns, articleimages.websiteID, articleimages.fileName, articleimages.description, articleimages.url AS articleImageURL, articleimages.created AS imageCreated, articleimages.modified AS imageModified, authors.ID AS authorID, authors.imageID AS authorImageID, givenNames, familyName, displayAs, authors.created AS authorCreated, authors.modified AS authorModified, authorimages.websiteID AS authorImageWebsiteID, authorimages.fileName AS authorImageFileName, authorimages.description AS authorImageDescription, authorimages.url AS authorImageURL, authorimages.created AS authorImageCreated, authorimages.modified AS authorImageModified FROM OnpubArticles AS articles LEFT JOIN OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = aamaps.articleID LEFT JOIN OnpubAuthors AS authors ON aamaps.authorID = authors.ID LEFT JOIN OnpubImages AS authorimages ON authors.imageID = authorimages.ID $where ORDER BY articles.$orderBy";
      }
    }
    else {
      return "SELECT $articleColumns, articleimages.websiteID, articleimages.fileName, articleimages.description, articleimages.url AS articleImageURL, articleimages.created AS imageCreated, articleimages.modified AS imageModified, authors.ID AS authorID, authors.imageID AS authorImageID, givenNames, familyName, displayAs, authors.created AS authorCreated, authors.modified AS authorModified, authorimages.websiteID AS authorImageWebsiteID, authorimages.fileName AS authorImageFileName, authorimages.description AS authorImageDescription, authorimages.url AS authorImageURL, authorimages.created AS authorImageCreated, authorimages.modified AS authorImageModified FROM OnpubArticles AS articles LEFT JOIN OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = aamaps.articleID LEFT JOIN OnpubAuthors AS authors ON aamaps.authorID = authors.ID LEFT JOIN OnpubImages AS authorimages ON authors.imageID = authorimages.ID $where";
    }
  }

  /**
   * Get a single article from the database.
   *
   * @param int $ID ID of the article to get.
   * @param OnpubQueryOptions $queryOptions Optional database query options.
   * @return OnpubArticle An {@link OnpubArticle} object. NULL if the article does not exist in the database.
   */
  public function get($ID, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->getQuery($ID, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
      return NULL;
    }

    $row = $rows[0];

    if ($queryOptions->includeContent) {
      $content = $row["content"];
    }
    else {
      $content = "";
    }

    $article = new OnpubArticle();

    $article->ID = $row["ID"];
    $article->imageID = $row["imageID"];
    $article->title = $row["title"];
    $article->content = $content;
    $article->url = $row["url"];
    $article->setCreated(new DateTime($row["created"]));
    $article->setModified(new DateTime($row["modified"]));

    if (!$queryOptions->includeAuthors) {
      return $article;
    }

    if ($row["imageID"]) {
      $image = new OnpubImage();

      $image->ID = $row["imageID"];
      $image->websiteID = $row["websiteID"];
      $image->fileName = $row["fileName"];
      $image->description = $row["description"];
      $image->setCreated(new DateTime($row["imageCreated"]));
      $image->setModified(new DateTime($row["imageModified"]));

      $article->image = $image;
    }

    $authorsassoc = array();

    foreach ($rows as $row) {
      $authorID = $row["authorID"];

      if ($authorID) {
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
    return $article;
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
      $orderBy = "aamaps.ID";
    }

    if ($queryOptions->order) {
      $order = $queryOptions->order;
    }
    else {
      $order = "";
    }

    $where = "WHERE articles.ID = $ID";

    if ($queryOptions->dateLimit) {
      $dateLimit = $queryOptions->dateLimit->format('Y-m-d H:i:s');
      $where .= " AND articles.created <= '$dateLimit'";
    }

    if ($queryOptions->includeContent) {
      $articleColumns = "articles.ID, articles.imageID, title, content, " .
                        "articles.url, articles.created, articles.modified";
    }
    else {
      $articleColumns = "articles.ID, articles.imageID, title, articles.url, " .
                        "articles.created, articles.modified";
    }

    if ($queryOptions->includeAuthors) {
      return "SELECT $articleColumns, articleimages.websiteID, " .
             "articleimages.fileName, articleimages.description, " .
             "articleimages.url AS articleImageURL, articleimages.created AS " .
             "imageCreated, articleimages.modified AS imageModified, " .
             "authors.ID AS authorID, authors.imageID AS authorImageID, " .
             "givenNames, familyName, displayAs, authors.created AS " .
             "authorCreated, authors.modified AS authorModified, " .
             "authorimages.websiteID AS authorImageWebsiteID, " .
             "authorimages.fileName AS authorImageFileName, " .
             "authorimages.description AS authorImageDescription, " .
             "authorimages.url AS authorImageURL, authorimages.created AS " .
             "authorImageCreated, authorimages.modified AS authorImageModified " .
             "FROM OnpubArticles AS articles LEFT JOIN OnpubImages AS " .
             "articleimages ON articles.imageID = articleimages.ID " .
             "LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = aamaps.articleID " .
             "LEFT JOIN OnpubAuthors AS authors ON aamaps.authorID = authors.ID " .
             "LEFT JOIN OnpubImages AS authorimages ON " .
             "authors.imageID = authorimages.ID $where ORDER BY $orderBy $order";
    }
    else {
      return "SELECT $articleColumns FROM OnpubArticles as articles WHERE ID = $ID";
    }
  }

  /**
   * Get the ID of an article in the database.
   *
   * Looks for an article in the database with the same title and content.
   * If there's a match, the ID of the article is returned.
   *
   * @param OnpubArticle $article Article whose ID you want to get.
   * @return int The ID of the article. NULL if the article does not exist in the database.
   */
  public function getID(OnpubArticle $article)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubArticles WHERE title = :title AND content = :content");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $title = OnpubDatabase::utf8Decode(trim($article->title));
    $content = OnpubDatabase::utf8Decode(trim($article->content));

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * Insert new article(s) into the database.
   *
   * @param mixed $articles A single {@link OnpubArticle} object or an array of {@link OnpubArticle} objects (to insert multiple articles at a time).
   * @return mixed The ID(s) of the new article(s). An int will be returned if a single article was inserted. An array of ints will be returned if multiple articles were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($articles)
  {
    $oimages = new OnpubImages($this->pdo, FALSE);
    $oauthors = new OnpubAuthors($this->pdo, FALSE);
    $oaamaps = new OnpubAAMaps($this->pdo, FALSE);
    $osamaps = new OnpubSAMaps($this->pdo, FALSE);
    $IDs = array();
    $isArray = TRUE;

    if (!is_array($articles)) {
      $articles = array ($articles);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubArticles (ID, imageID, title, content, url, created, modified) VALUES (:ID, :imageID, :title, :content, :url, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($articles as $article) {
      if ($article->image) {
        try {
          $imageID = $oimages->insert($article->image);
          $article->imageID = $imageID;
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }

      try {
        $ID = $this->getID($article);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($ID) {
        $IDs[] = $ID;
        $article->ID = $ID;
      }
      else {
        $ID = $article->ID;
        $imageID = $article->imageID;
        $title = OnpubDatabase::utf8Decode(trim($article->title));
        $content = OnpubDatabase::utf8Decode(trim($article->content));
        $url = OnpubDatabase::utf8Decode(trim($article->url));
        $created = $article->getCreated()->format('Y-m-d H:i:s');
        $modified = $article->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':imageID', $imageID);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $IDs[] = $this->pdo->lastInsertId();
        $article->ID = $this->pdo->lastInsertId();
      }

      $authors = $article->authors;

      try {
        $oauthors->insert($authors);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      $aamaps = array();

      foreach ($authors as $author) {
        $aamap = new OnpubAAMap();

        $aamap->articleID = $article->ID;
        $aamap->authorID = $author->ID;
        $aamap->setCreated($article->getCreated());
        $aamap->setModified($article->getModified());

        $aamaps[] = $aamap;
      }

      try {
        $oaamaps->insert($aamaps);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      $sectionIDs = $article->sectionIDs;
      $samaps = array();

      foreach ($sectionIDs as $sectionID) {
        $samap = new OnpubSAMap();

        $samap->sectionID = $sectionID;
        $samap->articleID = $article->ID;
        $samap->setCreated($article->getCreated());
        $samap->setModified($article->getModified());

        $samaps[] = $samap;
      }

      try {
        $osamaps->insert($samaps);
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
   * Get multiple articles from the database.
   *
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubArticle} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL, $sectionID = NULL, $websiteID = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions, $sectionID, $websiteID);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $articles = array();

    if ($rows) {
      $lastID = null;
      $article = new OnpubArticle();

      foreach ($rows as $row) {
        if ($lastID != $row["ID"]) {
          $article = new OnpubArticle();
        }

        if ($queryOptions->includeContent) {
          $content = $row["content"];
        }
        else {
          $content = "";
        }

        $article->ID = $row["ID"];
        $article->imageID = $row["imageID"];
        $article->title = $row["title"];
        $article->content = $content;
        $article->url = $row["url"];
        $article->setCreated(new DateTime($row["created"]));
        $article->setModified(new DateTime($row["modified"]));

        if ($row["imageID"]) {
          $image = new OnpubImage();

          $image->ID = $row["imageID"];
          $image->websiteID = $row["imageWebsiteID"];
          $image->fileName = $row["imageFileName"];
          $image->description = $row["imageDescription"];
          $image->setCreated(new DateTime($row["imageCreated"]));
          $image->setModified(new DateTime($row["imageModified"]));

          $article->image = $image;
        }

        if ($sectionID || $websiteID) {
          $article->sectionIDs[] = $row["sectionID"];
        }

        if ($lastID != $row["ID"]) {
          $articles[] = $article;
        }

        $lastID = $article->ID;
      }
    }

    $result->closeCursor();
    return $articles;
  }

  private function selectQuery(OnpubQueryOptions $queryOptions = NULL, $sectionID = NULL, $websiteID = NULL)
  {
    if ($sectionID) $sectionID = ctype_digit($sectionID) ? $sectionID : $this->pdo->quote($sectionID);
    if ($websiteID) $websiteID = ctype_digit($websiteID) ? $websiteID : $this->pdo->quote($websiteID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $articleColumns = "articles.ID, articles.imageID, articles.title, " .
                      "articles.url, articles.created, articles.modified, " .
                      "articleimages.websiteID AS imageWebsiteID, articleimages.fileName AS imageFileName, " .
                      "articleimages.description AS imageDescription, articleimages.url AS " .
                      "articleImageURL, articleimages.created AS imageCreated, " .
                      "articleimages.modified AS imageModified";
    $where = "";
    $orderBy = "";
    $limit = "";

    if ($queryOptions->includeContent) {
      $articleColumns = "articles.ID, articles.imageID, articles.title, " .
                        "articles.content, articles.url, articles.created, " .
                        "articles.modified, articleimages.websiteID AS " .
                        "imageWebsiteID, articleimages.fileName AS " .
                        "imageFileName, articleimages.description AS " .
                        "imageDescription, articleimages.url AS " .
                        "articleImageURL, articleimages.created AS " .
                        "imageCreated, articleimages.modified AS imageModified";
    }

    if ($sectionID || $websiteID) {
      $articleColumns .= ", samaps.sectionID AS sectionID";
    }

    if ($queryOptions->dateLimit) {
      $where = "WHERE articles.created <= " . $this->pdo->quote($queryOptions->dateLimit->format('Y-m-d H:i:s'));

      if ($sectionID && !$websiteID) {
        $where .= " AND samaps.sectionID = $sectionID";
      }

      if ($websiteID && !$sectionID) {
        $where .= " AND wsmaps.websiteID = $websiteID";
      }
    }
    else {
      if ($sectionID && !$websiteID) {
        $where = "WHERE samaps.sectionID = $sectionID";
      }

      if ($websiteID && !$sectionID) {
        $where = "WHERE wsmaps.websiteID = $websiteID";
      }
    }

    if ($queryOptions->orderBy) {
      $orderBy = "ORDER BY articles." . $queryOptions->orderBy;

      if ($queryOptions->order) {
        $orderBy .= " " . $queryOptions->order;
      }
    }
    else {
      if ($sectionID && !$websiteID) {
        $orderBy = "ORDER BY samaps.ID ASC";
      }
    }

    if ($queryOptions->getPage() && $queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT " . (($queryOptions->getPage() - 1) * $queryOptions->rowLimit) .
               "," . $queryOptions->rowLimit;
    }
    elseif ($queryOptions->rowLimit && $queryOptions->rowLimit > 0) {
      $limit = "LIMIT 0," . $queryOptions->rowLimit;
    }

    if ($sectionID && !$websiteID) {
      return "SELECT $articleColumns FROM OnpubArticles AS articles LEFT JOIN " .
             "OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN " .
             "OnpubSAMaps AS samaps ON articles.ID = samaps.articleID " .
             "$where $orderBy $limit";
    }
    elseif ($websiteID && !$sectionID) {
      return "SELECT $articleColumns FROM OnpubArticles AS articles LEFT JOIN " .
             "OnpubImages AS articleimages ON articles.imageID = articleimages.ID LEFT JOIN " .
             "OnpubSAMaps AS samaps ON articles.ID = samaps.articleID " .
             "LEFT JOIN OnpubWSMaps AS wsmaps ON samaps.sectionID = " .
             "wsmaps.sectionID $where $orderBy $limit";
    }
    else {
      return "SELECT $articleColumns FROM OnpubArticles AS articles LEFT JOIN " .
             "OnpubImages AS articleimages ON articles.imageID = articleimages.ID " .
             "$where $orderBy $limit";
    }
  }

  /**
   * Update an article already in the database.
   *
   * If you set the article's sectionIDs to NULL, it will be unmapped from
   * any sections it was previously mapped to.
   *
   * @param OnpubArticle $article The article to be updated.
   * @param bool $overwriteAAMaps False by default. If set to TRUE, the
   * article-author maps for this article will be deleted and recreated, if
   * applicable.
   * @return int 1 if the article was updated. 0 if the article does not exist in the database.
   */
  public function update(OnpubArticle $article, $overwriteAAMaps = FALSE)
  {
    $oaamaps = new OnpubAAMaps($this->pdo, FALSE);
    $oauthors = new OnpubAuthors($this->pdo, FALSE);
    $osamaps = new OnpubSAMaps($this->pdo, FALSE);
    $oimages = new OnpubImages($this->pdo, FALSE);
    $now = new DateTime();

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("UPDATE OnpubArticles SET imageID = :imageID, title = :title, content = :content, url = :url, created = :created, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    if ($article->image) {
      try {
        $imageID = $oimages->insert($article->image);
        $article->imageID = $imageID;
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }
    }

    $ID = $article->ID;
    $imageID = $article->imageID;
    $title = OnpubDatabase::utf8Decode(trim($article->title));
    $content = OnpubDatabase::utf8Decode(trim($article->content));
    $url = OnpubDatabase::utf8Decode(trim($article->url));
    $created = $article->getCreated()->format('Y-m-d H:i:s');
    $modified = $now->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':imageID', $imageID);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':created', $created);
    $stmt->bindParam(':modified', $modified);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    if ($overwriteAAMaps) {
      try {
        $oaamaps->delete($article->ID, NULL);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }
    }

    $authors = $article->authors;

    foreach ($authors as $author) {
      if ($author->ID) {
        try {
          $oauthors->update($author);
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }
      else {
        try {
          $oauthors->insert($author);
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }

      try {
        $aamap = new OnpubAAMap();
        $aamap->articleID = $article->ID;
        $aamap->authorID = $author->ID;
        $oaamaps->insert($aamap);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }
    }

    $sectionIDs = $article->sectionIDs;

    if ($sectionIDs === NULL) {
      try {
        $samaps = $osamaps->delete(NULL, $article->ID);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }
    }
    elseif (sizeof($sectionIDs)) {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "ID";
      $queryOptions->order = "ASC";

      try {
        $samaps = $osamaps->select($queryOptions, NULL, $article->ID);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      // Unmap sections not included in $sectionIDs.
      foreach ($samaps as $samap) {
        if (!in_array($samap->sectionID, $sectionIDs)) {
          try {
            $osamaps->delete($samap->sectionID, $article->ID);
          }
          catch (PDOException $e) {
            if ($this->enableTransactions)
              $this->pdo->rollBack();

            throw $e;
          }
        }
      }

      foreach ($sectionIDs as $sectionID) {
        $samap = new OnpubSAMap();

        $samap->sectionID = $sectionID;
        $samap->articleID = $article->ID;
        $samap->setCreated($article->getCreated());
        $samap->setModified($article->getModified());

        try {
          $samapID = $osamaps->getID($samap);
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }

        if ($samapID) {
          $samap->ID = $samapID;

          try {
            $osamaps->update($samap);
          }
          catch (PDOException $e) {
            if ($this->enableTransactions)
              $this->pdo->rollBack();

            throw $e;
          }
        }
        else {
          try {
            $osamaps->insert($samap);
          }
          catch (PDOException $e) {
            if ($this->enableTransactions)
              $this->pdo->rollBack();

            throw $e;
          }
        }
      }
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->commit();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    return $stmt->rowCount();
  }
}
?>
<?php

/**
 * Manage websites in an Onpub database.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubWebsites
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
    return "SELECT COUNT(ID) AS count FROM OnpubWebsites";
  }

  /**
   * @param int $ID ID of the website to delete.
   * @return int 1 if the website was deleted, 0 if the website does not exist in the database.
   */
  public function delete($ID)
  {
    $stmt = $this->pdo->prepare("DELETE FROM OnpubWebsites WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $stmt->bindParam(':ID', $ID);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    return $stmt->rowCount();
  }

  /**
   * @param string $keywords Keyword(s) to search for.
   * @return array All the websites which were found as an array of {@link OnpubWebsite} objects.
   */
  public function search($keywords, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->searchQuery($keywords, $queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $websites = array();

    if ($rows) {
      foreach ($rows as $row) {
        $website = new OnpubWebsite();

        $website->ID = $row["ID"];
        $website->imageID = $row["imageID"];
        $website->name = $row["name"];
        $website->url = $row["url"];
        $website->imagesURL = $row["imagesURL"];
        $website->imagesDirectory = $row["imagesDirectory"];
        $website->setCreated(new DateTime($row["created"]));
        $website->setModified(new DateTime($row["modified"]));

        $websites[] = $website;
      }
    }

    $result->closeCursor();
    return $websites;
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
        return "SELECT ID, imageID, name, url, imagesURL, imagesDirectory, created, modified FROM OnpubWebsites WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords ORDER BY $orderBy $order";
      }
      else {
        return "SELECT ID, imageID, name, url, imagesURL, imagesDirectory, created, modified FROM OnpubWebsites WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords ORDER BY $orderBy";
      }
    }
    else {
      return "SELECT ID, imageID, name, url, imagesURL, imagesDirectory, created, modified FROM OnpubWebsites WHERE ID RLIKE $keywords OR name RLIKE $keywords OR modified RLIKE $keywords";
    }
  }

  /**
   * @param OnpubQueryOptions $queryOptions Database query options.
   * @return array An array of {@link OnpubWebsite} objects.
   */
  public function select(OnpubQueryOptions $queryOptions = NULL)
  {
    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    $query = $this->selectQuery($queryOptions);
    $result = $this->pdo->query($query);
    OnpubDatabase::verifyQuery($this->pdo, $result, FALSE);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    $websites = array();

    if ($rows) {
      foreach ($rows as $row) {
        $website = new OnpubWebsite();

        $website->ID = $row["ID"];
        $website->imageID = $row["imageID"];
        $website->name = $row["name"];
        $website->url = $row["url"];
        $website->imagesURL = $row["imagesURL"];
        $website->imagesDirectory = $row["imagesDirectory"];
        $website->setCreated(new DateTime($row["created"]));
        $website->setModified(new DateTime($row["modified"]));

        $websites[] = $website;
      }
    }

    $result->closeCursor();
    return $websites;
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

    return "SELECT ID, imageID, ID, name, url, imagesURL, imagesDirectory, created, modified FROM OnpubWebsites $where $orderBy $limit";
  }

  /**
   * @param int $ID ID of the website to get.
   * @return OnpubWebsite An {@link OnpubWebsite} object. NULL if the website does not exist in the database.
   */
  public function get($ID, OnpubQueryOptions $queryOptions = NULL, $flatArticleList = FALSE)
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

    $website = new OnpubWebsite();

    $website->ID = $row["ID"];
    $website->imageID = $row["imageID"];
    $website->name = $row["name"];
    $website->url = $row["url"];
    $website->imagesURL = $row["imagesURL"];
    $website->imagesDirectory = $row["imagesDirectory"];
    $website->setCreated(new DateTime($row["created"]));
    $website->setModified(new DateTime($row["modified"]));

    if ($row["imageID"]) {
      $image = new OnpubImage();

      $image->ID = $row["imageID"];
      $image->websiteID = $row["ID"];
      $image->fileName = $row["fileName"];
      $image->description = $row["description"];
      $image->setCreated(new DateTime($row["imageCreated"]));
      $image->setModified(new DateTime($row["imageModified"]));

      $website->image = $image;
    }

    if (!$queryOptions->includeSections) {
      return $website;
    }

    $osections = new OnpubSections($this->pdo);
    $sections = array();
    $sectionsassoc = array();

    foreach ($rows as $row) {
      $sectionID = $row["sectionID"];
      $ID = $row["ID"];

      if ($sectionID) {
        $section = new OnpubSection();

        $section->ID = $sectionID;
        $section->imageID = $row["sectionImageID"];
        $section->websiteID = $row["sectionWebsiteID"];
        $section->parentID = $row["sectionParentID"];
        $section->name = $row["sectionName"];
        $section->url = $row["sectionURL"];
        $section->setCreated(new DateTime($row["sectionCreated"]));
        $section->setModified(new DateTime($row["sectionModified"]));

        if (isset($sectionsassoc["$sectionID"])) {
          $section = $sectionsassoc["$sectionID"];
        }
        else {
          $sectionsassoc["$sectionID"] = $section;
        }

        if ($row["sectionImageID"]) {
          $image = new OnpubImage();

          $image->ID = $row["sectionImageID"];
          $image->websiteID = $row["sectionImageWebsiteID"];
          $image->fileName = $row["sectionImageFileName"];
          $image->description = $row["sectionImageDescription"];
          $image->setCreated(new DateTime($row["sectionImageCreated"]));
          $image->setModified(new DateTime($row["sectionImageModified"]));

          $section->image = $image;
        }

        $sections = $website->sections;
        $exists = FALSE;

        foreach ($sections as $s) {
          if ($s->ID == $section->ID) {
            $exists = TRUE;
          }
        }

        if ($exists == FALSE) {
          $sections[] = $section;
          $website->sections = $sections;
        }
      }
    }

    // An array to track the subsections indexes in the sections array.
    $subsections = array();

    // Loop through all sections and link subsections to their parents and
    // vice-versa.
    for ($i = 0; $i < sizeof($sections); $i++) {
      $section = $sections[$i];

      if ($section->parentID) {
        $parentID = $section->parentID;

        if (isset($sectionsassoc["$parentID"])) {
          $parentSection = $sectionsassoc["$parentID"];
          $parentSection->sections[] = $section;
          $section->parent = $parentSection;
        }

        $subsections[] = $i;
      }
    }

    // Unset subsections from the original sections array now that they are
    // linked to their parent sections.
    foreach ($subsections as $subsection) {
      unset($sections[$subsection]);
    }

    // Array now might not be offset from 0 for sections. Re-index from 0.
    $website->sections = array_values($sections);

    if (!$queryOptions->includeArticles) {
      return $website;
    }

    $articlesassoc = array();

    reset ($rows);

    foreach ($rows as $row) {
      $articleID = $row["articleID"];
      $sectionID = $row["sectionID"];

      if ($articleID && $sectionID) {
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
          $article->sectionIDs[] = $sectionID;
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

        $section = $sectionsassoc["$sectionID"];
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

    if ($flatArticleList) {
      $articles = array();

      foreach ($articlesassoc as $article) {
        $articles[] = $article;
      }

      $website->articles = $articles;
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
    return $website;
  }

  private function getQuery($ID, OnpubQueryOptions $queryOptions = NULL)
  {
    if ($ID) $ID = ctype_digit($ID) ? $ID : $this->pdo->quote($ID);

    if ($queryOptions === NULL)
      $queryOptions = new OnpubQueryOptions();

    if ($queryOptions->includeArticles) {
      if ($queryOptions->orderBy) {
        $orderBy = $queryOptions->orderBy;

        if ($queryOptions->order) {
          $order = $queryOptions->order;
          $orderBy = "articles.$orderBy $order";
        }
        else {
          $orderBy = "articles.$orderBy ASC";
        }
      }
      else {
        if ($queryOptions->order) {
          $order = $queryOptions->order;
          $orderBy = "wsmaps.ID ASC, samaps.ID $order";
        }
        else {
          $orderBy = "wsmaps.ID ASC, samaps.ID ASC";
        }
      }
    }
    else {
      if ($queryOptions->orderBy) {
        $orderBy = $queryOptions->orderBy;

        if ($queryOptions->order) {
          $order = $queryOptions->order;
          $orderBy = "wsmaps.$orderBy $order";
        }
        else {
          $orderBy = "wsmaps.$orderBy ASC";
        }
      }
      else {
        if ($queryOptions->order) {
          $order = $queryOptions->order;
          $orderBy = "wsmaps.ID $order";
        }
        else {
          $orderBy = "wsmaps.ID ASC";
        }
      }
    }

    $where = "WHERE websites.ID = $ID";

    if ($queryOptions->dateLimit && $queryOptions->includeArticles) {
      $dateLimit = $queryOptions->dateLimit->format('Y-m-d H:i:s');
      $where .= " AND articles.created <= '$dateLimit'";
    }

    if ($queryOptions->dateAfter && !$queryOptions->dateLimit && $queryOptions->includeArticles) {
      $dateAfter = $queryOptions->dateAfter->format('Y-m-d H:i:s');
      $where .= " AND articles.created >= '$dateAfter'";
    }

    if ($queryOptions->includeContent) {
      $articleColumns = "articles.ID AS articleID, articles.imageID AS " .
                        "articleImageID, title, content, articles.url AS " .
                        "articleURL, articles.created AS articleCreated, " .
                        "articles.modified AS articleModified";
    }
    else {
      $articleColumns = "articles.ID AS articleID, articles.imageID AS " .
                        "articleImageID, title, articles.url AS articleURL, " .
                        "articles.created AS articleCreated, " .
                        "articles.modified AS articleModified";
    }

    if ($queryOptions->includeSections) {
      if ($queryOptions->includeArticles) {
        return
          "SELECT websites.ID, websites.imageID, websites.name, " .
          "websites.url, websites.imagesURL, websites.imagesDirectory, " .
          "websites.created, websites.modified, websiteimages.fileName, " .
          "websiteimages.description, websiteimages.url AS websiteImageURL, " .
          "websiteimages.created AS imageCreated, websiteimages.modified AS " .
          "imageModified, sections.ID AS sectionID, sections.imageID AS " .
          "sectionImageID, sections.websiteID AS sectionWebsiteID, " .
          "sections.parentID AS sectionParentID, sections.name AS " .
          "sectionName, sections.url AS sectionURL, sections.created AS sectionCreated, " .
          "sections.modified AS sectionModified, sectionimages.websiteID AS " .
          "sectionImageWebsiteID, sectionimages.fileName AS " .
          "sectionImageFileName, sectionimages.description AS " .
          "sectionImageDescription, sectionimages.url AS sectionImageURL, " .
          "sectionimages.created AS sectionImageCreated, " .
          "sectionimages.modified AS sectionImageModified, $articleColumns, " .
          "articleimages.websiteID AS articleImageWebsiteID, " .
          "articleimages.fileName AS articleImageFileName, " .
          "articleimages.description AS articleImageDescription, " .
          "articleimages.url AS articleImageURL, articleimages.created AS " .
          "articleImageCreated, articleimages.modified AS " .
          "articleImageModified, authors.ID AS authorID, authors.imageID AS " .
          "authorImageID, authors.givenNames, authors.familyName, " .
          "authors.displayAs, authors.created AS authorCreated, " .
          "authors.modified AS authorModified, authorimages.websiteID AS " .
          "authorImageWebsiteID, authorimages.fileName AS " .
          "authorImageFileName, authorimages.description AS " .
          "authorImageDescription, authorimages.url AS authorImageURL, " .
          "authorimages.created AS authorImageCreated, " .
          "authorimages.modified AS authorImageModified FROM OnpubWebsites " .
          "AS websites LEFT JOIN OnpubImages AS websiteimages ON " .
          "websites.imageID = websiteimages.ID LEFT JOIN OnpubWSMaps AS " .
          "wsmaps ON websites.ID = wsmaps.websiteID LEFT JOIN OnpubSections " .
          "AS sections ON wsmaps.sectionID = sections.ID LEFT JOIN " .
          "OnpubImages AS sectionimages ON sections.imageID = " .
          "sectionimages.ID LEFT JOIN OnpubSAMaps AS samaps ON " .
          "sections.ID = samaps.sectionID LEFT JOIN OnpubArticles AS " .
          "articles ON samaps.articleID = articles.ID LEFT JOIN OnpubImages " .
          "AS articleimages ON articles.imageID = articleimages.ID " .
          "LEFT JOIN OnpubAAMaps AS aamaps ON articles.ID = " .
          "aamaps.articleID LEFT JOIN OnpubAuthors AS authors ON " .
          "aamaps.authorID = authors.ID LEFT JOIN OnpubImages AS " .
          "authorimages ON authors.imageID = authorimages.ID $where " .
          "ORDER BY $orderBy";
      }
      else {
        return
          "SELECT websites.ID, websites.imageID, websites.name, " .
          "websites.url, websites.imagesURL, websites.imagesDirectory, " .
          "websites.created, websites.modified, websiteimages.fileName, " .
          "websiteimages.description, websiteimages.url AS websiteImageURL, " .
          "websiteimages.created AS imageCreated, websiteimages.modified AS " .
          "imageModified, sections.ID AS sectionID, sections.imageID AS " .
          "sectionImageID, sections.websiteID AS sectionWebsiteID, " .
          "sections.parentID AS sectionParentID, sections.name AS " .
          "sectionName, sections.url AS sectionURL, sections.created AS sectionCreated, " .
          "sections.modified AS sectionModified, sectionimages.websiteID " .
          "AS sectionImageWebsiteID, sectionimages.fileName AS " .
          "sectionImageFileName, sectionimages.description AS " .
          "sectionImageDescription, sectionimages.url AS sectionImageURL, " .
          "sectionimages.created AS sectionImageCreated, " .
          "sectionimages.modified AS sectionImageModified FROM " .
          "OnpubWebsites AS websites LEFT JOIN OnpubImages AS websiteimages " .
          "ON websites.imageID = websiteimages.ID LEFT JOIN OnpubWSMaps AS " .
          "wsmaps ON websites.ID = wsmaps.websiteID LEFT JOIN OnpubSections " .
          "AS sections ON wsmaps.sectionID = sections.ID LEFT JOIN " .
          "OnpubImages AS sectionimages ON sections.imageID = " .
          "sectionimages.ID $where ORDER BY $orderBy";
      }
    }
    else {
      return "SELECT websites.ID, imageID, name, websites.url, imagesURL, " .
             "imagesDirectory, websites.created, websites.modified, " .
             "websiteimages.fileName, websiteimages.description, " .
             "websiteimages.url AS websiteImageURL, websiteimages.created " .
             "AS imageCreated, websiteimages.modified AS imageModified " .
             "FROM OnpubWebsites AS websites LEFT JOIN OnpubImages AS " .
             "websiteimages ON websites.imageID = websiteimages.ID " .
             "WHERE websites.ID = $ID";
    }
  }

  /**
   * @param OnpubWebsite $website Website whose ID you want to get.
   * @return int The ID of the website. NULL if the website does not exist in the database.
   */
  public function getID(OnpubWebsite $website)
  {
    $stmt = $this->pdo->prepare("SELECT ID FROM OnpubWebsites WHERE name = :name");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, FALSE);

    $name = OnpubDatabase::utf8Decode(trim($website->name));

    $stmt->bindParam(':name', $name);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, FALSE, $stmt->errorInfo());

    if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
      return $row["ID"];
    }

    return NULL;
  }

  /**
   * @param mixed $websites A single {@link OnpubWebsite} object or an array of {@link OnpubWebsite} objects (to insert multiple websites at a time).
   * @return mixed The ID(s) of the new website(s). An int will be returned if a single website was inserted. An array of ints will be returned if multiple websites were inserted.
   * @throws PDOException if there's a database error.
   */
  public function insert($websites)
  {
    $oimages = new OnpubImages($this->pdo, FALSE);
    $IDs = array();
    $isArray = TRUE;

    if (!is_array($websites)) {
      $websites = array ($websites);
      $isArray = FALSE;
    }

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("INSERT INTO OnpubWebsites (ID, imageID, name, url, imagesURL, imagesDirectory, created, modified) VALUES (:ID, :imageID, :name, :url, :imagesURL, :imagesDirectory, :created, :modified)");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    foreach ($websites as $website) {
      if ($website->image) {
        try {
          $imageID = $oimages->insert($website->image);
          $website->imageID = $imageID;
        }
        catch (PDOException $e) {
          if ($this->enableTransactions)
            $this->pdo->rollBack();

          throw $e;
        }
      }

      try {
        $ID = $this->getID($website);
      }
      catch (PDOException $e) {
        if ($this->enableTransactions)
          $this->pdo->rollBack();

        throw $e;
      }

      if ($ID) {
        $IDs[] = $ID;
        $website->ID = $ID;
      }
      else {
        $ID = $website->ID;
        $imageID = $website->imageID;
        $name = OnpubDatabase::utf8Decode(trim($website->name));
        $url = OnpubDatabase::utf8Decode(trim($website->url));
        $imagesURL = OnpubDatabase::utf8Decode(trim($website->imagesURL));
        $imagesDirectory = OnpubDatabase::utf8Decode(trim($website->imagesDirectory));
        $created = $website->getCreated()->format('Y-m-d H:i:s');
        $modified = $website->getModified()->format('Y-m-d H:i:s');

        $stmt->bindParam(':ID', $ID);
        $stmt->bindParam(':imageID', $imageID);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':imagesURL', $imagesURL);
        $stmt->bindParam(':imagesDirectory', $imagesDirectory);
        $stmt->bindParam(':created', $created);
        $stmt->bindParam(':modified', $modified);
        $result = $stmt->execute();
        OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

        $IDs[] = $this->pdo->lastInsertId();
        $website->ID = $this->pdo->lastInsertId();
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
   * @param OnpubWebsite $website The website to be updated.
   * @return int 1 if the website was updated. 0 if the website does not exist in the database.
   */
  public function update(OnpubWebsite $website)
  {
    $owsmaps = new OnpubWSMaps($this->pdo, FALSE);
    $now = new DateTime();

    if ($this->enableTransactions) {
      $status = $this->pdo->beginTransaction();
      OnpubDatabase::verifyTransaction($this->pdo, $status);
    }

    $stmt = $this->pdo->prepare("UPDATE OnpubWebsites SET imageID = :imageID, name = :name, url = :url, imagesURL = :imagesURL, imagesDirectory = :imagesDirectory, modified = :modified WHERE ID = :ID");
    OnpubDatabase::verifyPrepare($this->pdo, $stmt, $this->enableTransactions);

    $ID = $website->ID;
    $imageID = $website->imageID;
    $name = OnpubDatabase::utf8Decode(trim($website->name));
    $url = OnpubDatabase::utf8Decode(trim($website->url));
    $imagesURL = OnpubDatabase::utf8Decode(trim($website->imagesURL));
    $imagesDirectory = OnpubDatabase::utf8Decode(trim($website->imagesDirectory));
    $modified = $now->format('Y-m-d H:i:s');

    $stmt->bindParam(':ID', $ID);
    $stmt->bindParam(':imageID', $imageID);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':imagesURL', $imagesURL);
    $stmt->bindParam(':imagesDirectory', $imagesDirectory);
    $stmt->bindParam(':modified', $modified);
    $result = $stmt->execute();
    OnpubDatabase::verifyExecute($this->pdo, $result, $this->enableTransactions, $stmt->errorInfo());

    try {
      $owsmaps->delete($website->ID, NULL);
    }
    catch (PDOException $e) {
      if ($this->enableTransactions)
        $this->pdo->rollBack();

      throw $e;
    }

    $sections = $website->sections;
    $wsmaps = array();

    foreach ($sections as $section) {
      $wsmap = new OnpubWSMap();

      $wsmap->websiteID = $section->websiteID;
      $wsmap->sectionID = $section->ID;

      $wsmaps[] = $wsmap;
    }

    try {
      $owsmaps->insert($wsmaps);
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
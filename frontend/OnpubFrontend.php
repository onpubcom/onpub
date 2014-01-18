<?php

/* Onpub (TM)
 * Copyright (C) 2012 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

class OnpubFrontend
{
  protected $page;
  protected $website;
  protected $articles;
  protected $samaps;
  protected $loginStatus;
  public $currentSection;
  protected $parentSection;
  protected $requestedSectionID;
  protected $sections;
  public $currentArticle;
  protected $requestedArticleID;
  protected $lastPDOException;
  protected $schemaInstalled;
  protected $pdoInstalled;
  protected $labelUpdates = 'News';

  function __construct()
  {
  }

  protected function init()
  {
    global $onpub_db_host, $onpub_db_name, $onpub_db_user, $onpub_db_pass, $onpub_disp_website;

    if (!ini_get("date.timezone")) {
      date_default_timezone_set('America/New_York');
    }

    $this->page = 'home';
    $this->requestedSectionID = null;
    $this->currentSection = null;
    $this->requestedArticleID = null;
    $this->currentArticle = null;
    $this->schemaInstalled = false;

    if (class_exists('PDO')) {
      $this->pdoInstalled = true;

      try {
        $onpub_pdo = new PDO('mysql:host=' . $onpub_db_host . ';dbname=' . $onpub_db_name, $onpub_db_user, $onpub_db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES latin1 COLLATE latin1_general_ci'));
        $this->lastPDOException = null;
      }
      catch (PDOException $e) {
        // Connection error. PDO_MYSQL driver isn't installed or DB credentials are incorrect.
        $onpub_pdo = null;
        $this->lastPDOException = $e;
      }
    }
    else {
      // PDO is not install at all.
      $this->pdoInstalled = false;
      $onpub_pdo = null;
      $this->lastPDOException = null;
    }

    if ($onpub_pdo) {
      $onpub_websites = new OnpubWebsites($onpub_pdo);
      $this->sections = new OnpubSections($onpub_pdo);
      $this->articles = new OnpubArticles($onpub_pdo);
      $this->samaps = new OnpubSAMaps($onpub_pdo);
      $onpub_images = new OnpubImages($onpub_pdo);
      $onpub_wsmaps = new OnpubWSMaps($onpub_pdo);

      $qo = new OnpubQueryOptions();
      $qo->includeSections = true;

      try {
        $this->website = $onpub_websites->get($onpub_disp_website, $qo);
        $this->schemaInstalled = true;
        $this->lastPDOException = null;
      }
      catch (PDOException $e) {
        $this->website = null;

        if ($e->getCode() == 1146) {
          // Schema has not yet been installed.
          $this->schemaInstalled = false;
          $this->lastPDOException = null;
        }
        else {
          // There was some other DB error.
          $this->schemaInstalled = true;
          $this->lastPDOException = $e;
        }
      }
    }
    else {
      $this->website = null;
      $this->schemaInstalled = false;
    }

    if ($this->schemaInstalled) {
      // Check for legacy GET query params..
      if (isset($_GET['sectionID']) && !isset($_GET['articleID'])) {
        if (!ctype_digit($_GET['sectionID'])) {
          en('<span style="color: red;">sectionID must be an integer.</span>');
          exit;
        }

        $this->page = 'section';
        $this->requestedSectionID = $_GET['sectionID'];

        $this->currentSection = $this->sections->get($this->requestedSectionID);

        $this->parentSection = null;

        if ($this->currentSection && $this->currentSection->parentID) {
          $this->parentSection = $this->sections->get($this->currentSection->parentID);
        }
      }
      elseif (!isset($_GET['sectionID']) && isset($_GET['articleID'])) {
        if (!ctype_digit($_GET['articleID'])) {
          en('<span style="color: red;">articleID must be an integer.</span>');
          exit;
        }

        $this->page = 'article';
        $this->requestedArticleID = $_GET['articleID'];

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->currentArticle = $this->articles->get($this->requestedArticleID, $qo);
      }
      elseif (isset($_GET['sectionID']) && isset($_GET['articleID'])) {
        if (!ctype_digit($_GET['sectionID'])) {
          en('<span style="color: red;">sectionID must be an integer.</span>');
          exit;
        }

        if (!ctype_digit($_GET['articleID'])) {
          en('<span style="color: red;">articleID must be an integer.</span>');
          exit;
        }

        $this->page = 'section-article';
        $this->requestedSectionID = $_GET['sectionID'];
        $this->requestedArticleID = $_GET['articleID'];

        $this->currentSection = $this->sections->get($this->requestedSectionID);

        $this->parentSection = null;

        if ($this->currentSection && $this->currentSection->parentID) {
          $this->parentSection = $this->sections->get($this->currentSection->parentID);
        }

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->currentArticle = $this->articles->get($this->requestedArticleID, $qo);
      }
      elseif (isset($_GET['rss'])) {
        $this->page = 'rss';
      }

      // Check for new short/optimized GET query params..
      if (isset($_GET['s']) && !isset($_GET['a'])) {
        if (!ctype_digit($_GET['s'])) {
          en('<span style="color: red;">s must be an integer.</span>');
          exit;
        }

        $this->page = 'section';
        $this->requestedSectionID = $_GET['s'];

        $this->currentSection = $this->sections->get($this->requestedSectionID);

        $this->parentSection = null;

        if ($this->currentSection && $this->currentSection->parentID) {
          $this->parentSection = $this->sections->get($this->currentSection->parentID);
        }
      }
      elseif (!isset($_GET['s']) && isset($_GET['a'])) {
        if (!ctype_digit($_GET['a'])) {
          en('<span style="color: red;">a must be an integer.</span>');
          exit;
        }

        $this->page = 'article';
        $this->requestedArticleID = $_GET['a'];

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->currentArticle = $this->articles->get($_GET['a'], $qo);
      }
      elseif (isset($_GET['s']) && isset($_GET['a'])) {
        if (!ctype_digit($_GET['s'])) {
          en('<span style="color: red;">s must be an integer.</span>');
          exit;
        }

        if (!ctype_digit($_GET['a'])) {
          en('<span style="color: red;">a must be an integer.</span>');
          exit;
        }

        $this->page = 'section-article';
        $this->requestedSectionID = $_GET['s'];
        $this->requestedArticleID = $_GET['a'];

        $this->currentSection = $this->sections->get($this->requestedSectionID);

        $this->parentSection = null;

        if ($this->currentSection && $this->currentSection->parentID) {
          $this->parentSection = $this->sections->get($this->currentSection->parentID);
        }

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->currentArticle = $this->articles->get($this->requestedArticleID, $qo);
      }
      elseif (isset($_GET['rss'])) {
        $this->page = 'rss';
      }
    }
  }

  public function display()
  {
    global $onpub_dir_frontend;

    $this->init();

    switch ($this->page) {
      case 'rss':
      include $onpub_dir_frontend . 'libs/FeedWriter.php';
      $this->rss();
      break;

      default:
      $this->skel();
      break;
    }
  }

  protected function meta()
  {
    en('<meta name="viewport" content="width=device-width; initial-scale=1.0">');
    en('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">');
    en('<meta http-equiv="Content-Style-Type" content="text/css">');

    if ($this->currentArticle) {
      // Add the meta description tag for article pages.
      $description = $this->currentArticle->getSummary();

      if ($description) {
        if (substr($description, -1, 1) == '.') {
          $description = $description . '..';
        }
        else {
          $description = $description . '...';
        }

        en('<meta name="description" content="' . $description . '">');
      }
    }
  }

  protected function title()
  {
    if ($this->website) {
      if ($this->page == 'home') {
        en('<title>' . $this->website->name . '</title>');
      }
      elseif ($this->page == 'section') {
        if ($this->currentSection) {
          if ($this->parentSection) {
            en('<title>' . $this->currentSection->name . ' - ' . $this->parentSection->name . ' - ' . $this->website->name . '</title>');
          }
          else {
            en('<title>' . $this->currentSection->name . ' - ' . $this->website->name . '</title>');
          }
        }
        else {
          en('<title>' . $this->website->name . ' - Section ' . $this->requestedSectionID . ' not found...</title>');
        }
      }
      elseif ($this->page == 'article') {
        if ($this->currentArticle) {
          en('<title>' . $this->currentArticle->title . ' - ' . $this->website->name . '</title>');
        }
        else {
          en('<title>' . $this->website->name . ' - Article ' . $this->requestedArticleID . ' not found...</title>');
        }
      }
      elseif ($this->page == 'section-article') {
        if ($this->currentSection && $this->currentArticle) {
          if ($this->parentSection) {
            en('<title>' . $this->currentArticle->title . ' - ' . $this->currentSection->name . ' - ' . $this->parentSection->name . ' - ' . $this->website->name . '</title>');
          }
          else {
            en('<title>' . $this->currentArticle->title . ' - ' . $this->currentSection->name . ' - ' . $this->website->name . '</title>');
          }
        }

        if ($this->currentSection && !$this->currentArticle) {
          en('<title>' . $this->website->name . ' - Article ' . $this->requestedArticleID . ' not found...</title>');
        }

        if (!$this->currentSection && $this->currentArticle) {
          en('<title>' . $this->website->name . ' - Section ' . $this->requestedSectionID . ' not found...</title>');
        }

        if (!$this->currentSection && !$this->currentArticle) {
          en('<title>' . $this->website->name . ' - Section ' . $this->requestedSectionID . ' and Article ' . $this->requestedArticleID . ' not found...</title>');
        }
      }
    }
    else {
      en('<title>Onpub</title>');
    }
  }

  protected function header()
  {
    global $onpub_dir_manage;

    if ($this->website) {
      if ($this->website->image) {
        en('<div id="onpub-logo"><a href="index.php"><img src="' . addTrailingSlash($this->website->imagesURL) . $this->website->image->fileName . '" alt="' . $this->website->image->fileName . '" title="' . $this->website->image->description . '"></a></div>');
      }
      else {
        en('<div id="onpub-logo" style="margin-bottom: .25em; font-size: 197%;"><a class="onpub-footer-nav" href="index.php">' . $this->website->name . '</a></div>');
      }
    }
    else {
      en('<div id="onpub-logo"><a href="index.php"><img src="' . $onpub_dir_manage . 'images/onpub-small.png" alt="Onpub" title="Onpub"></a></div>');
    }
  }

  protected function menuSubSections($section)
  {
    global $onpub_disp_friendly_urls;
      
    $subsections = $section->sections;

    foreach ($subsections as $sub) {
      if ($sub->url) {
        en('<li class="yui3-menuitem">');
        en('<a class="yui3-menuitem-content" href="' . $this->friendlyURLs($sub->url, true) . '">' . $sub->name . '</a>');
        en('</li>');
      }
      else {
        en('<li>');
        
        if ($onpub_disp_friendly_urls) {
          en('<a class="yui3-menu-label" href="' . $this->generateFriendlyURL($sub) . '">' . $sub->name . '</a>');            
        }
        else {
          en('<a class="yui3-menu-label" href="index.php?s=' . $sub->ID . '">' . $sub->name . '</a>');
        }
        
        en('<div id="s' . $sub->ID . '" class="yui3-menu">');
        en('<div class="yui3-menu-content">');
        en('<ul>');

        $articles = $this->articles->select(null, $sub->ID);

        foreach ($articles as $a) {
          if ($a->url) {
            en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $this->friendlyURLs($a->url, true) . '">' . $a->title . '</a></li>');
          }
          else {
            if ($onpub_disp_friendly_urls) {
              en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $this->generateFriendlyURL($sub, $a) . '">' . $a->title . '</a></li>');
            }
            else {
              en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $sub->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
            }
          }
        }

        if (sizeof($sub->sections)) {
          $this->menuSubSections($sub);
        }

        en('</ul>');
        en('</div>');
        en('</div>');
        en('</li>');
      }
    }
  }

  protected function menu()
  {
    global $onpub_disp_menu, $onpub_disp_friendly_urls;

    if ($this->website) {
      if ($onpub_disp_menu) {
        $sections = $this->website->sections;

        if (sizeof($sections)) {
          en('<div id="onpub-menubar" class="yui3-menu yui3-menu-horizontal yui3-menubuttonnav">');
          en('<div class="yui3-menu-content">');
          en('<ul>');

          foreach ($sections as $s) {
            if ($s->url) {
              en('<li class="yui3-menuitem">');
              en('<a class="yui3-menuitem-content" href="' . $this->friendlyURLs($s->url, true) . '">' . $s->name . '</a>');
              en('</li>');
            }
            else {
              en('<li>');
              
              if ($onpub_disp_friendly_urls) {
                en('<a class="yui3-menu-label" href="' . $this->generateFriendlyURL($s) . '"><em>' . $s->name . '</em></a>');
              }
              else {
                en('<a class="yui3-menu-label" href="index.php?s=' . $s->ID . '"><em>' . $s->name . '</em></a>');                  
              }
              
              en('<div id="s' . $s->ID . '" class="yui3-menu">');
              en('<div class="yui3-menu-content">');
              en('<ul>');

              $articles = $this->articles->select(null, $s->ID);

              foreach ($articles as $a) {
                if ($a->url) {
                  en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $this->friendlyURLs($a->url, true) . '">' . $a->title . '</a></li>');
                }
                else {
                  if ($onpub_disp_friendly_urls) {
                    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $this->generateFriendlyURL($s, $a) . '">' . $a->title . '</a></li>');                      
                  }
                  else {
                    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
                  }
                }
              }

              $this->menuSubSections($s);

              en('</ul>');
              en('</div>');
              en('</div>');
              en('</li>');
            }
          }

          en('</ul>');
          en('</div>');
          en('</div>');
        }
      }
    }
  }

  protected function extractSectionIDs($sections)
  {
    static $ids = array();

    foreach ($sections as $s) {
      $ids[] = $s->ID;

      if (sizeof($s->sections)) {
        $this->extractSectionIDs($s->sections);
      }
    }

    return $ids;
  }
  
  protected function mapSections($sections)
  {
    static $map = array();

    foreach ($sections as $s) {
      $map["$s->ID"] = $s;

      if (sizeof($s->sections)) {
        $this->mapSections($s->sections);
      }
    }

    return $map;
  }

  protected function news()
  {
    global $onpub_disp_article, $onpub_disp_updates_num, $onpub_inc_article_updates,
           $onpub_dir_phpthumb, $onpub_dir_manage, $onpub_disp_friendly_urls;

    $qo = new OnpubQueryOptions();
    $qo->includeContent = true;
    $qo->includeAuthors = true;
    $qo->orderBy = 'created';
    $qo->order = 'DESC';
    $qo->rowLimit = $onpub_disp_updates_num + 1;

    $articles = $this->articles->select($qo, null, $this->website->ID);

    $isNews = sizeof($articles) && !(sizeof($articles) == 1 && $articles[0]->ID == $onpub_disp_article);

    en('<div class="yui3-g">');

    if ($isNews) {
      en('<div class="yui3-u-3-4">');
    }
    else {
      en('<div class="yui3-u-1">');
    }

    if ($onpub_disp_article) {
      $this->currentArticle = $this->articles->get($onpub_disp_article);

      if ($this->currentArticle) {
        en($this->friendlyURLs($this->currentArticle->content));
      }
      else {
        en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
      }
    }

    en('</div>');

    if ($isNews) {
      en('<div class="yui3-u-1-4">');

      en('<h1 style="margin-right: 0;">' . $this->labelUpdates . '</h1>');

      $websiteSectionIDs = $this->extractSectionIDs($this->website->sections);

      $i = 0;

      foreach ($articles as $a) {
        if ($i == $onpub_disp_updates_num) {
          break;
        }

        if ($a->ID != $onpub_disp_article) {
          $samaps = $this->samaps->select(null, null, $a->ID);

          $sectionIDs = array();

          foreach ($samaps as $samap) {
            $sectionIDs[] = $samap->sectionID;
          }

          $visibleSIDs = array_values(array_intersect($websiteSectionIDs, $sectionIDs));

          if ($a->url) {
            $url = $this->friendlyURLs($a->url, true);
          }
          else {
            if ($onpub_disp_friendly_urls) {
              $url = $this->generateFriendlyURL(NULL, $a, $visibleSIDs[0]);
            }
            else {
              $url = 'index.php?s=' . $visibleSIDs[0] . '&amp;a=' . $a->ID;
            }
          }

          en('<div class="yui3-g">');

          if ($a->image) {
            en('<div class="yui3-u-3-4">');
          }
          else {
            en('<div class="yui3-u-1">');
          }

          en('<h2 class="onpub-article-link"><a href="' . $url . '">' . $a->title . '</a></h2>');

          en('<p class="onpub-article-summary"><span class="onpub-article-summary-date">' . $a->getCreated()->format('M j, Y') . '</span>');

          if (($summary = $a->getSummary(10))) {
            if (substr($summary, -1, 1) == '.') {
              en(' &ndash; ' . $summary . '..</p>');
            }
            else {
              en(' &ndash; ' . $summary . '...</p>');
            }
          }
          else {
            en('</p>');
          }

          en('</div>');

          if ($a->image) {
            en('<div class="yui3-u-1-4">');
            $a->image->website = $this->website;
            en('<a href="' . $url . '"><img src="' . OnpubImages::getThumbURL('src=' . urlencode($a->image->getFullPath()) . '&w=50&f=png', $onpub_dir_phpthumb) . '" align="left" style="margin-right: 0.75em;" alt="' . $a->image->fileName . '" title="' . $a->image->description . '"></a>');
            en('</div>');
          }

          en('</div>');

          $i++;
        }
      }

      if (file_exists($onpub_inc_article_updates)) {
        en('<div>');
        include $onpub_inc_article_updates;
        en('</div>');
      }

      en('</div>');
    }
    
    en('</div>');
  }

  protected function home()
  {
    global $onpub_disp_updates, $onpub_disp_article, $onpub_dir_frontend,
           $onpub_dir_manage, $onpub_disp_friendly_urls;

    if ($this->website) {
      if ($onpub_disp_updates) {
        $this->news();
      }
      else {
        if ($onpub_disp_article) {
          $this->currentArticle = $this->articles->get($onpub_disp_article);

          if ($this->currentArticle) {
            en($this->friendlyURLs($this->currentArticle->content));
          }
          else {
            en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
          }
        }
      }

      if ($this->loginStatus && $this->currentArticle) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }
    }
    else {
      en('<h1 style="margin-right: 0;">Welcome to Onpub</h1>');

      if ($this->lastPDOException) {
        en('<h3><span class="onpub-error">PDOException:</span> ' . $this->lastPDOException->getMessage() . '</h3>');

        switch ($this->lastPDOException->getCode()) {
          case 1044: // Bad database name.
            en('<p>Onpub is unable to connect to the specified MySQL database.</p>');
            en('<p>Please make sure the Onpub frontend database configuration is correct.</p>');
            en('<p>Read <a href="http://onpub.com/index.php?s=8&a=96#activate" target="_blank">How to Activate the Onpub Frontend</a> for more information.</p>');
            break;

          case 1045: // Bad credentials.
            en('<p>Onpub is unable to connect to the specified MySQL database using the current username/password.</p>');
            en('<p>Please make sure the Onpub frontend database configuration is correct.</p>');
            en('<p>Read <a href="http://onpub.com/index.php?s=8&a=96#activate" target="_blank">How to Activate the Onpub Frontend</a> for more information.</p>');
            break;

          case 1064: // Bad query.
            en('<p>A database query error occured.</p>');
            break;

          case 2002: // Server is down
            en('<p>Onpub is unable to connect to the database server.</p>');
            en('<p>Start the specified MySQL server and reload this page to try again.</p>');
            break;

          case 2003: // Server is inaccessible (firewall, wrong port, etc.)
            en('<p>Onpub is unable to access the specified MySQL database server.</p>');
            break;

          case 2005: // Bad host name
            en('<p>Onpub is unable to connect to the specified MySQL database server host.</p>');
            en('<p>Please make sure the Onpub frontend database configuration is correct.</p>');
            en('<p>Read <a href="http://onpub.com/index.php?s=8&a=96#activate" target="_blank">How to Activate the Onpub Frontend</a> for more information.</p>');
            break;
        }

        if ($this->lastPDOException->getMessage() == 'could not find driver') {
          en('<p>PDO_MYSQL is not installed or is not configured correctly.</p>');
          en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
          en('<p>You will be unable to use Onpub until PDO_MYSQL is installed.</p>');
          en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
        }
      }
      else {
        if ($this->schemaInstalled) {
          en('<h3>You have successfully installed Onpub. This is the default Onpub frontend interface.</h3>');
          en('<p>The frontend is now configured to instantly display the content you publish using the Onpub content management interface.</p>');
          en('<p><a href="' . $onpub_dir_manage .
            'index.php?onpub=NewWebsite" target="_onpub">Create a website</a> and then reload this page to get started.</p>');
        }
        elseif ($this->pdoInstalled) {
          en('<h3>Almost there.. Follow the instructions below to complete the Onpub installation.</h3>');
          en('<p><a href="' . $onpub_dir_manage .
            'index.php" target="_onpub">Login</a> to the Onpub content management interface to install the Onpub database schema. You will be unable to publish a website until you perform this step.</p>');
          en('<p>See <a href="http://onpub.com/index.php?s=8&a=118" target="_blank">How to Install Onpub</a> for more information.</p>');
        }
        else {
          en('<h3><span class="onpub-error">PDO is not installed or is not configured correctly.</span></h3>');
          en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
          en('<p>You will be unable to use Onpub until PDO and PDO_MYSQL are installed.</p>');
          en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
        }
      }
    }
  }

  protected function footer()
  {
    global $onpub_disp_login, $onpub_dir_manage;

    $dt = new DateTime();

    en('<div class="yui3-g">');
    en('<div class="yui3-u-3-4">');

    if ($this->website) {
      en('<p>&copy; ' . $dt->format('Y') . ' <a class="onpub-footer-nav" href="index.php">' . $this->website->name . '</a>. All rights reserved.</p>');
    }
    else {
      en('<p>Onpub ' . ONPUBAPI_VERSION . ', &copy; 2012 <a class="onpub-footer-nav" href="http://onpub.com/" target="_blank">Onpub.com</a>.</p>');
    }

    en('</div>');
    en('<div class="yui3-u-1-4" style="text-align: right;">');

    if ($onpub_disp_login) {
      if ($this->loginStatus) {
        en('<span class="onpub-login"><a class="onpub-footer-nav" href="' . $onpub_dir_manage . 'index.php?onpub=Logout" target="_onpub">Logout</a></span>');
      }
      else {
        en('<span class="onpub-login"><a class="onpub-footer-nav" href="' . $onpub_dir_manage . 'index.php" target="_onpub">Login</a></span>');
      }
    }

    en('</div>');
    en('</div>');
  }

  protected function section()
  {
    global $onpub_dir_phpthumb, $onpub_dir_manage, $onpub_dir_frontend,
           $onpub_disp_friendly_urls;

    if ($this->currentSection) {
      // Get subsections.
      $sections = $this->sections->select(null, null, true, $this->currentSection->ID);
      $subsections = false;

      if (sizeof($sections) || $this->parentSection) {
        $subsections = true;
      }

      en('<div class="yui3-g">');

      if ($subsections) {
        en('<div class="yui3-u-3-4 onpub-section">');
        en('<h1>' . $this->currentSection->name . '</h1>');
      }
      else {
        en('<div class="yui3-u onpub-section">');
        en('<h1>' . $this->currentSection->name . '</h1>');
      }

      /* Code for displaying section image
      if ($this->onpub_section->imageID) {
        if (($section_image = $onpub_images->get($this->onpub_section->imageID))) {
          if ($this->onpub_website->ID == $section_image->websiteID) {
            en('<img src="' . addTrailingSlash($this->onpub_website->imagesURL) . $section_image->fileName . '" align="right" alt="' . $section_image->fileName . '" title="' . $section_image->description . '">');
          }
        }
      }
      */

      $qo = new OnpubQueryOptions();
      $qo->includeContent = true;

      $articles = $this->articles->select($qo, $this->currentSection->ID);
      $i = 0;
      $even = true;

      foreach ($articles as $a) {
        if ($i % 2 == 0) {
          $even = true;
        }
        else {
          $even = false;
        }

        if ($even) {
          en('<div class="yui3-g">');
          en('<div class="yui3-u-1-2">');
          en('<div style="padding-right: 1em;">');
        }
        else {
          en('<div class="yui3-u-1-2">');
          en('<div style="padding-right: 1em;">');
        }

        $url = '';

        if ($a->url) {
          $url = $this->friendlyURLs($a->url, true);
        }
        else {
          if ($onpub_disp_friendly_urls) {
            $url = $this->generateFriendlyURL($this->currentSection, $a);
          }
          else {
            $url = 'index.php?s=' . $this->requestedSectionID . '&amp;a=' . $a->ID;
          }
        }

        en('<div class="yui3-g">');

        if ($a->image) {
          en('<div class="yui3-u-3-4">');
        }
        else {
          en('<div class="yui3-u-1">');
        }

        en('<h2 class="onpub-article-link"><a href="' . $url . '">' . $a->title . '</a></h2>');

        en('<p class="onpub-article-summary"><span class="onpub-article-summary-date">' . $a->getCreated()->format('M j, Y') . '</span>');

        if (($summary = $a->getSummary(20))) {
          if (substr($summary, -1, 1) == '.') {
            en(' &ndash; ' . $summary . '..</p>');
          }
          else {
            en(' &ndash; ' . $summary . '...</p>');
          }
        }
        else {
          en('</p>');
        }

        en('</div>');

        if ($a->image) {
          en('<div class="yui3-u-1-4">');
          $a->image->website = $this->website;
          en('<a href="' . $url . '"><img src="' . OnpubImages::getThumbURL('src=' . urlencode($a->image->getFullPath()) . '&w=80&f=png', $onpub_dir_phpthumb) . '" align="left" style="margin-right: 0.75em;" alt="' . $a->image->fileName . '" title="' . $a->image->description . '"></a>');
          en('</div>');
        }

        en('</div>');

        if ($even) {
          if ($i + 1 == sizeof($articles)) {
            en('</div>');
            en('</div>');
            en('<div class="yui3-u-1-2">&nbsp;</div>');
            en('</div>');
          }
          else {
            en('</div>');
            en('</div>');
          }
        }
        else {
          en('</div>');
          en('</div>');
          en('</div>');
        }

        $i++;
      }

      if ($this->loginStatus) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditSection&amp;sectionID=' . $this->currentSection->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Section" title="Edit this Section"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditSection&amp;sectionID=' . $this->currentSection->ID .
          '" target="_onpub" title="Edit this Section">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      en('</div>');

      if ($subsections) {
        en('<div class="yui3-u-1-4 onpub-section-nav">');

        if ($this->parentSection) {
          if ($this->parentSection->url) {
            en('<h1 class="onpub-section-nav"><a href="' . $this->friendlyURLs($this->parentSection->url, true) . '" class="onpub-section-nav">' . $this->parentSection->name . '</a></h1>');
          }
          else {
            if ($onpub_disp_friendly_urls) {
              en('<h1 class="onpub-section-nav"><a href="' . $this->generateFriendlyURL($this->parentSection) . '" class="onpub-section-nav">' . $this->parentSection->name . '</a></h1>');              
            }
            else {
              en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $this->parentSection->ID . '" class="onpub-section-nav">' . $this->parentSection->name . '</a></h1>');
            }
          }

          $articles = $this->articles->select(null, $this->parentSection->ID);

          foreach ($articles as $a) {
            if ($a->url) {
              en('<div class="onpub-section-nav-link"><a href="' . $this->friendlyURLs($a->url, true) . '" class="onpub-section-nav">' . $a->title . '</a></div>');
            }
            else {
              if ($onpub_disp_friendly_urls) {
                en('<div class="onpub-section-nav-link"><a href="' . $this->generateFriendlyURL($this->parentSection, $a) . '" class="onpub-section-nav">' . $a->title . '</a></div>');                
              }
              else {
                en('<div class="onpub-section-nav-link"><a href="index.php?s=' . $this->parentSection->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></div>');
              }
            }
          }

          // Get subsections.
          $sections = $this->sections->select(null, null, true, $this->parentSection->ID);

          foreach ($sections as $s) {
            if ($s->ID == $this->currentSection->ID) {
              en('<div class="onpub-section-nav-link" style="font-weight: bold;">' . $s->name . '</div>');
            }
            else {
              if ($s->url) {
                en('<div class="onpub-section-nav-link"><a href="' . $this->friendlyURLs($s->url, true) . '" class="onpub-section-nav">' . $s->name . '</a></div>');
              }
              else {
                if ($onpub_disp_friendly_urls) {
                  en('<div class="onpub-section-nav-link"><a href="' . $this->generateFriendlyURL($s) . '" class="onpub-section-nav">' . $s->name . '</a></div>');                  
                }
                else {
                  en('<div class="onpub-section-nav-link"><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></div>');
                }
              }
            }
          }
        }
        else {
          foreach ($sections as $s) {
            if ($s->url) {
              en('<h1 class="onpub-section-nav"><a href="' . $this->friendlyURLs($s->url, true) . '" class="onpub-section-nav">' . $s->name . '</a></h1>');
            }
            else {
              if ($onpub_disp_friendly_urls) {
                en('<h1 class="onpub-section-nav"><a href="' . $this->generateFriendlyURL($s) . '" class="onpub-section-nav">' . $s->name . '</a></h1>');                
              }
              else {
                en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></h1>');
              }
            }

            $articles = $this->articles->select(null, $s->ID);

            foreach ($articles as $a) {
              if ($a->url) {
                en('<div class="onpub-section-nav-link"><a href="' . $this->friendlyURLs($a->url, true) . '" class="onpub-section-nav">' . $a->title . '</a></div>');
              }
              else {
                if ($onpub_disp_friendly_urls) {
                  en('<div class="onpub-section-nav-link"><a href="' . $this->generateFriendlyURL($s, $a) . '" class="onpub-section-nav">' . $a->title . '</a></div>');
                }
                else {
                  en('<div class="onpub-section-nav-link"><a href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></div>');                  
                }
              }
            }
          }
        }

        en('</div>');
      }

      en('</div>');
    }
    else {
      en('<h1>Section ' . $this->requestedSectionID . ' not found... <a href="index.php">Home</a></h1>');
    }
  }

  protected function sectionArticle()
  {
    global $onpub_inc_article_info, $onpub_dir_phpthumb, $onpub_inc_article_foot,
           $onpub_dir_manage, $onpub_dir_frontend, $onpub_disp_friendly_urls;

    if ($this->currentSection && $this->currentArticle) {
      en('<div class="yui3-g">');
      en('<div class="yui3-u-3-4 onpub-article">');

      en('<h1>' . $this->currentArticle->title . '</h1>');

      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      en('<p class="onpub-article-info">');

      $created = $this->currentArticle->getCreated();
      $modified = $this->currentArticle->getModified();

      if (function_exists('date_diff')) {
        $diff = $created->diff($modified);

        if (sizeof($this->currentArticle->authors)) {
          $author = $this->currentArticle->authors[0];

          if ($diff->days > 0) {
            en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
          }
          else {
            en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '.');
          }
        }
        else {
          if ($diff->days > 0) {
            en('Published: ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
          }
          else {
            en('Published: ' . $created->format('M j, Y') . '.');
          }
        }
      }
      else {
        if (sizeof($this->currentArticle->authors)) {
          $author = $this->currentArticle->authors[0];

          en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
        }
        else {
          en('Published: ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
        }
      }

      en('</p>');
      en('</div>');
      en('<div class="yui3-u-1-2">');

      if (file_exists($onpub_inc_article_info)) include $onpub_inc_article_info;

      en('</div>');
      en('</div>');

      en('<div style="padding-right: 1em;">');
      if ($this->currentArticle->image) {
        $this->currentArticle->image->website = $this->website;
        en('<img src="' . OnpubImages::getThumbURL('src=' . urlencode($this->currentArticle->image->getFullPath()) . '&w=280&f=png', $onpub_dir_phpthumb) . '" align="right" style="margin-right: 0.75em;" alt="' . $this->currentArticle->image->fileName . '" title="' . $this->currentArticle->image->description . '">');
      }

      en($this->friendlyURLs($this->currentArticle->content));
      en('</div>');

      if ($this->loginStatus) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      if (file_exists($onpub_inc_article_foot)) include $onpub_inc_article_foot;

      en('</div>');
      en('<div class="yui3-u-1-4 onpub-section-nav">');

      if ($onpub_disp_friendly_urls) {
        en('<h1 class="onpub-section-nav"><a href="' . $this->generateFriendlyURL($this->currentSection) . '" class="onpub-section-nav">' . $this->currentSection->name . '</a></h1>');        
      }
      else {
        en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $this->currentSection->ID . '" class="onpub-section-nav">' . $this->currentSection->name . '</a></h1>');
      }

      $articles = $this->articles->select(null, $this->currentSection->ID);

      foreach ($articles as $a) {
        if ($a->ID == $this->currentArticle->ID) {
          en('<div class="onpub-section-nav-link" style="font-weight: bold;">' . $a->title . '</div>');
        }
        else {
          if ($a->url) {
            en('<div class="onpub-section-nav-link"><a href="' . $this->friendlyURLs($a->url, true) . '" class="onpub-section-nav">' . $a->title . '</a></div>');
          }
          else {
            if ($onpub_disp_friendly_urls) {
              en('<div class="onpub-section-nav-link"><a href="' . $this->generateFriendlyURL($this->currentSection, $a) . '" class="onpub-section-nav">' . $a->title . '</a></div>');    
            }
            else {
              en('<div class="onpub-section-nav-link"><a href="index.php?s=' . $this->currentSection->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></div>');
            }
          }
        }
      }

      // Get subsections.
      $sections = $this->sections->select(null, null, true, $this->currentSection->ID);

      foreach ($sections as $s) {
        if ($s->url) {
          en('<div class="onpub-section-nav-link"><a href="' . $this->friendlyURLs($s->url, true) . '" class="onpub-section-nav">' . $s->name . '</a></div>');
        }
        else {
          if ($onpub_disp_friendly_urls) {
            en('<div class="onpub-section-nav-link"><a href="' . $this->generateFriendlyURL($s) . '" class="onpub-section-nav">' . $s->name . '</a></div>');            
          }
          else {
            en('<div class="onpub-section-nav-link"><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></div>');
          }
        }
      }

      en('</div>');
      en('</div>');
    }

    if ($this->currentSection && !$this->currentArticle) {
      en('<h1>Article ' . $this->requestedArticleID . ' not found... <a href="index.php">Home</a></h1>');
    }

    if (!$this->currentSection && $this->currentArticle) {
      en('<h1>Section ' . $this->requestedSectionID . ' not found... <a href="index.php">Home</a></h1>');
    }

    if (!$this->currentSection && !$this->currentArticle) {
      en('<h1>Section ' . $this->requestedSectionID . ' and Article ' . $this->requestedArticleID . ' not found... <a href="index.php">Home</a></h1>');
    }
  }

  protected function article()
  {
    global $onpub_inc_article_info, $onpub_dir_phpthumb, $onpub_dir_manage,
           $onpub_dir_frontend, $onpub_inc_article_foot, $onpub_disp_friendly_urls;

    en('<div class="yui3-g">');
    en('<div class="yui3-u onpub-article">');

    if ($this->currentArticle) {
      en('<h1 style="margin-right: 0;">' . $this->currentArticle->title . '</h1>');

      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      en('<p class="onpub-article-info">');

      $created = $this->currentArticle->getCreated();
      $modified = $this->currentArticle->getModified();

      if (function_exists('date_diff')) {
        $diff = $created->diff($modified);

        if (sizeof($this->currentArticle->authors)) {
          $author = $this->currentArticle->authors[0];

          if ($diff->days > 0) {
            en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
          }
          else {
            en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '.');
          }
        }
        else {
          if ($diff->days > 0) {
            en('Published: ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
          }
          else {
            en('Published: ' . $created->format('M j, Y') . '.');
          }
        }
      }
      else {
        if (sizeof($this->currentArticle->authors)) {
          $author = $this->currentArticle->authors[0];

          en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
        }
        else {
          en('Published: ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
        }
      }

      en('</p>');
      en('</div>');
      en('<div class="yui3-u-1-2">');

      if (file_exists($onpub_inc_article_info)) include $onpub_inc_article_info;

      en('</div>');
      en('</div>');

      if ($this->currentArticle->imageID) {
        $this->currentArticle->image->website = $this->website;
        en('<img src="' . OnpubImages::getThumbURL('src=' . urlencode($this->currentArticle->image->getFullPath()) . '&w=400&f=png', $onpub_dir_phpthumb) . '" align="right" alt="' . $this->currentArticle->image->fileName . '" title="' . $this->currentArticle->image->description . '">');
      }

      en($this->friendlyURLs($this->currentArticle->content));

      if ($this->loginStatus) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->currentArticle->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      if (file_exists($onpub_inc_article_foot)) include $onpub_inc_article_foot;
    }
    else {
      en('<h1>Article ' . $this->requestedArticleID . ' not found... <a href="index.php">Home</a></h1>');
    }

    en('</div>');
    en('</div>');
  }

  protected function skel()
  {
    global $onpub_disp_rss, $onpub_dir_yui, $onpub_inc_css, $onpub_inc_css_menu,
           $onpub_inc_head, $onpub_inc_banner, $onpub_dir_root, $onpub_yui_version,
           $onpub_dir_frontend, $onpub_inc_foot, $onpub_disp_friendly_urls;

    header("Content-Type: text/html; charset=iso-8859-1");

    if ($onpub_disp_friendly_urls) {
      $friendlyURL = $this->friendlyURLs($_SERVER['REQUEST_URI'], true);

      if ($_SERVER['REQUEST_URI'] != $friendlyURL) {
        // Received a request for a non-friendly URL.
        // Re-direct permanently to the friendly equivalent.
        header('Location: ' . $friendlyURL, true, 301);
        return;
      }
    }

    session_name("onpubpdo");
    session_set_cookie_params(0, '/', '', false, true);
    session_start();

    $this->loginStatus = false;

    if (isset($_SESSION['PDO_HOST']) && isset($_SESSION['PDO_USER']) && isset($_SESSION['PDO_PASSWORD']) && isset($_SESSION['PDO_DATABASE'])) {
      $this->loginStatus = true;
    }

    en('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
    en('<html>');
    en('<head>');

    $this->meta();

    $this->title();

    if ($this->website && $onpub_disp_rss) {
      en('<link rel="alternate" type="application/rss+xml" href="index.php?rss" title="' . $this->website->name . ' RSS Feed">');
    }

    if (file_exists($onpub_dir_yui)) {
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssreset/cssreset-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssfonts/cssfonts-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssgrids/cssgrids-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssbase/cssbase-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'node-menunav/assets/skins/sam/node-menunav.css">');
    }
    else {
      $onpub_dir_yui = null;
      en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' .
        $onpub_yui_version . '/build/cssreset/cssreset-min.css&' . $onpub_yui_version .
        '/build/cssfonts/cssfonts-min.css&' . $onpub_yui_version .
        '/build/cssgrids/cssgrids-min.css&' . $onpub_yui_version .
        '/build/cssbase/cssbase-min.css&' . $onpub_yui_version .
        '/build/node-menunav/assets/skins/sam/node-menunav.css">');
    }

    if (file_exists($onpub_inc_css)) {
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_inc_css . '">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_frontend . 'css/onpub.css">');
    }

    if (file_exists($onpub_inc_css_menu)) {
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_inc_css_menu . '">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_frontend . 'css/onpub-menu.css">');
    }

    en('<script type="text/javascript">');
    en('document.documentElement.className = "yui3-loading";');
    en('var onpub_dir_root = "' . $onpub_dir_root . '";');

    if ($onpub_dir_yui) {
      en('var onpub_dir_yui = "' . $onpub_dir_yui . '";');
    }
    else {
      en('var onpub_dir_yui = null;');
    }

    en('var onpub_yui_version = "' . $onpub_yui_version . '";');
    en('</script>');

    if (file_exists($onpub_inc_head)) include $onpub_inc_head;
    en('</head>');

    en('<body class="yui3-skin-sam">');

    if (file_exists($onpub_inc_banner)) {
      en('<div id="onpub-banner">');
      include $onpub_inc_banner;
      en('</div>');
    }

    en('<div id="onpub-header">');
    $this->header();
    en('</div>');

    en('<div id="onpub-page">');

    $this->menu();

    switch ($this->page)
    {
      case 'home':
        en('<div id="onpub-body">');
        $this->home();
        en('</div>');
        break;

      case 'section':
        en('<div id="onpub-body" style="padding-right: 0em;">');
        $this->section();
        en('</div>');
        break;

      case 'article':
        en('<div id="onpub-body">');
        $this->article();
        en('</div>');
        break;

      case 'section-article':
        en('<div id="onpub-body" style="padding-right: 0em;">');
        $this->sectionArticle();
        en('</div>');
        break;

      default: break;
    }

    en('</div>');

    en('<div id="onpub-footer">');
    en('<div id="onpub-footer-content">');
    $this->footer();
    en('</div>');
    en('</div>');

    if ($onpub_dir_yui) {
      en('<script type="text/javascript" src="' . $onpub_dir_yui . 'yui/yui-min.js"></script>');
    }
    else {
      en('<script type="text/javascript" src="http://yui.yahooapis.com/combo?' . $onpub_yui_version . '/build/yui/yui-min.js"></script>');
    }

    en('<script type="text/javascript" src="' . $onpub_dir_frontend . 'js/site.js"></script>');

    if (file_exists($onpub_inc_foot)) include $onpub_inc_foot;

    en('</body>');
    en('</html>');
  }

  protected function rss()
  {
    global $onpub_disp_rss, $onpub_disp_updates_num, $onpub_disp_friendly_urls;

    if ($this->website && $onpub_disp_rss) {
      // See the following OnpubAPI tutorial for more info:
      // http://onpub.com/index.php?s=20&a=78

      // This example is based on an example by Anis uddin Ahmad, the author of
      // Universal Feed Writer.

      //Creating an instance of FeedWriter class.
      //The constant RSS2 is passed to mention the version
      $feed = new FeedWriter(RSS2);

      //Setting the channel elements
      //Use wrapper functions for common channel elements
      $feed->setTitle($this->website->name);
      $feed->setLink(addTrailingSlash($this->website->url));
      $feed->setDescription('');

      //Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
      if ($this->website->image) {
        $feed->setImage($this->website->name, addTrailingSlash($this->website->url), addTrailingSlash($this->website->imagesURL) . $this->website->image->fileName);
      }
      else {
        $feed->setImage($this->website->name, addTrailingSlash($this->website->url), null);
      }

      //Use core setChannelElement() function for other optional channels
      $feed->setChannelElement('language', 'en-us');
      $feed->setChannelElement('pubDate', date(DATE_RSS, time()));

      $qo = new OnpubQueryOptions();
      $qo->includeContent = true;
      $qo->includeAuthors = true;
      $qo->orderBy = 'created';
      $qo->order = 'DESC';
      $qo->rowLimit = $onpub_disp_updates_num;

      $articles = $this->articles->select($qo, null, $this->website->ID);

      //Adding a feed. Genarally this portion will be in a loop and add all feeds.
      foreach ($articles as $article) {
        // Get the article's authors.
        $authors = $article->authors;

        //Create an empty FeedItem
        $newItem = $feed->createNewItem();

        //Add elements to the feed item
        //Use wrapper functions to add common feed elements
        // Use the OnpubArticle object to set the various properties of the FeedItem.
        $newItem->setTitle($article->title);

        $samaps = $this->samaps->select(null, null, $article->ID);

        if ($onpub_disp_friendly_urls) {
          $newItem->setLink(addTrailingSlash($this->website->url) . $this->generateFriendlyURL(NULL, $article, $samaps[0]->sectionID));
        }
        else {
          $newItem->setLink(addTrailingSlash($this->website->url) . 'index.php?s=' . $samaps[0]->sectionID . '&a=' . $article->ID);
        }

        //The parameter is a timestamp for setDate() function
        $newItem->setDate($article->getCreated()->format('c'));

        $newItem->setDescription($this->friendlyURLs($article->content));

        if (sizeof($authors)) {
          //Use core addElement() function for other supported optional elements
          $newItem->addElement('author', $authors[0]->displayAs);
        }

        //Now add the feed item
        $feed->addItem($newItem);
      }

      //OK. Everything is done. Now genarate the feed.
      $feed->genarateFeed();
    }
  }
  
  protected function generateFriendlyURL($section, $article = NULL, $sectionID = '')
  {
    $friendlyURL = '';

    if ($section && $section->name) {
      $friendlyURL = trim($section->name);
      $friendlyURL = strtolower($friendlyURL);
      $friendlyURL = preg_replace('/[\W\s]+/', '-', $friendlyURL);
      $friendlyURL = $friendlyURL . '-s' . $section->ID;
    }

    if ($article && $article->title) {
      $friendlyURL = trim($article->title);
      $friendlyURL = strtolower($friendlyURL);
      $friendlyURL = preg_replace('/[\W\s]+/', '-', $friendlyURL);

      if ($section && $section->ID) {
        $friendlyURL = $friendlyURL . '-s' . $section->ID;
      }
      elseif ($sectionID) {
        $friendlyURL = $friendlyURL . '-s' . $sectionID;
      }

      $friendlyURL = $friendlyURL . '-a' . $article->ID;
    }

    return $friendlyURL;
  }

  /**
   * Replaces all Onpub-style unfriendly URLs in the given $content with their
   * friendly equivalents.
   * @param string $content
   */
  protected function friendlyURLs($content, $staticLink = false)
  {
    global $onpub_disp_friendly_urls;

    if (!$onpub_disp_friendly_urls) return $content;

    $unfriendlyURLRegex = '%';
    $unfriendlyURLRegex .= '(';
    if (!$staticLink) $unfriendlyURLRegex .= '["]';
    $unfriendlyURLRegex .= '(' . $this->website->url . ')?index.php\?';
    $unfriendlyURLRegex .= 's(ectionID)?=(\d+)';
    $unfriendlyURLRegex .= '|';
    if (!$staticLink) $unfriendlyURLRegex .= '["]';
    $unfriendlyURLRegex .= '(' . $this->website->url . ')?index.php\?';
    $unfriendlyURLRegex .= 'a(rticleID)?=(\d+)';
    $unfriendlyURLRegex .= ')';
    $unfriendlyURLRegex .= '(';
    $unfriendlyURLRegex .= '&(amp;)?';
    $unfriendlyURLRegex .= '(';
    $unfriendlyURLRegex .= 's(ectionID)?=(\d+)';
    $unfriendlyURLRegex .= '|';
    $unfriendlyURLRegex .= 'a(rticleID)?=(\d+)';
    $unfriendlyURLRegex .= ')';
    $unfriendlyURLRegex .= ')?';
    if (!$staticLink) $unfriendlyURLRegex .= '["#]';
    $unfriendlyURLRegex .= '%';

    return preg_replace_callback($unfriendlyURLRegex, array($this, 'friendlyURLsCallback'), $content);
  }

  protected function friendlyURLsCallback($matches)
  {
    static $articles = array();
    static $sections = array();
    $queryOptions = new OnpubQueryOptions();
    $queryOptions->includeContent = FALSE;

    $url = $matches[0];

    $delimL = substr($url, 0, 1);
    if ($delimL != '"') $delimL = '';

    $delimR = substr($url, -1, 1);
    if ($delimR != '"' && $delimR != '#') $delimR = '';

    $articleID = isset($matches[7]) && $matches[7] ? $matches[7] : '';
    $sectionID = isset($matches[4]) && $matches[4] ? $matches[4] : '';

    if (!$articleID)
      $articleID = isset($matches[14]) && $matches[14] ? $matches[14] : '';

    if (!$sectionID)
      $sectionID = isset($matches[12]) && $matches[12] ? $matches[12] : '';

    if ($articleID && !isset($articles['a' . $articleID])) {
      if (($article = $this->articles->get($articleID, $queryOptions))) {
        $articles['a' . $article->ID] = $article;
      }
      else {
        // Article most likely does not exist anymore. Don't rewrite the URL.
        return $url;
      }
    }

    if ($articleID && isset($articles['a' . $articleID])) {
      $article = $articles['a' . $articleID];

      if (isset($matches[2]) && $matches[2])
        return $delimL . $matches[2] . $this->generateFriendlyURL(NULL, $article, $sectionID) . $delimR;

      return $delimL . $this->generateFriendlyURL(NULL, $article, $sectionID) . $delimR;
    }

    if ($sectionID && !isset($sections['s' . $sectionID])) {
      if (($section = $this->sections->get($sectionID))) {
        $sections['s' . $section->ID] = $section;
      }
    }

    if ($sectionID && isset($sections['s' . $sectionID])) {
      $section = $sections['s' . $sectionID];

      return $delimL . $this->generateFriendlyURL($section) . $delimR;
    }

    return $url;
  }
}

?>

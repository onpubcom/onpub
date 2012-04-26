<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

class OnpubFrontend
{
  private $onpub_index;
  private $onpub_website;
  private $onpub_articles;
  private $onpub_samaps;
  private $onpub_login_status;
  private $onpub_section;
  private $onpub_section_parent;
  private $onpub_section_id;
  private $onpub_sections;
  private $onpub_article;
  private $onpub_article_id;

  function __construct()
  {
  }

  private function init()
  {
    global $onpub_db_host, $onpub_db_name, $onpub_db_user, $onpub_db_pass, $onpub_disp_website;

    if (!ini_get("date.timezone")) {
      date_default_timezone_set ('America/New_York');
    }

    $this->onpub_index = 'home';
    $this->onpub_section_id = null;
    $this->onpub_section = null;
    $this->onpub_article_id = null;
    $this->onpub_article = null;
    $onpub_schema_installed = false;

    if (class_exists('PDO')) {
      $onpub_pdo_installed = true;

      try {
        $onpub_pdo = new PDO('mysql:host=' . $onpub_db_host . ';dbname=' . $onpub_db_name, $onpub_db_user, $onpub_db_pass);
        $onpub_pdo_exception = null;
      }
      catch (PDOException $e) {
        // Connection error. PDO_MYSQL driver isn't installed or DB credentials are incorrect.
        $onpub_pdo = null;
        $onpub_pdo_exception = $e;
      }
    }
    else {
      // PDO is not install at all.
      $onpub_pdo_installed = false;
      $onpub_pdo = null;
      $onpub_pdo_exception = null;
    }

    if ($onpub_pdo) {
      $onpub_websites = new OnpubWebsites($onpub_pdo);
      $this->onpub_sections = new OnpubSections($onpub_pdo);
      $this->onpub_articles = new OnpubArticles($onpub_pdo);
      $this->onpub_samaps = new OnpubSAMaps($onpub_pdo);
      $onpub_images = new OnpubImages($onpub_pdo);
      $onpub_wsmaps = new OnpubWSMaps($onpub_pdo);

      $qo = new OnpubQueryOptions();
      $qo->includeSections = true;

      try {
        $this->onpub_website = $onpub_websites->get($onpub_disp_website, $qo);
        $onpub_schema_installed = true;
        $onpub_pdo_exception = null;
      }
      catch (PDOException $e) {
        $this->onpub_website = null;

        if ($e->getCode() == 1146) {
          // Schema has not yet been installed.
          $onpub_schema_installed = false;
          $onpub_pdo_exception = null;
        }
        else {
          // There was some other DB error.
          $onpub_schema_installed = true;
          $onpub_pdo_exception = $e;
        }
      }
    }
    else {
      $this->onpub_website = null;
      $onpub_schema_installed = false;
    }

    if ($onpub_schema_installed) {
      // Check for legacy GET query params..
      if (isset($_GET['sectionID']) && !isset($_GET['articleID'])) {
        if (!ctype_digit($_GET['sectionID'])) {
          en('<span style="color: red;">sectionID must be an integer.</span>');
          exit;
        }

        $this->onpub_index = 'section';
        $this->onpub_section_id = $_GET['sectionID'];

        $this->onpub_section = $this->onpub_sections->get($this->onpub_section_id);

        $this->onpub_section_parent = null;

        if ($this->onpub_section && $this->onpub_section->parentID) {
          $this->onpub_section_parent = $this->onpub_sections->get($this->onpub_section->parentID);
        }
      }
      elseif (!isset($_GET['sectionID']) && isset($_GET['articleID'])) {
        if (!ctype_digit($_GET['articleID'])) {
          en('<span style="color: red;">articleID must be an integer.</span>');
          exit;
        }

        $this->onpub_index = 'article';
        $this->onpub_article_id = $_GET['articleID'];

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->onpub_article = $this->onpub_articles->get($this->onpub_article_id, $qo);
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

        $this->onpub_index = 'section-article';
        $this->onpub_section_id = $_GET['sectionID'];
        $this->onpub_article_id = $_GET['articleID'];

        $this->onpub_section = $this->onpub_sections->get($this->onpub_section_id);

        $this->onpub_section_parent = null;

        if ($this->onpub_section && $this->onpub_section->parentID) {
          $this->onpub_section_parent = $this->onpub_sections->get($this->onpub_section->parentID);
        }

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->onpub_article = $this->onpub_articles->get($this->onpub_article_id, $qo);
      }
      elseif (isset($_GET['rss'])) {
        $this->onpub_index = 'rss';
      }

      // Check for new short/optimized GET query params..
      if (isset($_GET['s']) && !isset($_GET['a'])) {
        if (!ctype_digit($_GET['s'])) {
          en('<span style="color: red;">s must be an integer.</span>');
          exit;
        }

        $this->onpub_index = 'section';
        $this->onpub_section_id = $_GET['s'];

        $this->onpub_section = $this->onpub_sections->get($this->onpub_section_id);

        $this->onpub_section_parent = null;

        if ($this->onpub_section && $this->onpub_section->parentID) {
          $this->onpub_section_parent = $this->onpub_sections->get($this->onpub_section->parentID);
        }
      }
      elseif (!isset($_GET['s']) && isset($_GET['a'])) {
        if (!ctype_digit($_GET['a'])) {
          en('<span style="color: red;">a must be an integer.</span>');
          exit;
        }

        $this->onpub_index = 'article';
        $this->onpub_article_id = $_GET['a'];

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->onpub_article = $this->onpub_articles->get($_GET['a'], $qo);
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

        $this->onpub_index = 'section-article';
        $this->onpub_section_id = $_GET['s'];
        $this->onpub_article_id = $_GET['a'];

        $this->onpub_section = $this->onpub_sections->get($this->onpub_section_id);

        $this->onpub_section_parent = null;

        if ($this->onpub_section && $this->onpub_section->parentID) {
          $this->onpub_section_parent = $this->onpub_sections->get($this->onpub_section->parentID);
        }

        $qo = new OnpubQueryOptions();
        $qo->includeAuthors = true;
        $this->onpub_article = $this->onpub_articles->get($this->onpub_article_id, $qo);
      }
      elseif (isset($_GET['rss'])) {
        $this->onpub_index = 'rss';
      }
    }
  }

  public function display()
  {
    global $onpub_dir_frontend;

    $this->init();

    switch ($this->onpub_index) {
      case 'rss':
      include $onpub_dir_frontend . 'libs/FeedWriter.php';
      $this->rss();
      break;

      default:
      $this->skel();
      break;
    }
  }

  protected function title()
  {
    global $onpub_disp_rss;

    if ($this->onpub_website) {
      if ($this->onpub_index == 'home') {
        en('<title>' . $this->onpub_website->name . '</title>');
      }
      elseif ($this->onpub_index == 'section') {
        if ($this->onpub_section) {
          if ($this->onpub_section_parent) {
            en('<title>' . $this->onpub_section->name . ' - ' . $this->onpub_section_parent->name . ' - ' . $this->onpub_website->name . '</title>');
          }
          else {
            en('<title>' . $this->onpub_section->name . ' - ' . $this->onpub_website->name . '</title>');
          }
        }
        else {
          en('<title>' . $this->onpub_website->name . ' - Section ' . $this->onpub_section_id . ' not found...</title>');
        }
      }
      elseif ($this->onpub_index == 'article') {
        if ($this->onpub_article) {
          en('<title>' . $this->onpub_article->title . ' - ' . $this->onpub_website->name . '</title>');
        }
        else {
          en('<title>' . $this->onpub_website->name . ' - Article ' . $this->onpub_article_id . ' not found...</title>');
        }
      }
      elseif ($this->onpub_index == 'section-article') {
        if ($this->onpub_section && $this->onpub_article) {
          if ($this->onpub_section_parent) {
            en('<title>' . $this->onpub_article->title . ' - ' . $this->onpub_section->name . ' - ' . $this->onpub_section_parent->name . ' - ' . $this->onpub_website->name . '</title>');
          }
          else {
            en('<title>' . $this->onpub_article->title . ' - ' . $this->onpub_section->name . ' - ' . $this->onpub_website->name . '</title>');
          }
        }

        if ($this->onpub_section && !$this->onpub_article) {
          en('<title>' . $this->onpub_website->name . ' - Article ' . $this->onpub_article_id . ' not found...</title>');
        }

        if (!$this->onpub_section && $this->onpub_article) {
          en('<title>' . $this->onpub_website->name . ' - Section ' . $this->onpub_section_id . ' not found...</title>');
        }

        if (!$this->onpub_section && !$this->onpub_article) {
          en('<title>' . $this->onpub_website->name . ' - Section ' . $this->onpub_section_id . ' and Article ' . $this->onpub_article_id . ' not found...</title>');
        }
      }
    }
    else {
      en('<title>Onpub</title>');
    }
  }

  protected function hd()
  {
    if ($this->onpub_website) {
      if ($this->onpub_website->image) {
        en('<div id="onpub-logo"><a href="index.php"><img src="' . addTrailingSlash($this->onpub_website->imagesURL) . $this->onpub_website->image->fileName . '" alt="' . $this->onpub_website->image->fileName . '" title="' . $this->onpub_website->image->description . '"></a></div>');
      }
      else {
        en('<div id="onpub-logo" style="margin-bottom: .5em;"><a href="index.php">' . $this->onpub_website->name . '</a></div>');
      }
    }
    else {
      en('<div id="onpub-logo"><a href="index.php"><img src="' . $onpub_dir_manage . 'images/onpub-small.png" alt="Onpub" title="Onpub"></a></div>');
    }
  }

  protected function onpub_output_sub_sections($section)
  {
    $subsections = $section->sections;

    foreach ($subsections as $sub) {
      if ($sub->url) {
        en('<li class="yui3-menuitem">');
        en('<a class="yui3-menuitem-content" href="' . $sub->url . '">' . $sub->name . '</a>');
        en('</li>');
      }
      else {
        en('<li>');
        en('<a class="yui3-menu-label" href="index.php?s=' . $sub->ID . '">' . $sub->name . '</a>');
        en('<div class="yui3-menu">');
        en('<div class="yui3-menu-content">');
        en('<ul>');

        $articles = $this->onpub_articles->select(null, $sub->ID);

        foreach ($articles as $a) {
          if ($a->url) {
            en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $a->url . '">' . $a->title . '</a></li>');
          }
          else {
            en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $sub->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
          }
        }

        if (sizeof($sub->sections)) {
          onpub_output_sub_sections($sub);
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
    global $onpub_disp_menu;

    if ($this->onpub_website) {
      if ($onpub_disp_menu) {
        $sections = $this->onpub_website->sections;

        if (sizeof($sections)) {
          en('<div id="onpub-menubar" class="yui3-menu yui3-menu-horizontal yui3-menubuttonnav">');
          en('<div class="yui3-menu-content">');
          en('<ul>');

          $i = 0;

          foreach ($sections as $s) {
            if ($s->url) {
              en('<li class="yui3-menuitem">');
              if ($i) {
                en('<a class="yui3-menuitem-content" href="' . $s->url . '">' . $s->name . '</a>');
              }
              else {
                en('<a class="yui3-menuitem-content" href="' . $s->url . '">' . $s->name . '</a>');
              }
              en('</li>');
            }
            else {
              en('<li>');
              if ($i) {
                en('<a class="yui3-menu-label" href="index.php?s=' . $s->ID . '"><em>' . $s->name . '</em></a>');
              }
              else {
                en('<a class="yui3-menu-label" href="index.php?s=' . $s->ID . '"><em>' . $s->name . '</em></a>');
              }
              en('<div class="yui3-menu">');
              en('<div class="yui3-menu-content">');
              en('<ul>');

              $articles = $this->onpub_articles->select(null, $s->ID);

              foreach ($articles as $a) {
                if ($a->url) {
                  en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $a->url. '">' . $a->title . '</a></li>');
                }
                else {
                  en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
                }
              }

              $this->onpub_output_sub_sections($s);

              en('</ul>');
              en('</div>');
              en('</div>');
              en('</li>');
            }

            $i++;
          }

          en('</ul>');
          en('</div>');
          en('</div>');
        }
      }
    }
  }

  private function onpub_extract_section_ids($sections)
  {
    static $ids = array();

    foreach ($sections as $s) {
      $ids[] = $s->ID;

      if (sizeof($s->sections)) {
        onpub_extract_section_ids($s->sections);
      }
    }

    return $ids;
  }

  protected function home()
  {
    global $onpub_disp_updates, $onpub_disp_article, $onpub_disp_updates_num,
           $onpub_disp_rss, $onpub_dir_frontend, $onpub_dir_phpthumb,
           $onpub_inc_article_updates, $onpub_dir_manage;

    if ($this->onpub_website) {
      if ($onpub_disp_updates) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-3-4">');

        if ($onpub_disp_article) {
          $this->onpub_article = $this->onpub_articles->get($onpub_disp_article);

          if ($this->onpub_article) {
            en($this->onpub_article->content);
          }
          else {
            en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
          }
        }

        en('</div>');
        en('<div class="yui3-u-1-4">');

        $qo = new OnpubQueryOptions();
        $qo->includeContent = true;
        $qo->includeAuthors = true;
        $qo->orderBy = 'created';
        $qo->order = 'DESC';
        $qo->rowLimit = $onpub_disp_updates_num + 1;

        $articles = $this->onpub_articles->select($qo, null, $this->onpub_website->ID);

        if (sizeof($articles) && !(sizeof($articles) == 1 && $articles[0]->ID == $onpub_disp_article)) {
          if ($onpub_disp_rss)
          {
            en('<h1 style="margin-right: 0;">What\'s New <a href="index.php?rss"><img src="' . $onpub_dir_frontend . 'images/rss.png" width="14" height="14" alt="' . $this->onpub_website->name . ' RSS Feed" title="' . $this->onpub_website->name . ' RSS Feed"></a></h1>');
          }
          else
          {
            en('<h1 style="margin-right: 0;">What\'s New</h1>');
          }

          $onpub_website_section_ids = $this->onpub_extract_section_ids($this->onpub_website->sections);

          $i = 0;

          foreach ($articles as $a) {
            if ($i == $onpub_disp_updates_num) {
              break;
            }

            if ($a->ID != $onpub_disp_article) {
              $samaps = $this->onpub_samaps->select(null, null, $a->ID);

              $sectionIDs = array();

              foreach ($samaps as $samap) {
                $sectionIDs[] = $samap->sectionID;
              }

              $visibleSIDs = array_values(array_intersect($onpub_website_section_ids, $sectionIDs));

              if ($a->url) {
                $url = $a->url;
              }
              else {
                $url = 'index.php?s=' . $visibleSIDs[0] . '&amp;a=' . $a->ID;
              }

              en('<div class="yui3-g">');

              if ($a->image) {
                en('<div class="yui3-u-1-4">');
                $a->image->website = $this->onpub_website;
                en('<a href="' . $url . '"><img src="' . OnpubImages::getThumbURL('src=' . urlencode($a->image->getFullPath()) . '&w=50&f=png', $onpub_dir_phpthumb) . '" align="left" style="margin-right: 0.75em;" alt="' . $a->image->fileName . '" title="' . $a->image->description . '"></a>');
                en('</div>');
                en('<div class="yui3-u-3-4">');
              }
              else {
                en('<div class="yui3-u-1">');
              }

              en('<h2 class="onpub-article-link"><a href="' . $url . '">' . $a->title . '</a></h2>');

              en('<p class="onpub-article-summary">' . $a->getCreated()->format('M j, Y'));

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

              en('</div>');

              $i++;
            }
          }
        }

        if (file_exists($onpub_inc_article_updates)) {
          en('<div>');
          include $onpub_inc_article_updates;
          en('</div>');
        }

        en('</div>');
        en('</div>');
      }
      else {
        if ($onpub_disp_article) {
          $this->onpub_article = $this->onpub_articles->get($onpub_disp_article);

          if ($this->onpub_article) {
            en($this->onpub_article->content);
          }
          else {
            en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
          }
        }
      }

      if ($this->onpub_login_status && $this->onpub_article) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }
    }
    else {
      en('<h1 style="margin-right: 0;">Welcome to Onpub</h1>');

      if ($onpub_pdo_exception) {
        en('<h3><span class="onpub-error">PDOException:</span> ' . $onpub_pdo_exception->getMessage() . '</h3>');

        switch ($onpub_pdo_exception->getCode()) {
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

        if ($onpub_pdo_exception->getMessage() == 'could not find driver') {
          en('<p>PDO_MYSQL is not installed or is not configured correctly.</p>');
          en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
          en('<p>You will be unable to use Onpub until PDO_MYSQL is installed.</p>');
          en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
        }
      }
      else {
        if ($onpub_schema_installed) {
          en('<h3>You have successfully installed Onpub. This is the default Onpub frontend interface.</h3>');
          en('<p>The frontend is now configured to instantly display the content you publish using the Onpub content management interface.</p>');
          en('<p><a href="' . $onpub_dir_manage .
            'index.php?onpub=NewWebsite" target="_onpub">Create a website</a> and then reload this page to get started.</p>');
        }
        elseif ($onpub_pdo_installed) {
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

  protected function ft()
  {
    global $onpub_disp_login, $onpub_dir_manage;

    $dt = new DateTime();

    en('<div class="yui3-g">');
    en('<div class="yui3-u-3-4">');

    if ($this->onpub_website) {
      en('<p>&copy; ' . $dt->format('Y') . ' <a href="index.php">' . $this->onpub_website->name . '</a>. All rights reserved.</p>');
    }
    else {
      en('<p>Onpub ' . ONPUBAPI_VERSION . ', &copy; 2011 <a href="http://onpub.com/" target="_blank">Onpub.com</a>.</p>');
    }

    en('</div>');
    en('<div class="yui3-u-1-4">');

    if ($onpub_disp_login) {
      if ($this->onpub_login_status) {
        en('<p style="text-align: right;">Powered by <a href="' . $onpub_dir_manage . 'index.php" target="_onpub">Onpub</a> &raquo; ');
        en('<a href="' . $onpub_dir_manage . 'index.php?onpub=Logout" target="_onpub">Logout</a></p>');
      }
      else {
        en('<p style="text-align: right;">Powered by <a href="http://onpub.com/" target="_blank">Onpub</a> &raquo; ');
        en('<a href="' . $onpub_dir_manage . 'index.php" target="_onpub">Login</a></p>');
      }
    }

    en('</div>');
    en('</div>');
  }

  protected function section()
  {
    global $onpub_dir_phpthumb, $onpub_dir_manage, $onpub_dir_frontend;

    if ($this->onpub_section) {
      // Get subsections.
      $sections = $this->onpub_sections->select(null, null, true, $this->onpub_section->ID);
      $subsections = false;

      if (sizeof($sections) || $this->onpub_section_parent) {
        $subsections = true;
      }

      en('<div class="yui3-g">');

      if ($subsections) {
        en('<div class="yui3-u-3-4">');
        en('<h1>' . $this->onpub_section->name . '</h1>');
      }
      else {
        en('<div class="yui3-u-1">');
        en('<h1>' . $this->onpub_section->name . '</h1>');
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

      $articles = $this->onpub_articles->select($qo, $this->onpub_section->ID);
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
          $url = $a->url;
        }
        else {
          $url = 'index.php?s=' . $this->onpub_section_id . '&amp;a=' . $a->ID;
        }

        en('<div class="yui3-g">');

        if ($a->image) {
          en('<div class="yui3-u-1-4">');
          $a->image->website = $this->onpub_website;
          en('<a href="' . $url . '"><img src="' . OnpubImages::getThumbURL('src=' . urlencode($a->image->getFullPath()) . '&w=80&f=png', $onpub_dir_phpthumb) . '" align="left" style="margin-right: 0.75em;" alt="' . $a->image->fileName . '" title="' . $a->image->description . '"></a>');
          en('</div>');
          en('<div class="yui3-u-3-4">');
        }
        else {
          en('<div class="yui3-u-1">');
        }

        en('<h2 class="onpub-article-link"><a href="' . $url . '">' . $a->title . '</a></h2>');

        en('<p class="onpub-article-summary">' . $a->getCreated()->format('M j, Y'));

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

      if ($this->onpub_login_status) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditSection&amp;sectionID=' . $this->onpub_section->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Section" title="Edit this Section"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditSection&amp;sectionID=' . $this->onpub_section->ID .
          '" target="_onpub" title="Edit this Section">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      en('</div>');

      if ($subsections) {
        en('<div class="yui3-u-1-4 onpub-section-nav">');

        if ($this->onpub_section_parent) {
          if ($this->onpub_section_parent->url) {
            en('<h1 class="onpub-section-nav"><a href="' . $this->onpub_section_parent->url . '" class="onpub-section-nav">' . $this->onpub_section_parent->name . '</a></h1>');
          }
          else {
            en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $this->onpub_section_parent->ID . '" class="onpub-section-nav">' . $this->onpub_section_parent->name . '</a></h1>');
          }

          $articles = $this->onpub_articles->select(null, $this->onpub_section_parent->ID);

          en('<ul class="onpub-section-nav">');

          foreach ($articles as $a) {
            if ($a->url) {
              en('<li><a href="' . $a->url . '" class="onpub-section-nav">' . $a->title . '</a></li>');
            }
            else {
              en('<li><a href="index.php?s=' . $this->onpub_section_parent->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></li>');
            }
          }

          // Get subsections.
          $sections = $this->onpub_sections->select(null, null, true, $this->onpub_section_parent->ID);

          foreach ($sections as $s) {
            if ($s->ID == $this->onpub_section->ID) {
              en('<li>' . $s->name . '</li>');
            }
            else {
              if ($s->url) {
                en('<li><a href="' . $s->url . '" class="onpub-section-nav">' . $s->name . '</a></li>');
              }
              else {
                en('<li><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></li>');
              }
            }
          }

          en('</ul>');
        }
        else {
          foreach ($sections as $s) {
            if ($s->url) {
              en('<h1 class="onpub-section-nav"><a href="' . $s->url . '" class="onpub-section-nav">' . $s->name . '</a></h1>');
            }
            else {
              en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></h1>');
            }

            $articles = $this->onpub_articles->select(null, $s->ID);

            en('<ul class="onpub-section-nav">');

            foreach ($articles as $a) {
              if ($a->url) {
                en('<li><a href="' . $a->url . '" class="onpub-section-nav">' . $a->title . '</a></li>');
              }
              else {
                en('<li><a href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></li>');
              }
            }

            en('</ul>');
          }
        }

        en('</div>');
      }

      en('</div>');
    }
    else {
      en('<h1>Section ' . $this->onpub_section_id . ' not found... <a href="index.php">Home</a></h1>');
    }
  }

  protected function sectionarticle()
  {
    global $onpub_inc_article_info, $onpub_dir_phpthumb, $onpub_inc_article_foot,
           $onpub_dir_manage, $onpub_dir_frontend;

    if ($this->onpub_section && $this->onpub_article) {
      en('<div class="yui3-g">');
      en('<div class="yui3-u-3-4">');

      en('<h1>' . $this->onpub_article->title . '</h1>');

      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      en('<p class="onpub-article-info">');

      $created = $this->onpub_article->getCreated();
      $modified = $this->onpub_article->getModified();

      if (function_exists('date_diff')) {
        $diff = $created->diff($modified);

        if (sizeof($this->onpub_article->authors)) {
          $author = $this->onpub_article->authors[0];

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
        if (sizeof($this->onpub_article->authors)) {
          $author = $this->onpub_article->authors[0];

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

      en('<div style="padding-right: 0.5em;">');
      if ($this->onpub_article->image) {
        $this->onpub_article->image->website = $this->onpub_website;
        en('<img src="' . OnpubImages::getThumbURL('src=' . urlencode($this->onpub_article->image->getFullPath()) . '&w=280&f=png', $onpub_dir_phpthumb) . '" align="right" style="margin-right: 0.75em;" alt="' . $this->onpub_article->image->fileName . '" title="' . $this->onpub_article->image->description . '">');
      }

      en($this->onpub_article->content);
      en('</div>');

      if ($this->onpub_login_status) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      if (file_exists($onpub_inc_article_foot)) include $onpub_inc_article_foot;

      en('</div>');
      en('<div class="yui3-u-1-4 onpub-section-nav">');

      en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $this->onpub_section->ID . '" class="onpub-section-nav">' . $this->onpub_section->name . '</a></h1>');

      $articles = $this->onpub_articles->select(null, $this->onpub_section->ID);

      en('<ul class="onpub-section-nav">');

      foreach ($articles as $a) {
        if ($a->ID == $this->onpub_article->ID) {
          en('<li>' . $a->title . '</li>');
        }
        else {
          if ($a->url) {
            en('<li><a href="' . $a->url . '" class="onpub-section-nav">' . $a->title . '</a></li>');
          }
          else {
            en('<li><a href="index.php?s=' . $this->onpub_section->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></li>');
          }
        }
      }

      // Get subsections.
      $sections = $this->onpub_sections->select(null, null, true, $this->onpub_section->ID);

      foreach ($sections as $s) {
        if ($s->url) {
          en('<li><a href="' . $s->url . '" class="onpub-section-nav">' . $s->name . '</a></li>');
        }
        else {
          en('<li><a href="index.php?s=' . $s->ID . '" class="onpub-section-nav">' . $s->name . '</a></li>');
        }
      }

      en('</ul>');

      en('</div>');
      en('</div>');
    }

    if ($this->onpub_section && !$this->onpub_article) {
      en('<h1>Article ' . $this->onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
    }

    if (!$this->onpub_section && $this->onpub_article) {
      en('<h1>Section ' . $this->onpub_section_id . ' not found... <a href="index.php">Home</a></h1>');
    }

    if (!$this->onpub_section && !$this->onpub_article) {
      en('<h1>Section ' . $this->onpub_section_id . ' and Article ' . $this->onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
    }
  }

  protected function article()
  {
    global $onpub_inc_article_info, $onpub_dir_phpthumb, $onpub_dir_manage,
           $onpub_dir_frontend, $onpub_inc_article_foot;

    en('<div class="yui3-g">');
    en('<div class="yui3-u-1">');

    if ($this->onpub_article) {
      en('<h1 style="margin-right: 0;">' . $this->onpub_article->title . '</h1>');

      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      en('<p class="onpub-article-info">');

      $created = $this->onpub_article->getCreated();
      $modified = $this->onpub_article->getModified();

      if (function_exists('date_diff')) {
        $diff = $created->diff($modified);

        if (sizeof($this->onpub_article->authors)) {
          $author = $this->onpub_article->authors[0];

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
        if (sizeof($this->onpub_article->authors)) {
          $author = $this->onpub_article->authors[0];

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

      if ($this->onpub_article->imageID) {
        $this->onpub_article->image->website = $this->onpub_website;
        en('<img src="' . OnpubImages::getThumbURL('src=' . urlencode($this->onpub_article->image->getFullPath()) . '&w=400&f=png', $onpub_dir_phpthumb) . '" align="right" alt="' . $this->onpub_article->image->fileName . '" title="' . $this->onpub_article->image->description . '">');
      }

      en ($this->onpub_article->content);

      if ($this->onpub_login_status) {
        en('<div class="yui3-g">');
        en('<div class="yui3-u-1">');
        en('<span class="onpub-edit">');
        en('<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub"><img src="' . $onpub_dir_frontend .
          'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
          '<a href="' . $onpub_dir_manage .
          'index.php?onpub=EditArticle&amp;articleID=' . $this->onpub_article->ID .
          '" target="_onpub" title="Edit this Article">EDIT</a>');
        en('</span>');
        en('</div>');
        en('</div>');
      }

      if (file_exists($onpub_inc_article_foot)) include $onpub_inc_article_foot;
    }
    else {
      en('<h1>Article ' . $this->onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
    }

    en('</div>');
    en('</div>');
  }

  protected function skel()
  {
    global $onpub_disp_rss, $onpub_dir_yui, $onpub_inc_css, $onpub_inc_css_menu,
           $onpub_inc_head, $onpub_inc_banner, $onpub_dir_root, $onpub_yui_version,
           $onpub_dir_frontend, $onpub_inc_foot;

    header("Content-Type: text/html; charset=iso-8859-1");

    session_name("onpubpdo");
    session_set_cookie_params(0, '/', '', false, true);
    session_start();

    $this->onpub_login_status = false;

    if (isset($_SESSION['PDO_HOST']) && isset($_SESSION['PDO_USER']) && isset($_SESSION['PDO_PASSWORD']) && isset($_SESSION['PDO_DATABASE'])) {
      $this->onpub_login_status = true;
    }

    en('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
    en('<html>');
    en('<head>');
    en('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">');
    en('<meta http-equiv="Content-Style-Type" content="text/css">');
    $this->title();

    if ($this->onpub_website && $onpub_disp_rss) {
      en('<link rel="alternate" type="application/rss+xml" href="index.php?rss" title="' . $this->onpub_website->name . ' RSS Feed">');
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
    $this->hd();
    en('</div>');

    en('<div id="onpub-page">');

    $this->menu();

    switch ($this->onpub_index)
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
        $this->sectionarticle();
        en('</div>');
        break;

      default: break;
    }

    en('</div>');

    en('<div id="onpub-footer">');
    en('<div id="onpub-footer-content">');
    $this->ft();
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
    global $onpub_disp_rss, $onpub_disp_updates_num;

    if ($this->onpub_website && $onpub_disp_rss) {
      // See the following OnpubAPI tutorial for more info:
      // http://onpub.com/index.php?s=20&a=78

      // This example is based on an example by Anis uddin Ahmad, the author of
      // Universal Feed Writer.

      //Creating an instance of FeedWriter class.
      //The constant RSS2 is passed to mention the version
      $feed = new FeedWriter(RSS2);

      //Setting the channel elements
      //Use wrapper functions for common channel elements
      $feed->setTitle($this->onpub_website->name);
      $feed->setLink(addTrailingSlash($this->onpub_website->url));
      $feed->setDescription('');

      //Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
      if ($this->onpub_website->image) {
        $feed->setImage($this->onpub_website->name, addTrailingSlash($this->onpub_website->url), addTrailingSlash($this->onpub_website->imagesURL) . $this->onpub_website->image->fileName);
      }
      else {
        $feed->setImage($this->onpub_website->name, addTrailingSlash($this->onpub_website->url), null);
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

      $articles = $this->onpub_articles->select($qo, null, $this->onpub_website->ID);

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

        $samaps = $this->onpub_samaps->select(null, null, $article->ID);

        if (sizeof($samaps)) {
          $newItem->setLink(addTrailingSlash($this->onpub_website->url) . 'index.php?s=' . $samaps[0]->sectionID . '&a=' . $article->ID);
        }
        else {
          $newItem->setLink(addTrailingSlash($this->onpub_website->url) . 'index.php?a=' . $article->ID);
        }

        //The parameter is a timestamp for setDate() function
        $newItem->setDate($article->getCreated()->format('c'));

        $newItem->setDescription($article->content);

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
}

?>

<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if (!ini_get("date.timezone")) {
  date_default_timezone_set ('America/New_York');
}

$onpub_index = 'home';
$onpub_section_id = null;
$onpub_section = null;
$onpub_article_id = null;
$onpub_article = null;
$onpub_schema_installed = false;

try {
  $onpub_pdo = new PDO('mysql:host=' . $onpub_db_host . ';dbname=' . $onpub_db_name, $onpub_db_user, $onpub_db_pass);
  $onpub_pdo_exception = null;
}
catch (PDOException $e) {
  // Connection error. PDO isn't installed or DB credentials are incorrect.
  $onpub_pdo = null;
  $onpub_pdo_exception = $e;
}

if ($onpub_pdo) {
  $onpub_websites = new OnpubWebsites($onpub_pdo);
  $onpub_sections = new OnpubSections($onpub_pdo);
  $onpub_articles = new OnpubArticles($onpub_pdo);
  $onpub_samaps = new OnpubSAMaps($onpub_pdo);

  $qo = new OnpubQueryOptions();
  $qo->includeSections = true;

  try {
    $onpub_website = $onpub_websites->get($onpub_disp_website, $qo);
    $onpub_schema_installed = true;
    $onpub_pdo_exception = null;
  }
  catch (PDOException $e) {
    // Schema most likely has not yet been installed.
    $onpub_website = null;
    $onpub_schema_installed = false;
    $onpub_pdo_exception = null;
  }
}
else {
  $onpub_website = null;
  $onpub_schema_installed = false;
}

if ($onpub_schema_installed) {
  // Check for legacy GET query params..
  if (isset($_GET['sectionID']) && !isset($_GET['articleID'])) {
    if (!ctype_digit($_GET['sectionID'])) {
      en('<span style="color: red;">sectionID must be an integer.</span>');
      exit;
    }
  
    $onpub_index = 'section';
    $onpub_section_id = $_GET['sectionID'];
  
    $onpub_section = $onpub_sections->get($onpub_section_id);
  
    $onpub_section_parent = null;
  
    if ($onpub_section && $onpub_section->parentID) {
      $onpub_section_parent = $onpub_sections->get($onpub_section->parentID);
    }
  }
  elseif (!isset($_GET['sectionID']) && isset($_GET['articleID'])) {
    if (!ctype_digit($_GET['articleID'])) {
      en('<span style="color: red;">articleID must be an integer.</span>');
      exit;
    }
  
    $onpub_index = 'article';
    $onpub_article_id = $_GET['articleID'];
  
    $qo = new OnpubQueryOptions();
    $qo->includeAuthors = true;
    $onpub_article = $onpub_articles->get($onpub_article_id, $qo);
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
  
    $onpub_index = 'section-article';
    $onpub_section_id = $_GET['sectionID'];
    $onpub_article_id = $_GET['articleID'];
  
    $onpub_section = $onpub_sections->get($onpub_section_id);
  
    $onpub_section_parent = null;
  
    if ($onpub_section && $onpub_section->parentID) {
      $onpub_section_parent = $onpub_sections->get($onpub_section->parentID);
    }
  
    $qo = new OnpubQueryOptions();
    $qo->includeAuthors = true;
    $onpub_article = $onpub_articles->get($onpub_article_id, $qo);
  }
  elseif (isset($_GET['rss'])) {
    $onpub_index = 'rss';
  }
  
  // Check for new short/optimized GET query params..
  if (isset($_GET['s']) && !isset($_GET['a'])) {
    if (!ctype_digit($_GET['s'])) {
      en('<span style="color: red;">s must be an integer.</span>');
      exit;
    }
  
    $onpub_index = 'section';
    $onpub_section_id = $_GET['s'];
  
    $onpub_section = $onpub_sections->get($onpub_section_id);
  
    $onpub_section_parent = null;
  
    if ($onpub_section && $onpub_section->parentID) {
      $onpub_section_parent = $onpub_sections->get($onpub_section->parentID);
    }
  }
  elseif (!isset($_GET['s']) && isset($_GET['a'])) {
    if (!ctype_digit($_GET['a'])) {
      en('<span style="color: red;">a must be an integer.</span>');
      exit;
    }
  
    $onpub_index = 'article';
    $onpub_article_id = $_GET['a'];
  
    $qo = new OnpubQueryOptions();
    $qo->includeAuthors = true;
    $onpub_article = $onpub_articles->get($_GET['a'], $qo);
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
  
    $onpub_index = 'section-article';
    $onpub_section_id = $_GET['s'];
    $onpub_article_id = $_GET['a'];
  
    $onpub_section = $onpub_sections->get($onpub_section_id);
  
    $onpub_section_parent = null;
  
    if ($onpub_section && $onpub_section->parentID) {
      $onpub_section_parent = $onpub_sections->get($onpub_section->parentID);
    }
  
    $qo = new OnpubQueryOptions();
    $qo->includeAuthors = true;
    $onpub_article = $onpub_articles->get($onpub_article_id, $qo);
  }
  elseif (isset($_GET['rss'])) {
    $onpub_index = 'rss';
  }
}

switch ($onpub_index) {
  case 'rss':
  include $onpub_dir_root . $onpub_dir_frontend . 'libs/FeedWriter.php';
  include $onpub_dir_root . $onpub_dir_frontend . 'rss.php';
  break;

  default:
  include $onpub_dir_root . $onpub_dir_frontend . 'skel.php';
  break;
}

?>
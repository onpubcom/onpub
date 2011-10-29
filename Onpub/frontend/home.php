<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

function onpub_extract_section_ids($sections)
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


if ($onpub_website) {
  if ($onpub_disp_updates) {
    en('<div class="yui3-g">');
    en('<div class="yui3-u-3-4">');

    if ($onpub_disp_article) {
      $onpub_article = $onpub_articles->get($onpub_disp_article);

      if ($onpub_article) {
        en($onpub_article->content);
      }
      else {
        en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
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

    $articles = $onpub_articles->select($qo, null, $onpub_website->ID);

    if (sizeof($articles) && !(sizeof($articles) == 1 && $articles[0]->ID == $onpub_disp_article)) {
      en('<h1 style="margin-right: 0;">What\'s New <a href="index.php?rss"><img src="' . $onpub_dir_root . $onpub_dir_data . $onpub_dir_frontend . 'images/rss.png" width="14" height="14" alt="' . $onpub_website->name . ' RSS Feed" title="' . $onpub_website->name . ' RSS Feed"></a></h1>');

      $onpub_website_section_ids = onpub_extract_section_ids($onpub_website->sections);

      $i = 0;

      foreach ($articles as $a) {
        if ($i == $onpub_disp_updates_num) {
          break;
        }

        if ($a->ID != $onpub_disp_article) {
          $samaps = $onpub_samaps->select(null, null, $a->ID);

          if (sizeof($samaps)) {
            $sectionIDs = array();

            foreach ($samaps as $samap) {
              $sectionIDs[] = $samap->sectionID;
            }

            $visibleSIDs = array_values(array_intersect($onpub_website_section_ids, $sectionIDs));

            en('<h2 class="onpub-article-link"><a href="index.php?s=' . $visibleSIDs[0] . '&amp;a=' . $a->ID . '">' . $a->title . '</a></h2>');
          }

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
          
          $i++;
        }
      }
    }

    if (file_exists($onpub_dir_local . $onpub_inc_article_updates)) {
      en('<div>');
      include $onpub_dir_local . $onpub_inc_article_updates;
      en('</div>');
    }
    
    en('</div>');
    en('</div>');
  }
  else {
    if ($onpub_disp_article) {
      $onpub_article = $onpub_articles->get($onpub_disp_article);

      if ($onpub_article) {
        en($onpub_article->content);
      }
      else {
        en('<h2 style="margin-top: 1em;"><a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_onpub">Publish a new article</a> to customize this page.</h2>');
      }
    }
  }

  if ($onpub_login_status && $onpub_article) {
    en('<div class="yui3-g">');
    en('<div class="yui3-u-1">');
    en('<span class="onpub-edit">');
    en('<a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php?onpub=EditArticle&amp;articleID=' . $onpub_article->ID .
       '" target="_onpub"><img src="' . $onpub_dir_root . $onpub_dir_data . $onpub_dir_frontend .
       'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
       '<a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php?onpub=EditArticle&amp;articleID=' . $onpub_article->ID .
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
      en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage .
         'index.php?onpub=NewWebsite" target="_onpub">Create a website</a> and then reload this page to get started.</p>');
    }
    elseif ($onpub_pdo_installed) {
      en('<h3>Almost there.. Follow the instructions below to complete the Onpub installation.</h3>');
      en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage .
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

?>
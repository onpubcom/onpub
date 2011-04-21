<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

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
        br();
        en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_blank">Create a new article</a> to customize this page.</p>');
      }
    }

    en('</div>');
    en('<div class="yui3-u-1-4">');

    $qo = new OnpubQueryOptions();
    $qo->includeContent = true;
    $qo->includeAuthors = true;
    $qo->orderBy = 'created';
    $qo->order = 'DESC';
    $qo->rowLimit = 6;

    $articles = $onpub_articles->select($qo);

    if (sizeof($articles) > 1) {
      en('<h1>What\'s New <a href="index.php?rss"><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/rss.png" width="12" height="12" alt="' . $onpub_website->name . ' RSS Feed" title="' . $onpub_website->name . ' RSS Feed"></a></h1>');

      foreach ($articles as $a) {
        if ($a->ID != $onpub_disp_article) {
          $samaps = $onpub_samaps->select(null, null, $a->ID);

          if (sizeof($samaps)) {
            if (in_array($samaps[0]->sectionID, $sectIDs)) {
              // Only include s in links if current section is visible.
              en('<h2 class="onpub-article-link"><a href="index.php?s=' . $samaps[0]->sectionID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></h2>');

              en('<p class="onpub-article-summary"><em>' . $a->getCreated()->format('M j, Y') . '</em>');

              if ($a->getSummary()) {
                en('<br>' . $a->getSummary() . '...</p>');
              }
              else {
                en('</p>');
              }
            }
            else {
              en('<h2 class="onpub-article-link"><a href="index.php?a=' . $a->ID . '">' . $a->title . '</a></h2>');

              en('<p class="onpub-article-summary"><em>' . $a->getCreated()->format('M j, Y') . '</em>');

              if ($a->getSummary()) {
                en('<br>' . $a->getSummary() . '...</p>');
              }
              else {
                en('</p>');
              }
            }
          }
          else {
            en('<h2 class="onpub-article-link"><a href="index.php?a=' . $a->ID . '">' . $a->title . '</a></h2>');

            en('<p class="onpub-article-summary"><em>' . $a->getCreated()->format('M j, Y') . '</em>');

            if ($a->getSummary()) {
              en('<br>' . $a->getSummary() . '...</p>');
            }
            else {
              en('</p>');
            }
          }
        }
      }
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
        br();
        en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_blank">Create a new article</a> to customize this page.</p>');
      }
    }
  }
}
else {
  en('<h1>Welcome to Onpub</h1>');

    en('<h3>You have successfully installed Onpub. This is the default Onpub frontend interface.</h3>');

  if ($onpub_schema_installed) {
    en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php?onpub=NewWebsite" target="_onpub">Create a new website</a> to start customizing this site.</p>');
  }
  else {
    en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php" target="_onpub">Login</a> to the Onpub content management interface now to complete the database setup for this installation.</p>');
    en('<p>See <a href="http://onpub.com/index.php?s=8&a=118" target="_blank">How to Install Onpub</a> for more information.</p>');
  }
}

?>
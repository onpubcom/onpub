<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

en('<div class="yui3-g">');
en('<div class="yui3-u-23-24">');

if ($onpub_website) {
  if ($onpub_disp_article) {
    $onpub_article = $onpub_articles->get($onpub_disp_article);

    if ($onpub_article) {
      en('<h1>' . $onpub_article->title . '</h1>');
      en($onpub_article->content);
    }
    else {
      br();
      en('<p><a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewArticle" target="_blank">Create a new article</a> to customize this page.</p>');
    }
  }
  if ($onpub_disp_updates) {
    $qo = new OnpubQueryOptions();
    $qo->includeContent = true;
    $qo->orderBy = 'created';
    $qo->order = 'DESC';
    $qo->rowLimit = 6;

    $articles = $onpub_articles->select($qo);

    if (sizeof($articles) > 1) {
      en('<h1>What\'s New</h1>');

      foreach ($articles as $a) {
        if ($a->ID != $onpub_disp_article) {
          $samaps = $onpub_samaps->select(null, null, $a->ID);

          if (sizeof($samaps)) {
            if (in_array($samaps[0]->sectionID, $sectIDs)) {
              // Only include sectionID in links if current section is visible.
              en('<h2><a href="index.php?sectionID=' . $samaps[0]->sectionID . '&amp;articleID=' . $a->ID . '">' . $a->title . '</a></h2>');

              en('<p><em>' . $a->getCreated()->format('M j, Y') . '</em>');

              if ($a->getSummary()) {
                en(' &ndash; ' . $a->getSummary() . '...<a href="index.php?sectionID=' . $samaps[0]->sectionID . '&amp;articleID=' . $a->ID . '"><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/bullet_go.png" width="16" height="16" alt="Read more." title="Read more." align="top"></a><a href="index.php?sectionID=' . $samaps[0]->sectionID . '&amp;articleID=' . $a->ID . '">Read more</a></p>');
              }
              else {
                en('</p>');
              }
            }
            else {
              en('<h2><a href="index.php?articleID=' . $a->ID . '">' . $a->title . '</a></h2>');

              en('<p><em>' . $a->getCreated()->format('M j, Y') . '</em>');

              if ($a->getSummary()) {
                en(' &ndash; ' . $a->getSummary() . '...<a href="index.php?articleID=' . $a->ID . '"><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/bullet_go.png" width="16" height="16" alt="Read more." title="Read more." align="top"></a><a href="index.php?articleID=' . $a->ID . '">Read more</a></p>');
              }
              else {
                en('</p>');
              }
            }
          }
          else {
            en('<h2><a href="index.php?articleID=' . $a->ID . '">' . $a->title . '</a></h2>');

            en('<p><em>' . $a->getCreated()->format('M j, Y') . '</em>');

            if ($a->getSummary()) {
              en(' &ndash; ' . $a->getSummary() . '...<a href="index.php?articleID=' . $a->ID . '"><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/bullet_go.png" width="16" height="16" alt="Read more." title="Read more." align="top"></a><a href="index.php?articleID=' . $a->ID . '">Read more</a></p>');
            }
            else {
              en('</p>');
            }
          }
        }
      }
    }
  }
}
else {
  br();
  en('<p>Onpub\'s installed. <a href="' . $onpub_dir_root . $onpub_dir_manage . 'index.php?onpub=NewWebsite" target="_blank">Create a new website</a> and then reload this page to get started.</p>');
}

en('</div>');
en('<div class="yui3-u-1-24"></div>');
en('</div>');

?>
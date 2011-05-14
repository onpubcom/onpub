<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if ($onpub_section && $onpub_article) {
  en('<div class="yui3-g">');
  en('<div class="yui3-u-3-4">');

  en('<h1 style="margin-right: 1em;">' . $onpub_article->title . '</h1>');

  en('<div class="yui3-g">');
  en('<div class="yui3-u-2-3">');
  en('<p class="onpub-article-info">');

  $created = $onpub_article->getCreated();
  $modified = $onpub_article->getModified();

  if (function_exists('date_diff')) {
    $diff = $created->diff($modified);

    if (sizeof($onpub_article->authors)) {
      $author = $onpub_article->authors[0];

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
    if (sizeof($onpub_article->authors)) {
      $author = $onpub_article->authors[0];

      en('By ' . $author->displayAs . ' on ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
    }
    else {
      en('Published: ' . $created->format('M j, Y') . '. Updated: ' .  $modified->format('M j, Y') . '.');
    }
  }

  en('</p>');
  en('</div>');
  en('<div class="yui3-u-1-3">');

  if (file_exists($onpub_dir_local . $onpub_inc_article_info)) include $onpub_dir_local . $onpub_inc_article_info;

  en('</div>');
  en('</div>');

  en('<div style="padding-right: 0.5em;">');
  en($onpub_article->content);
  en('</div>');

  if ($onpub_login_status) {
    en('<div class="yui3-g">');
    en('<div class="yui3-u-1">');
    en('<span class="onpub-edit">');
    en('<a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php?onpub=EditArticle&amp;articleID=' . $onpub_article->ID .
       '" target="_onpub"><img src="' . $onpub_dir_root . $onpub_dir_frontend .
       'images/page_edit.png" width="16" height="16" alt="Edit this Article" title="Edit this Article"></a> ' .
       '<a href="' . $onpub_dir_root . $onpub_dir_manage .
       'index.php?onpub=EditArticle&amp;articleID=' . $onpub_article->ID .
       '" target="_onpub" title="Edit this Article">EDIT</a>');
    en('</span>');
    en('</div>');
    en('</div>');
  }

  if (file_exists($onpub_dir_local . $onpub_inc_article_foot)) include $onpub_dir_local . $onpub_inc_article_foot;

  en('</div>');
  en('<div class="yui3-u-1-4 onpub-section-nav">');

  en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $onpub_section->ID . '" class="onpub-section-nav">' . $onpub_section->name . '</a></h1>');

  $articles = $onpub_articles->select(null, $onpub_section->ID);

  en('<ul class="onpub-section-nav">');

  foreach ($articles as $a) {
    if ($a->ID == $onpub_article->ID) {
      en('<li>' . $a->title . '</li>');
    }
    else {
      if ($a->url) {
        en('<li><a href="' . $a->url . '" class="onpub-section-nav">' . $a->title . '</a></li>');
      }
      else {
        en('<li><a href="index.php?s=' . $onpub_section->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></li>');
      }
    }
  }

  // Get subsections.
  $sections = $onpub_sections->select(null, null, true, $onpub_section->ID);

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

if ($onpub_section && !$onpub_article) {
  en('<h1>Article ' . $onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
}

if (!$onpub_section && $onpub_article) {
  en('<h1>Section ' . $onpub_section_id . ' not found... <a href="index.php">Home</a></h1>');
}

if (!$onpub_section && !$onpub_article) {
  en('<h1>Section ' . $onpub_section_id . ' and Article ' . $onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
}

?>
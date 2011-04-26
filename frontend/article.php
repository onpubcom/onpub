<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

en('<div class="yui3-g">');
en('<div class="yui3-u-1">');

if ($onpub_article) {
  en('<h1>' . $onpub_article->title . '</h1>');

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

  en ($onpub_article->content);

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
       '" target="_onpub" title="Edit this Article">Edit</a>');
    en('</span>');
    en('</div>');
    en('</div>');
  }

  if (file_exists($onpub_dir_local . $onpub_inc_article_foot)) include $onpub_dir_local . $onpub_inc_article_foot;
}
else {
  en('<h1>Article ' . $onpub_article_id . ' not found... <a href="index.php">Home</a></h1>');
}

en('</div>');
en('</div>');

?>
<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if ($onpub_article) {
  en('<h1>' . $onpub_article->title . '</h1>');

  en('<div class="yui3-main">');
  en('<div class="yui3-b">');
  en('<div class="yui3-gc onpub-article-info">');
  en('<div class="yui3-u first">');

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

  en('</div>');
  en('<div class="yui3-u" style="margin: 0px; text-align: right;">');

  if (file_exists($onpub_dir_local . $onpub_inc_article_info)) include $onpub_dir_local . $onpub_inc_article_info;

  en('</div>');
  en('</div>');
  en('</div>');
  en('</div>');

  en ($onpub_article->content);

  if (file_exists($onpub_dir_local . $onpub_inc_article_foot)) include $onpub_dir_local . $onpub_inc_article_foot;
}
else {
  en('<h1>Article ' . $_GET['articleID'] . ' not found... <a href="index.php">Home</a></h1>');
}

?>
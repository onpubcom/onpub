<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if ($onpub_website) {
  if ($onpub_index == 'home') {
    en('<title>' . $onpub_website->name . '</title>');
  }
  elseif ($onpub_index == 'section') {
    if ($onpub_section) {
      if ($onpub_section_parent) {
        en('<title>' . $onpub_section->name . ' - ' . $onpub_section_parent->name . ' - ' . $onpub_website->name . '</title>');
      }
      else {
        en('<title>' . $onpub_section->name . ' - ' . $onpub_website->name . '</title>');
      }
    }
    else {
      en('<title>' . $onpub_website->name . ' - Section ' . $onpub_section_id . ' not found...</title>');
    }
  }
  elseif ($onpub_index == 'article') {
    if ($onpub_article) {
      en('<title>' . $onpub_article->title . ' - ' . $onpub_website->name . '</title>');
    }
    else {
      en('<title>' . $onpub_website->name . ' - Article ' . $onpub_article_id . ' not found...</title>');
    }
  }
  elseif ($onpub_index == 'section-article') {
    if ($onpub_section && $onpub_article) {
      if ($onpub_section_parent) {
        en('<title>' . $onpub_article->title . ' - ' . $onpub_section->name . ' - ' . $onpub_section_parent->name . ' - ' . $onpub_website->name . '</title>');
      }
      else {
        en('<title>' . $onpub_article->title . ' - ' . $onpub_section->name . ' - ' . $onpub_website->name . '</title>');
      }
    }

    if ($onpub_section && !$onpub_article) {
      en('<title>' . $onpub_website->name . ' - Article ' . $onpub_article_id . ' not found...</title>');
    }

    if (!$onpub_section && $onpub_article) {
      en('<title>' . $onpub_website->name . ' - Section ' . $onpub_section_id . ' not found...</title>');
    }

    if (!$onpub_section && !$onpub_article) {
      en('<title>' . $onpub_website->name . ' - Section ' . $onpub_section_id . ' and Article ' . $onpub_article_id . ' not found...</title>');
    }
  }
}
else {
  en('<title>Onpub</title>');
}

?>
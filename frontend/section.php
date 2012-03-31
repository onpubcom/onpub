<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if ($onpub_section) {
  // Get subsections.
  $sections = $onpub_sections->select(null, null, true, $onpub_section->ID);
  $subsections = false;

  if (sizeof($sections) || $onpub_section_parent) {
    $subsections = true;
  }

  en('<div class="yui3-g">');

  if ($subsections) {
    en('<div class="yui3-u-3-4">');
    en('<h1>' . $onpub_section->name . '</h1>');
  }
  else {
    en('<div class="yui3-u-1">');
    en('<h1>' . $onpub_section->name . '</h1>');
  }

  /* Code for displaying section image
  if ($onpub_section->imageID) {
    if (($section_image = $onpub_images->get($onpub_section->imageID))) {
      if ($onpub_website->ID == $section_image->websiteID) {
        en('<img src="' . addTrailingSlash($onpub_website->imagesURL) . $section_image->fileName . '" align="right" alt="' . $section_image->fileName . '" title="' . $section_image->description . '">');
      }
    }
  }
  */

  $qo = new OnpubQueryOptions();
  $qo->includeContent = true;

  $articles = $onpub_articles->select($qo, $onpub_section->ID);
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
      $url = 'index.php?s=' . $onpub_section_id . '&amp;a=' . $a->ID;
    }

    en('<div class="yui3-g">');

    if ($a->image) {
      en('<div class="yui3-u-1-4">');
      $a->image->website = $onpub_website;
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

  if ($onpub_login_status) {
    en('<div class="yui3-g">');
    en('<div class="yui3-u-1">');
    en('<span class="onpub-edit">');
    en('<a href="' . $onpub_dir_manage .
       'index.php?onpub=EditSection&amp;sectionID=' . $onpub_section->ID .
       '" target="_onpub"><img src="' . $onpub_dir_frontend .
       'images/page_edit.png" width="16" height="16" alt="Edit this Section" title="Edit this Section"></a> ' .
       '<a href="' . $onpub_dir_manage .
       'index.php?onpub=EditSection&amp;sectionID=' . $onpub_section->ID .
       '" target="_onpub" title="Edit this Section">EDIT</a>');
    en('</span>');
    en('</div>');
    en('</div>');
  }

  en('</div>');

  if ($subsections) {
    en('<div class="yui3-u-1-4 onpub-section-nav">');

    if ($onpub_section_parent) {
      if ($onpub_section_parent->url) {
        en('<h1 class="onpub-section-nav"><a href="' . $onpub_section_parent->url . '" class="onpub-section-nav">' . $onpub_section_parent->name . '</a></h1>');
      }
      else {
        en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $onpub_section_parent->ID . '" class="onpub-section-nav">' . $onpub_section_parent->name . '</a></h1>');
      }

      $articles = $onpub_articles->select(null, $onpub_section_parent->ID);

      en('<ul class="onpub-section-nav">');

      foreach ($articles as $a) {
        if ($a->url) {
          en('<li><a href="' . $a->url . '" class="onpub-section-nav">' . $a->title . '</a></li>');
        }
        else {
          en('<li><a href="index.php?s=' . $onpub_section_parent->ID . '&amp;a=' . $a->ID . '" class="onpub-section-nav">' . $a->title . '</a></li>');
        }
      }

      // Get subsections.
      $sections = $onpub_sections->select(null, null, true, $onpub_section_parent->ID);

      foreach ($sections as $s) {
        if ($s->ID == $onpub_section->ID) {
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

        $articles = $onpub_articles->select(null, $s->ID);

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
  en('<h1>Section ' . $onpub_section_id . ' not found... <a href="index.php">Home</a></h1>');
}

?>
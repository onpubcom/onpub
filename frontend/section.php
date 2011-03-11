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
  }
  else {
    en('<div class="yui3-u-1">');
  }

  en('<h1>' . $onpub_section->name . '</h1>');

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

    if ($a->url) {
      en('<h2><a href="' . $a->url . '">' . $a->title . '</a></h2>');
    }
    else {
      en('<h2><a href="index.php?s=' . $onpub_section_id . '&amp;a=' . $a->ID . '">' . $a->title . '</a></h2>');
    }

    en('<p><em>' . $a->getCreated()->format('M j, Y') . '</em><br>' . $a->getSummary(40) . '...<a href="index.php?s=' . $onpub_section_id . '&amp;a=' . $a->ID . '"><br><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/bullet_go.png" width="16" height="16" alt="Read more." title="Read more." align="top"></a><a href="index.php?s=' . $onpub_section_id . '&amp;a=' . $a->ID . '">more</a></p>');

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

  en('</div>');

  if ($subsections) {
    en('<div class="yui3-u-1-4 onpub-section-nav">');

    if ($onpub_section_parent) {
      if ($onpub_section_parent->url) {
        en('<h1 class="onpub-section-nav"><a href="' . $onpub_section_parent->url . '">' . $onpub_section_parent->name . '</a></h1>');
      }
      else {
        en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $onpub_section_parent->ID . '">' . $onpub_section_parent->name . '</a></h1>');
      }

      $articles = $onpub_articles->select(null, $onpub_section_parent->ID);

      en('<ul class="onpub-section-nav">');

      foreach ($articles as $a) {
        if ($a->url) {
          en('<li><a href="' . $a->url . '">' . $a->title . '</a></li>');
        }
        else {
          en('<li><a href="index.php?s=' . $onpub_section_parent->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
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
            en('<li><a href="' . $s->url . '">' . $s->name . '</a></li>');
          }
          else {
            en('<li><a href="index.php?s=' . $s->ID . '">' . $s->name . '</a></li>');
          }
        }
      }

      en('</ul>');
    }
    else {
      foreach ($sections as $s) {
        if ($s->url) {
          en('<h1 class="onpub-section-nav"><a href="' . $s->url . '">' . $s->name . '</a></h1>');
        }
        else {
          en('<h1 class="onpub-section-nav"><a href="index.php?s=' . $s->ID . '">' . $s->name . '</a></h1>');
        }

        $articles = $onpub_articles->select(null, $s->ID);

        en('<ul class="onpub-section-nav">');

        foreach ($articles as $a) {
          if ($a->url) {
            en('<li><a href="' . $a->url . '">' . $a->title . '</a></li>');
          }
          else {
            en('<li><a href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
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
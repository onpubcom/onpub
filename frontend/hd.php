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
  if ($onpub_website->image) {
    //en('<div id="onpub-logo"><a href="index.php"><img src="' . addTrailingSlash($onpub_website->imagesURL) . $onpub_website->image->fileName . '" alt="' . $onpub_website->image->fileName . '" title="' . $onpub_website->image->description . '"></a></div>');
  }
  else {
    en('<div id="onpub-logo" style="margin-bottom: .5em;"><a href="index.php">' . $onpub_website->name . '</a></div>');
  }

  $sections = $onpub_sections->select(null, $onpub_website->ID, FALSE);

  $sectionsassoc = array();

  foreach ($sections as $s) {
    $sectionsassoc['s' . $s->ID] = $s;
  }

  $sectIDs = array();

  foreach ($onpub_website->sections as $s) {
    $sectIDs[] = $s->ID;
  }

  $sections = array();

  foreach ($sectIDs as $sID) {
    if (isset($sectionsassoc['s' . $sID])) {
      $sections[] = $sectionsassoc['s' . $sID];
    }
  }

  if (sizeof($sections)) {
    if ($onpub_disp_menu) {
      en('<div id="onpub-menubar" class="yui3-menu yui3-menu-horizontal yui3-menubuttonnav">');
      en('<div class="yui3-menu-content">');
      en('<ul>');

      $i = 0;

      foreach ($sections as $s) {
        if ($s->url) {
          en('<li class="yui3-menuitem">');
          if ($i) {
            en('<a class="yui3-menuitem-content" href="' . $s->url . '">' . $s->name . '</a>');
          }
          else {
            en('<a class="yui3-menuitem-content" href="' . $s->url . '">' . $s->name . '</a>');
          }
          en('</li>');
        }
        else {
          en('<li>');
          if ($i) {
            en('<a class="yui3-menu-label" href="index.php?s=' . $s->ID . '"><em>' . $s->name . '</em></a>');
          }
          else {
            // Round the left corner of the first menu label.
            en('<a class="yui3-menu-label" href="index.php?s=' . $s->ID . '"><em>' . $s->name . '</em></a>');
          }
          en('<div class="yui3-menu">');
          en('<div class="yui3-menu-content">');
          en('<ul>');

          $articles = $onpub_articles->select(null, $s->ID);

          foreach ($articles as $a) {
            if ($a->url) {
              en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $a->url. '">' . $a->title . '</a></li>');
            }
            else {
              en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $s->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
            }
          }

          $subsections = $s->sections;

          foreach ($subsections as $sub) {
            if ($sub->url) {
              en('<li class="yui3-menuitem">');
              en('<a class="yui3-menuitem-content" href="' . $sub->url . '">' . $sub->name . '</a>');
              en('</li>');
            }
            else {
              en('<li>');
              en('<a class="yui3-menu-label" href="index.php?s=' . $sub->ID . '">' . $sub->name . '</a>');
              en('<div class="yui3-menu">');
              en('<div class="yui3-menu-content">');
              en('<ul>');

              $articles = $onpub_articles->select(null, $sub->ID);

              foreach ($articles as $a) {
                if ($a->url) {
                  en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="' . $a->url . '">' . $a->title . '</a></li>');
                }
                else {
                  en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?s=' . $sub->ID . '&amp;a=' . $a->ID . '">' . $a->title . '</a></li>');
                }
              }

              en('</ul>');
              en('</div>');
              en('</div>');
              en('</li>');
            }
          }

          en('</ul>');
          en('</div>');
          en('</div>');
          en('</li>');
        }

        $i++;
      }

      en('</ul>');
      en('</div>');
      en('</div>');
    }
  }
}
else {
  en('<div id="onpub-logo"><a href="index.php"><img src="' . $onpub_dir_root . $onpub_dir_manage . 'images/onpub.png" alt="Onpub" title="Onpub"></a></div>');
}

?>
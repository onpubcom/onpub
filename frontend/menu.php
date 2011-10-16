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
  static $sectIDs = array();
  
  foreach ($sections as $s) {
    $sectIDs[] = $s->ID;
    
    if (sizeof($s->sections)) {
      onpub_extract_section_ids($s->sections);
    }
  }
  
  return $sectIDs;
}

function onpub_output_sub_sections($section, $visSectIDs)
{
  global $onpub_articles;
  $subsections = $section->sections;

  foreach ($subsections as $sub) {
    if (in_array($sub->ID, $visSectIDs)) {
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
  
        if (sizeof($sub->sections)) {
          onpub_output_sub_sections($sub, $visSectIDs);
        }
        
        en('</ul>');
        en('</div>');
        en('</div>');
        en('</li>');
      }
    }
  }
}

if ($onpub_website) {
  if ($onpub_disp_menu) {
    $sections = $onpub_sections->select(null, $onpub_website->ID, FALSE);
  
    $sectionsassoc = array();
  
    foreach ($sections as $s) {
      $sectionsassoc['s' . $s->ID] = $s;
    }
    
    $sectIDs = onpub_extract_section_ids($onpub_website->sections);
  
    $sections = array();
  
    foreach ($sectIDs as $sID) {
      if (isset($sectionsassoc['s' . $sID])) {
        $sections[] = $sectionsassoc['s' . $sID];
      }
    }
  
    if (sizeof($sections)) {
      $wsmaps = $onpub_wsmaps->select(null, $onpub_website->ID);

      $visSectIDs = array();

      foreach ($wsmaps as $wsmap) {
        $visSectIDs[] = $wsmap->sectionID;
      }

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
  
          onpub_output_sub_sections($s, $visSectIDs);
  
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

?>

<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

define("ONPUBAPI_VERSION", "1.0");
define("ONPUBAPI_SCHEMA_VERSION", 1);

// Database Class
include("classes/OnpubDatabase.php");

// Table Classes
include("classes/OnpubAAMaps.php");
include("classes/OnpubArticles.php");
include("classes/OnpubAuthors.php");
include("classes/OnpubImages.php");
include("classes/OnpubSAMaps.php");
include("classes/OnpubSections.php");
include("classes/OnpubWebsites.php");
include("classes/OnpubWSMaps.php");

// Row Classes
include("classes/OnpubArticle.php");
include("classes/OnpubAAMap.php");
include("classes/OnpubAuthor.php");
include("classes/OnpubImage.php");
include("classes/OnpubSection.php");
include("classes/OnpubSAMap.php");
include("classes/OnpubWebsite.php");
include("classes/OnpubWSMap.php");

// Option Classes
include("classes/OnpubQueryOptions.php");

// Useful string functions

// Outputs a string with newline(s) at the end.
function en($string, $n = 1, $b = 0)
{
  echo $string;

  if ($b) {
    br($b, $n - 1);
  }

  while ($n > 0) {
    echo "\n";
    $n--;
  }
}

// Outputs an HTML break tag(s) and also newline(s).
function br($b = 1, $n = 1)
{
  $br = '';

  while ($b > 0) {
    $br .= '<br>';
    $b--;
  }
  en($br, $n);
}

function hasTrailingSlash($in_str)
{
  $in_str_trm = trim($in_str);

  $pos = strrpos($in_str_trm, '/');

  if ($pos !== FALSE) {
    if (($pos + 1) == strlen($in_str_trm)) {
      return TRUE;
    }
  }

  $pos = strrpos($in_str_trm, '\\');

  if ($pos !== FALSE) {
    if (($pos + 1) == strlen($in_str_trm)) {
      return TRUE;
    }
  }

  return FALSE;
}

function addTrailingSlash($in_str)
{
  if ($in_str === "") {
    return $in_str;
  }

  if (hasTrailingSlash($in_str)) {
    return $in_str;
  }

  if (substr($in_str, 1, 1) == ':') {
    return $in_str . '\\';
  }

  return $in_str . '/';
}
?>
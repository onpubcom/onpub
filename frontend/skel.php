<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

header("Content-Type: text/html; charset=iso-8859-1");

session_name("onpubpdo");
session_set_cookie_params(0, '/', '', false, true);
session_start();

$onpub_login_status = false;

if (isset($_SESSION['PDO_HOST']) && isset($_SESSION['PDO_USER']) && isset($_SESSION['PDO_PASSWORD']) && isset($_SESSION['PDO_DATABASE'])) {
  $onpub_login_status = true;
}

en('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
en('<html>');
en('<head>');
en('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">');
en('<meta http-equiv="Content-Style-Type" content="text/css">');
include $onpub_dir_frontend . 'title.php';

if ($onpub_website && $onpub_disp_rss) {
  en('<link rel="alternate" type="application/rss+xml" href="index.php?rss" title="' . $onpub_website->name . ' RSS Feed">');
}

if (file_exists($onpub_dir_yui)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssreset/cssreset-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssfonts/cssfonts-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssgrids/cssgrids-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'cssbase/cssbase-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_yui . 'node-menunav/assets/skins/sam/node-menunav.css">');
}
else {
  $onpub_dir_yui = null;
  en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' .
     $onpub_yui_version . '/build/cssreset/cssreset-min.css&' . $onpub_yui_version .
     '/build/cssfonts/cssfonts-min.css&' . $onpub_yui_version .
     '/build/cssgrids/cssgrids-min.css&' . $onpub_yui_version .
     '/build/cssbase/cssbase-min.css&' . $onpub_yui_version .
     '/build/node-menunav/assets/skins/sam/node-menunav.css">');
}

if (file_exists($onpub_inc_css)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_inc_css . '">');
}
else {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_frontend . 'css/onpub.css">');
}

if (file_exists($onpub_inc_css_menu)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_inc_css_menu . '">');
}
else {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_frontend . 'css/onpub-menu.css">');
}

en('<script type="text/javascript">');
en('document.documentElement.className = "yui3-loading";');
en('var onpub_dir_root = "' . $onpub_dir_root . '";');

if ($onpub_dir_yui) {
  en('var onpub_dir_yui = "' . $onpub_dir_yui . '";');
}
else {
  en('var onpub_dir_yui = null;');
}

en('var onpub_yui_version = "' . $onpub_yui_version . '";');
en('</script>');

if (file_exists($onpub_inc_head)) include $onpub_inc_head;
en('</head>');

en('<body class="yui3-skin-sam">');

if (file_exists($onpub_inc_banner)) {
  en('<div id="onpub-banner">');
  include $onpub_inc_banner;
  en('</div>');
}

en('<div id="onpub-header">');
include $onpub_dir_frontend . 'hd.php';
en('</div>');

en('<div id="onpub-page">');

include $onpub_dir_frontend . 'menu.php';

switch ($onpub_index)
{
  case 'home':
    en('<div id="onpub-body">');
    include $onpub_dir_frontend . 'home.php';
    en('</div>');
    break;

  case 'section':
    en('<div id="onpub-body" style="padding-right: 0em;">');
    include $onpub_dir_frontend . 'section.php';
    en('</div>');
    break;

  case 'article':
    en('<div id="onpub-body">');
    include $onpub_dir_frontend . 'article.php';
    en('</div>');
    break;

  case 'section-article':
    en('<div id="onpub-body" style="padding-right: 0em;">');
    include $onpub_dir_frontend . 'section-article.php';
    en('</div>');
    break;

  default: break;
}

en('</div>');

en('<div id="onpub-footer">');
en('<div id="onpub-footer-content">');
include $onpub_dir_frontend . 'ft.php';
en('</div>');
en('</div>');

if ($onpub_dir_yui) {
  en('<script type="text/javascript" src="' . $onpub_dir_yui . 'yui/yui-min.js"></script>');
}
else {
  en('<script type="text/javascript" src="http://yui.yahooapis.com/combo?' . $onpub_yui_version . '/build/yui/yui-min.js"></script>');
}

en('<script type="text/javascript" src="' . $onpub_dir_frontend . 'js/site.js"></script>');

if (file_exists($onpub_inc_foot)) include $onpub_inc_foot;

en('</body>');
en('</html>');
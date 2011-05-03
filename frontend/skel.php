<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

header ("Content-Type: text/html; charset=iso-8859-1");

session_name("onpubpdo");
session_set_cookie_params(0, '/', '', false, true);
session_start();

$onpub_login_status = false;

if (isset($_SESSION['PDO_HOST']) && isset($_SESSION['PDO_USER']) && isset($_SESSION['PDO_PASSWORD']) && isset($_SESSION['PDO_DATABASE'])) {
  $onpub_login_status = true;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<?php include $onpub_dir_root . $onpub_dir_frontend . 'title.php'; ?>

<?php

if ($onpub_website) {
  en('<link rel="alternate" type="application/rss+xml" href="index.php?rss" title="' . $onpub_website->name . ' RSS Feed">');
}

if (file_exists($onpub_dir_root . $onpub_dir_yui)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssreset/reset-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssfonts/fonts-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssgrids/grids-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssbase/base-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'node-menunav/assets/skins/sam/node-menunav.css">');
}
else {
  $onpub_dir_yui = null;
  en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' .
     $onpub_yui_version . '/build/cssreset/reset-min.css&' . $onpub_yui_version .
     '/build/cssfonts/fonts-min.css&' . $onpub_yui_version . '/build/cssgrids/grids-min.css&' .
     $onpub_yui_version . '/build/cssbase/base-min.css&' . $onpub_yui_version .
     '/build/node-menunav/assets/skins/sam/node-menunav.css">');
}

if (file_exists($onpub_dir_local . $onpub_inc_css)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_local . $onpub_inc_css . '">');
}
else {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub.css">');
}

if (file_exists($onpub_dir_local . $onpub_inc_css_menu)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub-menu.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_local . $onpub_inc_css_menu . '">');
}
else {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub-menu.css">');
}

?>

<script type="text/javascript">
document.documentElement.className = "yui3-loading";
var onpub_dir_root = "<?php echo $onpub_dir_root; ?>";
<?php
if ($onpub_dir_yui) {
  en('var onpub_dir_yui = "' . $onpub_dir_root . $onpub_dir_yui . '";');
}
else {
  en('var onpub_dir_yui = null;');
}
if (file_exists($onpub_dir_local . $onpub_inc_css_menu)) {
  en('var onpub_inc_css_menu = "' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub-menu.css";');
  en('var onpub_inc_css_menu_local = "' . $onpub_dir_local . $onpub_inc_css_menu . '";');
}
else {
  en('var onpub_inc_css_menu = "' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub-menu.css";');
  en('var onpub_inc_css_menu_local = null;');
}
en('var onpub_yui_version = "' . $onpub_yui_version . '";', 0);
?>

</script>

<?php if (file_exists($onpub_dir_local . $onpub_inc_head)) include $onpub_dir_local . $onpub_inc_head; ?>
</head>

<body class="yui3-skin-sam">

<div id="onpub-page">

<?php

if (file_exists($onpub_dir_local . $onpub_inc_banner)) {
  en('<div id="onpub-banner">');
  include $onpub_dir_local . $onpub_inc_banner;
  en('</div>');
}

?>

<div id="onpub-header">
<?php include $onpub_dir_root . $onpub_dir_frontend . 'hd.php'; ?>
</div>

<?php

switch ($onpub_index)
{
  case 'home':
    en('<div id="onpub-body">');
    include $onpub_dir_root . $onpub_dir_frontend . 'home.php';
    en('</div>');
    break;

  case 'section':
    en('<div id="onpub-body" style="padding-right: 0em;">');
    include $onpub_dir_root . $onpub_dir_frontend . 'section.php';
    en('</div>');
    break;

  case 'article':
    en('<div id="onpub-body">');
    include $onpub_dir_root . $onpub_dir_frontend . 'article.php';
    en('</div>');
    break;

  case 'section-article':
    en('<div id="onpub-body" style="padding-right: 0em;">');
    include $onpub_dir_root . $onpub_dir_frontend . 'section-article.php';
    en('</div>');
    break;

  default: break;
}

?>

<div id="onpub-footer">
<?php include $onpub_dir_root . $onpub_dir_frontend . 'ft.php'; ?>
</div>

</div>

<?php

if ($onpub_dir_yui) {
  en('<script type="text/javascript" src="' . $onpub_dir_root . $onpub_dir_yui . 'yui/yui-min.js"></script>');
}
else {
  en('<script type="text/javascript" src="http://yui.yahooapis.com/combo?' . $onpub_yui_version . '/build/yui/yui-min.js"></script>');
}

en('<script type="text/javascript" src="' . $onpub_dir_root . $onpub_dir_frontend . 'js/site.js"></script>');

if (file_exists($onpub_dir_local . $onpub_inc_foot)) include $onpub_dir_local . $onpub_inc_foot;

?>

</body>
</html>
<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

header ("Content-Type: text/html; charset=iso-8859-1");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Style-Type" content="text/css">
<?php include $onpub_dir_root . $onpub_dir_frontend . 'title.php'; ?>
<link rel="alternate" type="application/rss+xml" href="index.php?rss" title="<?php if ($onpub_website) echo $onpub_website->name; ?> RSS Feed">

<?php

if (file_exists($onpub_dir_root . $onpub_dir_yui)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssreset/reset-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssfonts/fonts-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssgrids/grids-min.css">');
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_yui . 'cssbase/base-min.css">');
}
else {
  $onpub_dir_yui = null;
  en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.2.0/build/cssreset/reset-min.css&3.2.0/build/cssfonts/fonts-min.css&3.2.0/build/cssgrids/grids-min.css&3.2.0/build/cssbase/base-min.css">');
}

if (file_exists($onpub_dir_local . $onpub_inc_css)) {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_local . $onpub_inc_css . '">');
}
else {
  en('<link rel="stylesheet" type="text/css" href="' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub.css">');
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
  en('var onpub_inc_css_menu = "' . $onpub_dir_local . $onpub_inc_css_menu . '";');
}
else {
  en('var onpub_inc_css_menu = "' . $onpub_dir_root . $onpub_dir_frontend . 'css/onpub-menu.css";');
}

en('var onpub_yui_version = "' . $onpub_yui_version . '";', 0);

?>

</script>

<?php if (file_exists($onpub_dir_local . $onpub_inc_head)) include $onpub_dir_local . $onpub_inc_head; ?>
</head>

<body class="yui3-skin-sam">

<div id="onpub-page">

<div id="onpub-header">
<?php include $onpub_dir_root . $onpub_dir_frontend . 'hd.php'; ?>
</div>

<div id="onpub-body">

<?php
switch ($onpub_index)
{
  case 'home':
    include $onpub_dir_root . $onpub_dir_frontend . 'home.php';
    break;

  case 'section':
    include $onpub_dir_root . $onpub_dir_frontend . 'section.php';
    break;

  case 'article':
    include $onpub_dir_root . $onpub_dir_frontend . 'article.php';
    break;

  case 'section-article':
    include $onpub_dir_root . $onpub_dir_frontend . 'section-article.php';
    break;

  default: break;
}
?>

</div>

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
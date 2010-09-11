<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

$dt = new DateTime();

if ($onpub_website) {
  en('<div class="yui3-g">');
  en('<div class="yui3-u-3-4">');

  en('<p>&copy; ' . $dt->format('Y') . ' <a href="index.php">' . $onpub_website->name . '</a>. All rights reserved. <a href="index.php?rss"><img src="' . $onpub_dir_root . $onpub_dir_frontend . 'images/feed-icon-14x14.png" align="top" width="14" height="14" alt="' . $onpub_website->name . ' RSS Feed" title="' . $onpub_website->name . ' RSS Feed"></a> <a href="index.php?rss">RSS Feed</a></p>');

  en('</div>');
  en('<div class="yui3-u-1-4">');

  if ($onpub_disp_login) {
    en('<p style="text-align: right;">&raquo; <a href="' . $onpub_dir_root . $onpub_dir_manage . '" target="_blank">Login</a></p>');
  }

  en('</div>');
  en('</div>');
}
else {
  en('<div class="yui3-g">');
  en('<div class="yui3-u-3-4">');

  en('<p>Onpub ' . ONPUBAPI_VERSION . ', &copy; 2010 <a href="http://onpub.com/" target="_blank">Onpub.com</a>.</p>');

  en('</div>');
  en('<div class="yui3-u-1-4">');

  if ($onpub_disp_login) {
    en('<p style="text-align: right;">&raquo; <a href="' . $onpub_dir_root . $onpub_dir_manage . '" target="_blank">Login</a></p>');
  }

  en('</div>');
  en('</div>');
}

?>
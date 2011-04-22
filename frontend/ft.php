<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
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

  en('<p>&copy; ' . $dt->format('Y') . ' <a href="index.php">' . $onpub_website->name . '</a>. All rights reserved.</p>');

  en('</div>');
  en('<div class="yui3-u-1-4">');

  if ($onpub_disp_login) {
    en('<p style="text-align: right;">Powered by <a href="http://onpub.com/" target="_blank">Onpub</a> &raquo; <a href="' . $onpub_dir_root . $onpub_dir_manage . '" target="_onpub">Login</a></p>');
  }

  en('</div>');
  en('</div>');
}
else {
  en('<div class="yui3-g">');
  en('<div class="yui3-u-3-4">');

  en('<p>Onpub ' . ONPUBAPI_VERSION . ', &copy; 2011 <a href="http://onpub.com/" target="_blank">Onpub.com</a>.</p>');

  en('</div>');
  en('<div class="yui3-u-1-4">');

  if ($onpub_disp_login) {
    en('<p style="text-align: right;">Powered by <a href="http://onpub.com/" target="_blank">Onpub</a> &raquo; <a href="' . $onpub_dir_root . $onpub_dir_manage . '" target="_onpub">Login</a></p>');
  }

  en('</div>');
  en('</div>');
}

?>
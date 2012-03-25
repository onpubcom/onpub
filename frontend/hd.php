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
    en('<div id="onpub-logo"><a href="index.php"><img src="' . addTrailingSlash($onpub_website->imagesURL) . $onpub_website->image->fileName . '" alt="' . $onpub_website->image->fileName . '" title="' . $onpub_website->image->description . '"></a></div>');
  }
  else {
    en('<div id="onpub-logo" style="margin-bottom: .5em;"><a href="index.php">' . $onpub_website->name . '</a></div>');
  }
}
else {
  en('<div id="onpub-logo"><a href="index.php"><img src="' . $onpub_dir_manage . 'images/onpub-small.png" alt="Onpub" title="Onpub"></a></div>');
}

?>
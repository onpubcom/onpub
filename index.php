<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

/*
This is the Onpub website frontend index file.
See http://onpub.com/index.php?s=8&a=96 for more information.
*/

// Include default Onpub config.
include './onpub_conf.php';

// Include local config customizations if file exists.
if (file_exists($onpub_conf_local)) {
  include $onpub_conf_local;
}

include $onpub_dir_root . $onpub_dir_api . 'onpubapi.php';

// Include frontend initialization file.
include $onpub_dir_root . $onpub_dir_frontend . 'init.php';

?>
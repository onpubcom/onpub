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

function onpub_get_dir_root()
{
	// A local install path takes precedence over a PEAR install.
	if (file_exists('./frontend/init.php')) return '';

	$include_paths = explode(':', get_include_path());

	foreach ($include_paths as $include_path) {
		if (stristr($include_path, 'pear') !== FALSE) {
			if (file_exists($include_path . '/PEAR.php') && file_exists($include_path . '/Onpub/index.php')) {
					// Onpub is installed via PEAR.
					// Use Onpub root dir relative to PEAR include_path.
					return 'Onpub/';
			}
		}
	}

	return '';
}

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
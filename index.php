<?php

/* Onpub (TM)
 * Copyright (C) 2015 Onpub.com <http://onpub.com/>
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

// Include local config customizations if file exists.
if (file_exists('./onpub_conf_local.php')) {
  include './onpub_conf_local.php';
}

// Include default Onpub config. Variables in this file will only be defined
// if their equivalents have not already been defined in the local configuration
// file included above.
include './onpub_conf.php';

// Include all OnpubAPI classes.
include $onpub_dir_api . 'onpubapi.php';

// Include frontend class.
include $onpub_dir_frontend . 'OnpubFrontend.php';

if (file_exists($onpub_dir_local . 'OnpubFrontendCustom.php'))
{
  // We've found a custom frontend.
  include $onpub_dir_local . 'OnpubFrontendCustom.php';
}

if (class_exists('OnpubFrontendCustom') && is_subclass_of('OnpubFrontendCustom', 'OnpubFrontend'))
{
  // Custom frontend class is defined and correctly extends standard object.
  $onpub_frontend = new OnpubFrontendCustom();
}
else {
  // No custom frontend is defined, use standard implementation.
  $onpub_frontend = new OnpubFrontend();
}

$onpub_frontend->display();

?>
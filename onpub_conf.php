<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

// LOCAL CONFIG
// Path to optional local config file.
// It's recommended that you copy customized variables from the default
// onpub_conf.php file to this file. That way when upgrading Onpub to new versions,
// your local config customization will not be overwritten by the defaults
// present in onpub_conf.php. This file is loaded immediately after onpub_conf.php.
$onpub_conf_local = './onpub_conf_local.php';

// DATABASE CONFIG
// MySQL server host where Onpub database is installed.
$onpub_db_host = 'localhost';
// MySQL database where Onpub tables are installed.
$onpub_db_name = '';
// MySQL username used to connect to Onpub database.
$onpub_db_user = '';
// MySQL password used to connect to Onpub database.
$onpub_db_pass = '';

// DIRECTORY CONFIG
// If defined, all $onpub_dir variables must include a trailing slash.
// Path to local include files.
$onpub_dir_local = 'local/';
// Path to the root Onpub directory. All files and directories below this
// variable are included/referenced relative to the path defined here.
$onpub_dir_root = '';
// Path to YUI directory.
$onpub_dir_yui = 'yui/build/';
// Path to OnpubAPI directory.
$onpub_dir_api = 'api/';
// Path to default Onpub frontend directory.
$onpub_dir_frontend = 'frontend/';
// Path to the Onpub management interface.
$onpub_dir_manage = 'manage/';

// WEBSITE/LAYOUT CONFIG
// ID of Onpub Website to display.
$onpub_disp_website = 1;
// ID of Onpub Article to display on the home page. Set this to null if you
// don't want to display an article on the home page.
$onpub_disp_article = 1;
// Set this to false if you do not want to display "Updates" on the home page.
$onpub_disp_updates = true;
// Set this to false to hide the Onpub "Login" link.
$onpub_disp_login = true;
// Set this to false to hide the Onpub menu bar.
$onpub_disp_menu = true;

// CUSTOM OPTIONAL INCLUDE FILES
// The files below are included relative to the $onpub_dir_local directory.
// This file is included right before the closing </body> tag. Suitable for
// pasting in Google Analytics and/or other dynamic includes.
$onpub_inc_foot = 'onpub_foot.php';
// This file is included immediately before the opening <head> tag. Suitable
// for initializing JS variables and/or other dynamic PHP/JS code.
$onpub_inc_head = 'onpub_head.php';
// This file is included in the right-side column after the published/updated
// dates on article pages.
$onpub_inc_article_info = 'onpub_article_info.php';
// This file is included immediately after the main article content.
$onpub_inc_article_foot = 'onpub_article_foot.php';
// If present, this file is included in place of the default Onpub CSS file.
// This allows you to define your own CSS styles to create a completely
// original layout (in combination with tweaking the yui variables below).
$onpub_inc_css = 'onpub.css';
// If present, this file is included in place of the default menu CSS file.
// This allows you to define your own menu style.
$onpub_inc_css_menu = 'onpub-menu.css';

// YUI CONTROL VARIABLES
// Version of YUI bundled with Onpub. If bundled YUI does not exist then this
// is the version Onpub will attempt to download from Yahoo!'s CDN.
$onpub_yui_version = '3.2.0';

?>
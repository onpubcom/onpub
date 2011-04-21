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
This is the Onpub website frontend configuration file.
See http://onpub.com/index.php?s=8&a=96 for more information.
*/

// Local Config

/*
The path to an optional local configuration file. It's recommended that you
copy customized variables from the default onpub_conf.php file to this file.
That way when upgrading Onpub to new versions, your local config customization
will not be overwritten by the defaults present in onpub_conf.php. This file is
loaded by the Onpub frontend immediately after onpub_conf.php.
*/
$onpub_conf_local = './onpub_conf_local.php';

// Database Config

/*
MySQL server host where the Onpub database schema is installed. Change the
value of this variable to the hostname of your MySQL server if it's not running
on the same server as Onpub.
*/
$onpub_db_host = 'localhost';

/*
MySQL database where Onpub schema is installed. Change the value of this
variable to the name of the MySQL database where you'd like to install the
Onpub schema the first time you login to the Onpub content management interface.
*/
$onpub_db_name = '';

/*
MySQL username used to connect to Onpub database. Change the value of this
variable to the username that has at least read access to the database where
you installed the Onpub database schema.
*/
$onpub_db_user = '';

/*
MySQL password used to connect to Onpub database. Change the value of this
variable to the password of the MySQL user defined by the variable above.
*/
$onpub_db_pass = '';

// Directories Config

/*
Path to local include files. Please note that all path names defined in this
section must include a trailing slash ('/') character. Change the value of this
variable if you want to store your local include files in a directory other
than the default defined here. See the Local Files Include section below for a
list of file names that will automatically be included by the frontend if they
exist in this directory.
*/
$onpub_dir_local = 'local/';

/*
Path to the root Onpub directory. All files and directories below this variable
are included/referenced relative to the path defined here. For example if you
change the value of this variable to 'onpub/', all directories defined below
will be referenced by the frontend relative to this path, e.g.: onpub/yui/,
onpub/api/, onpub/frontend/, etc. This allows you to load the frontend
index.php and onpub_conf_*.php files from a location outside of the root onpub/
installation directory.
*/
$onpub_dir_root = '';

/*
Path to YUI directory. This directory contains the YUI 3 distribution. Rename
or delete this directory if you'd like to include the YUI files from Yahoo!'s
CDN instead.
*/
$onpub_dir_yui = 'yui/build/';

/*
Path to OnpubAPI directory. It is recommended that you leave this value
unchanged.
*/
$onpub_dir_api = 'api/';

/*
Path to default Onpub frontend directory. This variable is useful for pointing
Onpub to a different frontend include path. For example, you could copy the
entire frontend/ directory to a new directory called frontend-custom/ and then
change the value of this variable to 'frontend-custom/'. Onpub would then
automatically include the frontend files from the frontend-custom/ directory.
This is one way to use the existing Onpub frontend code as a starting-point to
create a totally custom layout/design while still using the Onpub content
management interface to keep your site updated.
*/
$onpub_dir_frontend = 'frontend/';

/*
Path to the Onpub management interface. It is recommended that you leave this
value unchanged.
*/
$onpub_dir_manage = 'manage/';

// Frontend Display Config

/*
ID of Onpub Website to display. Change this value to the ID of the Onpub
website you want the frontend to display by default. Most people will not need
to modify this variable.
*/
$onpub_disp_website = 1;

/*
ID of Onpub Article to display on the home page. Set this to null if you don't
want to display an article on the home page. Or change this to the ID of the
Onpub article you want to display on the frontend home/index page. By default,
the first article you create with Onpub is displayed on the frontend home page
(index.php).
*/
$onpub_disp_article = 1;

/*
Set this to false if you do not want to display the "What's New" section on the
home page.
*/
$onpub_disp_updates = true;

/*
Set this to flase to prevent the Onpub "Login" hyperlink from showing up
anywhere on the frontend interface.
*/
$onpub_disp_login = true;

/*
Set this to false to hide the Onpub horizontal navigation menu bar.
*/
$onpub_disp_menu = true;

// Local File Includes Config

/*
If this file exists in the $onpub_dir_local directory it will automatically be
included by the frontend right before the closing </body> tag on every page.
This file is recommended for pasting in Google Analytics and/or other dynamic
includes.
*/
$onpub_inc_foot = 'onpub_foot.php';

/*
If this file exists in the $onpub_dir_local directory, it is included by the
frontend immediately before the opening <head> tag on every page. Suitable for
initializing JS variables and/or other dynamic PHP/JS code.
*/
$onpub_inc_head = 'onpub_head.php';

/*
If this file exists in the $onpub_dir_local directory, it is included by the
frontend immediately before the "onpub-header" <div> on every page. This file
is useful for including code that will display an ad banner or other page
header content that's separate from the site logo and navigation at the top of
every frontend page.
*/
$onpub_inc_banner = 'onpub_banner.php';

/*
If this file exists in the $onpub_dir_local directory, it is included in the
right-side column beside the published/updated dates <div> on article pages
only. We use this file to include our AddThis code for our articles and blog
posts.
*/
$onpub_inc_article_info = 'onpub_article_info.php';

/*
If this file exists in the $onpub_dir_local directory, it is included
immediately after the main content section on all frontend article pages. We
use this file to include our Disqus comment threading code.
*/
$onpub_inc_article_foot = 'onpub_article_foot.php';

/*
If this file exists in the $onpub_dir_local directory, this file is included in
addition to the default Onpub frontend CSS file. This allows you to selectively
override the frontends default CSS classes. Tweaking this file allows you to
create your own custom-branded Onpub frontend design.
*/
$onpub_inc_css = 'onpub.css';

/*
If this file exists in the $onpub_dir_local directory, this file is included in
addition to the default Onpub frontend horizontal navigation menu CSS file.
This allows you to selectively override the frontends default CSS classes that
specifies the design of the navigation menu. Tweaking this file allows you to
create your own custom nav menu design.
*/
$onpub_inc_css_menu = 'onpub-menu.css';

// YUI Config

/*
This variable is only used if the $onpub_dir_yui directory does not exist.
If that directory is missing this variable specified the version of YUI that is
to be automatically downloaded from Yahoo!'s CDN. The default value of this
variable will usually be set to whatever the most recent version of YUI 3 is at
the time new versions of Onpub are released.
*/
$onpub_yui_version = '3.3.0';

?>
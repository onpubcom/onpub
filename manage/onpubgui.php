<?php

/* Onpub (TM)
 * Copyright (C) 2015 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

// Default database host. Comment this out to allow the user to
// specify a databse hostname at the Login page.
define("ONPUBGUI_PDO_HOST", "localhost");

// Directory Configuration
// Icons, buttons and other Onpub GUI graphics are stored in this directory
define("ONPUBGUI_IMAGE_DIRECTORY", "images/");
define("ONPUBGUI_YUI_DIRECTORY", "../api/yui/build/");
define("ONPUBGUI_YUI_VERSION", "3.17.2");

// UI Configuration
// Controls how many rows get displayed on each page in the Select UIs
define("ONPUBGUI_PDO_ROW_LIMIT", 10);
// Controls what time zone to use if date.timezone is not set in php.ini.
define("ONPUBGUI_DEFAULT_TZ", "America/New_York");

// Error codes for GUI exceptions
define("ONPUBGUI_ERROR_MOVE_UPLOADED_FILE", 2);
define("ONPUBGUI_ERROR_FILE_SIZE", 3);
define("ONPUBGUI_ERROR_IMAGE_TYPE", 4);
define("ONPUBGUI_ERROR_IMAGE_EXISTS", 5);

// Suffix to use for temporary image files
define("ONPUBGUI_TMP_IMG_SUFFIX", '.onpubnew');

// User Interface Classes
include ("classes/OnpubNewArticle.php");
include ("classes/OnpubNewSection.php");
include ("classes/OnpubNewWebsite.php");

include ("classes/OnpubDeleteArticles.php");

include ("classes/OnpubEditArticle.php");
include ("classes/OnpubEditImage.php");
include ("classes/OnpubEditSection.php");
include ("classes/OnpubEditWebsite.php");

include ("classes/OnpubMoveArticles.php");

include ("classes/OnpubLogin.php");
include ("classes/OnpubWelcome.php");

include ("classes/OnpubEditArticles.php");
include ("classes/OnpubEditImages.php");
include ("classes/OnpubEditSections.php");
include ("classes/OnpubEditWebsites.php");

include ("classes/OnpubUploadImages.php");

include ("classes/OnpubWidgetArticles.php");
include ("classes/OnpubWidgetDateCreated.php");
include ("classes/OnpubWidgetFooter.php");
include ("classes/OnpubWidgetHeader.php");
include ("classes/OnpubWidgetImages.php");
include ("classes/OnpubWidgetPaginator.php");
include ("classes/OnpubWidgetPDOException.php");
include ("classes/OnpubWidgetSearch.php");
include ("classes/OnpubWidgetSections.php");
include ("classes/OnpubWidgetStats.php");
include ("classes/OnpubWidgetSelectWebsite.php");
include ("classes/OnpubWidgetWebsites.php");

?>
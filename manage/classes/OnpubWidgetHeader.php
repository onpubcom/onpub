<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetHeader
{
  private $title;

  function __construct($title = "")
  {
    $this->title = $title;
  }

  function display()
  {
    en('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
    en('<html>');
    en('<head>');
    en('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">');
    en('<meta http-equiv="Content-Style-Type" content="text/css">');
    en('<title>' . strip_tags("Onpub (on " . $_SERVER['SERVER_NAME'] . ") - " . $this->title) . '</title>');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssreset/reset-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssfonts/fonts-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssgrids/grids-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssbase/base-min.css">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' . ONPUBGUI_YUI_VERSION . '/build/cssreset/reset-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssfonts/fonts-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssgrids/grids-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssbase/base-min.css">');
    }

    en('<link rel="stylesheet" type="text/css" href="css/onpub.css">');

    en('<script type="text/javascript">');
    en('document.documentElement.className = "yui3-loading";');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('var onpub_dir_yui = "' . ONPUBGUI_YUI_DIRECTORY . '";');
    }
    else {
      en('var onpub_dir_yui = null;');
    }

    en('var onpub_yui_version = "' . ONPUBGUI_YUI_VERSION . '";');
    en('</script>');

    en('</head>');
    en('<body class="yui3-skin-sam">');

    en('<div id="onpub-page" class="yui3-d2">');

    en('<div id="onpub-header">');

    en('<div id="onpub-logo"><a href="index.php"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'onpub.png" width="143" height="29" alt="Onpub" title="Onpub" border="0"></a></div>', 1);

    en('<div id="onpub-menubar" class="yui3-menu yui3-menu-horizontal yui3-menubuttonnav">');
    en('<div class="yui3-menu-content">');
    en('<ul>');
    en('<li>');
    en('<a class="yui3-menu-label" href="#new"><em>New</em></a>');
    en('<div id="new" class="yui3-menu">');
    en('<div class="yui3-menu-content">');
    en('<ul>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=NewArticle">Article</a></li>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=NewSection">Section</a></li>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=NewWebsite">Website</a></li>');
    en('</ul>');
    en('</div>');
    en('</div>');
    en('</li>');
    en('<li>');
    en('<a class="yui3-menu-label" href="#edit"><em>Edit</em></a>');
    en('<div id="edit" class="yui3-menu">');
    en('<div class="yui3-menu-content">');
    en('<ul>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditArticles">Articles</a></li>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditImages">Images</a></li>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditSections">Sections</a></li>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditWebsites">Websites</a></li>');
    en('</ul>');
    en('</div>');
    en('</div>');
    en('</li>');
    en('<li>');
    en('<a class="yui3-menu-label" href="#upload"><em>Upload</em></a>');
    en('<div id="upload" class="yui3-menu">');
    en('<div class="yui3-menu-content">');
    en('<ul>');
    en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=UploadImages">Images</a></li>');
    en('</ul>');
    en('</div>');
    en('</div>');
    en('</li>');
    en('<li class="yui3-menuitem">');
    en('<a class="yui3-menuitem-content" href="http://onpub.com/index.php?sectionID=8" target="_blank">Help</a>');
    en('</li>');
    en('<li class="yui3-menuitem">');
    en('<a class="yui3-menuitem-content" href="index.php?onpub=Logout">Logout</a>');
    en('</li>');
    en('</ul>');
    en('</div>');
    en('</div>');

    en('</div>');

    en('<div id="onpub-body">');
    en('<div class="yui3-g">');
    en('<div class="yui3-u-23-24">');

    if ($this->title) {
      en('<h1>' . strip_tags($this->title) . '</h1>');
    }
    else {
      en('<h1>Onpub (on ' . $_SERVER['SERVER_NAME'] . ')</h1>');
    }
  }
}
?>
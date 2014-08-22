<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2012, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetHeader
{
  private $title;
  private $dbstatus;

  function __construct($title = "", $dbstatus = ONPUBAPI_SCHEMA_VERSION, $pdo = null)
  {
    $this->title = $title;
    $this->dbstatus = $dbstatus;
    $this->pdo = $pdo;
  }

  function display()
  {
    en('<!DOCTYPE html>');
    en('<html>');
    en('<head>');
    en('<meta name="viewport" content="width=device-width; initial-scale=1.0">');
    en('<meta charset="ISO-8859-1">');
    en('<title>' . strip_tags("Onpub (on " . $_SERVER['SERVER_NAME'] . ") - " . $this->title) . '</title>');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssnormalize/cssnormalize-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssfonts/cssfonts-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssgrids/cssgrids-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'node-menunav/assets/skins/sam/node-menunav.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'widget-base/assets/skins/sam/widget-base.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'widget-stack/assets/skins/sam/widget-stack.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'overlay/assets/skins/sam/overlay.css">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' .
         ONPUBGUI_YUI_VERSION . '/build/cssnormalize/cssnormalize-min.css&' . ONPUBGUI_YUI_VERSION .
         '/build/cssfonts/cssfonts-min.css&' . ONPUBGUI_YUI_VERSION .
         '/build/cssgrids/cssgrids-min.css&' . ONPUBGUI_YUI_VERSION .
         '/build/node-menunav/assets/skins/sam/node-menunav.css&' . ONPUBGUI_YUI_VERSION .
         '/build/widget-base/assets/skins/sam/widget-base.css&' . ONPUBGUI_YUI_VERSION .
         '/build/widget-stack/assets/skins/sam/widget-stack.css&' . ONPUBGUI_YUI_VERSION .
         '/build/overlay/assets/skins/sam/overlay.css">');
    }

    en("<link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>");
    en('<link rel="stylesheet" type="text/css" href="css/onpub.css">');
    en('<link rel="stylesheet" type="text/css" href="css/onpub-menu.css">');

    if (file_exists('ckeditor/ckeditor.js')) {
      en('<script type="text/javascript" src="ckeditor/ckeditor.js"></script>');
    }

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

    en('<div id="onpub-header">');
    en('<div id="onpub-logo">');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-4">');
    en('<a href="index.php"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'onpub-small.png" width="150" height="60" alt="Onpub" title="Onpub" border="0"></a>', 1);
    en('</div>');

    en('<div class="yui3-u-3-4">');
    en('<div id="onpub-menubar" class="yui3-menu yui3-menu-horizontal yui3-menubuttonnav" style="float: right; margin-top: 2.25em;">');
    en('<div class="yui3-menu-content">');
    en('<ul>');

    if ($this->dbstatus == ONPUBAPI_SCHEMA_VERSION) {
      if ($this->pdo) {
        $oarticles = new OnpubArticles($this->pdo);
      }
      else {
        $oarticles = null;
      }

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

      if ($oarticles) {
        $queryOptions = new OnpubQueryOptions();
        $queryOptions->includeContent = FALSE;
        $queryOptions->orderBy = "modified";
        $queryOptions->order = "DESC";
        $queryOptions->setPage(1, ONPUBGUI_PDO_ROW_LIMIT);

        try {
          $articles = $oarticles->select($queryOptions);
        }
        catch (PDOException $e) {
          $articles = null;
        }
      }
      else {
        $articles = null;
      }

      if ($articles) {
        en('<li>');
        en('<a class="yui3-menu-label" href="index.php?onpub=EditArticles"><em>Articles</em></a>');
        en('<div id="edit" class="yui3-menu">');
        en('<div class="yui3-menu-content">');
        en('<ul>');
  
        foreach ($articles as $a) {
          en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditArticle&articleID=' . $a->ID . '">' . $a->title . '</a></li>');
        }
  
        en('<li class="yui3-menuitem"><a class="yui3-menuitem-content" href="index.php?onpub=EditArticles">All Articles..</a></li>');
        en('</ul>');
        en('</div>');
        en('</div>');
        en('</li>');
      }
      else {
        en('<li class="yui3-menuitem">');
        en('<a class="yui3-menuitem-content" href="index.php?onpub=EditArticles">Articles</a>');
        en('</li>');
      }

      en('<li class="yui3-menuitem">');
      en('<a class="yui3-menuitem-content" href="index.php?onpub=EditImages">Images</a>');
      en('</li>');
      en('<li class="yui3-menuitem">');
      en('<a class="yui3-menuitem-content" href="index.php?onpub=EditSections">Sections</a>');
      en('</li>');
      en('<li class="yui3-menuitem">');
      en('<a class="yui3-menuitem-content" href="index.php?onpub=EditWebsites">Websites</a>');
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
    }

    en('</ul>');
    en('</div>');
    en('</div>');
    en('</div>');



    en('</div>');

    en('</div>');
    en('</div>');

    en('<div id="onpub-page">');

    en('<div id="onpub-body">');
    en('<div class="yui3-g">');
    en('<div class="yui3-u-1">');

    if ($this->title) {
      en('<h1 style="margin-right: 0;">' . $this->title . '</h1>');
    }
    else {
      en('<h1 style="margin-right: 0;">Onpub (on ' . $_SERVER['SERVER_NAME'] . ')</h1>');
    }
  }
}
?>
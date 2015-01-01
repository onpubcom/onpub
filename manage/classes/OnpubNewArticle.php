<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubNewArticle
{
  private $pdo;
  private $oarticle;
  private $oauthor;

  function __construct(PDO $pdo, OnpubArticle $oarticle, OnpubAuthor $oauthor)
  {
    $this->pdo = $pdo;
    $this->oarticle = $oarticle;
    $this->oauthor = $oauthor;
  }

  public function display()
  {
    $owebsites = new OnpubWebsites($this->pdo);
    $osections = new OnpubSections($this->pdo);
    $oimages = new OnpubImages($this->pdo);

    try {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "fileName";
      $queryOptions->order = "ASC";
      $images = $oimages->select($queryOptions);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("New Article", ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post">');
    en('<div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');

    if ($this->oarticle->title === NULL) {
      en('<p><span class="onpub-field-header">Title</span> <input type="text" maxlength="255" size="40" name="title" value=""> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field"></p>');
    }
    else {
      en('<p><span class="onpub-field-header">Title</span> <input type="text" maxlength="255" size="40" name="title" value="' . htmlentities($this->oarticle->title) . '"></p>');
    }

    en('</div>');

    en('<div class="yui3-u-1-2">');

    en('<p><span class="onpub-field-header">Author</span> <input type="text" maxlength="255" size="40" name="displayAs" value="' . htmlentities($this->oauthor->displayAs) . '"></p>');

    en('</div>');

    en('</div>');

    en('<p><textarea rows="25" name="content" style="width: 100%;">' . htmlentities($this->oarticle->content) . '</textarea></p>');

    if (file_exists('ckeditor/ckeditor.js')) {
      ?>
      <script type="text/javascript">
        CKEDITOR.replace('content', {
          'height': 350,
          'uiColor': '#eff0f0',
          'resize_dir': 'vertical',
          'dataIndentationChars': '  ',
          'allowedContent': true,
          <?php
          if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
            en("'contentsCss': ['" . ONPUBGUI_YUI_DIRECTORY . "cssnormalize/cssnormalize-min.css', '" . ONPUBGUI_YUI_DIRECTORY . "cssfonts/cssfonts-min.css', '" . ONPUBGUI_YUI_DIRECTORY . "cssgrids/cssgrids-min.css', 'ckeditor/contents.css', 'css/ckeditor.css']");
          }
          else {
            en("'contentsCss': ['http://yui.yahooapis.com/" . ONPUBGUI_YUI_VERSION . "/build/cssnormalize/cssnormalize-min.css', 'http://yui.yahooapis.com/" . ONPUBGUI_YUI_VERSION . "/build/cssfonts/cssfonts-min.css', 'http://yui.yahooapis.com/" . ONPUBGUI_YUI_VERSION . "/build/cssgrids/cssgrids-min.css', 'ckeditor/contents.css', 'css/ckeditor.css']");
          }
          ?>
        });
      </script>
      <?php
    }

    $widget = new OnpubWidgetDateCreated($this->oarticle->getCreated());
    $widget->display();

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');
    $widget = new OnpubWidgetSections();
    $widget->sectionIDs = $this->oarticle->sectionIDs;
    $widget->websites = $websites;
    $widget->osections = $osections;
    $widget->display();
    en('</div>');

    en('<div class="yui3-u-1-2">');
    $widget = new OnpubWidgetImages("Image", $this->oarticle->imageID, $images);
    $widget->display();
    en('</div>');

    en('</div>');

    en('<input type="submit" value="Save">');

    en('<input type="hidden" name="onpub" value="NewArticleProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function process()
  {
    $oarticles = new OnpubArticles($this->pdo);

    if ($this->oauthor->displayAs) {
      $authors = array ($this->oauthor);
      $this->oarticle->authors = $authors;
    }

    try {
      $oarticles->insert($this->oarticle);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }

  public function validate()
  {
    if (!$this->oarticle->title) {
      $this->oarticle->title = NULL;
      return FALSE;
    }

    return TRUE;
  }
}
?>
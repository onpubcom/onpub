<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
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

    try {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "fileName";
      $queryOptions->order = "ASC";
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("New Article");
    $widget->display();

    en('<form action="index.php" method="post">');
    en('<div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u first">');

    if ($this->oarticle->title === NULL) {
      en('<b>Title</b><br><input type="text" maxlength="255" size="40" name="title" value=""> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field">', 1, 2);
    }
    else {
      en('<b>Title</b><br><input type="text" maxlength="255" size="40" name="title" value="'
        . htmlentities($this->oarticle->title) . '">', 1, 2);
    }

    en('</div>');

    en('<div class="yui3-u">');

    en('<b>Author</b><br><input type="text" maxlength="255" size="40" name="displayAs" value="' . htmlentities($this->oauthor->displayAs) . '">', 1, 2);

    en('</div>');

    en('</div>');

    en('<b>Content</b>', 1, 1);

    en('<textarea rows="25" cols="100" name="content">' . htmlentities($this->oarticle->content) . '</textarea>');

    if (file_exists('ckeditor/ckeditor_php5.php')) {
      include './ckeditor/ckeditor_php5.php';
      $config = array();
      $events = array();

      $ck = new CKEditor();
      $ck->basePath = 'ckeditor/';

      $config['height'] = 320;
      $config['contentsCss'] = array('ckeditor/contents.css', 'css/ckeditor.css');
      $events['instanceReady'] = 'function (ev) {
        var w = ev.editor.dataProcessor.writer;
        w.indentationChars = "";
        w.selfClosingEnd = ">";
      }';

      $ck->replace('content', $config, $events);

      br();
    }
    else {
      br(2);
    }

    $widget = new OnpubWidgetDateCreated($this->oarticle->getCreated());
    $widget->display();

    br(2);

    $widget = new OnpubWidgetSections();
    $widget->sectionIDs = $this->oarticle->sectionIDs;
    $widget->websites = $websites;
    $widget->osections = $osections;
    $widget->display();

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
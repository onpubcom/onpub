<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditArticle
{
  private $pdo;
  private $oarticle;

  function __construct(PDO $pdo, OnpubArticle $oarticle)
  {
    $this->pdo = $pdo;
    $this->oarticle = $oarticle;
  }

  public function display()
  {
    $oarticles = new OnpubArticles($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $osamaps = new OnpubSAMaps($this->pdo);
    $osections = new OnpubSections($this->pdo);

    try {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->includeAuthors = TRUE;
      $this->oarticle = $oarticles->get($this->oarticle->ID, $queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "fileName";
      $queryOptions->order = "ASC";

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $samaps = $osamaps->select($queryOptions, NULL, $this->oarticle->ID);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $author = $this->oarticle->authors;

    if (sizeof($author)) {
      $author = $author[0];
    }
    else {
      $author = new OnpubAuthor();
    }

    $widget = new OnpubWidgetHeader("Article " . $this->oarticle->ID . " - " . $this->oarticle->title);
    $widget->display();

    en('<form action="index.php" method="post">');
    en('<div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');

    en('<b>Title</b><br><input type="text" maxlength="255" size="40" name="title" value="' . htmlentities($this->oarticle->title) . '">', 1, 2);

    en('</div>');

    en('<div class="yui3-u-1-2">');

    en('<b>Author</b><br><input type="text" maxlength="255" size="40" name="displayAs" value="' . htmlentities($author->displayAs) . '">', 1, 2);

    en('</div>');

    en('</div>');

    en('<b>Content</b><br>');

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

    $modified = $this->oarticle->getModified();

    en('<b>Modified</b><br>' . $modified->format('M j, Y g:i:s A'), 1, 2);

    if ($this->oarticle->url) {
      $go = ' <b><a href="' . $this->oarticle->url . '" target="_blank"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'world_go.png" border="0" align="top" alt="Go" title="Go" width="16" height="16"></a></b>';
    }
    else {
      $go = '';
    }

    en('<b>URL</b><br><input type="text" maxlength="255" size="75" name="url" value="' . htmlentities($this->oarticle->url) . '">' . $go . '', 1, 2);

    $widget = new OnpubWidgetSections();
    $widget->websites = $websites;
    $widget->osections = $osections;
    $widget->samaps = $samaps;
    $widget->display();

    en('<input type="submit" value="Save"> <input type="button" value="Delete" id="deleteArticle">');

    en('<input type="hidden" name="articleID" id="articleID" value="' . $this->oarticle->ID . '">');
    en('<input type="hidden" name="imageID" value="' . $this->oarticle->imageID . '">');
    en('<input type="hidden" name="authorID" value="' . $author->ID . '">');
    en('<input type="hidden" name="authorImageID" value="' . $author->imageID . '">');
    en('<input type="hidden" name="lastDisplayAs" value="'  . htmlentities($author->displayAs) . '">');
    en('<input type="hidden" name="onpub" value="EditArticleProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    if (!$this->oarticle->title) {
      $this->oarticle->title = NULL;
      return FALSE;
    }

    return TRUE;
  }

  public function process()
  {
    $oarticles = new OnpubArticles($this->pdo);

    try {
      $oarticles->update($this->oarticle, TRUE);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }

  public function delete()
  {
    $oarticles = new OnpubArticles($this->pdo);

    try {
      $oarticles->delete($this->oarticle->ID);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
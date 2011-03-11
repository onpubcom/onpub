<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubMoveArticles
{
  private $pdo;
  private $articleIDs;
  private $sectionIDs;

  function __construct(PDO $pdo, $articleIDs, $sectionIDs = array ())
  {
    $this->pdo = $pdo;
    $this->articleIDs = $articleIDs;
    $this->sectionIDs = $sectionIDs;
  }

  public function display()
  {
    $oarticles = new OnpubArticles($this->pdo);
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $articles = array ();
    $websites = array ();

    try {
      if (is_array($this->articleIDs)) {
        $queryOptions = new OnpubQueryOptions();
        $queryOptions->includeContent = FALSE;

        for ($i = 0; $i < sizeof($this->articleIDs); $i++) {
          $articles[] = $oarticles->get($this->articleIDs[$i], $queryOptions);
        }
      }

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Move Articles");
    $widget->display();

    en('<form action="index.php" method="post">');
    en('<div>');

    if (sizeof($articles)) {
      en('<p>Select the section(s) below to move the selected articles to. Click Move to apply the change.</p>');

      en('<strong>Selected Articles</strong>');
      en('<ul>');

      for ($i = 0; $i < sizeof($articles); $i++) {
        en('<li><a href="index.php?onpub=EditArticle&amp;articleID=' . $articles[$i]->ID . '" title="Edit">' . $articles[$i]->title . '</a></li>');
        en('<input type="hidden" name="articleIDs[]" value="' . $articles[$i]->ID . '">');
      }

      en('</ul>');

      $widget = new OnpubWidgetSections();
      $widget->websites = $websites;
      $widget->osections = $osections;
      $widget->display();

      en('<input type="submit" value="Move">');
    }
    else {
      en('<span class="onpub-error">No articles were selected to move.</span>');
    }

    en('<input type="hidden" name="onpub" value="ArticleMoveProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate() { }

  public function process()
  {
    $oarticles = new OnpubArticles($this->pdo);
    $queryOptions = new OnpubQueryOptions();
    $queryOptions->includeAuthors = TRUE;

    try {
      for ($i = 0; $i < sizeof($this->articleIDs); $i++) {
        $article = $oarticles->get($this->articleIDs[$i], $queryOptions);
        $article->sectionIDs = $this->sectionIDs;
        $oarticles->update($article);
      }
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
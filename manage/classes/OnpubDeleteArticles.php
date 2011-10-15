<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubDeleteArticles
{
  private $pdo;
  private $articleIDs;

  function __construct(PDO $pdo, $articleIDs)
  {
    $this->pdo = $pdo;
    $this->articleIDs = $articleIDs;
  }

  public function display()
  {
    $oarticles = new OnpubArticles($this->pdo);
    $articles = array ();

    try {
      if (is_array($this->articleIDs)) {
        $queryOptions = new OnpubQueryOptions();
        $queryOptions->includeContent = false;

        for ($i = 0; $i < sizeof($this->articleIDs); $i++) {
          $articles[] = $oarticles->get($this->articleIDs[$i], $queryOptions);
        }
      }
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Delete Articles", ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post">');
    en('<div>');

    if (sizeof($articles)) {
      en('<h3>Selected Articles</h3>');
      en('<ul>');

      for ($i = 0; $i < sizeof($articles); $i++) {
        en('<li><a href="index.php?onpub=EditArticle&amp;articleID=' . $articles[$i]->ID . '" title="Edit">' . $articles[$i]->title . '</a></li>');
        en('<input type="hidden" name="articleIDs[]" value="' . $articles[$i]->ID . '">');
      }

      en('</ul>');

      en('<input type="submit" value="Delete" id="confirmDeleteArticle">');
    }
    else {
      en('<span class="onpub-error">No articles were selected to delete.</span>');
    }

    en('<input type="hidden" name="onpub" value="DeleteArticleProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate() { }

  public function process()
  {
    $oarticles = new OnpubArticles($this->pdo);

    try {
      for ($i = 0; $i < sizeof($this->articleIDs); $i++) {
        $oarticles->delete($this->articleIDs[$i]);
      }
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetArticles
{
  private $pdo;
  private $osection;

  function __construct(PDO $pdo, $osection)
  {
    $this->pdo = $pdo;
    $this->osection = $osection;
  }

  function display()
  {
    $osamaps = new OnpubSAMaps($this->pdo);
    $oarticles = new OnpubArticles($this->pdo);
    $articles = array();

    en('<h3 class="onpub-field-header">Visible Articles</h3>');
    en('<p>');
    en('<small>These articles are displayed by the frontend navigation menu in the same order as listed below</small>', 1, 1);

    en('<select name="articleIDs[]" size="10" multiple="multiple" id="articles">');

    $queryOptions = new OnpubQueryOptions();
    $queryOptions->orderBy = "ID";
    $queryOptions->order = "ASC";
    $samaps = $osamaps->select($queryOptions, $this->osection->ID);

    if (sizeof($samaps)) {
      for ($i = 0; $i < sizeof($samaps); $i++) {
        $article = $oarticles->get($samaps[$i]->articleID);
        en('<option value="' . $article->ID . '">' . strip_tags($article->title) . '</option>');
        $articles[] = $article;
      }
    }
    else {
      en('<option value="">None</option>');
    }

    en('</select>');
    en('</p>');

    en('<p><input type="button" value="Move Up" id="moveUp"> <input type="button" value="Move Down" id="moveDown"> <input type="button" value="Sort By Date" id="sortByDate"> <input type="button" value="Hide" id="hide"></p>');

    // Output articles as JS objects to enable sorting articles list.
    en('<script type="text/javascript">');
    en('var onpub_articles = [');

    for ($i = 0; $i < sizeof($articles); $i++) {
      $created = $articles[$i]->getCreated();
      en('{ID: ' . $articles[$i]->ID . ', title: "' . $articles[$i]->title . '", created: new Date(' . $created->format("Y") . ', ' . ($created->format("n") - 1) . ', ' . $created->format("j") . ', ' . $created->format("G") . ', ' . $created->format("i") . ', ' . $created->format("s") . ')}', 0);
      if ($i + 1 < sizeof($articles)) {
        en(',');
      }
    }

    en('];');

    en('</script>');
  }
}
?>
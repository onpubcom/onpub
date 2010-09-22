<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetSelectSection
{
  private $pdo;
  private $sectionID;

  function __construct(PDO $pdo, $sectionID)
  {
    $this->pdo = $pdo;
    $this->sectionID = $sectionID;
  }

  public function display()
  {
    $owebsites = new OnpubWebsites($this->pdo);
    $osections = new OnpubSections($this->pdo);

    en('<strong>Display articles in...</strong><br>');
    en('<select name="sectionID"  onchange="document.forms[0].submit();">');
    en('<option value="">All Sections</option>');

    $queryOptions = new OnpubQueryOptions();
    $queryOptions->orderBy = "name";
    $queryOptions->order = "ASC";
    $websites = $owebsites->select($queryOptions);

    for ($i = 0; $i < sizeof($websites); $i++) {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $sections = $osections->select($queryOptions, $websites[$i]->ID);

      for ($j = 0; $j < sizeof($sections); $j++) {
        if ($sections[$j]->ID == $this->sectionID) {
          en('<option value="' . $sections[$j]->ID . '" selected="selected">'
            . strip_tags($websites[$i]->name . ' &ndash; '
            . $sections[$j]->name) . '</option>');
        }
        else {
          en('<option value="' . $sections[$j]->ID . '">'
            . strip_tags($websites[$i]->name . ' &ndash; '
            . $sections[$j]->name) . '</option>');
        }
      }
    }

    en('</select>');
  }
}
?>
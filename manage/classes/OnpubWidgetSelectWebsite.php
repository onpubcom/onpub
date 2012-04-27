<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2012, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetSelectWebsite
{
  private $pdo;
  private $websiteID;

  function __construct(PDO $pdo, $websiteID)
  {
    $this->pdo = $pdo;
    $this->websiteID = $websiteID;
  }

  public function display()
  {
    $owebsites = new OnpubWebsites($this->pdo);

    en('<h3 class="onpub-field-header">Display sections in..</h3>');
    en('<p>');
    en('<select name="websiteID"  onchange="document.forms[0].submit();">');
    en('<option value="">All Websites</option>');

    $queryOptions = new OnpubQueryOptions();
    $queryOptions->orderBy = "name";
    $queryOptions->order = "ASC";
    $websites = $owebsites->select($queryOptions);

    for ($i = 0; $i < sizeof($websites); $i++) {
      if ($websites[$i]->ID == $this->websiteID) {
        en('<option value="' . $websites[$i]->ID . '" selected="selected">'
          . strip_tags($websites[$i]->name) . '</option>');
      }
      else {
        en('<option value="' . $websites[$i]->ID . '">'
          . strip_tags($websites[$i]->name) . '</option>');
      }
    }

    en('</select>');
    en('</p>');
  }
}
?>
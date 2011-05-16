<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditSection
{
  private $pdo;
  private $osection;
  private $visible;

  function __construct(PDO $pdo, OnpubSection $osection, $visible = FALSE)
  {
    $this->pdo = $pdo;
    $this->osection = $osection;
    $this->visible = $visible;
  }

  public function display()
  {
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $oarticles = new OnpubArticles($this->pdo);
    $oimages = new OnpubImages($this->pdo);
    $owsmaps = new OnpubWSMaps($this->pdo);
    $queryOptions = new OnpubQueryOptions();
    $queryOptions->includeArticles = TRUE;
    $queryOptions->includeContent = FALSE;

    try {
      $this->osection = $osections->get($this->osection->ID, $queryOptions);

      $website = $owebsites->get($this->osection->websiteID);
      $numOfArticles = $oarticles->count();

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "fileName";
      $queryOptions->order = "ASC";
      $images = $oimages->select($queryOptions);

      $wsmap = new OnpubWSMap();
      $wsmap->websiteID = $this->osection->websiteID;
      $wsmap->sectionID = $this->osection->ID;
      $this->visible = $owsmaps->getID($wsmap);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Section " . $this->osection->ID . " - "
      . $this->osection->name);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post">');
    en('<div>');

    if ($this->osection->name === NULL) {
      en('<strong>Name</strong><br><input type="text" maxlength="255" size="75" name="name" value=""> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field">', 1, 2);
    }
    else {
      en('<strong>Name</strong><br><input type="text" maxlength="255" size="75" name="name" value="' . htmlentities($this->osection->name) . '">', 1, 2);
    }

    if ($this->osection->url) {
      $go = ' <strong><a href="' . $this->osection->url . '" target="_blank"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'world_go.png" border="0" align="top" alt="Go" title="Go" width="16" height="16"></a></strong>';
    }
    else {
      $go = '';
    }

    $widget = new OnpubWidgetImages("Image", $this->osection->imageID, $images, $website);
    $widget->display();

    if ($this->osection->parentID) {
      $sectionIDs = array ($this->osection->parentID);
    }
    else {
      $sectionIDs = array ();
    }

    $widget = new OnpubWidgetSections();
    $widget->sectionIDs = $sectionIDs;
    $widget->websites = array ($website);
    $widget->osections = $osections;
    $widget->heading = "Parent Section";
    $widget->multiple = FALSE;
    $widget->fieldName = "parentID";
    $widget->osection = $this->osection;
    $widget->display();

    en('<strong>Website</strong><br><a href="index.php?onpub=EditWebsite&amp;websiteID=' . $website->ID . '" title="Edit">' . $website->name . '</a>', 1, 2);

    if ($this->visible !== NULL) {
      en('<strong>Visibility</strong>', 1, 1);
      en('<input type="checkbox" id="id_visible" name="visible" value="1" checked="checked"> <label for="id_visible">Uncheck to unlink this section from the frontend navigation menu</label>', 1, 2);
    }
    else {
      en('<strong>Visibility</strong>', 1, 1);
      en('<input type="checkbox" id="id_visible" name="visible" value="1"> <label for="id_visible">Check to link this section to the frontend navigation menu</label>', 1, 2);
    }

    if ($numOfArticles) {
      $widget = new OnpubWidgetArticles($this->pdo, $this->osection);
      $widget->display();
    }
    else {
      en('<strong>Articles</strong><br>');
      en('There are 0 articles in the database. <a href="index.php?onpub=NewArticle">New Article</a>.');
      br (2);
    }

    en('<h3 class="onpub-field-header">Static Link</h3><p><small>Leave this field blank to make the frontend manage the link for this section (reccomended).</small><br><input type="text" maxlength="255" size="75" name="url" value="' . htmlentities($this->osection->url) . '">' . $go . '</p>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');
    en('<strong>Created</strong><br>' . $this->osection->getCreated()->format('M j, Y g:i:s A'), 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-2">');
    en('<h3 class="onpub-field-header">Modified</h3><p>' . $this->osection->getModified()->format('M j, Y g:i:s A') . '</p>');
    en('</div>');

    en('</div>');

    en('<p><input type="submit" value="Save" id="selectAll"> <input type="button" value="Delete" id="deleteSection"></p>');

    en('<input type="hidden" name="onpub" value="EditSectionProcess">');
    en('<input type="hidden" name="sectionID" value="' . $this->osection->ID . '">');
    en('<input type="hidden" name="websiteID" value="' . $this->osection->websiteID . '">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    if (!$this->osection->name) {
      $this->osection->name = NULL;
      return FALSE;
    }

    return TRUE;
  }

  public function process()
  {
    $osections = new OnpubSections($this->pdo);
    $owsmaps = new OnpubWSMaps($this->pdo);
    $wsmap = new OnpubWSMap();
    $wsmap->websiteID = $this->osection->websiteID;
    $wsmap->sectionID = $this->osection->ID;

    try {
      $osections->update($this->osection);

      if ($this->visible) {
        $owsmaps->insert($wsmap);
      }
      else {
        $owsmaps->delete($this->osection->websiteID, $this->osection->ID);
      }
    }
    catch (PDOException $e) {
      throw $e;
    }
  }

  public function delete()
  {
    $osections = new OnpubSections($this->pdo);

    try {
      $osections->delete($this->osection->ID);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
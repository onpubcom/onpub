<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubNewSection
{
  private $pdo;
  private $osection;
  private $visible;

  function __construct(PDO $pdo, OnpubSection $osection, $visible = TRUE)
  {
    $this->pdo = $pdo;
    $this->osection = $osection;
    $this->visible = $visible;
  }

  public function display()
  {
    $owebsites = new OnpubWebsites($this->pdo);
    $osections = new OnpubSections($this->pdo);

    $queryOptions = new OnpubQueryOptions();
    $queryOptions->orderBy = "name";
    $queryOptions->order = "ASC";

    try {
      $websites = $owebsites->select($queryOptions);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("New Section", ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post" enctype="multipart/form-data">');
    en('<div>');

    if ($this->osection->name === NULL) {
      en('<h3 class="onpub-field-header">Name</h3><p><input type="text" maxlength="255" size="' . 30 . '" name="name" value="' . htmlentities($this->osection->name) . '"> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field"></p>');
    }
    else {
      en('<h3 class="onpub-field-header">Name</h3><p><input type="text" maxlength="255" size="' . 30 . '" name="name" value="' . htmlentities($this->osection->name) . '"></p>');
    }

    if ($this->osection->parentID) {
      $sectionIDs = array ();
      $sectionIDs[] = $this->osection->parentID;
    }
    else {
      $sectionIDs = NULL;
    }

    $widget = new OnpubWidgetSections();
    $widget->sectionIDs = $sectionIDs;
    $widget->websites = $websites;
    $widget->osections = $osections;
    $widget->heading = "Parent Section";
    $widget->multiple = FALSE;
    $widget->fieldName = "sectionID";
    $widget->parentID = $this->osection->parentID;
    $widget->display();

    $widget = new OnpubWidgetWebsites($this->osection->websiteID, $websites, "section");
    $widget->display();

    if (sizeof($websites)) {
      if ($this->visible) {
        en('<h3 class="onpub-field-header">Visibility</h3>');
        en('<p><input type="checkbox" id="id_visible" name="visible" value="1" checked="checked"> <label for="id_visible">De-select to hide this section from the frontend navigation menu</label></p>');
      }
      else {
        en('<h3 class="onpub-field-header">Visibility</h3>');
        en('<p><input type="checkbox" id="id_visible" name="visible" value="1"> <label for="id_visible">Select to show this section on the frontend navigation menu</label></p>');
      }
    }

    en('<input type="submit" value="Save">');

    en('<input type="hidden" name="onpub" value="NewSectionProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    $osections = new OnpubSections($this->pdo);
    $valid = TRUE;

    if (!$this->osection->name) {
      $this->osection->name = NULL;
      $valid = FALSE;
    }

    if (!$this->osection->websiteID) {
      $this->osection->websiteID = "";
      $valid = FALSE;
    }
    else {
      if ($this->osection->parentID) {
        try {
          $parent = $osections->get($this->osection->parentID);
        }
        catch (PDOException $e) {
          throw $e;
        }

        if ($parent->websiteID != $this->osection->websiteID) {
          $this->osection->parentID = "";
          $valid = FALSE;
        }
      }
    }

    return $valid;
  }

  public function process()
  {
    $osections = new OnpubSections($this->pdo);

    try {
      $osections->insert($this->osection, $this->visible);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
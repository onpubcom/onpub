<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetSections
{
  public $sectionIDs;
  public $websites;
  public $osections;
  public $samaps;
  public $heading;
  public $multiple;
  public $fieldName;
  public $owebsite;
  public $parentID;
  public $size;
  public $ID;
  public $tooltip;

  function __construct()
  {
    $this->sectionIDs = array ();
    $this->websites = array ();
    $this->osections = NULL;
    $this->samaps = array ();
    $this->heading = "Sections";
    $this->multiple = TRUE;
    $this->fieldName = "sectionIDs[]";
    $this->owebsite = NULL;
    $this->parentID = NULL;
    $this->size = "10";
    $this->osection = NULL;
    $this->parentIDs = array ();
    $this->sectionsMap = array ();
    $this->websitesMap = array ();
    $this->ID = "sections";
    $this->tooltip = "Hold Ctrl to select/deselect multiple Sections";
  }

  function display()
  {
    $areSections = FALSE;

    if (sizeof($this->websites)) {
      foreach ($this->websites as $w) {
        $ID = $w->ID;
        $this->websitesMap["$ID"] = $w;
      }

      if (!$this->owebsite) {
        $queryOptions = new OnpubQueryOptions();
        $queryOptions->orderBy = "name";
        $queryOptions->order = "ASC";

        for ($i = 0; $i < sizeof($this->websites); $i++) {
          try {
            $sections = $this->osections->select($queryOptions, $this->websites[$i]->ID, FALSE);
          }
          catch (PDOException $e) {
            throw $e;
          }

          $websiteName = $this->websites[$i]->name;
          $this->sectionsMap["$websiteName"] = $sections;

          if (sizeof($sections)) {
            $areSections = TRUE;
          }
        }
      }
    }

    if ($areSections) {
      en('<h3 class="onpub-field-header">' . $this->heading . '</h3>');
      en('<p>');

      if ($this->multiple) {
        en('<small>' . $this->tooltip . '</small><br>');
      }

      if ($this->multiple) {
        en('<select name="' . $this->fieldName . '" multiple="multiple" size="' . $this->size . '" id="' . $this->ID . '">');
      }
      else {
        en('<select name="' . $this->fieldName . '" size="1" id="' . $this->ID . '">');
        en('<option value="">None</option>');
      }

      if (sizeof($this->samaps)) {
        $this->sectionIDs = array ();

        for ($i = 0; $i < sizeof($this->samaps); $i++) {
          $this->sectionIDs[$i] = $this->samaps[$i]->sectionID;
        }

        for ($i = 0; $i < sizeof($this->websites); $i++) {
          $websiteName = $this->websites[$i]->name;
          $sections = $this->sectionsMap["$websiteName"];

          for ($j = 0; $j < sizeof($sections); $j++) {
            $this->outputSections($this->websites[$i]->name, $sections[$j]);
          }
        }
      }
      else {
        for ($i = 0; $i < sizeof($this->websites); $i++) {
          $websiteName = $this->websites[$i]->name;
          $sections = $this->sectionsMap["$websiteName"];

          for ($j = 0; $j < sizeof($sections); $j++) {
            $this->outputSections($this->websites[$i]->name, $sections[$j]);
          }
        }
      }

      if ($this->parentID === "") {
        en('</select> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'folder_error.png" align="top" width="16" height="16" alt="Parent Section must belong to the same Website" title="Parent Section must belong to the same Website">');
      }
      else {
        en('</select>');
      }

      en('</p>');
    }
    else {
      if ($this->owebsite) {
        en('<h3 class="onpub-field-header">' . $this->heading . '</h3>');
        en('<p>');

        en('<small>Hold Ctrl to select/deselect multiple Sections</small><br>');
        en('<select name="' . $this->fieldName . '" multiple="multiple" size="' . $this->size . '" id="' . $this->ID . '">');

        $sections = $this->owebsite->sections;

        if (sizeof($sections)) {
          for ($i = 0; $i < sizeof($sections); $i++) {
            if ($sections[$i]->websiteID == $this->owebsite->ID) {
              $this->outputSections($this->owebsite->name, $sections[$i]);
            }
            else {
              $ID = $sections[$i]->websiteID;
              $website = $this->websitesMap["$ID"];
              $this->outputSections($website->name, $sections[$i]);
            }
          }
        }
        else {
          en('<option value="" selected="selected">None</option>');
        }

        en('</select>');
        en('</p>');
      }
    }
  }

  private function outputSections($websiteName, $section)
  {
    $subSections = $section->sections;
    $parentID = NULL;

    if ($this->osection) {
      $parentID = $this->osection->ID;
    }

    if (!$section->parentID) {
      if ($section->ID != $parentID) {
        if (sizeof($this->sectionIDs) && in_array($section->ID, $this->sectionIDs)) {
          en('<option value="' . $section->ID . '" selected="selected">'
            . $websiteName . ' &ndash; ' . strip_tags($section->name) . '</option>');
        }
        else {
          en('<option value="' . $section->ID . '">' . $websiteName . ' &ndash; ' . strip_tags($section->name) . '</option>');
        }
      }
    }

    for ($i = 0; $i < sizeof($subSections); $i++) {
      $subSection = $subSections[$i];
      $sectionName = $websiteName;
      $sectionNames = array ();

      $sectionNames[] = $subSection->name;

      if ($subSection->parentID) {
        $parent = $subSection->parent;
        $sectionNames[] = $parent->name;

        if (!in_array($parent->ID, $this->parentIDs)) {
          $this->parentIDs[] = $parent->ID;
        }

        while ($parent->parentID) {
          $parent = $parent->parent;
          $sectionNames[] = $parent->name;

          if (!in_array($parent->ID, $this->parentIDs)) {
            $this->parentIDs[] = $parent->ID;
          }
        }
      }

      $sectionNames = array_reverse($sectionNames);

      foreach ($sectionNames as $sN) {
        $sectionName .= ' &ndash; ' . $sN;
      }

      if ($subSection->ID != $parentID && !in_array($parentID, $this->parentIDs)) {
        if (sizeof($this->sectionIDs) && in_array($subSection->ID, $this->sectionIDs)) {
          en('<option value="' . $subSection->ID . '" selected="selected">'
            . strip_tags($sectionName) . '</option>');
        }
        else {
          en('<option value="' . $subSection->ID . '">'
            . strip_tags($sectionName) . '</option>');
        }
      }

      $this->outputSections($websiteName, $subSection);
    }

    $this->parentIDs = array ();
  }
}
?>
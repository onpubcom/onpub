<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditWebsite
{
  private $pdo;
  private $owebsite;
  private $sectionIDs;

  function __construct(PDO $pdo, $owebsite, $sectionIDs = NULL)
  {
    $this->pdo = $pdo;
    $this->owebsite = $owebsite;
    $this->sectionIDs = $sectionIDs;
  }

  public function display()
  {
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $oimages = new OnpubImages($this->pdo);
    $queryOptions = new OnpubQueryOptions();
    $queryOptions->includeSections = TRUE;

    try {
      $totalSections = $osections->count();
      $this->owebsite = $owebsites->get($this->owebsite->ID, $queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);

      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "fileName";
      $queryOptions->order = "ASC";
      $images = $oimages->select($queryOptions);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Website " . $this->owebsite->ID . " - " . $this->owebsite->name, ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post" enctype="multipart/form-data">');
    en('<div>');

    en('<div class="yui3-g">');
    en('<div class="yui3-u-1-2">');
    en('<h3 class="onpub-field-header">Name</h3><p><small>This website\'s name.</small><br><input type="text" maxlength="255" size="40" name="name" value="' . htmlentities($this->owebsite->name) . '"></p>');
    en('</div>');
    en('<div class="yui3-u-1-2">');

    $message = "";

    if ($this->owebsite->url) {
      $go = ' <a href="' . $this->owebsite->url . '" target="_blank"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'world_go.png" border="0" align="top" alt="Go" title="Go" width="16" height="16"></a>';
      $message = '<small>Frontend URL for this website.</small>';
    }
    else {
      $go = '';
      $message = '<small>Frontend URL for this website. Example: http://onpub.com/tryonpub.</small>';
    }

    en('<h3 class="onpub-field-header">Frontend URL</h3><p>' . $message . '<br><input type="text" maxlength="255" size="40" name="url" value="' . htmlentities($this->owebsite->url) . '">' . $go . '</p>');
    en('</div>');
    en('</div>');

    $message = "";

    if (file_exists($this->owebsite->imagesDirectory)) {
      if (!is_writable($this->owebsite->imagesDirectory)) {
        $message = '<img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'drive_error.png" align="top" width="16" height="16" alt="Directory is not writable" title="Directory is not writable">';
      }
    }
    else {
      if ($this->owebsite->imagesDirectory) {
        $message = '<img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'drive_error.png" align="top" width="16" height="16" alt="Directory does not exist on ' . $_SERVER['SERVER_NAME'] . '" title="Directory does not exist on ' . $_SERVER['SERVER_NAME'] . '">';
      }
    }

    en('<div class="yui3-g">');
    en('<div class="yui3-u-1-2">');
    en('<h3 class="onpub-field-header">Image Uploads Directory</h3><p><small>Images uploaded to this website will be saved to this directory on <i>' . $_SERVER['SERVER_NAME'] . '.</i></small><br><input type="text" maxlength="255" size="40" name="imagesDirectory" value="' . htmlentities($this->owebsite->imagesDirectory) . '"> ' . $message . '</p>');
    en('</div>');
    en('<div class="yui3-u-1-2">');

    $message = "";

    if ($this->owebsite->imagesURL) {
      $message = '<small>This URL should map to the Image Uploads Directory.</small>';
    }
    else {
      $message = '<small>This URL should map to the Image Uploads Directory, example: http://onpub.com/images/.</small>';
    }

    if ($this->owebsite->imagesURL) {
      $go = ' <a href="' . $this->owebsite->imagesURL . '" target="_blank"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'world_go.png" border="0" align="top" alt="Go" title="Go" width="16" height="16"></a>';
    }
    else {
      $go = '';
    }

    en('<h3 class="onpub-field-header">Image Uploads URL</h3><p>' . $message . '<br><input type="text" maxlength="255" size="40" name="imagesURL" value="' . htmlentities($this->owebsite->imagesURL) . '">' . $go . '</p>');
    en('</div>');
    en('</div>');

    if ($totalSections) {
      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      $widget = new OnpubWidgetSections();
      $widget->websites = $websites;
      $widget->osections = $osections;
      $widget->heading = "All Sections";
      $widget->fieldName = "existingSections";
      $widget->ID = "existing";
      $widget->display();
      en('<p><input type="button" value="Show &raquo;" id="add"></p>');
      en('</div>');
      en('<div class="yui3-u-1-2">');
      $widget = new OnpubWidgetSections();
      $widget->sectionIDs = $this->sectionIDs;
      $widget->websites = $websites;
      $widget->osections = $osections;
      $widget->heading = "Visible Sections";
      $widget->owebsite = $this->owebsite;
      $widget->tooltip = 'These sections will be displayed by the Frontend in the same order as listed below.';
      $widget->display();
      en('<p><input type="button" value="Move Up" id="moveUp"> <input type="button" value="Move Down" id="moveDown"> <input type="button" value="Hide" id="hide"></p>');
      en('</div>');
      en('</div>');
    }

    $widget = new OnpubWidgetImages("Logo Image", $this->owebsite->imageID, $images);
    $widget->display();

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');
    en('<h3 class="onpub-field-header">Created</h3><p>' . $this->owebsite->getCreated()->format('M j, Y g:i:s A') . '</p>');
    en('</div>');

    en('<div class="yui3-u-1-2">');
    en('<h3 class="onpub-field-header">Modified</h3><p>' . $this->owebsite->getModified()->format('M j, Y g:i:s A') . '</p>');
    en('</div>');

    en('</div>');

    en('<input type="submit" value="Save" id="selectAll"> <input type="button" value="Delete" id="deleteWebsite">');

    en('<input type="hidden" name="onpub" value="EditWebsiteProcess">');
    en('<input type="hidden" name="websiteID" value="' . $this->owebsite->ID . '">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    if (!$this->owebsite->name) {
      $this->owebsite->name = NULL;
      return FALSE;
    }

    if ($this->owebsite->url) {
      $url = $this->validateURL($this->owebsite->url);

      if ($url) {
        $this->owebsite->url = $url;
      }
      else {
        return FALSE;
      }
    }

    if ($this->owebsite->imagesURL) {
      $url = $this->validateURL($this->owebsite->imagesURL);

      if ($url) {
        $this->owebsite->imagesURL = $url;
      }
      else {
        return FALSE;
      }
    }

    return TRUE;
  }

  private function validateURL($url)
  {
    $parsed_url = parse_url($url);
    $validated_url = FALSE;

    if ($parsed_url === FALSE) {
      return $validated_url;
    }

    if (!isset($parsed_url['scheme']) || !isset($parsed_url['host'])) {
      return $validated_url;
    }

    if (isset($parsed_url['path'])) {
      $path = pathinfo($parsed_url['path']);

      if (isset($path['extension'])) {
        $dirname = $path['dirname'];
      }
      else {
        $dirname = addTrailingSlash($path['dirname']) . $path['basename'];
      }
    }
    else {
      $dirname = '';
    }

    if (isset($parsed_url['user']) && isset($parsed_url['pass'])) {
      $validated_url = $parsed_url['scheme'] . '://' . $parsed_url['user'] . ':' . $parsed_url['pass'] . '@' . $parsed_url['host'] . $dirname;
    }
    else {
      $validated_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $dirname;
    }

    if (hasTrailingSlash($url)) {
      return addTrailingSlash($validated_url);
    }

    return $validated_url;
  }

  public function process()
  {
    $owebsites = new OnpubWebsites($this->pdo);

    $sections = array();

    for ($i = 0; $i < sizeof($this->sectionIDs); $i++) {
      if ($this->sectionIDs[$i]) {
        $section = new OnpubSection();

        $section->ID = $this->sectionIDs[$i];
        $section->websiteID = $this->owebsite->ID;

        $sections[] = $section;
      }
    }

    $this->owebsite->sections = $sections;

    try {
      $owebsites->update($this->owebsite);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }

  public function delete()
  {
    $owebsites = new OnpubWebsites($this->pdo);

    try {
      $owebsites->delete($this->owebsite->ID);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditImage
{
  private $pdo;
  private $oimage;
  private $oldImageFileName;

  function __construct(PDO $pdo, OnpubImage $oimage, $oldImageFileName = NULL)
  {
    $this->pdo = $pdo;
    $this->oimage = $oimage;
    $this->oldImageFileName = $oldImageFileName;
  }

  public function display()
  {
    $oimages = new OnpubImages($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $imageFileSize = NULL;
    $imageWidth = NULL;
    $imageHeight = NULL;
    $imageDimensions = NULL;
    $imageFileInfo = NULL;
    $fileExists = FALSE;

    try {
      $this->oimage = $oimages->get($this->oimage->ID);
      $website = $owebsites->get($this->oimage->websiteID);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $fileExists = file_exists(addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName);

    if ($fileExists) {
      if (function_exists("getimagesize")) {
        $imageDimensions = getimagesize(addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName);
      }

      $imageFileInfo = stat(addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName);
    }

    if ($imageFileInfo) {
      $imageFileSize = $imageFileInfo[7];
    }

    if ($imageDimensions) {
      $imageWidth = $imageDimensions[0];
      $imageHeight = $imageDimensions[1];
    }

    $widget = new OnpubWidgetHeader("Image " . $this->oimage->ID . " - " . $this->oimage->fileName);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post">');
    en('<div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-3">');
    en('<strong>File Name</strong><br><input type="text" maxlength="255" size="' . 30 . '" name="fileName" value="' . htmlentities($this->oimage->fileName) . '">', 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-3">');
    en('<strong>Description</strong><br><input type="text" maxlength="255" size="' . 30 . '" name="description" value="'
      . htmlentities($this->oimage->description) . '">', 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-3">');
    en('<strong>Website</strong><br><a href="index.php?onpub=EditWebsite&amp;websiteID='
      . $website->ID . '" title="Edit">' . $website->name . '</a>', 1, 2);
    en('</div>');

    en('</div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-3">');
    en('<strong>URL</strong><br><a href="' . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '" target="_blank">' . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '</a>', 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-3">');

    if ($fileExists) {
      en('<strong>File Path</strong><br>' . addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName . ' (on <i>' . $_SERVER['SERVER_NAME'] . '</i>)', 1, 2);
    }
    en('</div>');

    en('<div class="yui3-u-1-3">');
    en('</div>');

    en('</div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-3">');
    en('<strong>Created</strong><br>'
      . $this->oimage->getCreated()->format('M j, Y g:i:s A'), 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-3">');
    en('<strong>Modified</strong><br>'
      . $this->oimage->getModified()->format('M j, Y g:i:s A'), 1, 2);
    en('</div>');

    en('<div class="yui3-u-1-3">');
    en('</div>');

    en('</div>');

    if ($fileExists) {
      en('<div class="yui3-g">');

      en('<div class="yui3-u-1-3">');
      en('<strong>Width</strong><br>');
      en($imageWidth . 'px', 1, 2);
      en('</div>');

      en('<div class="yui3-u-1-3">');
      en('<strong>Height</strong><br>');
      en($imageHeight . 'px', 1, 2);
      en('</div>');

      en('<div class="yui3-u-1-3">');
      en('<strong>File Size</strong><br>');

      if ($imageFileSize >= 1024) {
        en(round(($imageFileSize / 1024)) . ' KB (' . $imageFileSize . ' bytes)', 1, 2);
      }
      else {
        en($imageFileSize . ' bytes', 1, 2);
      }
      en('</div>');

      en('</div>');
    }

    en('<input type="submit" value="Save"> <input type="button" value="Delete" id="deleteImage">', 1, 2);

    if (@fopen(addTrailingSlash($website->imagesURL) . rawurlencode($this->oimage->fileName), 'r')) {
      if ($imageWidth && $imageHeight) {
        en('<a href="' . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '" target="_blank"><img src="'
          . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '" width="' . $imageWidth . '" height="' . $imageHeight . '" alt="'
          . $this->oimage->fileName . '" title="' . $this->oimage->fileName . '" border="0"></a>', 1, 2);
      }
      else {
        en('<a href="' . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '" target="_blank"><img src="'
          . addTrailingSlash($website->imagesURL) . $this->oimage->fileName . '" alt="' . $this->oimage->fileName . '" title="'
          . $this->oimage->fileName . '" border="0"></a>', 1, 2);
      }
    }
    else {
      en('<img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'picture_error.png" align="top" width="16" height="16" alt="Image not found" title="Image not found"><br><span class="onpub-error">Make sure the Image Uploads URL of <a href="index.php?onpub=EditWebsite&amp;websiteID='
        . $website->ID . '">' . $website->name . '</a> is setup correctly.</span>');
    }

    en('<input type="hidden" name="onpub" value="EditImageProcess">');
    en('<input type="hidden" name="imageID" value="' . $this->oimage->ID . '">');
    en('<input type="hidden" name="oldImageFileName" value="' . htmlentities($this->oimage->fileName) . '">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    return TRUE;
  }

  public function process()
  {
    $oimages = new OnpubImages($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);

    $image = $oimages->get($this->oimage->ID);
    $website = $owebsites->get($image->websiteID);

    if ($this->oimage->fileName != $this->oldImageFileName) {
      if (file_exists(addTrailingSlash($website->imagesDirectory) . $this->oldImageFileName)) {
        rename(addTrailingSlash($website->imagesDirectory) . $this->oldImageFileName, addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName);
      }
    }

    try {
      $oimages->update($this->oimage);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }

  public function delete()
  {
    $oimages = new OnpubImages($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $this->oimage = $oimages->get($this->oimage->ID);
    $website = $owebsites->get($this->oimage->websiteID);

    try {
      $oimages->delete($this->oimage->ID);
    }
    catch (PDOException $e) {
      throw $e;
    }

    if (file_exists(addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName)) {
      unlink(addTrailingSlash($website->imagesDirectory) . $this->oimage->fileName);
    }
  }
}
?>
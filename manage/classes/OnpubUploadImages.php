<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubUploadImages
{
  private $pdo;
  private $imageID;
  private $imageFiles;
  private $websiteID;

  function __construct(PDO $pdo, $imageFiles = array(), $websiteID = NULL)
  {
    $this->pdo = $pdo;
    $this->imageID = NULL;
    $this->imageFiles = $imageFiles;
    $this->websiteID = $websiteID;
  }

  public function display()
  {
    $owebsites = new OnpubWebsites($this->pdo);

    try {
      $queryOptions = new OnpubQueryOptions();
      $queryOptions->orderBy = "name";
      $queryOptions->order = "ASC";
      $websites = $owebsites->select($queryOptions);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Upload Images");
    $widget->display();

    en('<form action="index.php" method="post" enctype="multipart/form-data">');
    en('<div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');

    if ($this->imageFiles === NULL) {
      en('<b>Image File</b><br><input type="file" size="30" name="imageFiles[]"> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field"><br><br>');
    }
    else {
      en('<b>Image File</b><br><input type="file" size="30" name="imageFiles[]"><br><br>');
    }

    en('</div>');

    en('<div class="yui3-u-1-2">');

    en('<b>Image File</b><br><input type="file" size="30" name="imageFiles[]">', 1, 2);

    en('</div>');

    en('</div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');

    en('<b>Image File</b><br><input type="file" size="30" name="imageFiles[]">', 1, 2);

    en('</div>');

    en('<div class="yui3-u-1-2">');

    en('<b>Image File</b><br><input type="file" size="30" name="imageFiles[]">', 1, 2);

    en('</div>');

    en('</div>');

    $widget = new OnpubWidgetWebsites($this->websiteID, $websites, "image");
    $widget->display();

    br (2);

    en('<input type="submit" value="Upload">');

    en('<input type="hidden" name="onpub" value="UploadImagesProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function displayException(Exception $e)
  {
    $owebsites = new OnpubWebsites($this->pdo);

    try {
      $website = $owebsites->get($this->websiteID);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $widget = new OnpubWidgetHeader("Upload Images");
    $widget->display();

    switch ($e->getCode())
    {
      case ONPUBGUI_ERROR_MOVE_UPLOADED_FILE:
        en('<span class="onpub-error">' . $e->getMessage() . '</span>', 1, 2);
        en('Make sure the Images Directory of <a href="index.php?onpub=EditWebsite&amp;websiteID=' . $this->websiteID . '">' . $website->name . '</a> is a valid path and is writable by the web server account.');
        break;

      default:
        en('<span class="onpub-error">' . $e->getMessage() . '</span>');
        break;
    }

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    $valid = FALSE;

    if (is_array($this->imageFiles)) {
      foreach ($this->imageFiles['name'] as $fileName) {
        if ($fileName) {
          $valid = TRUE;
          break;
        }
      }
    }

    if (!$valid) {
      $this->imageFiles = NULL;
    }

    if (!$this->websiteID) {
      $this->websiteID = "";
      $valid = FALSE;
    }

    return $valid;
  }

  public function process()
  {
    if (!ini_get("file_uploads")) {
      $message = "File uploads are disabled in the current PHP configuration.";

      throw new Exception($message, ONPUBGUI_ERROR_IMAGE_TYPE);
    }

    $oimages = new OnpubImages($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);

    for ($i = 0; $i < sizeof($this->imageFiles['name']); $i++) {
      if ($this->imageFiles['name'][$i]) {
        if (!$this->isValidImage($this->imageFiles['name'][$i])) {
          $message = "<i>" . $this->imageFiles['name'][$i] . "</i> is an unsupported image file type.";

          throw new Exception($message, ONPUBGUI_ERROR_IMAGE_TYPE);
        }

        $image = new OnpubImage();
        $image->websiteID = $this->websiteID;
        $image->fileName = $this->imageFiles['name'][$i];

        try {
          $this->imageID = $oimages->getID($image);
        }
        catch (PDOException $e) {
          throw $e;
        }

        if (!$this->imageID) {
          try {
            $website = $owebsites->get($image->websiteID);
          }
          catch (PDOException $e) {
            throw $e;
          }

          if (is_uploaded_file($this->imageFiles['tmp_name'][$i])) {
            if (!@move_uploaded_file($this->imageFiles['tmp_name'][$i], addTrailingSlash($website->imagesDirectory) . $this->imageFiles['name'][$i])) {
              $imagesDirectory = $website->imagesDirectory;
              $message = "Unable to move <i>" . $this->imageFiles['tmp_name'][$i] . "</i> to <i>" . addTrailingSlash($imagesDirectory) . $this->imageFiles['name'][$i] . "</i>.";

              throw new Exception($message, ONPUBGUI_ERROR_MOVE_UPLOADED_FILE);
            }

            try {
              $this->imageID = $oimages->insert($image);
            }
            catch (PDOException $e) {
              throw $e;
            }
          }
          else {
            $imagesDirectory = $website->imagesDirectory;
            $message = "<i>" . $this->imageFiles['name'][$i] . "</i> file size is larger than the current PHP configuration allows.";

            throw new Exception($message, ONPUBGUI_ERROR_FILE_SIZE);
          }
        }
      }
    }
  }

  public function getImageID()
  {
    return $this->imageID;
  }

  public function isValidImage($fileName)
  {
    $ext = NULL;
    $validTypes = array
    (
      'gif',
      'jpg',
      'jpeg',
      'png',
      'swf',
      'psd',
      'bmp',
      'tiff',
      'jpc',
      'jp2',
      'jpf',
      'jb2',
      'swc',
      'aiff',
      'wbmp',
      'xbm',
      'ico'
    );

    $pathinfo = pathinfo($fileName);

    if (isset($pathinfo['extension'])) {
      $ext = strtolower($pathinfo['extension']);
    }

    if (in_array($ext, $validTypes)) {
      return $ext;
    }

    return FALSE;
  }
}
?>
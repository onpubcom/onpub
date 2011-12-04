<?php

/**
 * An image.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubImage
{
  /**
   * Image ID. Default is NULL.
   *
   * @var int
   */
  public $ID;
  /**
   * Website ID. Default is NULL.
   *
   * @var int
   */
  public $websiteID;
  /**
   * Image file name. Default is an empty string.
   *
   * @var string
   */
  public $fileName;
  /**
   * Image description. Default is an empty string.
   *
   * @var string
   */
  public $description;
  /**
   * Image URL. Default is an empty string.
   *
   * @var string
   */
  public $url;
  private $created;
  private $modified;
  /**
   * Image website. Default is NULL.
   *
   * @var OnpubWebsite
   */
  public $website;

  function __construct()
  {
    $this->ID = NULL;
    $this->websiteID = NULL;
    $this->fileName = "";
    $this->description = "";
    $this->url = "";
    $this->created = NULL;
    $this->modified = NULL;
    $this->website = NULL;
  }

  /**
   * Get this image's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubImage::setCreated()} to explicitly set
   * this image's creation date.
   *
   * @return DateTime
   */
  public function getCreated()
  {
    if ($this->created === NULL) {
      $this->created = new DateTime();
    }

    return $this->created;
  }

  /**
   * @param DateTime
   */
  public function setCreated(DateTime $created)
  {
    $this->created = $created;
  }

  /**
   * Get this image's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubImage::setModified()} to explicitly set
   * this image's last modification date.
   *
   * @return DateTime
   */
  public function getModified()
  {
    if ($this->modified === NULL) {
      $this->modified = new DateTime();
    }

    return $this->modified;
  }

  /**
   * @param DateTime
   */
  public function setModified(DateTime $modified)
  {
    $this->modified = $modified;
  }

  public function getSourceURL()
  {
    return addTrailingSlash($this->website->imagesURL) . $this->fileName;
  }

  public function getSourcePath()
  {
    return addTrailingSlash($this->website->imagesDirectory) . $this->fileName;
  }

  public function getThumbURL($parameters)
  {
    global $PHPTHUMB_CONFIG;

    if ($PHPTHUMB_CONFIG['high_security_enabled']) {
      return phpThumbURL($parameters);
    }

    return str_replace(@$PHPTHUMB_CONFIG['document_root'], '', dirname(__FILE__)).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'phpThumb'.DIRECTORY_SEPARATOR.'phpThumb.php?'.$parameters;
  }
}
?>
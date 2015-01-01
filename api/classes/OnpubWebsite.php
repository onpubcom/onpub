<?php

/**
 * A website.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubWebsite
{
  /**
   * Website ID. Default is NULL.
   *
   * @var int
   */
  public $ID;
  /**
   * Image ID. Default is NULL.
   *
   * @var int
   */
  public $imageID;
  /**
   * Website name. Default is an empty string.
   *
   * @var string
   */
  public $name;
  /**
   * Website URL. Default is an empty string.
   *
   * @var string
   */
  public $url;
  /**
   * Images URL. Default is an empty string.
   *
   * @var string
   */
  public $imagesURL;
  /**
   * Images directory. Default is an empty string.
   *
   * @var string
   */
  public $imagesDirectory;
  private $created;
  private $modified;
  /**
   * Website sections. Default is an empty array.
   *
   * @var array
   */
  public $sections;
  /**
   * Website articles. Default is an empty array.
   *
   * @var array
   */
  public $articles;
  /**
   * Website image. Default is NULL.
   *
   * @var OnpubImage
   */
  public $image;

  function __construct()
  {
    $this->ID = NULL;
    $this->imageID = NULL;
    $this->name = "";
    $this->url = "";
    $this->imagesURL = "";
    $this->imagesDirectory = "";
    $this->created = NULL;
    $this->modified = NULL;
    $this->sections = array();
    $this->articles = array();
    $this->image = NULL;
  }

  /**
   * Get this website's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubWebsite::setCreated()} to explicitly set
   * this website's creation date.
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
   * Get this website's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubWebsite::setModified()} to explicitly set
   * this website's last modification date.
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
}
?>
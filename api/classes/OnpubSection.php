<?php

/**
 * A section.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubSection
{
  /**
   * Section ID. Default is NULL.
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
   * Website ID. Default is NULL.
   *
   * @var int
   */
  public $websiteID;
  /**
   * Parent section ID. Default is NULL.
   *
   * @var int
   */
  public $parentID;
  /**
   * Section name. Default is an empty string.
   *
   * @var string
   */
  public $name;
  /**
   * Section URL. Default is an empty string.
   *
   * @var string
   */
  public $url;
  private $created;
  private $modified;
  /**
   * Section articles. Default is an empty array.
   *
   * @var array
   */
  public $articles;
  /**
   * Section sections. Default is an empty array.
   *
   * @var array
   */
  public $sections;
  /**
   * Section image. Default is NULL.
   *
   * @var OnpubImage
   */
  public $image;
  /**
   * Parent section. Default is NULL.
   *
   * @var OnpubSection
   */
  public $parent;

  function __construct()
  {
    $this->ID = NULL;
    $this->imageID = NULL;
    $this->websiteID = NULL;
    $this->parentID = NULL;
    $this->name = "";
    $this->url = "";
    $this->created = NULL;
    $this->modified = NULL;
    $this->articles = array();
    $this->sections = array();
    $this->image = NULL;
    $this->parent = NULL;
  }

  /**
   * Get this section's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubSection::setCreated()} to explicitly set
   * this section's creation date.
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
   * Get this section's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubSection::setModified()} to explicitly set
   * this section's last modification date.
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
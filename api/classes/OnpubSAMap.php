<?php

/**
 * A section-article map.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubSAMap
{
  /**
   * Section-article map ID. Default is NULL.
   *
   * @var int
   */
  public $ID;
  /**
   * Section ID. Default is NULL.
   *
   * @var int
   */
  public $sectionID;
  /**
   * Article ID. Default is NULL.
   *
   * @var int
   */
  public $articleID;
  private $created;
  private $modified;

  function __construct()
  {
    $this->ID = NULL;
    $this->sectionID = NULL;
    $this->articleID = NULL;
    $this->created = NULL;
    $this->modified = NULL;
  }

  /**
   * Get this map's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubSAMap::setCreated()} to explicitly set
   * this map's creation date.
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
   * Get this map's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubSAMap::setModified()} to explicitly set
   * this map's last modification date.
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
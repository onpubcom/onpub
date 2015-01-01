<?php

/**
 * An author.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubAuthor
{
  /**
   * Author ID. Default is NULL.
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
   * Author's given names. Default is an empty string.
   *
   * @var string
   */
  public $givenNames;
  /**
   * Author's family name. Default is an empty string.
   *
   * @var string
   */
  public $familyName;
  /**
   * Author's display name. Default is an empty string.
   *
   * @var string
   */
  public $displayAs;
  /**
   * Author's URL. Default is an empty string.
   *
   * @var string
   */
  public $url;
  private $created;
  private $modified;
  /**
   * Author image. Default is NULL.
   *
   * @var OnpubImage
   */
  public $image;

  function __construct()
  {
    $this->ID = NULL;
    $this->imageID = NULL;
    $this->givenNames = "";
    $this->familyName = "";
    $this->displayAs = "";
    $this->url = "";
    $this->created = NULL;
    $this->modified = NULL;
    $this->image = NULL;
  }

  /**
   * Get this author's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubAuthor::setCreated()} to explicitly set
   * this author's creation date.
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
   * Get this author's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubAuthor::setModified()} to explicitly set
   * this author's last modification date.
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
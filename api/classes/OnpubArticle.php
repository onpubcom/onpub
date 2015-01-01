<?php

/**
 * An article.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubArticle
{
  /**
   * Article ID. Default is NULL.
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
   * Article title. Default is an empty string.
   *
   * @var string
   */
  public $title;
  /**
   * Article content. Default is an empty string.
   *
   * @var string
   */
  public $content;
  /**
   * Article URL. Default is an empty string.
   *
   * @var string
   */
  public $url;
  private $created;
  private $modified;
  /**
   * Article authors. Default is an empty array.
   *
   * @var array
   */
  public $authors;
  /**
   * Article image. Default is NULL.
   *
   * @var OnpubImage
   */
  public $image;
  /**
   * Article section IDs. Default is an empty array.
   *
   * @var array
   */
  public $sectionIDs;

  /**
   * Construct a new article.
   */
  function __construct()
  {
    $this->ID = NULL;
    $this->imageID = NULL;
    $this->title = "";
    $this->content = "";
    $this->url = "";
    $this->created = NULL;
    $this->modified = NULL;
    $this->authors = array();
    $this->image = NULL;
    $this->sectionIDs = array();
  }

  /**
   * Get this article's creation date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubArticle::setCreated()} to explicitly set
   * this article's creation date.
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
   * Set this article's creation date.
   *
   * @param DateTime
   */
  public function setCreated(DateTime $created)
  {
    $this->created = $created;
  }

  /**
   * Get this article's last modification date.
   *
   * By default, returns the date at the time this method was called for the
   * first time. Call {@link OnpubArticle::setModified()} to explicitly set
   * this article's last modification date.
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
   * Set this article's last modification date.
   *
   * @param DateTime
   */
  public function setModified(DateTime $modified)
  {
    $this->modified = $modified;
  }

  /**
   * Get a summary of this article's content.
   *
   * By default a 30 word summary is generated.
   *
   * @param int $limit The maximum number of words to include in the summary.
   * @return string
   */
  public function getSummary($limit = 30)
  {
    // Replace all new line charcaters and non-breaking spaces.
    $content = preg_replace('/[\n\r]|(&nbsp;)/i', '', $this->content);
    // Strip all HTML tags.
    $content = strip_tags($content);
    // Trim whitespace.
    $content = trim($content);
    // Split string in to words.
    $words = preg_split("/\s+/", $content, $limit + 1);

    if (sizeof($words) > $limit) {
      array_pop($words);
    }

    return implode(' ', $words);
  }
}
?>
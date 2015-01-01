<?php

/**
 * Onpub database query options.
 *
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package OnpubAPI
 */
class OnpubQueryOptions
{
  /**
   * Default is FALSE. Set to TRUE if you would like article
   * data to be included in the database query results (where applicable).
   *
   * @var bool
   */
  public $includeArticles;
  /**
   * Default is FALSE.
   *
   * @var bool
   */
  public $includeAuthors;
  /**
   * Default is TRUE.
   *
   * @var bool
   */
  public $includeContent;
  /**
   * Default is FALSE.
   *
   * @var bool
   */
  public $includeSections;
  /**
   * Default is NULL.
   *
   * @var string
   */
  public $orderBy;
  /**
   * Default is NULL.
   *
   * @var string
   */
  public $order;
  /**
   * Default is NULL.
   *
   * @var int
   */
  public $rowLimit;
  /**
   * Default is NULL.
   *
   * @var DateTime
   */
  public $dateAfter;
  /**
   * Default is NULL.
   *
   * @var DateTime
   */
  public $dateLimit;
  /**
   * Default is FALSE.
   *
   * @var bool
   */
  public $fullTextSearch;

  private $page;

  function __construct()
  {
    $this->includeArticles = FALSE;
    $this->includeAuthors = FALSE;
    $this->includeContent = TRUE;
    $this->includeSections = FALSE;
    $this->orderBy = NULL;
    $this->order = NULL;
    $this->page = NULL;
    $this->rowLimit = NULL;
    $this->dateAfter = NULL;
    $this->dateLimit = NULL;
    $this->fullTextSearch = FALSE;
  }

  /**
   * @return int
   */
  public function getPage()
  {
    return $this->page;
  }

  /**
   * Paginate a set of rows.
   *
   * @param int
   * @param int
   */
  public function setPage($page, $rowLimit)
  {
    $this->page = $page;
    $this->rowLimit = $rowLimit;
  }
}
?>
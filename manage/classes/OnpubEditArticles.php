<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditArticles
{
  private $pdo;
  private $orderBy;
  private $order;
  private $page;
  private $keywords;
  private $fullTextSearch;
  private $sectionID;

  function __construct(PDO $pdo, $orderBy = NULL, $order = NULL, $page = NULL, $keywords = NULL, $fullTextSearch = NULL, $sectionID = NULL)
  {
    $this->pdo = $pdo;
    $this->orderBy = $orderBy;
    $this->order = $order;
    $this->page = $page;
    $this->keywords = $keywords;
    $this->fullTextSearch = $fullTextSearch;
    $this->sectionID = $sectionID;
  }

  public function display()
  {
    $oarticles = new OnpubArticles($this->pdo);
    $counter = 0;
    $currentPage = 1;

    if ($this->page) {
      $currentPage = $this->page;
    }

    if ($this->orderBy && $this->order) {
      if ($this->keywords) {
        try {
          $queryOptions = new OnpubQueryOptions();
          $queryOptions->includeContent = FALSE;
          $queryOptions->fullTextSearch = $this->fullTextSearch;
          $queryOptions->orderBy = $this->orderBy;
          $queryOptions->order = $this->order;

          $articles = $oarticles->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalArticles = sizeof($articles);
      }
      else {
        if ($this->sectionID) {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->includeContent = FALSE;
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $articles = $oarticles->select($queryOptions, $this->sectionID);
            $totalArticles = $oarticles->count($this->sectionID);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->includeContent = FALSE;
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $articles = $oarticles->select($queryOptions);
            $totalArticles = $oarticles->count();
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
      }
    }
    else {
      if ($this->keywords) {
        try {
          $queryOptions = new OnpubQueryOptions();
          $queryOptions->includeContent = FALSE;
          $queryOptions->fullTextSearch = $this->fullTextSearch;
          $queryOptions->orderBy = "created";
          $queryOptions->order = "DESC";

          $articles = $oarticles->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalArticles = sizeof($articles);
      }
      else {
        if ($this->sectionID) {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->includeContent = FALSE;
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $articles = $oarticles->select($queryOptions, $this->sectionID);
            $totalArticles = $oarticles->count($this->sectionID);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->includeContent = FALSE;
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $articles = $oarticles->select($queryOptions);
            $totalArticles = $oarticles->count();
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
      }
    }

    $widget = new OnpubWidgetHeader("Articles");
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="get">');
    en('<div>');
    en('<input type="hidden" name="onpub" value="EditArticles">');

    if ($totalArticles) {
      if (!$this->keywords) {
        $selector = new OnpubWidgetSelectSection($this->pdo, $this->sectionID);
        $selector->display();
      }

      $widget = new OnpubWidgetPaginator($totalArticles, $this->orderBy, $this->order, $this->page, $this->keywords, $this->fullTextSearch, "sectionID", $this->sectionID, "EditArticles");
      $widget->display();

      en('<table>');

      en('<tr>');
      en('<td></td>');

      if ($this->keywords) {
        $this->keywords = urlencode($this->keywords);

        if ($this->fullTextSearch) {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "title":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=DESC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;fullTextSearch=' . $this
                                      ->
                                        fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;
              }
              break;
          }
        }
        else {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "title":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=DESC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;
              }
              break;
          }
        }

        $this->keywords = urldecode($this->keywords);
      }
      else {
        if ($this->sectionID) {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=ASC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;
              }
              break;

            case "title":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC&sectionID=' . $this
                                  ->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;
              }
              break;
          }
        }
        else {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "title":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=DESC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=title&amp;order=ASC">Title</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditArticles&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;
              }
              break;
          }
        }
      }

      en('</tr>');

      if ($this->keywords) {
        $index = (($currentPage - 1) * ONPUBGUI_PDO_ROW_LIMIT);
      }
      else {
        $index = 0;
      }

      for ($i = 0; $i < ONPUBGUI_PDO_ROW_LIMIT && $index < sizeof($articles); $i++) {
        $articleID = $articles[$index]->ID;
        $title = $articles[$index]->title;
        $created = $articles[$index]->getCreated()->format("M j, Y");

        en('<tr valign="top">');
        en('<td align="right"><input type="checkbox" id="articleIDs" value="' . $articleID . '"></td>');

        switch ($this->order)
        {
          case "ASC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          case "DESC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          default:
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $articleID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditArticle&amp;articleID='
                  . $articleID . '" title="Edit">' . $title . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;
        }

        en('</tr>');

        if ($counter + 1 == 2) {
          $counter = 0;
        }
        else {
          $counter++;
        }

        $index++;
      }

      en('</table>');
    }
    else {
      if ($this->keywords) {
        en('<p>Your search did not yield any results. <a href="index.php?onpub=EditArticles">Display all articles</a>.</p>');
      }
      else {
        if ($this->sectionID) {
          $selector = new OnpubWidgetSelectSection($this->pdo, $this->sectionID);
          $selector->display();

          en('<p>There are 0 articles in the selected section. <a href="index.php?onpub=EditArticles&amp;sectionID=">Display all articles</a>.</p>');
        }
        else {
          en('<p>There are 0 articles in the database. <a href="index.php?onpub=NewArticle">New Article</a>.</p>');
        }
      }
    }

    if ($totalArticles) {
      en('<p>');
      en('<select id="actions">');
      en('<option value="EditArticles">Select an action..</option>');
      en('<option value="DeleteArticle">Delete selected articles</option>');
      en('<option value="ArticleMove">Move selected articles</option>');
      en('</select>');
      en('</p>');

      $widget = new OnpubWidgetStats($totalArticles, $this->keywords, $this->sectionID, "Article", "Section");
      $widget->display();
    }

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }
}
?>
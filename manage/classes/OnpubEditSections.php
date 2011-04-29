<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditSections
{
  private $pdo;
  private $orderBy;
  private $order;
  private $page;
  private $keywords;
  private $fullTextSearch;
  private $websiteID;

  function __construct(PDO $pdo, $orderBy = NULL, $order = NULL, $page = NULL, $keywords = NULL, $fullTextSearch = NULL, $websiteID = NULL)
  {
    $this->pdo = $pdo;
    $this->orderBy = $orderBy;
    $this->order = $order;
    $this->page = $page;
    $this->keywords = $keywords;
    $this->fullTextSearch = $fullTextSearch;
    $this->websiteID = $websiteID;
  }

  public function display()
  {
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $this->fullTextSearch = "NA";
    $counter = 0;
    $currentPage = 1;

    if ($this->page) {
      $currentPage = $this->page;
    }

    if ($this->orderBy && $this->order) {
      if ($this->keywords) {
        try {
          $queryOptions = new OnpubQueryOptions();
          $queryOptions->orderBy = $this->orderBy;
          $queryOptions->order = $this->order;

          $sections = $osections->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalSections = sizeof($sections);
      }
      else {
        if ($this->websiteID) {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $sections = $osections->select($queryOptions, $this->websiteID);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          $totalSections = sizeof($sections);
        }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $sections = $osections->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalSections = $osections->count();
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
          $queryOptions->orderBy = "created";
          $queryOptions->order = "DESC";

          $sections = $osections->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalSections = sizeof($sections);
      }
      else {
        if ($this->websiteID) {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $sections = $osections->select($queryOptions, $this->websiteID);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          $totalSections = sizeof($sections);
        }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $sections = $osections->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalSections = $osections->count();
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
      }
    }

    $widget = new OnpubWidgetHeader("Sections");
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="get">');
    en('<div>');

    if ($totalSections) {
      if (!$this->keywords) {
        $widget = new OnpubWidgetSelectWebsite($this->pdo, $this->websiteID);
        $widget->display();
      }

      br(2);

      $widget = new OnpubWidgetPaginator($totalSections, $this->orderBy, $this->order, $this->page, $this->keywords, $this->fullTextSearch, "websiteID", $this->websiteID, "EditSections");
      $widget->display();

      en('<table>');
      en('<tr>');
      //en('<td></td>');

      if ($this->keywords) {
        $this->keywords = urlencode($this->keywords);

        if ($this->fullTextSearch) {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;fullTextSearch=' . $this
                                      ->
                                        fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;
              }
              break;
          }
        }

        $this->keywords = urldecode($this->keywords);
      }
      else {
        if ($this->websiteID) {
          switch ($this->orderBy)
          {
            case "ID":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=ASC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID='
                    . $this->websiteID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC&websiteID=' . $this
                                  ->websiteID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=ASC&websiteID='
                    . $this->websiteID . '">Created</a></strong></td>');
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditSections&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong>Website</strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditSections&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;
              }
              break;
          }
        }
      }

      en('</tr>');

      if ($this->keywords || $this->websiteID) {
        $index = (($currentPage - 1) * ONPUBGUI_PDO_ROW_LIMIT);
      }
      else {
        $index = 0;
      }

      for ($i = 0; $i < ONPUBGUI_PDO_ROW_LIMIT && $index < sizeof($sections); $i++) {
        $section = $sections[$index];
        $this->ID = $sections[$index]->ID;
        $name = $sections[$index]->name;
        $created = $sections[$index]->getCreated()->format("M j, Y");
        $website = $owebsites->get($sections[$index]->websiteID);
        $websiteName = $website->name;

        if ($section->parentID) {
          $names = array ();
          $names[] = $name;
          $name = "";

          while ($section->parentID) {
            $section = $section->parent;
            $names[] = $section->name;
          }

          $names = array_reverse($names);

          for ($j = 0; $j < sizeof($names); $j++) {
            if ($j == 0) {
              $name .= $names[$j];
            }
            else {
              $name .= ' &ndash; ' . $names[$j];
            }
          }
        }

        en('<tr valign="top">');
        //en('<td align="right"><input type="checkbox" name="sectionIDs[]" value="' . $this->ID . '"></td>');

        switch ($this->order)
        {
          case "ASC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $websiteName . '</td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $websiteName . '</td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          case "DESC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $websiteName . '</td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $websiteName . '</td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          default:
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $websiteName . '</td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $this->ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditSection&amp;sectionID='
                  . $this->ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $websiteName . '</td>');
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
        en('Your search did not yield any results. <a href="javascript:clearSearchField(); submitForm();">Display all sections</a>.');
      }
      else {
        if ($this->websiteID) {
          $widget = new OnpubWidgetSelectWebsite($this->pdo, $this->websiteID);
          $widget->display();

          br(2);

          en('There are 0 sections in the selected website. <a href="index.php?onpub=EditSections&amp;websiteID=">Display all sections</a>.');
        }
        else {
          en('There are 0 sections in the database. <a href="index.php?onpub=NewSection">New Section</a>.');
        }
      }
    }

    if ($totalSections) {
      $widget = new OnpubWidgetStats($totalSections, $this->keywords, $this->websiteID, "Section", "Website");
      $widget->display();
    }

    en('<input type="hidden" name="onpub" value="EditSections">');
    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditImages
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
    $oimages = new OnpubImages($this->pdo);
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

          $images = $oimages->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalImages = sizeof($images);
      }
      else {
        if ($this->sectionID) { }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $images = $oimages->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalImages = $oimages->count();
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

          $images = $oimages->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalImages = sizeof($images);
      }
      else {
        if ($this->sectionID) { }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $images = $oimages->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalImages = $oimages->count();
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
      }
    }

    $widget = new OnpubWidgetHeader("Images", ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="get">');
    en('<div>');
    en('<input type="hidden" name="onpub" value="EditImages">');

    if ($totalImages) {
      $widget = new OnpubWidgetPaginator($totalImages, $this->orderBy, $this->order, $this->page, $this->keywords, $this->fullTextSearch, "sectionID", $this->sectionID, "EditImages");
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
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "fileName":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=DESC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;fullTextSearch=' . $this
                                      ->
                                        fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
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
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "fileName":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=DESC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
                    . $this->keywords . '&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;keywords='
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
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=ASC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;
              }
              break;

            case "fileName":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=DESC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC&sectionID=' . $this
                                  ->sectionID . '">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC&sectionID='
                    . $this->sectionID . '">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=ASC&sectionID='
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
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=ASC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            case "fileName":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=DESC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=DESC">Created</a></span></td>');
                  break;

                case "DESC":
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;

                  default:
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=ID&amp;order=DESC">ID</a></span></td>');
                  en('<td align="left"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=fileName&amp;order=ASC">File Name</a></span></td>');
                  en('<td align="left" class="onpub-highlight2"><span class="onpub-field-header"><a href="index.php?onpub=EditImages&amp;orderBy=created&amp;order=ASC">Created</a></span></td>');
                  break;
              }
              break;
          }
        }
      }

      en('<td><span class="onpub-field-header">Preview</span></td>');
      en('</tr>');

      if ($this->keywords || $this->sectionID) {
        $index = (($currentPage - 1) * ONPUBGUI_PDO_ROW_LIMIT);
      }
      else {
        $index = 0;
      }

      $websites = array ();

      for ($i = 0; $i < ONPUBGUI_PDO_ROW_LIMIT && $index < sizeof($images); $i++) {
        $image = $images[$index];
        $websiteID = $images[$index]->websiteID;
        $ID = $images[$index]->ID;
        $fileName = $images[$index]->fileName;
        $created = $images[$index]->getCreated()->format("M j, Y");

        if (!isset($websites[$websiteID])) {
          $websites[$websiteID] = $owebsites->get($websiteID);
        }

        $thumbURL = OnpubImages::getThumbURL('src=' . urlencode($image->getFullPath()) . '&w=50&f=png');

        en('<tr valign="top">');
        //en('<td align="right"><input type="checkbox" name="imageIDs[]" value="' . $ID . '"></td>');

        switch ($this->order)
        {
          case "ASC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight1"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight2"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
                break;
            }
            break;

          case "DESC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight1"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight2"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
                break;
            }
            break;

          default:
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight1"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditImage&amp;imageID='
                  . $ID . '" title="Edit">' . $fileName . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                en('<td class="onpub-highlight2"><a href="index.php?onpub=EditImage&amp;imageID=' . $ID . '" title="Edit"><img src="' . $thumbURL . '"></a></td>');
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
        en('Your search did not yield any results. <a href="javascript:clearSearchField(); submitForm();">Display all images</a>.');
      }
      else {
        if ($this->sectionID) { }
        else {
          en('There are 0 images on file. <a href="index.php?onpub=UploadImages">Upload Images</a>.');
        }
      }
    }

    if ($totalImages) {
      $widget = new OnpubWidgetStats($totalImages, $this->keywords, $this->sectionID, "Image", "");
      $widget->display();
    }

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }
}
?>
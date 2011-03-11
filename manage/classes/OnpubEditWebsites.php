<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubEditWebsites
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

          $websites = $owebsites->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalWebsites = sizeof($websites);
      }
      else {
        if ($this->sectionID) { }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = $this->orderBy;
            $queryOptions->order = $this->order;
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $websites = $owebsites->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalWebsites = $owebsites->count();
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

          $websites = $owebsites->search($this->keywords, $queryOptions);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          return;
        }

        $totalWebsites = sizeof($websites);
      }
      else {
        if ($this->sectionID) { }
        else {
          try {
            $queryOptions = new OnpubQueryOptions();
            $queryOptions->orderBy = "created";
            $queryOptions->order = "DESC";
            $queryOptions->setPage($currentPage, ONPUBGUI_PDO_ROW_LIMIT);
            $websites = $owebsites->select($queryOptions);
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }

          try {
            $totalWebsites = $owebsites->count();
          }
          catch (PDOException $e) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
            return;
          }
        }
      }
    }

    $widget = new OnpubWidgetHeader("Websites");
    $widget->display();

    en('<form action="index.php" method="get">');
    en('<div>');
    en('<input type="hidden" name="onpub" value="EditWebsites">');

    if ($totalWebsites) {
      $widget = new OnpubWidgetPaginator($totalWebsites, $this->orderBy, $this->order, $this->page, $this->keywords, $this->fullTextSearch, "sectionID", $this->sectionID, "EditWebsites");
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;fullTextSearch=' . $this
                                      ->
                                        fullTextSearch . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;fullTextSearch='
                    . $this->fullTextSearch . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords=' . $this
                                  ->
                                    keywords . '&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;keywords='
                    . $this->keywords . '&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=ASC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID='
                    . $this->sectionID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC&sectionID=' . $this
                                  ->sectionID . '">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=ASC&sectionID='
                    . $this->sectionID . '">Created</a></strong></td>');
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
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=ASC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            case "name":
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=DESC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;
              }
              break;

            default:
              switch ($this->order)
              {
                case "ASC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=DESC">Created</a></strong></td>');
                  break;

                case "DESC":
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;

                  default:
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=ID&amp;order=DESC">ID</a></strong></td>');
                  en('<td align="left"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=name&amp;order=ASC">Name</a></strong></td>');
                  en('<td align="left" class="onpub-highlight2"><strong><a href="index.php?onpub=EditWebsites&amp;orderBy=created&amp;order=ASC">Created</a></strong></td>');
                  break;
              }
              break;
          }
        }
      }

      en('</tr>');

      if ($this->keywords || $this->sectionID) {
        $index = (($currentPage - 1) * ONPUBGUI_PDO_ROW_LIMIT);
      }
      else {
        $index = 0;
      }

      for ($i = 0; $i < ONPUBGUI_PDO_ROW_LIMIT && $index < sizeof($websites); $i++) {
        $ID = $websites[$index]->ID;
        $name = $websites[$index]->name;
        $created = $websites[$index]->getCreated()->format("M j, Y");

        en('<tr valign="top">');
        //en('<td align="right"><input type="checkbox" name="websiteIDs[]" value="' . $ID . '"></td>');

        switch ($this->order)
        {
          case "ASC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          case "DESC":
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight2" align="left">' . $created . '</td>');
                break;
            }
            break;

          default:
            switch ($counter)
            {
              case 0:
                en('<td class="onpub-highlight1" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight1" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
                en('<td class="onpub-highlight1" align="left">' . $created . '</td>');
                break;

              case 1:
                en('<td class="onpub-highlight2" align="right">' . $ID . '</td>');
                en('<td class="onpub-highlight2" align="left"><a href="index.php?onpub=EditWebsite&amp;websiteID='
                  . $ID . '" title="Edit">' . $name . '</a></td>');
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
        en('Your search did not yield any results. <a href="javascript:clearSearchField(); submitForm();">Display all websites</a>.');
      }
      else {
        if ($this->sectionID) { }
        else {
          en('There are 0 websites in the database. <a href="index.php?onpub=NewWebsite">New Website</a>.');
        }
      }
    }

    if ($totalWebsites) {
      $widget = new OnpubWidgetStats($totalWebsites, $this->keywords, $this->sectionID, "Website", "");
      $widget->display();
    }

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }
}
?>
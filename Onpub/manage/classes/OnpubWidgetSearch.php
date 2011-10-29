<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetSearch
{
  private $totalArticles;
  private $keywords;
  private $fullTextSearch;

  function __construct($totalArticles, $keywords, $fullTextSearch)
  {
    $this->totalArticles = $totalArticles;
    $this->keywords = $keywords;
    $this->fullTextSearch = $fullTextSearch;
  }

  public function display()
  {
    if ($this->fullTextSearch == "NA") {
      if ($this->keywords) {
        if ($this->totalArticles == 1) {
          en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
            . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search">');
        }
        else {
          en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
            . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search">');
        }
      }
      else {
        if ($this->totalArticles == 1) {
          en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search">');
        }
        else {
          en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search">');
        }
      }
    }
    else {
      if ($this->fullTextSearch) {
        if ($this->keywords) {
          if ($this->totalArticles == 1) {
            en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
              . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1" checked="checked"><label for="id_fullTextSearch"> Fulltext</label>');
          }
          else {
            en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
              . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1" checked="checked"><label for="id_fullTextSearch"> Fulltext</label>');
          }
        }
        else {
          if ($this->totalArticles == 1) {
            en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1" checked="checked"><label for="id_fullTextSearch"> Fulltext</label>');
          }
          else {
            en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1" checked="checked"><label for="id_fullTextSearch"> Fulltext</label>');
          }
        }
      }
      else {
        if ($this->keywords) {
          if ($this->totalArticles == 1) {
            en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
              . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1"><label for="id_fullTextSearch"> Fulltext</label>');
          }
          else {
            en('<input type="submit" value="X" onclick="clearSearchField();"> <input type="text" accesskey="s" maxlength="255" size="'
              . 30 . '" name="keywords" value="' . htmlentities($this->keywords) . '"> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1"><label for="id_fullTextSearch"> Fulltext</label>');
          }
        }
        else {
          if ($this->totalArticles == 1) {
            en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1"><label for="id_fullTextSearch"> Fulltext</label>');
          }
          else {
            en('<input type="text" accesskey="s" maxlength="255" size="' . 30 . '" name="keywords" value=""> <input type="submit" value="Search"> <input type="checkbox" name="fullTextSearch" id="id_fullTextSearch" value="1"><label for="id_fullTextSearch"> Fulltext</label>');
          }
        }
      }
    }
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2012, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetStats
{
  private $totalArticles;
  private $keywords;
  private $id;
  private $listType;
  private $listTypeParent;

  function __construct($totalArticles, $keywords, $id, $listType, $listTypeParent)
  {
    $this->totalArticles = $totalArticles;
    $this->keywords = $keywords;
    $this->id = $id;
    $this->listType = $listType;
    $this->listTypeParent = $listTypeParent;
  }

  public function display()
  {
    if ($this->keywords) {
      if ($this->totalArticles == 1) {
        en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . ' Found</span>');
      }
      else {
        en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . 's Found</span>');
      }
    }
    else {
      if ($this->id) {
        if ($this->totalArticles == 1) {
          en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . ' in ' . $this->listTypeParent . '</span>');
        }
        else {
          en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . 's in ' . $this->listTypeParent . '</span>');
        }
      }
      else {
        if ($this->totalArticles == 1) {
          en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . ' in Total</span>');
        }
        else {
          en('<span class="onpub-field-header">' . $this->totalArticles . ' ' . $this->listType . 's in Total</span>');
        }
      }
    }
  }
}
?>
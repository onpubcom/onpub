<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2012, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubNewWebsite
{
  private $pdo;
  private $owebsite;

  function __construct(PDO $pdo, OnpubWebsite $owebsite)
  {
    $this->pdo = $pdo;
    $this->owebsite = $owebsite;
  }

  public function display()
  {
    $widget = new OnpubWidgetHeader("New Website", ONPUBAPI_SCHEMA_VERSION, $this->pdo);
    $widget->display();

    en('<form id="onpub-form" action="index.php" method="post" enctype="multipart/form-data">');
    en('<div>');

    if ($this->owebsite->name === NULL) {
      en('<h3 class="onpub-field-header">Name</h3><p><input type="text" maxlength="255" size="' . 30 . '" name="name" value="' . htmlentities($this->owebsite->name) . '"> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field"></p>');
    }
    else {
      en('<h3 class="onpub-field-header">Name</h3><p><input type="text" maxlength="255" size="' . 30 . '" name="name" value=""></p>');
    }

    en('<input type="submit" value="Save">');

    en('<input type="hidden" name="onpub" value="NewWebsiteProcess">');

    en('</div>');
    en('</form>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate()
  {
    if (!$this->owebsite->name) {
      $this->owebsite->name = NULL;
      return FALSE;
    }

    return TRUE;
  }

  public function process()
  {
    $owebsites = new OnpubWebsites($this->pdo);

    try {
      $owebsites->insert($this->owebsite);
    }
    catch (PDOException $e) {
      throw $e;
    }
  }
}
?>
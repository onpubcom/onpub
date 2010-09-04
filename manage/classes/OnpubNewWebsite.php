<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
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
    $widget = new OnpubWidgetHeader("New Website");
    $widget->display();

    en('<form action="index.php" method="post" enctype="multipart/form-data">');
    en('<div>');

    if ($this->owebsite->name === NULL) {
      en('<b>Name</b><br><input type="text" maxlength="255" size="' . 30 . '" name="name" value="' . htmlentities($this->owebsite->name) . '"> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field">', 1, 2);
    }
    else {
      en('<b>Name</b><br><input type="text" maxlength="255" size="' . 30 . '" name="name" value="">', 1, 2);
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
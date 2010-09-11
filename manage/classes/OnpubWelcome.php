<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWelcome
{
  private $pdo;

  function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function display()
  {
    $odatabase = new OnpubDatabase($this->pdo);
    $oarticles = new OnpubArticles($this->pdo);
    $oauthors = new OnpubAuthors($this->pdo);
    $oimages = new OnpubImages($this->pdo);
    $osections = new OnpubSections($this->pdo);
    $owebsites = new OnpubWebsites($this->pdo);
    $status = $odatabase->status();
    $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    $widget = new OnpubWidgetHeader("Welcome");
    $widget->display();

    if ($status == ONPUBAPI_SCHEMA_VERSION) {
      en('<div class="yui3-g">');

      en('<div class="yui3-u-1-3">');
      en('<b>Connection Info</b><br>');
      en('Database: ' . $_SESSION['PDO_DATABASE'], 1, 1);
      en('User: ' . $_SESSION['PDO_USER'], 1, 1);
      en('Client: ' . $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . ' on ' . $_SERVER['SERVER_NAME'], 1, 1);
      en('Driver: ' . $driver, 1, 1);
      en('Host: ' . $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS), 1, 1);
      en('Server: ' . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION), 1, 1);
      en('</div>');

      en('<div class="yui3-u-1-3">');
      en('<b>Content Stats</b><br>');
      en('Articles: ' . $oarticles->count(), 1, 1);
      en('Authors: ' . $oauthors->count(), 1, 1);
      en('Images: ' . $oimages->count(), 1, 1);
      en('Sections: ' . $osections->count(), 1, 1);
      en('Websites: ' . $owebsites->count());
      en('</div>');

      en('<div class="yui3-u-1-3">');

      if (is_array($status)) {
        $result = '<span class="onpub-error">';

        foreach ($status as $e) {
          $result .= '<br>' . $e->getMessage();
        }

        $result .= '</span><br><a href="index.php?onpub=SchemaInstall">Install missing tables</a> (required)';
      }
      else {
        if ($_SERVER['SERVER_NAME'] == 'newbeast') {
          $result = '<br>All tables are installed.<!--<br><a href="index.php?onpub=DataBackup">Backup the database</a><br><a href="index.php?onpub=DataRestore">Restore a previous backup</a>--><br><a href="index.php?onpub=DataDelete">Delete all tables</a>';
        }
        else {
          $result = '<br>All tables are installed.<!--<br><a href="index.php?onpub=DataBackup">Backup the database</a><br><a href="index.php?onpub=DataRestore">Restore a previous backup</a>-->';
        }
      }

      en('<b>Database Tables</b>');

      echo $result;
      en('</div>');

      en('</div>');
    }
    else {
      en('<div class="yui3-g">');

      en('<div class="yui3-u-1-2">');
      en('<b>Connection Info</b><br>');
      en('Database: ' . $_SESSION['PDO_DATABASE'], 1, 1);
      en('User: ' . $_SESSION['PDO_USER'], 1, 1);
      en('Client: ' . $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . ' on ' . $_SERVER['SERVER_NAME'], 1, 1);
      en('Driver: ' . $driver, 1, 1);
      en('Host: ' . $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS), 1, 1);
      en('Server: ' . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION), 1, 1);
      en('</div>');

      en('<div class="yui3-u-1-2">');

      if (is_array($status)) {
        $result = '<span class="onpub-error">';

        foreach ($status as $e) {
          $result .= '<br>' . $e->getMessage();
        }

        $result .= '</span><br><a href="index.php?onpub=SchemaInstall">Install missing tables</a> (required)';
      }
      else {
        if ($_SERVER['SERVER_NAME'] == 'newbeast') {
          $result = '<br>All tables are installed.<!--<br><a href="index.php?onpub=DataBackup">Backup the database</a><br><a href="index.php?onpub=DataRestore">Restore a previous backup</a>--><br><a href="index.php?onpub=DataDelete">Delete all tables</a>';
        }
        else {
          $result = '<br>All tables are installed.<!--<br><a href="index.php?onpub=DataBackup">Backup the database</a><br><a href="index.php?onpub=DataRestore">Restore a previous backup</a>-->';
        }
      }

      en('<b>Database Tables</b>');

      echo $result;
      en('</div>');

      en('</div>');
    }

    br (2);

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1-2">');
    en('<b>PHP Configuration</b><br>');

    if (get_magic_quotes_gpc()) {
      en('<a href="http://php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc">Magic Quotes</a>: <span class="onpub-error">On</span> (<a href="http://php.net/manual/en/security.magicquotes.disabling.php">Disabling Magic Quotes</a> is required)', 1, 1);
    }

    if (ini_get("file_uploads")) {
      en('<a href="http://php.net/ini.core#ini.file-uploads" target="_blank">Allow File Uploads</a>: Yes<br>');
    }
    else {
      en('<a href="http://php.net/ini.core#ini.file-uploads" target="_blank">Allow File Uploads</a>: No<br>');
    }

    if (ini_get("upload_max_filesize")) {
      en('<a href="http://php.net/ini.core#ini.upload-max-filesize" target="_blank">Upload Maximum File Size</a>: '
        . ini_get("upload_max_filesize"), 1, 1);
    }
    else {
      en('<a href="http://php.net/ini.core#ini.upload-max-filesize" target="_blank">Upload Maximum File Size</a>: undefined<br>');
    }

    if (ini_get("date.timezone")) {
      en('<a href="http://php.net/ref.datetime" target="_blank">Timezone</a>: '
        . ini_get("date.timezone"), 1, 1);
    }
    else {
      en('<a href="http://php.net/ref.datetime" target="_blank">Timezone</a>: undefined (using '
        . ONPUBGUI_DEFAULT_TZ . ' by default)<br>');
    }

    en('</div>');

    en('<div class="yui3-u-1-2">');

    en('</div>');

    en('</div>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate() { }

  public function process() { }
}
?>
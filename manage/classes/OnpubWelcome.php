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

    $widget = new OnpubWidgetHeader("Dashboard", $status);
    $widget->display();

    en('<div class="yui3-g">');

    if ($status == ONPUBAPI_SCHEMA_VERSION) {
      // Onpub schema is installed.
      $numsites = $owebsites->count();

      en('<div class="yui3-u-1-2">');

      if ($numsites == 0) {
        en('<p><strong>Welcome!</strong></p>');
        en('<p>You are now ready to start creating content with Onpub.</p>');
        en('<p><strong><a href="index.php?onpub=NewWebsite">Create a website...</a></strong></p>');
      }
      else {
        en('<form action="index.php" method="get">');
        en('<div>');
        en('<input type="hidden" name="onpub" value="EditArticles">');
        en('<p><input type="text" name="keywords" style="width: 19.4em;"> <input type="submit" value="Search"> For what: <select><option>Articles</option></select></p>');
        en('</div>');
        en('</form>');

        $queryOptions = new OnpubQueryOptions();
        $queryOptions->rowLimit = 10;
        $queryOptions->sortBy = "created";
        $queryOptions->order = "DESC";
        $articles = $oarticles->select($queryOptions);

        if (sizeof($articles)) {
          en('<table style="width: 100%;" colspan="2">');
          en('<tr><th style="text-align: left; width: 80%;">Recent Articles</th><th style="text-align: left;">Created</th></tr>');
  
          foreach ($articles as $article) {
            en('<tr><td><a href="index.php?onpub=EditArticle&amp;articleID=' . $article->ID . '" title="Edit">' . $article->title . '</a></td><td>' . $article->getCreated()->format("M j, Y") . '</td></tr>');
          } 
  
          en('</table>');
        }

        en('<p><strong>Quick Links</strong></p>');
        en('<ul>');
        en('<li><a href="index.php?onpub=NewArticle">New Article</a></li>');
        en('<li><a href="index.php?onpub=NewSection">New Section</a></li>');
        en('<li><a href="index.php?onpub=UploadImages">Upload Images</a></li>');
        en('</ul>');
      }

      en('</div>');

      en('<div class="yui3-u-1-4">');
      en('<table style="margin-left: auto; margin-right: auto;">');
      en('<tr><th colspan="2" style="text-align: left;">Content Stats</th></tr>');
      en('<tr><td><a href="index.php?onpub=EditArticles">Articles</a>:</td><td>' . $oarticles->count() . '</td></tr>');
      //en('<tr><td>Authors:</td><td>' . $oauthors->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditImages">Images</a>:</td><td>' . $oimages->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditSections">Sections</a>:</td><td>' . $osections->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditWebsites">Websites</a>:</td><td>' . $numsites . '</td></tr>');
      en('</table>');
      en('</div>');
      en('<div class="yui3-u-1-4">');      
    }
    else {
      // Onpub schema is not installed yet. Prompt user to install.
      en('<div class="yui3-u-3-4">');
      en('<p><strong>Welcome to Onpub!</strong></p>');
      en('<p>This appears to be the first time you have logged in.</p>');
      en('<p>Before you can start creating content with Onpub, please click the link below to add the Onpub schema to the connected MySQL database, <em>' . $_SESSION['PDO_DATABASE'] . '</em>.</p>');
      en('<p>You will be unable to start creating content until this step is performed.</p>');
      en('<ul><li><strong><a href="index.php?onpub=SchemaInstall">Install the Onpub database schema</a></strong></li></ul>');
      en('</div>');
      en('<div class="yui3-u-1-4">');
    }

    en('<table>');
    en('<tr><th colspan="2" style="text-align: left;">PHP Configuration</th></tr>');

    if (get_magic_quotes_gpc()) {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc" target="_blank">Magic Quotes</a>:</td><td><span class="onpub-error">On</span>: <a href="http://php.net/manual/en/security.magicquotes.disabling.php" target="_blank">Disabling Magic Quotes</a> is required.</td></tr>');
    }
    else {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc" target="_blank">Magic Quotes</a>:</td><td>Off</td></tr>');
    }

    if (ini_get("allow_url_fopen")) {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">Allow URL File Open</a>:</td><td>Yes</td></tr>');
    }
    else {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">Allow URL File Open</a>:</td><td><span class="onpub-error">No</span>: <a href="http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen" target="_blank">Enabling URL File Open</a> is required.</td></tr>');
    }

    if (ini_get("file_uploads")) {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ini.core#ini.file-uploads" target="_blank">Allow File Uploads</a>:</td><td>Yes</td></tr>');
    }
    else {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ini.core#ini.file-uploads" target="_blank">Allow File Uploads</a>:</td><td>No</td></tr>');
    }

    if (ini_get("upload_max_filesize")) {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ini.core#ini.upload-max-filesize" target="_blank">Upload Maximum File Size</a>:</td><td>' . ini_get("upload_max_filesize") . '</td></tr>');
    }
    else {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ini.core#ini.upload-max-filesize" target="_blank">Upload Maximum File Size</a>:</td><td>undefined</td></tr>');
    }

    if (ini_get("date.timezone")) {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ref.datetime" target="_blank">Timezone</a>:</td><td>' . ini_get("date.timezone") . '</td></tr>');
    }
    else {
      en('<tr style="vertical-align: top;"><td><a href="http://php.net/ref.datetime" target="_blank">Timezone</a>:</td><td>' . ONPUBGUI_DEFAULT_TZ . '</td></tr>');
    }

    en('</table>');

    en('<table>');
    en('<tr><th colspan="2" style="text-align: left;">Database Connection</th></tr>');
    en('<tr style="vertical-align: top;"><td>MySQL Host:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . '</td></tr>');
    en('<tr style="vertical-align: top;"><td>MySQL Client:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . ' on ' . $_SERVER['SERVER_NAME'] . '</td></tr>');
    en('<tr style="vertical-align: top;"><td>MySQL Server:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '</td></tr>');
    en('<tr style="vertical-align: top;"><td>MySQL User:</td><td>' . $_SESSION['PDO_USER'] . '</td></tr>');
    en('<tr style="vertical-align: top;"><td>Selected Database:</td><td>' . $_SESSION['PDO_DATABASE'] . '</td></tr>');

    if ($status == ONPUBAPI_SCHEMA_VERSION) {
      en('<tr><td>Onpub Schema:</td><td>Rev. ' . ONPUBAPI_SCHEMA_VERSION . '</td></tr>');
    }

    en('</table>');

    en('</div>');
    en('</div>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate() { }

  public function process() { }
}
?>
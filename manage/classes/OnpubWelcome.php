<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWelcome
{
  private $pdo;
  public $pdoException;

  function __construct($pdo)
  {
    $this->pdo = $pdo;
    $this->pdoExecption = null;
  }

  public function display()
  {
    if ($this->pdo) {
      $odatabase = new OnpubDatabase($this->pdo);
      $oarticles = new OnpubArticles($this->pdo);
      $oauthors = new OnpubAuthors($this->pdo);
      $oimages = new OnpubImages($this->pdo);
      $osections = new OnpubSections($this->pdo);
      $owebsites = new OnpubWebsites($this->pdo);
      $status = $odatabase->status();
      $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
      $widget = new OnpubWidgetHeader("Dashboard", $status, $this->pdo);
    }
    else {
      $status = null;
      $widget = new OnpubWidgetHeader("Dashboard", $status, $this->pdo);
    }

    $widget->display();

    en('<div class="yui3-g">');

    if ($status == ONPUBAPI_SCHEMA_VERSION) {
      // Onpub schema is installed.
      $numsites = $owebsites->count();
      $numarticles = $oarticles->count();

      en('<div class="yui3-u-1-2">');
      en('<div style="padding-right: 1em;">');

      if ($numsites == 0) {
        en('<h3 style="margin-top: 0;">You are ready to start publishing content with Onpub.</h3>');
        en('<p><b><a href="index.php?onpub=NewWebsite">Create a website</a></b> to get started.</p>');
      }
      else {
        if ($numarticles) {
          en('<form id="onpub-form" action="index.php" method="get">');
          en('<div>');
          en('<input type="hidden" name="onpub" value="EditArticles">');
          en('<input type="hidden" name="fullTextSearch" value="1">');
          en('<p style="margin-top: 0;"><input type="text" name="keywords" style="width: 18.5em;"> <input type="submit" value="Search Articles"></p>');
          //en(' For what: <select name="onpub"><option value="EditArticles">Articles</option><option value="EditSections">Sections</option><option value="EditWebsites">Websites</option></select>');
          en('</div>');
          en('</form>');
        }

        $queryOptions = new OnpubQueryOptions();
        $queryOptions->rowLimit = 10;
        $queryOptions->orderBy = "created";
        $queryOptions->order = "DESC";
        $articles = $oarticles->select($queryOptions);

        if (sizeof($articles)) {
          en('<table style="width: 100%;" colspan="2">');
          en('<tr><th style="text-align: left; width: 75%;">Recent Articles</th><th>Created</th></tr>');

          foreach ($articles as $article) {
            en('<tr><td><a href="index.php?onpub=EditArticle&amp;articleID=' . $article->ID . '" title="Edit">' . $article->title . '</a></td><td>' . $article->getCreated()->format("M j, Y") . '</td></tr>');
          }

          en('</table>');
        }
      }

      en('<div class="yui3-g">');
      en('<div class="yui3-u-1-2">');
      en('<h3 style="margin-top: 0;">Quick Links</h3>');
      en('<ul>');
      en('<li><a href="index.php?onpub=NewArticle">New Article</a></li>');
      en('<li><a href="index.php?onpub=NewSection">New Section</a></li>');
      en('<li><a href="index.php?onpub=UploadImages">Upload Images</a></li>');
      en('</ul>');
      en('</div>');

      en('<div class="yui3-u-1-2">');
      en('<table style="float: right;">');
      en('<tr><th colspan="2">Content Stats</th></tr>');
      en('<tr><td><a href="index.php?onpub=EditArticles">Articles</a>:</td><td>' . $numarticles . '</td></tr>');
      //en('<tr><td>Authors:</td><td>' . $oauthors->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditImages">Images</a>:</td><td>' . $oimages->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditSections">Sections</a>:</td><td>' . $osections->count() . '</td></tr>');
      en('<tr><td><a href="index.php?onpub=EditWebsites">Websites</a>:</td><td>' . $numsites . '</td></tr>');
      en('</table>');
      en('</div>');
      en('</div>');

      en('</div>');
      en('</div>');

      en('<div class="yui3-u-1-2">');
    }
    elseif ($this->pdo === NULL) {
      en('<div class="yui3-u-1-2">');

      en('<h3><span class="onpub-error">PDOException:</span> ' . $this->pdoException->getMessage() . '</h3>');

      switch ($this->pdoException->getCode()) {
        case 1044: // Bad database name.
          en('<p>Onpub is unable to connect to the specified MySQL database.</p>');
          break;

        case 1045: // Bad credentials.
          en('<p>Onpub is unable to connect to the specified MySQL database using the logged-in user\'s username and/or password.</p>');
          en('<p>Try logging out and log back in with the correct MySQL credentials.</p>');
          break;

        case 2002: // Server is down
          en('<p>Onpub is unable to connect to the database server.</p>');
          en('<p>Start the specified MySQL server and reload this page to try again.</p>');
          break;

        case 2003: // Server is inaccessible (firewall, wrong port, etc.)
          en('<p>Onpub is unable to access the specified MySQL database server.</p>');
          break;

        case 2005: // Bad host name
          en('<p>Onpub is unable to connect to the specified MySQL database server host.</p>');
          break;
      }

      if ($this->pdoException->getMessage() == 'could not find driver') {
        en('<p>Either PDO_MYSQL is not installed or it is not configured correctly.</p>');
        en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
        en('<p>You will be unable to use Onpub until PDO_MYSQL is installed.</p>');
        en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
      }

      en('</div>');
      en('<div class="yui3-u-1-2">');
    }
    else {
      // Onpub schema is not installed yet. Prompt user to install.
      en('<div class="yui3-u-1-2">');
      en('<h2 style="margin-top: 0;">Welcome to Onpub</h2>');

      if ($odatabase->current())
      {
        en('<p>This appears to be the first time you have connected to this MySQL database with Onpub.</p>');
        en('<p>Before you can publish a website with Onpub, you must add the Onpub schema to the connected database: <em>' . $_SESSION['PDO_DATABASE'] . '</em>.</p>');
        en('<p>Please click the link below to continue:</p>');
        en('<ul><li><b><a href="index.php?onpub=SchemaInstall">Install the Onpub MySQL database schema</a></b></li></ul>');
      }
      else
      {
        $dbs = $odatabase->listDBs();

        if (sizeof($dbs))
        {
          en('<p>Please select the MySQL Database that you would like to work with and click Connect.</p>');

          en('<form id="onpub-form" action="index.php" method="post">');
          en('<div>');
          en('<p>');
          en('<select name="pdoDatabase">');

          foreach ($dbs as $db)
          {
            en('<option value="' . $db . '">' . $db . '</option>');
          }

          en('</select>');
          en('</p>');

          en('<p><input type="submit" value="Connect"></p>');

          en('<input type="hidden" name="onpub" value="LoginProcess">');
          en('</div>');
          en('</form>');
        }
        else
        {
          // User has no database permissions.
          en('<p>Your MySQL User <em>' . $_SESSION['PDO_USER'] . '</em> does not have the permissions to access any databases on this host.</p>');
          en('<p><a href="http://onpub.com/index.php?s=8&a=118#setup" target="_blank">Click here for instructions</a> on how to setup a MySQL User and Database for use with Onpub.');
        }
      }

      en('</div>');
      en('<div class="yui3-u-1-2">');
    }

    if ($this->pdo) {
      en('<table>');
      en('<tr><th colspan="2">Database Connection</th></tr>');
      en('<tr style="vertical-align: top;"><td>MySQL Host:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . '</td></tr>');
      en('<tr style="vertical-align: top;"><td>MySQL Client:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . '</td></tr>');
      en('<tr style="vertical-align: top;"><td>MySQL Server:</td><td>' . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '</td></tr>');
      en('<tr style="vertical-align: top;"><td>MySQL User:</td><td>' . $_SESSION['PDO_USER'] . '</td></tr>');

      if ($_SESSION['PDO_DATABASE'])
      {
        en('<tr style="vertical-align: top;"><td>Connected Database:</td><td>' . $_SESSION['PDO_DATABASE'] . ' (<a href="index.php?onpub=Disconnect">Disconnect</a>)</td></tr>');
      }

      if ($status == ONPUBAPI_SCHEMA_VERSION) {
        en('<tr style="vertical-align: top;"><td>Onpub Schema:</td><td>Rev. ' . ONPUBAPI_SCHEMA_VERSION . '</td></tr>');
      }

      en('</table>');

      en('<table>');
      en('<tr><th colspan="2">PHP Configuration</th></tr>');

      if (function_exists("gd_info")) {
        $gdinfo = gd_info();

        en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/book.image.php" target="_blank">GD</a>:</td><td>Installed: ' . $gdinfo['GD Version'] . '</td></tr>');
      }
      else {
        en('<tr style="vertical-align: top;"><td><a href="http://php.net/manual/en/book.image.php" target="_blank">GD</a>:</td><td><span class="onpub-error">Not installed.</span> <a href="http://php.net/manual/en/image.setup.php" target="_blank">Installing GD</a> is required.</td></tr>');
      }

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
    }

    en('</div>');
    en('</div>');

    $widget = new OnpubWidgetFooter();
    $widget->display();
  }

  public function validate() { }

  public function process() { }
}
?>
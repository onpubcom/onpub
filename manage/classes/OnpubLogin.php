<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubLogin
{
  private $pdoDatabase;
  private $pdoHost;
  private $pdoUser;
  private $pdoPassword;
  private $logout;
  private $target;
  private $rememberLogin;
  private $exception;

  function __construct($pdoDatabase = "", $pdoHost = "localhost", $pdoUser = "", $pdoPassword = "", $logout = FALSE, $target = NULL, $rememberLogin = FALSE, $exception = NULL)
  {
    $this->pdoDatabase = trim($pdoDatabase);
    $this->pdoHost = trim($pdoHost);
    $this->pdoUser = trim($pdoUser);
    $this->pdoPassword = trim($pdoPassword);
    $this->logout = $logout;
    $this->target = $target;
    $this->rememberLogin = $rememberLogin;
    $this->exception = $exception;
  }

  public function display()
  {
    en('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
    en('<html>');
    en('<head>');
    en('<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">');
    en('<title>Onpub (on ' . $_SERVER['SERVER_NAME'] . ')</title>');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssreset/reset-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssfonts/fonts-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssgrids/grids-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssbase/base-min.css">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' . ONPUBGUI_YUI_VERSION . '/build/cssreset/reset-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssfonts/fonts-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssgrids/grids-min.css&amp;' . ONPUBGUI_YUI_VERSION . '/build/cssbase/base-min.css">');
    }

    en('<link rel="stylesheet" type="text/css" href="css/onpub.css">');
    en('</head>');
    en('<body>');

    en('<div id="onpub-page">');

    en('<div class="yui3-g">');

    //en('<div class="yui3-u-1-3">&nbsp;</div>');

    en('<div class="yui3-u-1">');
    en('<form action="index.php" method="post">');
    en('<div style="width: 25%; margin-left: auto; margin-right: auto;">');

    en('<p><a href="index.php"><img src="images/onpub.png" width="143" height="29" alt="Onpub" title="Onpub"></a></p>');

    if ($this->pdoDatabase === NULL) {
      en('<p><strong>Database</strong><br><input title="Database" type="text" maxlength="255" size="25" name="pdoDatabase" value=""> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field"></p>');
    }
    else {
      en('<p><strong>Database</strong><br><input title="Database" type="text" maxlength="255" size="25" name="pdoDatabase" value="'. htmlentities($this->pdoDatabase) . '"></p>');
    }

    if (defined('ONPUBGUI_PDO_HOST')) {
      en('<input type="hidden" name="pdoHost" value="' . ONPUBGUI_PDO_HOST . '">');
    }
    else {
      en('<p><strong>Host</strong><br><input title="Host" type="text" maxlength="255" size="25" name="pdoHost" value="' . htmlentities($this->pdoHost) . '"></p>');
    }

    en('<p><strong>Username</strong><br><input title="Username" type="text" maxlength="255" size="25" name="pdoUser" value="' . htmlentities($this->pdoUser) . '"></p>');

    en('<p><strong>Password</strong><br><input title="Password" type="password" maxlength="255" size="25" name="pdoPassword" value=""></p>');

    en('<p><input type="submit" value="Login"> <a href="http://onpub.com/index.php?s=8&a=118#login" target="_blank"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'help.png" align="top" alt="Help" title="Help"></a></p>');

    if ($this->target) {
      $newTarget = "";

      if (is_array($this->target)) {
        $keys = array_keys($this->target);

        for ($i = 0; $i < sizeof($keys); $i++) {
          $newTarget .= $keys[$i] . "=" . $this->target[$keys[$i]];

          if (($i + 1) != sizeof($keys)) {
            $newTarget .= "&";
          }
        }

        $this->target = $newTarget;
      }

      en('<input type="hidden" name="target" value="' . $this->target . '">');
    }

    en('<input type="hidden" name="onpub" value="LoginProcess">');

    en('</div>');
    en('</form>');
    en('</div>');

    //en('<div class="yui3-u-1-3">&nbsp;</div>');

    en('</div>');

    if ($this->exception) {
      en('<div class="yui3-g">');
      en('<div class="yui3-u-1">');
      en('<div style="text-align: center;">');

      switch ($this->exception->getCode()) {
        case 1044: // Bad database name.
          en('<h3><span class="onpub-error">Login error:</span> Invalid database name.</h3>');
          break;

        case 1045: // Bad credentials.
          en('<h3><span class="onpub-error">Login error:</span> Invalid username and/or password.</h3>');
          break;

        case 2002: // Server is down
          en('<h3><span class="onpub-error">Login error:</span> The MySQL server appears to be down.</h3>');
          break;

        case 2003: // Server is inaccessible (firewall, wrong port, etc.)
          en('<h3><span class="onpub-error">Login error:</span> The MySQL server host is inaccessible.</h3>');
          break;

        case 2005: // Bad host name
          en('<h3><span class="onpub-error">Login error:</span> The MySQL server host address is invalid.</h3>');
          break;

        default:
          en('<h3><span class="onpub-error">Login error:</span> ' . $this->exception->getMessage() . '</h3>');
          break;
      }

      if ($this->exception->getMessage() == 'could not find driver') {
        en('<p>Either PDO_MYSQL is not installed or it is not configured correctly.</p>');
        en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
        en('<p>You will be unable to use Onpub until PDO_MYSQL is installed.</p>');
        en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
      }

      en('</div>');
      en('</div>');
      en('</div>');
    }

    en('</div>');

    en('</body>');
    en('</html>');
  }

  public function validate()
  {
    if (!$this->pdoDatabase) {
      $this->pdoDatabase = NULL;
      return FALSE;
    }

    try {
      $pdo = new PDO("mysql:host=" . $this->pdoHost . ";dbname=$this->pdoDatabase", $this->pdoUser, $this->pdoPassword);
    }
    catch (PDOException $e) {
      throw $e;
    }

    $pdo = NULL;
    return TRUE;
  }

  public function process()
  {
    if ($this->logout) {
      $_SESSION = array ();

      if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), "", (time() - 42000), "/");
      }

      session_destroy();
    }

    session_regenerate_id();

    $_SESSION['PDO_HOST'] = $this->pdoHost;
    $_SESSION['PDO_USER'] = $this->pdoUser;
    $_SESSION['PDO_PASSWORD'] = $this->pdoPassword;
    $_SESSION['PDO_DATABASE'] = $this->pdoDatabase;
  }

  public function getTarget()
  {
    return $this->target;
  }

  public function setException($exception)
  {
    $this->exception = $exception;
  }
}
?>
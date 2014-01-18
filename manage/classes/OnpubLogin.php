<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2012, Onpub.com.
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
  private $pdoOptions;
  public $logout;
  private $target;
  private $rememberLogin;
  private $exception;

  function __construct($pdoDatabase = "", $pdoHost = "localhost", $pdoUser = "", $pdoPassword = "", $logout = FALSE, $target = NULL, $rememberLogin = FALSE, $exception = NULL)
  {
    $this->pdoDatabase = trim($pdoDatabase);
    $this->pdoHost = trim($pdoHost);
    $this->pdoUser = trim($pdoUser);
    $this->pdoPassword = trim($pdoPassword);
    $this->pdoOptions = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES latin1 COLLATE latin1_general_ci');
    $this->logout = $logout;
    $this->target = $target;
    $this->rememberLogin = $rememberLogin;
    $this->exception = $exception;
  }

  public function display()
  {
    en('<!DOCTYPE html>');
    en('<html>');
    en('<head>');
    en('<meta name="viewport" content="width=device-width; initial-scale=1.0">');
    en('<meta charset="ISO-8859-1">');
    en('<title>Onpub (on ' . $_SERVER['SERVER_NAME'] . ')</title>');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssnormalize/cssnormalize-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssfonts/cssfonts-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssgrids/cssgrids-min.css">');
      en('<link rel="stylesheet" type="text/css" href="' . ONPUBGUI_YUI_DIRECTORY . 'cssgrids-responsive/cssgrids-responsive-min.css">');
    }
    else {
      en('<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?' . ONPUBGUI_YUI_VERSION . '/build/cssnormalize/cssnormalize-min.css&' . ONPUBGUI_YUI_VERSION . '/build/cssfonts/cssfonts-min.css&' . ONPUBGUI_YUI_VERSION . '/build/cssgrids/cssgrids-min.css&' . ONPUBGUI_YUI_VERSION . '/build/cssgrids-responsive/cssgrids-responsive-min.css">');
    }

    en('<link rel="stylesheet" type="text/css" href="css/onpub.css">');
    en('</head>');
    en('<body>');

    en('<div id="onpub-page" style="margin-top: 2em; width: 800px;">');
    en('<div id="onpub-body">');

    en('<div style="text-align: center; margin-top: 2em; margin-bottom: 1.5em;"><a href="index.php"><img src="images/onpub.png" width="222" height="89" alt="Onpub" title="Onpub"></a></div>');

    en('<div class="yui3-g">');

    en('<div class="yui3-u-1">');
    en('<form id="onpub-form" action="index.php" method="post">');
    en('<div style="width: 24%; margin-left: auto; margin-right: auto; margin-bottom: 2.25em;">');

    if (defined('ONPUBGUI_PDO_HOST')) {
      en('<input type="hidden" name="pdoHost" value="' . ONPUBGUI_PDO_HOST . '">');
    }
    else {
      en('<h3 class="onpub-field-header">Host</h3><p><input title="Host" type="text" maxlength="255" size="25" name="pdoHost" value="' . htmlentities($this->pdoHost) . '"></p>');
    }

    en('<h3 class="onpub-field-header">Username</h3><p><input title="Username" type="text" maxlength="255" size="25" name="pdoUser" value="' . htmlentities($this->pdoUser) . '"></p>');

    en('<h3 class="onpub-field-header">Password</h3><p><input title="Password" type="password" maxlength="255" size="25" name="pdoPassword" value=""></p>');

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

        case 1049: // Bad credentials.
          en('<h3><span class="onpub-error">Login error:</span> Unknown database name.</h3>');
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
        en('<p>PDO_MYSQL is not installed or is not configured correctly.</p>');
        en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
        en('<p>You will be unable to use Onpub until PDO_MYSQL is installed.</p>');
        en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
      }
      elseif ($this->exception->getCode() === 1) {
        en('<p>Onpub requires the PDO and PDO_MYSQL PHP extensions in order to connect to a MySQL database server.</p>');
        en('<p>You will be unable to use Onpub until PDO and PDO_MYSQL are installed.</p>');
        en('<p>Please refer to the <a href="http://onpub.com/index.php?s=8&a=11" target="_blank">Onpub System Requirements</a> and the <a href="http://www.php.net/manual/en/ref.pdo-mysql.php" target="_blank">PHP Manual</a> for more information.</p>');
      }

      en('</div>');
      en('</div>');
      en('</div>');
    }

    en('</div>');
    en('</div>');

    en('</body>');
    en('</html>');
  }

  public function validate()
  {
    if (class_exists('PDO')) {
      try {
        $pdo = new PDO("mysql:host=" . $this->pdoHost, $this->pdoUser, $this->pdoPassword, $this->pdoOptions);
      }
      catch (PDOException $e) {
        throw $e;
      }
    }
    else {
      throw new Exception('PDO is not installed or is not configured correctly.', 1);
    }

    $pdo = NULL;
    return TRUE;
  }

  public function process()
  {
    if ($this->logout) {
      $_SESSION = array();

      if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), "", (time() - 42000), "/");
      }

      session_destroy();
      session_regenerate_id();
    }
    else
    {
      if ($this->pdoDatabase && !$this->pdoHost && !$this->pdoUser && !$this->pdoPassword)
      {
        // User is selecting a Database.
        $_SESSION['PDO_DATABASE'] = $this->pdoDatabase;
      }
      else
      {
        // User is attempting to login.
        session_regenerate_id();

        $_SESSION['PDO_HOST'] = $this->pdoHost;
        $_SESSION['PDO_USER'] = $this->pdoUser;
        $_SESSION['PDO_PASSWORD'] = $this->pdoPassword;
        $_SESSION['PDO_DATABASE'] = $this->pdoDatabase;
      }
    }
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
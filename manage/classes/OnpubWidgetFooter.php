<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2015, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetFooter
{
  function __construct() { }

  public function display()
  {
    en('</div>');
    en('</div>');

    en('</div>');

    en('</div>');

    en('<div id="onpub-footer">');
    en('<div id="onpub-footer-content">');

    en('<div class="yui3-g">');
    en('<div class="yui3-u-3-4">');

    en('<p>Onpub ' . ONPUBAPI_VERSION . '. &copy; 2012 <a class="onpub-footer-nav" href="http://onpub.com/" target="_blank">Onpub.com</a> | <a class="onpub-footer-nav" href="http://onpub.com/index.php?s=8" target="_blank">User Guide</a></p>');

    en('</div>');
    en('<div class="yui3-u-1-4" style="text-align: right;">');

    en('<span class="onpub-login"><a class="onpub-footer-nav" href="index.php?onpub=Logout">Logout</a></span>');

    en('</div>');
    en('</div>');

    en('</div>');
    en('</div>');

    if (file_exists(ONPUBGUI_YUI_DIRECTORY)) {
      en('<script type="text/javascript" src="../bower_components/yui3/build/yui/yui-min.js"></script>');
    }
    else {
      en('<script type="text/javascript" src="http://yui.yahooapis.com/combo?' . ONPUBGUI_YUI_VERSION . '/build/yui/yui-min.js"></script>');
    }

    en('<script type="text/javascript" src="js/onpub.js"></script>');

    en('</body>');
    en('</html>');
  }
}
?>
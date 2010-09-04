<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
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

    en('<div id="onpub-footer">');

    en('<p>Onpub ' . ONPUBAPI_VERSION . ', &copy; 2010 <a href="http://onpub.com/" target="_blank">Onpub.com</a>.</p>');

    en('</div>');

    en('</div>');

    en('<script type="text/javascript" src="../yui/build/yui/yui-min.js"></script>');
    en('<script type="text/javascript" src="js/onpub.js"></script>');

    en('<link rel="stylesheet" type="text/css" href="css/onpub-menu.css">');

    en('</body>');
    en('</html>');
  }
}
?>
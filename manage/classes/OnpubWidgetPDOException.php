<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetPDOException
{
  private $exception;
  private $header;
  private $footer;

  function __construct($exception, $header = TRUE, $footer = TRUE)
  {
    $this->exception = $exception;
    $this->header = $header;
    $this->footer = $footer;
  }

  public function display()
  {
    if ($this->header) {
      $widget = new OnpubWidgetHeader("PDO Exception");
      $widget->display();
    }

    en('<strong>Error Message</strong><br><span class="onpub-error">'
      . $this->exception->getMessage() . '</span>', 1, 2);

    en('<strong>Error Code</strong><br>' . $this->exception->getCode(), 1, 2);

    $sqlstate = $this->exception->errorInfo[0];

    en('<strong>SQLSTATE Code</strong><br>' . $sqlstate, 1, 2);

    en('<strong>Trace 0</strong><br>File: ' . $this->exception->getFile() . '<br>Line: ' . $this->exception->getLine(), 1, 2);

    $trace = $this->exception->getTrace();

    for ($i = 0; $i < sizeof($trace); $i++) {
      en('<strong>Trace ' . ($i + 1) . '</strong><br>');
      en('File: ' . $trace[$i]['file'] . '<br>');
      en('Line: ' . $trace[$i]['line'] . '<br>');

      en('Method: ' . $trace[$i]['class'] . $trace[$i]['type'] . $trace[$i]['function'] . '(');
      /*
      if ( isset( $trace[$i]['args'] ) ) {
          $args = $trace[$i]['args'];

          for ( $j = 0; $j < sizeof( $args ); $j++ ) {
              echo $args[$j];

              if ( $j + 1 != sizeof( $args ) ) {
                  en( ', ' );
              }
          }
      }
*/
      en(')', 1, 2);
    }

    if ($this->footer) {
      $widget = new OnpubWidgetFooter();
      $widget->display();
    }
  }
}
?>
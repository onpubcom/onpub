<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetDateCreated
{
  private $created;

  function __construct(DateTime $created)
  {
    $this->created = date_parse($created->format('c'));
  }

  public function display()
  {
    en('<strong>Created</strong>', 1, 1);
    en('Year: <input type="text" maxlength="4" size="4" name="createdYear" value="'
      . $this->created['year'] . '">');
    en('Month:');

    en('<select name="createdMonth">');

    for ($i = 1; $i <= 12; $i++) {
      $month = "";

      switch ($i)
      {
        case 1:
          $month = "Jan";
          break;

        case 2:
          $month = "Feb";
          break;

        case 3:
          $month = "Mar";
          break;

        case 4:
          $month = "Apr";
          break;

        case 5:
          $month = "May";
          break;

        case 6:
          $month = "Jun";
          break;

        case 7:
          $month = "Jul";
          break;

        case 8:
          $month = "Aug";
          break;

        case 9:
          $month = "Sep";
          break;

        case 10:
          $month = "Oct";
          break;

        case 11:
          $month = "Nov";
          break;

        case 12:
          $month = "Dec";
          break;
      }

      if ($this->created['month'] == $i) {
        en('<option value="' . $i . '" selected="selected">' . $month . '</option>');
      }
      else {
        en('<option value="' . $i . '">' . $month . '</option>');
      }
    }

    en('</select>');

    en('Day: <input type="text" maxlength="2" size="2" name="createdDay" value="'
      . $this->created['day'] . '">');

    en('Hour:');

    en('<select name="createdHour">');

    for ($i = 0; $i < 24; $i++) {
      if ($i < 10) {
        $num = "0" . $i;
      }
      else {
        $num = $i;
      }

      if ($this->created['hour'] == $i) {
        en('<option value="' . $num . '" selected="selected">' . $num . '</option>');
      }
      else {
        en('<option value="' . $num . '">' . $num . '</option>');
      }
    }

    en('</select>');

    en('Minute:');

    en('<select name="createdMinute">');

    for ($i = 0; $i < 60; $i++) {
      if ($i < 10) {
        $num = "0" . $i;
      }
      else {
        $num = $i;
      }

      if ($this->created['minute'] == $i) {
        en('<option value="' . $num . '" selected="selected">' . $num . '</option>');
      }
      else {
        en('<option value="' . $num . '">' . $num . '</option>');
      }
    }

    en('</select>');

    en('Second:');

    en('<select name="createdSecond">');

    for ($i = 0; $i < 60; $i++) {
      if ($i < 10) {
        $num = "0" . $i;
      }
      else {
        $num = $i;
      }

      if ($this->created['second'] == $i) {
        en('<option value="' . $num . '" selected="selected">' . $num . '</option>');
      }
      else {
        en('<option value="' . $num . '">' . $num . '</option>');
      }
    }

    en('</select>');
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetWebsites
{
  private $websiteID;
  private $websites;
  private $heading;

  function __construct($websiteID, $websites, $heading)
  {
    $this->websiteID = $websiteID;
    $this->websites = $websites;
    $this->heading = $heading;
  }

  function display()
  {
    en('<strong>Website</strong>', 1, 1);

    if (sizeof($this->websites)) {
      if (sizeof($this->websites) == 1) {
        en('<select name="websiteID" size="1">');
        en('<option value="' . $this->websites[0]->ID . '" selected="selected">' . strip_tags($this->websites[0]->name) . '</option>');
        en('</select>');
      }
      else {
        en('<select name="websiteID" size="1">');
        en('<option value="">Select a website...</option>');

        for ($i = 0; $i < sizeof($this->websites); $i++) {
          if ($this->websites[$i]->ID == $this->websiteID) {
            en('<option value="' . $this->websites[$i]->ID . '" selected="selected">' . strip_tags($this->websites[$i]->name) . '</option>');
          }
          else {
            en('<option value="' . $this->websites[$i]->ID . '">'
              . strip_tags($this->websites[$i]->name) . '</option>');
          }
        }

        if ($this->websiteID === "") {
          en('</select> <img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'exclamation.png" align="top" alt="Required field" title="Required field">');
        }
        else {
          en('</select>');
        }
      }
    }
    else {
      if ($this->websiteID === "") {
        en('<span class="onpub-error">There are 0 websites in the database.</span> <a href="index.php?onpub=NewWebsite">New Website</a>.');
      }
      else {
        en('There are 0 websites in the database. <a href="index.php?onpub=NewWebsite">New Website</a>.');
      }
    }
  }
}
?>
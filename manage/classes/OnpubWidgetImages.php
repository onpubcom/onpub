<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2010, Onpub.com.
 * {@link http://onpub.com/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * Version 2
 * @package onpubgui
 */
class OnpubWidgetImages
{
  private $heading;
  private $imageID;
  private $images;
  private $website;

  function __construct($heading, $imageID, $images, $website = NULL)
  {
    $this->heading = $heading;
    $this->imageID = $imageID;
    $this->images = $images;
    $this->website = $website;
  }

  function display()
  {
    $image = NULL;

    if (sizeof($this->images)) {
      en('<strong>' . $this->heading . '</strong><br>');

      if ($this->imageID) {
        en('<select name="imageID" size="1">');

        en('<option value="">None</option>');

        for ($i = 0; $i < sizeof($this->images); $i++) {
          if ($this->images[$i]->ID == $this->imageID) {
            $image = $this->images[$i];
            en('<option value="' . $this->images[$i]->ID . '" selected="selected">' . strip_tags($this->images[$i]->fileName) . '</option>');
          }
          else {
            en('<option value="' . $this->images[$i]->ID . '">' . strip_tags($this->images[$i]->fileName) . '</option>');
          }
        }

        en('</select>');

        if ($this->website && $image) {
          if ($this->website->imagesURL) {
            if (@fopen(addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName), 'r')) {
              en('<div style="padding-top: 0.25em;"><a href="index.php?onpub=EditImage&amp;imageID=' . $image->ID . '"><img src="' . addTrailingSlash($this->website->imagesURL) . $image->fileName . '" alt="Edit" title="Edit" border="0"></a></div>', 1, 1);
            }
            else {
              en('<div style="padding-top: 0.25em;"><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'picture_error.png" align="top" width="16" height="16" alt="' . addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName) . ' not found" title="' . addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName) . ' not found"> <span class="onpub-error">Make sure the Images URL of <a href="index.php?onpub=EditWebsite&amp;websiteID=' . $this->website->ID . '">' . $this->website->name . '</a> is setup correctly.</span></div>', 1, 1);
            }
          }
        }
      }
      else {
        en('<select name="imageID" size="1">');

        en('<option value="">None</option>');

        for ($i = 0; $i < sizeof($this->images); $i++) {
          en('<option value="' . $this->images[$i]->ID . '">' . strip_tags($this->images[$i]->fileName) . '</option>');
        }

        en('</select>', 1, 2);
      }
    }
  }
}
?>
<?php

/**
 * @author {@link mailto:corey@onpub.com Corey H.M. Taylor}
 * @copyright Onpub (TM). Copyright 2011, Onpub.com.
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

  function __construct($heading, $imageID, $images)
  {
    $this->heading = $heading;
    $this->imageID = $imageID;
    $this->images = $images;
  }

  function display()
  {
    $image = NULL;

    if (sizeof($this->images)) {

      en('<h3 class="onpub-field-header">' . $this->heading . '</h3>');

      en('<p>');
      en('<small>Mouse-over an image name below for a live preview</small>', 1, 1);
      en('<span id="widgetimagepreview">');
      en('<select id="widgetimages" name="imageID" size="10">');

      if ($this->imageID) {
        en('<option value="">None</option>');
      }
      else {
        en('<option value="" selected="selected">None</option>');
      }

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
      en('</span>');
      en('</p>');
      en('<script type="text/javascript">var onpubThumbURLs = [];');

      foreach ($this->images as $i) {
        en('onpubThumbURLs.push("' . OnpubImages::getThumbURL('src=' . $i->getFullPath() . '&h=120') . '");');                
      }

      en('</script>');

      /*
      if ($image) {
        if (@fopen($image->getFullPath(), 'r')) {
          en('<p><a href="index.php?onpub=EditImage&amp;imageID=' .
             $image->ID . '"><img id="widgetimage" src="' .
             OnpubImages::getThumbURL('src=' . $image->getFullPath() .
             '&h=120') . '" alt="Edit" title="Edit" border="0"></a></p>');
        }
        else {
          en('<p><img src="' . ONPUBGUI_IMAGE_DIRECTORY .
             'picture_error.png" align="top" width="16" height="16" alt="' .
             addTrailingSlash($this->website->imagesURL) .
             rawurlencode($image->fileName) . ' not found" title="' .
             addTrailingSlash($this->website->imagesURL) .
             rawurlencode($image->fileName) . ' not found"> ' .
             '<span class="onpub-error">Make sure the Image Uploads URL of ' .
             '<a href="index.php?onpub=EditWebsite&amp;websiteID=' .
             $this->website->ID . '">' . $this->website->name .
             '</a> is setup correctly.</span></p>');
        }
      }
      else {
        //en('<p><a href=""><img id="widgetimage" src="" alt="Edit" title="Edit" border="0" style="display: none;"></a></p>');
      }
      */
    }
  }
}
?>
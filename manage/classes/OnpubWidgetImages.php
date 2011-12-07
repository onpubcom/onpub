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
      en('<h3 class="onpub-field-header">' . $this->heading . '</h3>');

      if ($this->imageID) {
        en('<p style="margin-bottom: 0.25em;">');
        en('<select id="widgetimages" name="imageID" size="10">');

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
        en('</p>');

        if ($this->website && $image) {
          if ($this->website->imagesURL) {
            if (@fopen(addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName), 'r')) {
              en('<script type="text/javascript">var onpubThumbURLs = [];');

              foreach ($this->images as $i) {
                en('onpubThumbURLs.push("' . OnpubImages::getThumbURL('src=' . $i->getFullPath() . '&w=200') . '");');                
              }

              en('</script>');

              en('<p><a href="index.php?onpub=EditImage&amp;imageID=' . $image->ID . '"><img id="widgetimage" src="' . OnpubImages::getThumbURL('src=' . $image->getFullPath() . '&w=200') . '" alt="Edit" title="Edit" border="0"></a></p>');
            }
            else {
              en('<p><img src="' . ONPUBGUI_IMAGE_DIRECTORY . 'picture_error.png" align="top" width="16" height="16" alt="' . addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName) . ' not found" title="' . addTrailingSlash($this->website->imagesURL) . rawurlencode($image->fileName) . ' not found"> <span class="onpub-error">Make sure the Image Uploads URL of <a href="index.php?onpub=EditWebsite&amp;websiteID=' . $this->website->ID . '">' . $this->website->name . '</a> is setup correctly.</span></p>');
            }
          }
        }
      }
      else {
        en('<p>');
        en('<select name="imageID" size="1">');

        en('<option value="">None</option>');

        for ($i = 0; $i < sizeof($this->images); $i++) {
          en('<option value="' . $this->images[$i]->ID . '">' . strip_tags($this->images[$i]->fileName) . '</option>');
        }

        en('</select>');
        en('</p>');
      }
    }
  }
}
?>
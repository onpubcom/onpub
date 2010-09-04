<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

// See the following OnpubAPI tutorial for more info:
// http://onpub.com/index.php?articleID=78&sectionID=2

// This example is based on an example by Anis uddin Ahmad, the author of
// Universal Feed Writer.

// Specify the query options.
// Include articles in the returned section ordered by date, newest to oldest.
$qo = new OnpubQueryOptions();
$qo->includeContent = true;
$qo->orderBy = 'created';
$qo->order = 'DESC';
$qo->rowLimit = 10;

$articles = $onpub_articles->select($qo);

//Creating an instance of FeedWriter class.
//The constant RSS2 is passed to mention the version
$TestFeed = new FeedWriter(RSS2);

//Setting the channel elements
//Use wrapper functions for common channel elements
$TestFeed->setTitle($onpub_website->name);
$TestFeed->setLink(addTrailingSlash($onpub_website->url));
$TestFeed->setDescription('');

//Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
if ($onpub_website->image) {
  $TestFeed->setImage($onpub_website->name, addTrailingSlash($onpub_website->url), addTrailingSlash($onpub_website->imagesURL) . $onpub_website->image->fileName);
}
else {
  $TestFeed->setImage($onpub_website->name, addTrailingSlash($onpub_website->url), null);
}

//Use core setChannelElement() function for other optional channels
$TestFeed->setChannelElement('language', 'en-us');
$TestFeed->setChannelElement('pubDate', date(DATE_RSS, time()));

//Adding a feed. Genarally this portion will be in a loop and add all feeds.

foreach ($articles as $article) {
  // Get the article's authors.
  $authors = $article->authors;

  //Create an empty FeedItem
  $newItem = $TestFeed->createNewItem();

  //Add elements to the feed item
  //Use wrapper functions to add common feed elements
  // Use the OnpubArticle object to set the various properties of the FeedItem.
  $newItem->setTitle($article->title);

  $samaps = $onpub_samaps->select(null, null, $article->ID);

  if (sizeof($samaps)) {
    $newItem->setLink(addTrailingSlash($onpub_website->url) . 'index.php?sectionID=' . $samaps[0]->sectionID . '&articleID=' . $article->ID);
  }
  else {
    $newItem->setLink(addTrailingSlash($onpub_website->url) . 'index.php?articleID=' . $article->ID);
  }

  //The parameter is a timestamp for setDate() function
  $newItem->setDate($article->getCreated()->format('c'));

  $newItem->setDescription($article->content);

  if (sizeof($authors)) {
    //Use core addElement() function for other supported optional elements
    $newItem->addElement('author', $authors[0]->displayAs);
  }

  //Now add the feed item
  $TestFeed->addItem($newItem);
}

//OK. Everything is done. Now genarate the feed.
$TestFeed->genarateFeed();

?>

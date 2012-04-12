<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

if ($onpub_website && $onpub_disp_rss) {
  // See the following OnpubAPI tutorial for more info:
  // http://onpub.com/index.php?s=20&a=78

  // This example is based on an example by Anis uddin Ahmad, the author of
  // Universal Feed Writer.

  //Creating an instance of FeedWriter class.
  //The constant RSS2 is passed to mention the version
  $feed = new FeedWriter(RSS2);

  //Setting the channel elements
  //Use wrapper functions for common channel elements
  $feed->setTitle($onpub_website->name);
  $feed->setLink(addTrailingSlash($onpub_website->url));
  $feed->setDescription('');

  //Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
  if ($onpub_website->image) {
    $feed->setImage($onpub_website->name, addTrailingSlash($onpub_website->url), addTrailingSlash($onpub_website->imagesURL) . $onpub_website->image->fileName);
  }
  else {
    $feed->setImage($onpub_website->name, addTrailingSlash($onpub_website->url), null);
  }

  //Use core setChannelElement() function for other optional channels
  $feed->setChannelElement('language', 'en-us');
  $feed->setChannelElement('pubDate', date(DATE_RSS, time()));

  $qo = new OnpubQueryOptions();
  $qo->includeContent = true;
  $qo->includeAuthors = true;
  $qo->orderBy = 'created';
  $qo->order = 'DESC';
  $qo->rowLimit = $onpub_disp_updates_num;

  $articles = $onpub_articles->select($qo, null, $onpub_website->ID);

  //Adding a feed. Genarally this portion will be in a loop and add all feeds.
  foreach ($articles as $article) {
    // Get the article's authors.
    $authors = $article->authors;

    //Create an empty FeedItem
    $newItem = $feed->createNewItem();

    //Add elements to the feed item
    //Use wrapper functions to add common feed elements
    // Use the OnpubArticle object to set the various properties of the FeedItem.
    $newItem->setTitle($article->title);

    $samaps = $onpub_samaps->select(null, null, $article->ID);

    if (sizeof($samaps)) {
      $newItem->setLink(addTrailingSlash($onpub_website->url) . 'index.php?s=' . $samaps[0]->sectionID . '&a=' . $article->ID);
    }
    else {
      $newItem->setLink(addTrailingSlash($onpub_website->url) . 'index.php?a=' . $article->ID);
    }

    //The parameter is a timestamp for setDate() function
    $newItem->setDate($article->getCreated()->format('c'));

    $newItem->setDescription($article->content);

    if (sizeof($authors)) {
      //Use core addElement() function for other supported optional elements
      $newItem->addElement('author', $authors[0]->displayAs);
    }

    //Now add the feed item
    $feed->addItem($newItem);
  }

  //OK. Everything is done. Now genarate the feed.
  $feed->genarateFeed();
}

?>

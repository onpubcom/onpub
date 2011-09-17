<?php

/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

include("../api/onpubapi.php");

include("onpubgui.php");

if (!ini_get("date.timezone")) {
  date_default_timezone_set (ONPUBGUI_DEFAULT_TZ);
}

header("Content-Type: text/html; charset=iso-8859-1");

session_name("onpubpdo");
session_set_cookie_params(0, '/', '', false, true);
session_start();

$pdo = NULL;
$loginStatus = FALSE;
$dsn = "";
$username = "";
$password = "";

if (isset($_SESSION['PDO_HOST']) && isset($_SESSION['PDO_USER']) && isset($_SESSION['PDO_PASSWORD']) && isset($_SESSION['PDO_DATABASE'])) {
  $loginStatus = TRUE;
  $dsn = "mysql:host=" . $_SESSION['PDO_HOST'] . ";dbname=" . $_SESSION['PDO_DATABASE'];
  $username = $_SESSION['PDO_USER'];
  $password = $_SESSION['PDO_PASSWORD'];
}

if ($loginStatus && !class_exists('PDO')) {
  // PDO is not installed, destroy session.
  $login = new OnpubLogin();
  $login->logout = true;
  $login->process();

  // Bounce user back to login page.
  header("Location: index.php");
  return;    
}

if (isset($_POST['onpub'])) {
  $ajaxPost = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

  if (!$loginStatus && $_POST['onpub'] != "LoginProcess") {
    header("Location: index.php");
    return;
  }
 
  if ($loginStatus) {
    try {
      $pdo = new PDO($dsn, $username, $password);
    }
    catch (PDOException $e) {
      // PDO init error, bounce user back to Dashboard page.
      if ($ajaxPost) {
        header("Content-Type: application/json; charset=iso-8859-1");
        echo json_encode(array("code" => $e->getCode(),
                               "message" => $e->getMessage()));
      }
      else {
        header("Location: index.php");
      }

      return;
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
  }

  switch ($_POST['onpub'])
  {
    case "LoginProcess":
      if (isset($_POST['rememberLogin'])) {
        $rememberLogin = TRUE;
      }
      else {
        $rememberLogin = FALSE;
      }

      if (isset($_POST['target'])) {
        if ($_POST['target'] == "Logout") {
          $logout = TRUE;
          $target = NULL;
        }
        else {
          $logout = FALSE;
          $target = $_POST['target'];
        }
      }
      else {
        $logout = FALSE;
        $target = NULL;
      }

      $login = new OnpubLogin($_POST['pdoDatabase'], $_POST['pdoHost'], $_POST['pdoUser'], $_POST['pdoPassword'], $logout, $target, $rememberLogin);

      try {
        if (!$login->validate()) {
          $login->display();
          return;
        }
      }
      catch (Exception $e) {
        $login->setException($e);
        $login->display();
        return;
      }

      $login->process();

      if ($login->getTarget()) {
        header("Location: index.php?" . $login->getTarget());
        return;
      }
      else {
        header("Location: index.php");
        return;
      }
      break;

    case "EditImageProcess":
      $oimage = new OnpubImage();
      $oimage->ID = $_POST['imageID'];
      $oimage->fileName = $_POST['fileName'];
      $oimage->description = $_POST['description'];

      $edit = new OnpubEditImage($pdo, $oimage, $_POST['oldImageFileName']);

      if (!$edit->validate()) {
        $edit->display();
        return;
      }

      try {
        $edit->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditImage&imageID=" . $_POST['imageID']);
      return;
      break;

    case "NewArticleProcess":
      if (isset($_POST['sectionIDs'])) {
        $sectionIDs = $_POST['sectionIDs'];
      }
      else {
        $sectionIDs = array ();
      }

      if (isset($_POST['displayAs'])) {
        $displayAs = $_POST['displayAs'];
      }
      else {
        $displayAs = "";
      }

      if (isset($_POST['imageID'])) {
        $imageID = $_POST['imageID'];

        if (!$imageID) {
          $imageID = NULL;
        }
      }
      else {
        $imageID = NULL;
      }

      $odate = new DateTime();
      $odate->setDate($_POST['createdYear'], $_POST['createdMonth'], $_POST['createdDay']);
      $odate->setTime($_POST['createdHour'], $_POST['createdMinute'], $_POST['createdSecond']);

      $oarticle = new OnpubArticle();
      $oarticle->imageID = $imageID;
      $oarticle->title = $_POST['title'];
      $oarticle->content = $_POST['content'];
      $oarticle->setCreated($odate);
      $oarticle->sectionIDs = $sectionIDs;

      $oauthor = new OnpubAuthor();
      $oauthor->displayAs = $displayAs;

      $create = new OnpubNewArticle($pdo, $oarticle, $oauthor);

      if (!$create->validate()) {
        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $create->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditArticle&articleID=" . $oarticle->ID);
      return;
      break;

    case "EditArticleProcess":
      if (isset($_POST['authorID'])) {
        $authorID = $_POST['authorID'];
      }
      else {
        $authorID = NULL;
      }

      if (isset($_POST['sectionIDs'])) {
        $sectionIDs = $_POST['sectionIDs'];
      }
      else {
        $sectionIDs = NULL;
      }

      if (isset($_POST['displayAs'])) {
        $displayAs = $_POST['displayAs'];
      }
      else {
        $displayAs = "";
      }

      if (isset($_POST['imageID'])) {
        $imageID = $_POST['imageID'];

        if (!$imageID) {
          $imageID = NULL;
        }
      }
      else {
        $imageID = NULL;
      }

      $odate = new DateTime();
      $odate->setDate($_POST['createdYear'], $_POST['createdMonth'], $_POST['createdDay']);
      $odate->setTime($_POST['createdHour'], $_POST['createdMinute'], $_POST['createdSecond']);

      $oarticle = new OnpubArticle();
      $oarticle->ID = $_POST['articleID'];
      $oarticle->imageID = $imageID;
      $oarticle->title = $_POST['title'];
      $oarticle->content = $_POST['content'];
      $oarticle->url = $_POST['url'];
      $oarticle->setCreated($odate);
      $oarticle->sectionIDs = $sectionIDs;

      if ($displayAs !== "") {
        $oauthor = new OnpubAuthor();

        if ($displayAs == $_POST['lastDisplayAs']) {
          $oauthor->ID = $authorID;
        }

        if ($_POST['authorImageID']) {
          $oauthor->imageID = $_POST['authorImageID'];
        }

        $oauthor->displayAs = $displayAs;
        $oarticle->authors = array ($oauthor);
      }

      $edit = new OnpubEditArticle($pdo, $oarticle);

      if (!$edit->validate()) {
        try {
          if (!$ajaxPost)
            $edit->display();
        }
        catch (PDOException $e) {
          if (!$ajaxPost) {
            $widget = new OnpubWidgetPDOException($e);
            $widget->display();
          }

          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $edit->process();
      }
      catch (PDOException $e) {
        if (!$ajaxPost) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
        }

        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      if (!$ajaxPost) {
        // Standard browser POST/form submission
        header("Location: index.php?onpub=EditArticle&articleID=" . $_POST['articleID']);
      }

      return;
      break;

    case "UploadImagesProcess":
      if (isset($_POST['websiteID'])) {
        $websiteID = $_POST['websiteID'];

        if (!$websiteID) {
          $websiteID = NULL;
        }
      }
      else {
        $websiteID = NULL;
      }

      if (isset($_FILES['imageFiles'])) {
        $imageFiles = $_FILES['imageFiles'];
      }
      else {
        $imageFiles = NULL;
      }

      if (isset($_POST['overwrite'])) {
        $overwrite = $_POST['overwrite'];
        $overwriteFileName = $_POST['overwriteFileName'];
      }
      else {
        $overwrite = NULL;
        $overwriteFileName = NULL;
      }

      $upload = new OnpubUploadImages($pdo, $imageFiles, $websiteID);
      $upload->overwrite = $overwrite;
      $upload->overwriteFileName = $overwriteFileName;

      if (!$upload->validate() && $overwrite === NULL) {
        try {
          $upload->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $upload->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }
      catch (Exception $e) {
        try {
          $upload->displayException($e);
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditImage&imageID=" . $upload->getImageID());
      return;
      break;

    case "DeleteImageProcess":
      $oimage = new OnpubImage();
      $oimage->ID = $_POST['imageID'];

      $edit = new OnpubEditImage($pdo, $oimage, $_POST['oldImageFileName']);

      try {
        $edit->delete();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditImages");
      return;
      break;

    case "DeleteSectionProcess":
      $osection = new OnpubSection();
      $osection->ID = $_POST['sectionID'];

      $edit = new OnpubEditSection($pdo, $osection);

      try {
        $edit->delete();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditSections");
      return;
      break;

    case "DeleteWebsiteProcess":
      $owebsite = new OnpubWebsite();
      $owebsite->ID = $_POST['websiteID'];

      $edit = new OnpubEditWebsite($pdo, $owebsite);

      try {
        $edit->delete();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditWebsites");
      return;
      break;

    case "DeleteArticleProcess":
      if (isset($_POST['articleIDs'])) {
        $articleIDs = $_POST['articleIDs'];
      }
      else {
        $articleIDs = array ();
      }

      $delete = new OnpubDeleteArticles($pdo, $articleIDs);

      try {
        $delete->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditArticles");
      return;
      break;

    case "ArticleMoveProcess":
      if (isset($_POST['articleIDs'])) {
        $articleIDs = $_POST['articleIDs'];
      }
      else {
        $articleIDs = array ();
      }

      if (isset($_POST['sectionIDs'])) {
        $sectionIDs = $_POST['sectionIDs'];
      }
      else {
        $sectionIDs = array ();
      }

      $move = new OnpubMoveArticles($pdo, $articleIDs, $sectionIDs);

      try {
        $move->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditArticles");
      return;
      break;

    case "NewWebsiteProcess":
      $owebsite = new OnpubWebsite();

      $owebsite->name = $_POST['name'];

      $create = new OnpubNewWebsite($pdo, $owebsite);

      if (!$create->validate()) {
        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $create->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditWebsite&websiteID="
        . $owebsite->ID);
      return;
      break;

    case "NewSectionProcess":
      if (isset($_POST['websiteID'])) {
        $websiteID = $_POST['websiteID'];

        if (!$websiteID) {
          $websiteID = NULL;
        }
      }
      else {
        $websiteID = NULL;
      }

      if (isset($_POST['sectionID'])) {
        $parentID = $_POST['sectionID'];

        if (!$parentID) {
          $parentID = NULL;
        }
      }
      else {
        $parentID = NULL;
      }

      if (isset($_POST['visible'])) {
        $visible = TRUE;
      }
      else {
        $visible = FALSE;
      }

      $osection = new OnpubSection();
      $osection->websiteID = $websiteID;
      $osection->parentID = $parentID;
      $osection->name = $_POST['name'];

      $create = new OnpubNewSection($pdo, $osection, $visible);

      if (!$create->validate()) {
        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $create->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditSection&sectionID="
        . $osection->ID);
      return;
      break;

    case "EditSectionProcess":
      if (isset($_POST['articleIDs'])) {
        $articleIDs = $_POST['articleIDs'];
      }
      else {
        $articleIDs = array();
      }

      if (isset($_POST['imageID'])) {
        $imageID = $_POST['imageID'];

        if (!$imageID) {
          $imageID = NULL;
        }
      }
      else {
        $imageID = NULL;
      }

      if (isset($_POST['parentID'])) {
        $parentID = $_POST['parentID'];

        if (!$parentID) {
          $parentID = NULL;
        }
      }
      else {
        $parentID = NULL;
      }

      if (isset($_POST['visible'])) {
        $visible = TRUE;
      }
      else {
        $visible = FALSE;
      }

      $oarticles = array ();

      foreach ($articleIDs as $aID) {
        if ($aID) {
          $oarticle = new OnpubArticle();
          $oarticle->ID = $aID;
          $oarticles[] = $oarticle;
        }
      }

      $osection = new OnpubSection();
      $osection->ID = $_POST['sectionID'];
      $osection->websiteID = $_POST['websiteID'];
      $osection->parentID = $parentID;
      $osection->imageID = $imageID;
      $osection->name = $_POST['name'];
      $osection->url = $_POST['url'];
      $osection->articles = $oarticles;

      $edit = new OnpubEditSection($pdo, $osection, $visible);

      if (!$edit->validate()) {
        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $edit->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditSection&sectionID="
        . $osection->ID);
      return;
      break;

    case "EditWebsiteProcess":
      if (isset($_POST['sectionIDs'])) {
        $sectionIDs = $_POST['sectionIDs'];
      }
      else {
        $sectionIDs = NULL;
      }

      if (isset($_POST['imageID'])) {
        $imageID = $_POST['imageID'];

        if (!$imageID) {
          $imageID = NULL;
        }
      }
      else {
        $imageID = NULL;
      }

      $owebsite = new OnpubWebsite();
      $owebsite->ID = $_POST['websiteID'];
      $owebsite->imageID = $imageID;
      $owebsite->name = $_POST['name'];
      $owebsite->url = $_POST['url'];
      $owebsite->imagesURL = $_POST['imagesURL'];
      $owebsite->imagesDirectory = $_POST['imagesDirectory'];

      $edit = new OnpubEditWebsite($pdo, $owebsite, $sectionIDs);

      if (!$edit->validate()) {
        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        return;
      }

      try {
        $edit->process();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;

      header("Location: index.php?onpub=EditWebsite&websiteID=" . $_POST['websiteID']);
      return;
      break;
  }
}
else {
  if ($loginStatus) {
    try {
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
    catch (PDOException $e) {
      // PDO init error, show only Dashboard page.
      $pdo = NULL;

      if (isset($_GET['onpub']) && $_GET['onpub'] != "Logout") {
        $_GET['onpub'] = NULL;
      }
    }
  }

  if (isset($_GET['onpub'])) {
    if (!$loginStatus) {
      $login = new OnpubLogin(NULL, NULL, NULL, NULL, FALSE, $_GET);
      $login->display();
      return;
    }

    switch ($_GET['onpub'])
    {
      case "Logout":
        $login = new OnpubLogin("", "", "", "", TRUE);
        $login->process();

        header("Location: index.php");
        return;
        break;

      case "SchemaInstall":
        $odatabase = new OnpubDatabase($pdo);
        $owebsites = new OnpubWebsites($pdo);
        $osections = new OnpubSections($pdo);
        $oarticles = new OnpubArticles($pdo);

        try {
          $odatabase->install();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        header("Location: index.php");
        return;
        break;

      case "NewArticle":
        $oarticle = new OnpubArticle();
        $oauthor = new OnpubAuthor();

        $create = new OnpubNewArticle($pdo, $oarticle, $oauthor);

        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "DeleteArticle":
        $articleIDs = array ();

        if (isset($_GET['articleID'])) {
          $articleIDs[] = $_GET['articleID'];
        }
        elseif (isset($_GET['articleIDs'])) {
          $articleIDs = $_GET['articleIDs'];
        }

        $delete = new OnpubDeleteArticles($pdo, $articleIDs);

        try {
          $delete->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditArticle":
        $oarticle = new OnpubArticle();
        $oarticle->ID = $_GET['articleID'];

        $edit = new OnpubEditArticle($pdo, $oarticle);

        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "ArticleMove":
        $articleIDs = array ();

        if (isset($_GET['articleID'])) {
          $articleIDs[] = $_GET['articleID'];
        }
        elseif (isset($_GET['articleIDs'])) {
          $articleIDs = $_GET['articleIDs'];
        }

        $move = new OnpubMoveArticles($pdo, $articleIDs);

        try {
          $move->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditArticles":
        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
        }
        else {
          $fullTextSearch = NULL;
        }

        if (isset($_GET['sectionID'])) {
          $sectionID = $_GET['sectionID'];
        }
        else {
          $sectionID = NULL;
        }

        $select = new OnpubEditArticles($pdo, $orderBy, $order, $page, $keywords, $fullTextSearch, $sectionID);

        try {
          $select->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditImage":
        if (isset($_GET['imageID'])) {
          $imageID = $_GET['imageID'];
        }
        else {
          $imageID = null;
        }

        $oimage = new OnpubImage();
        $oimage->ID = $imageID;

        $edit = new OnpubEditImage($pdo, $oimage);

        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditImages":
        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
        }
        else {
          $fullTextSearch = NULL;
        }

        if (isset($_GET['sectionID'])) {
          $sectionID = $_GET['sectionID'];
        }
        else {
          $sectionID = NULL;
        }

        $select = new OnpubEditImages($pdo, $orderBy, $order, $page, $keywords, $fullTextSearch, $sectionID);

        try {
          $select->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "UploadImages":
        $upload = new OnpubUploadImages($pdo, array());

        try {
          $upload->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "DeleteImageProcess":
        $oimage = new OnpubImage();
        $oimage->ID = $_GET['imageID'];

        $edit = new OnpubEditImage($pdo, $_GET['imageID'], $_GET['fileName']);

        try {
          $edit->delete();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;

        header("Location: index.php?onpub=EditImages");
        return;
        break;

      case "EditSection":
        if (isset($_GET['sectionID'])) {
          $sectionID = $_GET['sectionID'];
        }
        else {
          $sectionID = null;
        }

        $osection = new OnpubSection();
        $osection->ID = $sectionID;

        $edit = new OnpubEditSection($pdo, $osection);

        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "NewSection":
        $osection = new OnpubSection();

        $create = new OnpubNewSection($pdo, $osection);

        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditSections":
        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
        }
        else {
          $fullTextSearch = NULL;
        }

        if (isset($_GET['websiteID'])) {
          $websiteID = $_GET['websiteID'];
        }
        else {
          $websiteID = NULL;
        }

        $select = new OnpubEditSections($pdo, $orderBy, $order, $page, $keywords, $fullTextSearch, $websiteID);

        try {
          $select->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "NewWebsite":
        $owebsite = new OnpubWebsite();

        $create = new OnpubNewWebsite($pdo, $owebsite);

        try {
          $create->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditWebsites":
        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
        }
        else {
          $fullTextSearch = NULL;
        }

        if (isset($_GET['websiteID'])) {
          $websiteID = $_GET['websiteID'];
        }
        else {
          $websiteID = NULL;
        }

        $select = new OnpubEditWebsites($pdo, $orderBy, $order, $page, $keywords, $fullTextSearch, $websiteID);

        try {
          $select->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      case "EditWebsite":
        if (isset($_GET['websiteID'])) {
          $websiteID = $_GET['websiteID'];
        }
        else {
          $websiteID = null;
        }

        $owebsite = new OnpubWebsite();
        $owebsite->ID = $websiteID;

        $edit = new OnpubEditWebsite($pdo, $owebsite);

        try {
          $edit->display();
        }
        catch (PDOException $e) {
          $widget = new OnpubWidgetPDOException($e);
          $widget->display();
          $pdo = NULL;
          exit($e->getCode());
        }

        $pdo = NULL;
        break;

      default:
        if (!$loginStatus) {
          $login = new OnpubLogin();
          $login->display();
        }
        else {
          $welcome = new OnpubWelcome($pdo);
          $welcome->display();
          $pdo = NULL;
        }
        break;
    }
  }
  else {
    if (!$loginStatus) {
      $login = new OnpubLogin();
      $login->display();
    }
    else {
      $welcome = new OnpubWelcome($pdo);

      if (isset($e)) {
        // There was a $pdo exception.
        $welcome->pdoException = $e;
      }

      try {
        $welcome->display();
      }
      catch (PDOException $e) {
        $widget = new OnpubWidgetPDOException($e);
        $widget->display();
        $pdo = NULL;
        exit($e->getCode());
      }

      $pdo = NULL;
    }
  }
}
?>
<?php

/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

include ("../api/onpubapi.php");

include ("onpubgui.php");

if (!ini_get("date.timezone")) {
  date_default_timezone_set (ONPUBGUI_DEFAULT_TZ);
}

header("Content-Type: text/html; charset=iso-8859-1");

session_name ("onpubpdo");
session_set_cookie_params (0, '/', '', false, true);
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

if (isset($_POST['onpub'])) {
  if (!$loginStatus && $_POST['onpub'] != "LoginProcess") {
    header("Location: index.php");
    return;
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
      catch (PDOException $e) {
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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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

      header("Location: index.php?onpub=EditImage&imageID="
        . $_POST['imageID']);
      return;
      break;

    case "NewArticleProcess":
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

      if (isset($_POST['sectionIDs'])) {
        $sectionIDs = $_POST['sectionIDs'];
        $_SESSION['NA_SIDS'] = $sectionIDs;
      }
      else {
        $sectionIDs = array ();
        $_SESSION['NA_SIDS'] = $sectionIDs;
      }

      if (isset($_POST['displayAs'])) {
        $displayAs = $_POST['displayAs'];
        $_SESSION['NA_DA'] = $displayAs;
      }
      else {
        $displayAs = "";
      }

      if (isset($_POST['imageID'])) {
        $imageID = $_POST['imageID'];
        $_SESSION['NA_IID'] = $imageID;

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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

      header("Location: index.php?onpub=EditArticle&articleID=" . $_POST['articleID']);
      return;
      break;

    case "UploadImagesProcess":
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

      if (isset($_POST['websiteID'])) {
        $websiteID = $_POST['websiteID'];

        if (!$websiteID) {
          $websiteID = NULL;
        }

        $_SESSION['UI_WID'] = $websiteID;
      }
      else {
        $websiteID = NULL;
        $_SESSION['UI_WID'] = $websiteID;
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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

      if (isset($_POST['websiteID'])) {
        $websiteID = $_POST['websiteID'];

        if (!$websiteID) {
          $websiteID = NULL;
        }

        $_SESSION['NS_WID'] = $websiteID;
      }
      else {
        $websiteID = NULL;
        $_SESSION['NS_WID'] = $websiteID;
      }

      if (isset($_POST['sectionID'])) {
        $parentID = $_POST['sectionID'];

        if (!$parentID) {
          $parentID = NULL;
        }

        $_SESSION['NS_SID'] = $parentID;
      }
      else {
        $parentID = NULL;
        $_SESSION['NS_SID'] = $parentID;
      }

      if (isset($_POST['visible'])) {
        $visible = TRUE;
        $_SESSION['NS_V'] = $visible;
      }
      else {
        $visible = FALSE;
        $_SESSION['NS_V'] = $visible;
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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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

      header("Location: index.php?onpub=EditWebsite&websiteID="
        . $_POST['websiteID']);
      return;
      break;
  }
}
else {
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_SESSION['NA_DA'])) {
          $displayAs = $_SESSION['NA_DA'];
        }
        else {
          $displayAs = "";
        }

        if (isset($_SESSION['NA_SIDS'])) {
          $sectionIDs = $_SESSION['NA_SIDS'];
        }
        else {
          $sectionIDs = array ();
        }

        if (isset($_SESSION['NA_IID'])) {
          $imageID = $_SESSION['NA_IID'];
        }
        else {
          $imageID = NULL;
        }

        $oarticle = new OnpubArticle();
        $oarticle->imageID = $imageID;
        $oarticle->sectionIDs = $sectionIDs;

        $oauthor = new OnpubAuthor();
        $oauthor->displayAs = $displayAs;

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
          $_SESSION['EAS_OB'] = $orderBy;
          $_SESSION['EAS_O'] = $order;
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
          $_SESSION['EAS_P'] = $page;
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];

          /*
          if (isset($_SESSION['EAS_S'])) {
            if ($_GET['keywords'] != $_SESSION['EAS_S']) {
              $page = NULL;
              $_SESSION['EAS_P'] = NULL;
            }
          }

          $_SESSION['EAS_S'] = $keywords;
          */
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];

          $_SESSION['EAS_F'] = $fullTextSearch;
        }
        else {
          $fullTextSearch = NULL;
          $_SESSION['EAS_F'] = $fullTextSearch;
        }

        if (isset($_GET['sectionID'])) {

          $sectionID = $_GET['sectionID'];

          if (isset($_SESSION['EAS_SID'])) {
            if ($_GET['sectionID'] != $_SESSION['EAS_SID']) {
              $page = NULL;
              $_SESSION['EAS_P'] = NULL;
            }
          }

          $_SESSION['EAS_SID'] = $sectionID;
        }
        else {
          $sectionID = NULL;
        }

        if (isset($_SESSION['EAS_OB']) && isset($_SESSION['EAS_O'])) {
          $orderBy = $_SESSION['EAS_OB'];
          $order = $_SESSION['EAS_O'];
        }

        if (isset($_SESSION['EAS_P'])) {
          $page = $_SESSION['EAS_P'];
        }

        if (isset($_SESSION['EAS_S'])) {
          $keywords = $_SESSION['EAS_S'];
        }

        if (isset($_SESSION['EAS_F'])) {
          $fullTextSearch = $_SESSION['EAS_F'];
        }

        if (isset($_SESSION['EAS_SID'])) {
          $sectionID = $_SESSION['EAS_SID'];
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
          $_SESSION['EIS_OB'] = $orderBy;
          $_SESSION['EIS_O'] = $order;
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
          $_SESSION['EIS_P'] = $page;
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];

          if (isset($_SESSION['EIS_S'])) {
            if ($_GET['keywords'] != $_SESSION['EIS_S']) {
              $page = NULL;
              $_SESSION['EIS_P'] = NULL;
            }
          }

          $_SESSION['EIS_S'] = $keywords;
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
          $_SESSION['EIS_F'] = $fullTextSearch;
        }
        else {
          $fullTextSearch = NULL;
          $_SESSION['EIS_F'] = $fullTextSearch;
        }

        if (isset($_GET['sectionID'])) {
          $sectionID = $_GET['sectionID'];

          if (isset($_SESSION['EIS_SID'])) {
            if ($_GET['sectionID'] != $_SESSION['EIS_SID']) {
              $page = NULL;
              $_SESSION['EIS_P'] = NULL;
            }
          }

          $_SESSION['EIS_SID'] = $sectionID;
        }
        else {
          $sectionID = NULL;
        }

        if (isset($_SESSION['EIS_OB']) && isset($_SESSION['EIS_O'])) {
          $orderBy = $_SESSION['EIS_OB'];
          $order = $_SESSION['EIS_O'];
        }

        if (isset($_SESSION['EIS_P'])) {
          $page = $_SESSION['EIS_P'];
        }

        if (isset($_SESSION['EIS_S'])) {
          $keywords = $_SESSION['EIS_S'];
        }

        if (isset($_SESSION['EIS_F'])) {
          $fullTextSearch = $_SESSION['EIS_F'];
        }

        if (isset($_SESSION['EIS_SID'])) {
          $sectionID = $_SESSION['EIS_SID'];
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_SESSION['UI_WID'])) {
          $websiteID = $_SESSION['UI_WID'];
        }
        else {
          $websiteID = NULL;
        }

        $upload = new OnpubUploadImages($pdo, array(), $websiteID);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_SESSION['NS_WID'])) {
          $websiteID = $_SESSION['NS_WID'];
        }
        else {
          $websiteID = NULL;
        }

        if (isset($_SESSION['NS_SID'])) {
          $parentID = $_SESSION['NS_SID'];
        }
        else {
          $parentID = NULL;
        }

        if (isset($_SESSION['NS_V'])) {
          $visible = $_SESSION['NS_V'];
        }
        else {
          $visible = TRUE;
        }

        $osection = new OnpubSection();
        $osection->websiteID = $websiteID;
        $osection->parentID = $parentID;

        $create = new OnpubNewSection($pdo, $osection, $visible);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
          $_SESSION['ESS_OB'] = $orderBy;
          $_SESSION['ESS_O'] = $order;
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
          $_SESSION['ESS_P'] = $page;
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];

          if (isset($_SESSION['ESS_S'])) {
            if ($_GET['keywords'] != $_SESSION['ESS_S']) {
              $page = NULL;
              $_SESSION['ESS_P'] = NULL;
            }
          }

          $_SESSION['ESS_S'] = $keywords;
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
          $_SESSION['ESS_F'] = $fullTextSearch;
        }
        else {
          $fullTextSearch = NULL;
          $_SESSION['ESS_F'] = $fullTextSearch;
        }

        if (isset($_GET['websiteID'])) {
          $websiteID = $_GET['websiteID'];

          if (isset($_SESSION['ESS_WID'])) {
            if ($_GET['websiteID'] != $_SESSION['ESS_WID']) {
              $page = NULL;
              $_SESSION['ESS_P'] = NULL;
            }
          }

          $_SESSION['ESS_WID'] = $websiteID;
        }
        else {
          $websiteID = NULL;
        }

        if (isset($_SESSION['ESS_OB']) && isset($_SESSION['ESS_O'])) {
          $orderBy = $_SESSION['ESS_OB'];
          $order = $_SESSION['ESS_O'];
        }

        if (isset($_SESSION['ESS_P'])) {
          $page = $_SESSION['ESS_P'];
        }

        if (isset($_SESSION['ESS_S'])) {
          $keywords = $_SESSION['ESS_S'];
        }

        if (isset($_SESSION['ESS_F'])) {
          $fullTextSearch = $_SESSION['ESS_F'];
        }

        if (isset($_SESSION['ESS_WID'])) {
          $websiteID = $_SESSION['ESS_WID'];
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        if (isset($_GET['orderBy']) && isset($_GET['order'])) {
          $orderBy = $_GET['orderBy'];
          $order = $_GET['order'];
          $_SESSION['EWS_OB'] = $orderBy;
          $_SESSION['EWS_O'] = $order;
        }
        else {
          $orderBy = NULL;
          $order = NULL;
        }

        if (isset($_GET['page'])) {
          $page = $_GET['page'];
          $_SESSION['EWS_P'] = $page;
        }
        else {
          $page = NULL;
        }

        if (isset($_GET['keywords'])) {
          $keywords = $_GET['keywords'];

          if (isset($_SESSION['EWS_S'])) {
            if ($_GET['keywords'] != $_SESSION['EWS_S']) {
              $page = NULL;
              $_SESSION['EWS_P'] = NULL;
            }
          }

          $_SESSION['EWS_S'] = $keywords;
        }
        else {
          $keywords = NULL;
        }

        if (isset($_GET['fullTextSearch'])) {
          $fullTextSearch = $_GET['fullTextSearch'];
          $_SESSION['EWS_F'] = $fullTextSearch;
        }
        else {
          $fullTextSearch = NULL;
          $_SESSION['EWS_F'] = $fullTextSearch;
        }

        if (isset($_GET['websiteID'])) {
          $websiteID = $_GET['websiteID'];

          if (isset($_SESSION['EWS_WID'])) {
            if ($_GET['websiteID'] != $_SESSION['EWS_WID']) {
              $page = NULL;
              $_SESSION['EWS_P'] = NULL;
            }
          }

          $_SESSION['EWS_WID'] = $websiteID;
        }
        else {
          $websiteID = NULL;
        }

        if (isset($_SESSION['EWS_OB']) && isset($_SESSION['EWS_O'])) {
          $orderBy = $_SESSION['EWS_OB'];
          $order = $_SESSION['EWS_O'];
        }

        if (isset($_SESSION['EWS_P'])) {
          $page = $_SESSION['EWS_P'];
        }

        if (isset($_SESSION['EWS_S'])) {
          $keywords = $_SESSION['EWS_S'];
        }

        if (isset($_SESSION['EWS_F'])) {
          $fullTextSearch = $_SESSION['EWS_F'];
        }

        if (isset($_SESSION['EWS_WID'])) {
          $websiteID = $_SESSION['EWS_WID'];
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
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

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
          $pdo = new PDO($dsn, $username, $password);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
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
      $pdo = new PDO($dsn, $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
      $welcome = new OnpubWelcome($pdo);

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
-- Onpub (TM)
-- Copyright (C) 2015 Onpub.com <http://onpub.com/>
-- Author: Corey H.M. Taylor <corey@onpub.com>
--
-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2.
--
-- Requires MySQL version 4.1.2 or newer

CREATE TABLE OnpubImages (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  websiteID INT UNSIGNED NOT NULL,
  fileName VARCHAR(255) NOT NULL,
  description VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX images_websiteID (websiteID),
  INDEX images_fileName (fileName),
  INDEX images_description (description),
  INDEX images_url (url),
  INDEX images_created (created),
  INDEX images_modified (modified)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubWebsites (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  imageID INT UNSIGNED NULL,
  name VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  imagesURL VARCHAR(255) NOT NULL,
  imagesDirectory VARCHAR(255) NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX websites_imageID (imageID),
  INDEX websites_name (name),
  INDEX websites_url (url),
  INDEX websites_imagesURL (imagesURL),
  INDEX websites_imagesDirectory (imagesDirectory),
  INDEX websites_created (created),
  INDEX websites_modified (modified),
  FOREIGN KEY (imageID) REFERENCES OnpubImages (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

ALTER TABLE OnpubImages ADD FOREIGN KEY (websiteID) REFERENCES OnpubWebsites (ID);

CREATE TABLE OnpubSections (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  imageID INT UNSIGNED NULL,
  websiteID INT UNSIGNED NOT NULL,
  parentID INT UNSIGNED NULL,
  name VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX sections_imageID (imageID),
  INDEX sections_websiteID (websiteID),
  INDEX sections_parentID (parentID),
  INDEX sections_name (name),
  INDEX sections_url (url),
  INDEX sections_created (created),
  INDEX sections_modified (modified),
  FOREIGN KEY (imageID) REFERENCES OnpubImages (ID),
  FOREIGN KEY (websiteID) REFERENCES OnpubWebsites (ID),
  FOREIGN KEY (parentID) REFERENCES OnpubSections (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubArticles (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  imageID INT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  url VARCHAR(255) NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX articles_imageID (imageID),
  INDEX articles_title (title),
  INDEX articles_url (url),
  INDEX articles_created (created),
  INDEX articles_modified (modified),
  FOREIGN KEY (imageID) REFERENCES OnpubImages (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubAuthors (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  imageID INT UNSIGNED NULL,
  givenNames VARCHAR(255) NOT NULL,
  familyName VARCHAR(255) NOT NULL,
  displayAs VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX authors_imageID (imageID),
  INDEX authors_givenNames (givenNames),
  INDEX authors_familyName (familyName),
  INDEX authors_displayAs (displayAs),
  INDEX authors_url (url),
  INDEX authors_created (created),
  INDEX authors_modified (modified),
  FOREIGN KEY (imageID) REFERENCES OnpubImages (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubAAMaps (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  articleID INT UNSIGNED NOT NULL,
  authorID INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX aamaps_articleID (articleID),
  INDEX aamaps_authorID (authorID),
  INDEX aamaps_created (created),
  INDEX aamaps_modified (modified),
  FOREIGN KEY (articleID) REFERENCES OnpubArticles (ID),
  FOREIGN KEY (authorID) REFERENCES OnpubAuthors (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubSAMaps (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  sectionID INT UNSIGNED NOT NULL,
  articleID INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX samaps_articleID (articleID),
  INDEX samaps_sectionID (sectionID),
  INDEX samaps_created (created),
  INDEX samaps_modified (modified),
  FOREIGN KEY (sectionID) REFERENCES OnpubSections (ID),
  FOREIGN KEY (articleID) REFERENCES OnpubArticles (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

CREATE TABLE OnpubWSMaps (
  ID INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  websiteID INT UNSIGNED NOT NULL,
  sectionID INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL,
  modified DATETIME NOT NULL,
  INDEX wsmaps_websiteID (websiteID),
  INDEX wsmaps_sectionID (sectionID),
  INDEX wsmaps_created (created),
  INDEX wsmaps_modified (modified),
  FOREIGN KEY (websiteID) REFERENCES OnpubWebsites (ID),
  FOREIGN KEY (sectionID) REFERENCES OnpubSections (ID)
) ENGINE = InnoDB CHARSET latin1 COLLATE latin1_general_ci;

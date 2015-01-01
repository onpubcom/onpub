-- Onpub (TM)
-- Copyright (C) 2015 Onpub.com <http://onpub.com/>
-- Author: Corey H.M. Taylor <corey@onpub.com>
--
-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2.
--
-- WARNING: If you already have the Onpub database tables installed, be sure to
-- back them up since running these commands will permanently delete them!

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE OnpubAAMaps;
DROP TABLE OnpubArticles;
DROP TABLE OnpubAuthors;
DROP TABLE OnpubImages;
DROP TABLE OnpubSAMaps;
DROP TABLE OnpubSections;
DROP TABLE OnpubWSMaps;
DROP TABLE OnpubWebsites;
SET FOREIGN_KEY_CHECKS = 1;

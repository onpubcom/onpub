<?php

require_once('PEAR/PackageFileManager2.php');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$pfm = PEAR_PackageFileManager2::importOptions('./package-template.xml');

$pfm_options = array('packagedirectory' => 'onpub/',
                     'baseinstalldir' => '/');

$pfm->setOptions($pfm_options);

$pfm->generateContents();

$pfm->writePackageFile();

?>

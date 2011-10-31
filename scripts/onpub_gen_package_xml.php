<?php

require_once('PEAR/PackageFileManager2.php');
require_once('../api/onpubapi.php');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$pfm = PEAR_PackageFileManager2::importOptions('./package-template.xml');

$pfm_options = array('packagedirectory' => 'onpub/',
                     'baseinstalldir' => 'Onpub/');

$pfm->setOptions($pfm_options);
$pfm->setReleaseVersion(ONPUBAPI_VERSION);
$pfm->setReleaseStability('stable');
$pfm->setAPIStability('stable');

$pfm->generateContents();

$pfm->writePackageFile();

?>

#!/bin/sh
# A script to package the latest Onpub code from Subversion.

# Export Onpub code from svn.
svn export https://onpub.com/svn/onpub onpub
rm -r onpub/scripts

# Get the latest stable release of CKeditor.
wget http://download.cksource.com/CKEditor/CKEditor/CKEditor%203.4/ckeditor_3.4.zip
unzip ckeditor_3.4.zip
rm -r ckeditor/_samples
mv ckeditor onpub/manage/

# Get the latest release of YUI 3.
wget http://yuilibrary.com/downloads/yui3/yui_3.2.0.zip
unzip yui_3.2.0.zip
# Remove some uneeded dirs to free up some space.
rm -r yui/as-api
rm -r yui/api
rm -r yui/tests
rm -r yui/examples
mv yui onpub/

# Everything is in place, zip up the onpub directory.
zip -9 -r onpub-`php onpub_version.php`.zip onpub

# Take a checksum of the file.
sha1sum onpub-`php onpub_version.php`.zip > onpub-`php onpub_version.php`.sha1

# Clean up.
rm ckeditor_3.4.zip
rm yui_3.2.0.zip
rm -r onpub

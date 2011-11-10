#!/bin/sh
# A script to package the latest Onpub code from Subversion.

# Export Onpub code from svn.
svn export https://onpubdev.googlecode.com/svn/trunk/ onpub
rm -r onpub/scripts

# Get the latest stable release of CKeditor.
wget http://download.cksource.com/CKEditor/CKEditor/CKEditor%203.6.2/ckeditor_3.6.2.zip
unzip ckeditor_3.6.2.zip
rm -r ckeditor/_samples
mv ckeditor onpub/manage/

# Get the latest release of YUI 3.
wget http://yui.zenfs.com/releases/yui3/yui_3.4.1.zip
unzip yui_3.4.1.zip

# Remove some uneeded YUI dirs to free up some space.
rm -r yui/docs
rm -r yui/releasenotes
rm -r yui/tests

# Remove uneeded debug and raw YUI .js files.
find yui/build/ -type f -name '*.js' | grep -v '\-min\.js' | grep -v 'lang/' | xargs /bin/rm

mv yui onpub/api/

# Everything is in place, zip up the onpub directory.
zip -r onpub-`php onpub_version.php`.zip onpub

# Take a checksum of the file.
sha1sum onpub-`php onpub_version.php`.zip > onpub-`php onpub_version.php`.sha1

# Clean up.
rm ckeditor_3.6.2.zip
rm yui_3.4.1.zip
rm -r onpub


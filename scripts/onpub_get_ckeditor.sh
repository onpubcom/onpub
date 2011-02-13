#!/bin/sh
GUIDIR="../manage/"
DLDIR="ckeditor"
DLURL="http://download.cksource.com/CKEditor/CKEditor/CKEditor%203.5.1/"
FNAME="ckeditor_3.5.1.zip"

if [ "$1" = "-f" ]; then
    rm -r "${GUIDIR}${DLDIR}"
fi

if [ -d "${GUIDIR}${DLDIR}" ]; then
    echo "'${GUIDIR}${DLDIR}'" directory exists. Specify '-f' to overwrite.
    exit 0;
fi

wget "${DLURL}${FNAME}"
unzip $FNAME
mv $DLDIR $GUIDIR
rm $FNAME

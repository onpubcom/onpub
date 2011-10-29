#!/bin/sh
APIDIR="../Onpub/data/"
DLDIR="yui"
DLURL="http://yui.zenfs.com/releases/yui3/"
FNAME="yui_3.4.1.zip"

if [ "$1" = "-f" ]; then
    rm -r "${APIDIR}${DLDIR}"
fi

if [ -d "${APIDIR}${DLDIR}" ]; then
    echo "'${APIDIR}${DLDIR}'" directory exists. Specify '-f' to overwrite.
    exit 0;
fi

wget "${DLURL}${FNAME}"
unzip $FNAME
mv $DLDIR $APIDIR
rm $FNAME

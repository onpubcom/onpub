#!/bin/sh
GUIDIR="../"
DLDIR="yui"
DLURL="http://yuilibrary.com/downloads/yui3/"
FNAME="yui_3.2.0.zip"

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

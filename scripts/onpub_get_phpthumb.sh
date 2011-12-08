#!/bin/sh
APIDIR="../api/"
DLDIR="phpThumb"
FNAME="phpThumb-1.7.11-201108081537-beta.zip"
DLURL="http://downloads.sourceforge.net/project/phpthumb/phpThumb%28%29/1.7.11/${FNAME}?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphpthumb%2Ffiles%2FphpThumb%2528%2529%2F1.7.11%2F&ts=1323309310&use_mirror=superb-dca2"


if [ "$1" = "-f" ]; then
    rm -rf "${APIDIR}${DLDIR}"
fi

if [ -d "${APIDIR}${DLDIR}" ]; then
    echo "'${APIDIR}${DLDIR}'" directory exists. Specify '-f' to overwrite.
    exit 0;
fi

wget "${DLURL}${FNAME}"
unzip $FNAME -d ${DLDIR}
mv $DLDIR $APIDIR
rm $FNAME

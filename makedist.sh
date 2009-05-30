#!/bin/sh

DIST_DIR="../ara-`cat VERSION`"
mkdir -p "$DIST_DIR"
cp -af * "$DIST_DIR"/

find "$DIST_DIR" -name .svn -type d -exec rm -Rf {} \; 2> /dev/null
rm "$DIST_DIR"/makedist.sh

tar -cO "$DIST_DIR" | gzip -9 -c > "$DIST_DIR.tar.gz"

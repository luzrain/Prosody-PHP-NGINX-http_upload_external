#!/bin/bash

#Days before file dellete
DAYS=7

UPLOADSDIR=`dirname $0`'/uploads'
find $UPLOADSDIR/* -mtime +$DAYS -exec rm {} \;

#!/bin/bash

wget -q http://sop.vtkyle.com/index.php/?load=rosters/index -O - | sed 's/<\/tr>/\n/gI' | sed 's/<\/td>/|/gI' | sed 's/<[^>]*>//g' | grep "|"
exit

wget -q http://sop.vtkyle.com/index.php/?load=rosters/index -O - | html2text -width 400 | grep -v -E '^\s*\*' | grep -v '^$' | grep -v NULL | grep -v -E '^Owner' | while read line
do
  owner=$(echo ${line} | awk '{print $1}')
  echo $owner
done

#!/bin/bash

wget -q http://sop.vtkyle.com/index.php/?load=drafts/recent -O - | sed 's/<\/tr>/\n/gI' | sed 's/<\/td>/,/gI' | sed 's/<[^>]*>//g' | grep -E '^[0-9]+,[0-9]+,[a-zA-Z. ]+,[a-zA-Z]{2,3}' | while read line
do
  owner=$(echo ${line} | awk -F, '{print $3}')
  player="$(echo ${line} | awk -F, '{print $4}')"
  #echo $owner $player
  echo "select id from Players where Name='${player}' and Season='2017'"
done

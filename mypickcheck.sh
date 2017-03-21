#!/bin/bash

if [ "$(wget -q http://sop.vtkyle.com/index.php/?load=drafts/recent -O - | grep Owner | head -1 | sed 's/<[^>]*>/ /g' | awk '{print $6}')" = "Matt" ]
then
  echo "It's your pick, shitbox" | mailx -s "It's your pick, shitbox" 3028937489@mms.att.net
fi

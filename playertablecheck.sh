#!/ramdisk/bin/bash
set -x

mysql="mysql matthear_fantasybaseball -N -s -r -e"

if [ "$1" = "" ]
then
  year=$(date +%Y)
else
  year=$1
fi

echo "Looking for duplicate player names..."
$mysql "select Name, count(*) c from Players where Season='${year}' group by Name having c > 1;"

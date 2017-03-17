#!/ramdisk/bin/bash

LEAGUE=1
SEASON=2016

if [ "$#" -gt 0 ]
then
  #echo $@
  NUMB=$1
else
  NUMB=5
fi

QUERY="select * from DraftPicks,FantasyTeams where ((DraftPicks.Season=2016) and (DraftPicks.Pick>((select Pick from DraftResult where Season=2016 order by pick desc limit 1))) and FantasyTeams.ID=DraftPicks.FantasyTeam) limit ${NUMB};"

echo "$QUERY"

#mysql -Ns -u matthear_hearnmd -p"" -e "$QUERY" matthear_fantasybaseball
mysql  -u matthear_hearnmd -p"" -e "$QUERY" matthear_fantasybaseball

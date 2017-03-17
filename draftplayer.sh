#!/ramdisk/bin/bash

LEAGUE=1
SEASON=2016

function usage() {
  echo "./draftplayer.php Owner Pick \"Player Name\""
}

OWNER=$1
shift
PICK=$1
shift
PLAYER=$@

echo "$OWNER $PICK \"$PLAYER\""

QUERY="insert into DraftResult (Season, League, FantasyTeam, Player, Pick) values ($SEASON, $LEAGUE, (select ID from FantasyTeams where (Owner='${OWNER}' and Season=${SEASON})), (select ID from Players where (Name=\"${PLAYER}\" and Season=${SEASON})), ${PICK});"

echo $QUERY

mysql -u matthear_hearnmd -p"" -e "$QUERY" matthear_fantasybaseball

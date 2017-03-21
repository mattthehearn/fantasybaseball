#!/bin/bash

baseurl="http://games.espn.com/flb/tools/projections?&slotCategoryId="

declare -A postrans=( ["C"]=0 ["1B"]=1 ["2B"]=2 ["3B"]=3 ["SS"]=4 ["OF"]=5 ["DH"]=11 ["SP"]=14 ["RP"]=15  )
declare -A playerpgct=( ["C"]=2 ["1B"]=2 ["2B"]=2 ["3B"]=2 ["SS"]=2 ["OF"]=3 ["DH"]=1 ["SP"]=4 ["RP"]=3 )

for pos in ${!postrans[@]}
do
  #echo ${pos} ${postrans[${pos}]}
  #pos="SP"
  urlpos=${postrans[${pos}]}
  url="${baseurl}${urlpos}&startIndex="
  pgct=0
  #echo "pgct ${playerpgct[${pos}]}"
  while [ ${pgct} -lt ${playerpgct[${pos}]} ]
  do
    case $pos in
      SP|RP)
        querystr="insert into Players (Season, ESPNRank, Name, RealTeam, PosString, K, W, SV, ERA, WHIP) values (\"2017\","
        ;;
      *)
        querystr="insert into Players (Season, ESPNRank, Name, RealTeam, PosString, R, HR, RBI, SB, BAVG) values (\"2017\","
        ;;
    esac
    wget -q "${url}$((${pgct}*40))" -O - | grep -i -e '</\?TABLE\|</\?TD\|</\?TR\|</\?TH' | sed 's/^[\ \t]*//g' | tr -d '\n' | sed 's/<\/TR[^>]*>/\n/Ig' | sed 's/<TD[^>]*>/|/Ig' | sed 's/<[^>]*>/|/Ig' | sed 's/&nbsp;/|/g' | sed 's/|, /|/g' | sed 's/||*/|/g' | sed 's/|$//g' | grep -E '^\|[1-9]' | sed 's/^|//g' | awk -F'|' '{print "\"" $1  "\",\"" $2 "\",\"" $3 "\",\"" $4 "\",\"" $(NF-4) "\",\"" $(NF-3) "\",\"" $(NF-2) "\",\"" $(NF-1) "\",\"" $NF "\""}' | sed 's/--//g' | while read line
    do
      echo "${querystr}${line});"
    done
    #wget -q "${url}$((${pgct}*40))" -O - | grep -i -e '</\?TABLE\|</\?TD\|</\?TR\|</\?TH' | sed 's/^[\ \t]*//g' | tr -d '\n' | sed 's/<\/TR[^>]*>/\n/Ig' | sed 's/<\/\?\(TABLE\|TR\)[^>]*>//Ig' | sed 's/^<T[DH][^>]*>\|<\/\?T[DH][^>]*>$//Ig' | sed 's/<\/T[DH][^>]*><T[DH][^>]*>/,/Ig' | sed 's/&nbsp;/,/g' | grep -E '^[0-9]' | sed 's/$/<BR>/g' | html2text -width 500 | sed 's/\[.*\]//g' | sed 's/, /,/g' | sed 's/^/"/g' | sed 's/,/","/g' | sed 's/$/"/g'
    #exit;
    #echo "${url}$((${pgct}*40))"
    pgct=$((${pgct}+1))
  done
done


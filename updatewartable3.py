#!/ramdisk/bin/python

from subprocess import Popen, PIPE
from collections import OrderedDict
import math
import mysql.connector
from mysql.connector import errorcode
import pprint

season=2016
league=1
numteams=12
batters=12
atbats=500
starters=4
relievers=2
pitchers=3
spinn=150
rpinn=50

statmax = OrderedDict([('R', 0.0), ('HR', 0.0), ('RBI', 0.0), ('SB', 0.0), ('BAVG', 0.0), ('K', 0.0), ('W', 0.0), ('SV', 0.0), ('ERA', 0.0), ('WHIP', 0.0)])
statmin = OrderedDict([('R', 1000.0), ('HR', 1000.0), ('RBI', 1000.0), ('SB', 1000.0), ('BAVG', 1000.0), ('K', 1000.0), ('W', 1000.0), ('SV', 1000.0), ('ERA', 1000.0), ('WHIP', 1000.0)])


statdict = OrderedDict() 
ownerdict = OrderedDict() 

# Column name dictionary
cnd = ( "ID", "Season", "Name", "RealTeam", "PosString", "FantTeam", "KeepPts", "R", "HR", "RBI", "SB", "BAVG", "K", "W", "SV", "ERA", "WHIP", "ESPNRank")

wardict = {}

# position dictionary
pcd = ['C', '1B', '2B', '3B', 'SS', 'OF', 'DH', 'SP', 'RP']
#pcd = ['C']


#query=("select * from Players limit 20")

try:
  dbconn = mysql.connector.connect(user="matthear_hearnmd", password="", host='localhost', database='matthear_fantasybaseball')
except mysql.connector.Error as err:
  if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
    print("Something is wrong with your user name or password")
  elif err.errno == errorcode.ER_BAD_DB_ERROR:
    print("Database does not exist")
  else:
    print(err)

sd = ( "R", "HR", "RBI", "SB", "BAVG", "K", "W", "SV", "ERA", "WHIP")
statloc = {}
statlocstart=7
for stat in sd:
  statloc[stat]=statlocstart
  statlocstart+=1 

###### Get team data
print "Getting current team data..."

teamquery="select * from FantasyTeams where (Season='" + str(season) +  "');"
teamcursor = dbconn.cursor(buffered=True)
teamcursor.execute(teamquery)

for teamline in teamcursor.fetchall():
  teamid=teamline[0]
  teamname=teamline[2] 
  ownerdict[teamid]=teamname
  query="select * from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016 and DraftResult.FantasyTeam=" + str(teamid) + ") order by DraftResult.Pick;"
  tpcursor = dbconn.cursor(buffered=True)
  tpcursor.execute(query)

  totdict=OrderedDict([('R',0.0),('HR',0.0),('RBI',0.0),('SB',0.0),('BAVG',0.0),('K',0.0),('W',0.0),('SV',0.0),('ERA',0.0),('WHIP',0.0)])
  pcount = 0
  bcount = 0

  for line in tpcursor.fetchall():
    name=line[8]
    pos=line[10].split(",")[0]
    if pos == "SP" or pos == "RP":
      totdict['K']+=float(line[18])
      totdict['W']+=float(line[19])
      totdict['SV']+=float(line[20])
      totdict['ERA']+=float(line[21])
      totdict['WHIP']+=float(line[22])
      pcount+=1
    else:
      totdict['R']+=float(line[13])
      totdict['HR']+=float(line[14])
      totdict['RBI']+=float(line[15])
      totdict['SB']+=float(line[16])
      totdict['BAVG']+=float(line[17])
      bcount+=1

  for item in ["R","HR","RBI","SB","BAVG"]:
    statdict[teamid,item]=round(totdict[item]/bcount,3)
    if statdict[teamid,item] > statmax[item]:
      statmax[item]=statdict[teamid,item]
    if statdict[teamid,item] < statmin[item]:
      statmin[item]=statdict[teamid,item]

  for item in ["K","W","SV","ERA","WHIP"]:
    statdict[teamid,item]=round(totdict[item]/pcount,3)
    if statdict[teamid,item] > statmax[item]:
      statmax[item]=statdict[teamid,item]
    if statdict[teamid,item] < statmin[item]:
      statmin[item]=statdict[teamid,item]


ptsdict=OrderedDict()
ptspstat={}
for statiter in sd:
  #print statiter
  ptspstat[statiter]=float((statmax[statiter]-statmin[statiter])/(numteams-1))
  if statiter in ["R","HR","RBI","SB","BAVG"]:
    ptspstat[statiter]=ptspstat[statiter]*batters
  else:
    ptspstat[statiter]=ptspstat[statiter]*(pitchers+starters+relievers)
  #print statiter + "ptspstat: " + str(ptspstat[statiter])

for teamkey in statdict.keys():
  teamid=teamkey[0]
  statstr=teamkey[1]
  #ptspstat=float((statmax[statstr]-statmin[statstr])/11)
  if statstr=="ERA" or statstr=="WHIP":
    ptsdict[(teamid,statstr)]=12-((statdict[(teamid,statstr)]-statmin[statstr])/ptspstat[statstr])
  else:
    ptsdict[(teamid,statstr)]=((statdict[(teamid,statstr)]-statmin[statstr])/ptspstat[statstr])+1





###### Get player data
print "Getting and setting player data..."
#reset cursor
teamcursor.close()
cursor = dbconn.cursor(buffered=True)
for positer in pcd:
  updatequery="update WARAssistTable set ";
  updatecursor = dbconn.cursor(buffered=True)
  for statiter in sd:
    #print "Getting " + statiter + " for " + positer + ":"
    if positer == "DH":
      query=("select " + statiter + " from Players where not Players.PosString like '%P' and Players.Season=" + str(season) + " and Players.id not in (select Player from DraftResult where season=" + str(season) + ") order by " + statiter + " desc limit 1;")
      #query=("select " + statiter + " from Players where not Players.PosString like '%P' and Players.Season=" + str(season) + " order by " + statiter + " desc limit 1;")
    else:
      if (statiter == "ERA" or statiter == "WHIP") and (positer=="RP" or positer=="SP"):
        query=("select " + statiter + " from Players where Players.PosString like '" + positer + "' and Players.K > 0 and Players.Season=" + str(season) + " and Players.id not in (select Player from DraftResult where season=" + str(season) + ") order by " + statiter + " limit 1;")
        #query=("select " + statiter + " from Players where Players.PosString like '" + positer + "' and Players.K > 0 and Players.Season=" + str(season) + " order by " + statiter + " limit 1;")
      else:
        query=("select " + statiter + " from Players where Players.PosString like '%" + positer + "%' and Players.Season=" + str(season) + " and Players.id not in (select Player from DraftResult where season=" + str(season) + ") order by " + statiter + " desc limit 1;")
        #query=("select " + statiter + " from Players where Players.PosString like '%" + positer + "%' and Players.Season=" + str(season) + " order by " + statiter + " desc limit 1;")

    #print query
    cursor.execute(query)

    #print output

    for row in cursor.fetchall():
      #print row[0]
      if row[0] is None:
        thisstat=0
      else:
        thisstat=float(row[0])
      wardict[positer,statiter]=thisstat
      #print positer + statiter + str(thisstat)
      updatequery=updatequery + statiter + "='" + str(thisstat) + "'"
      if statiter is not "WHIP":
        updatequery=updatequery + ", "
  updatequery=updatequery + " where CalcType=3 and Season=" + str(season) + " and League=" + str(league) + " and Position='" + positer + "';"
  #print updatequery
  updateoutput=updatecursor.execute(updatequery,multi = True)
  #for ur in updateoutput:
    #print ur
  dbconn.commit()
  updatecursor.close()

#reset the cursor, just in case
cursor.close()
cursor = dbconn.cursor(buffered=True)

pp=pprint.PrettyPrinter(depth=6)
pp.pprint(wardict)

#print wardict

#query = "select * from Players where Season=" + str(season) + " limit 20;"
query = "select * from Players where Season=" + str(season) + ";"
output = cursor.execute(query,multi = True)
for playerrows in output:
  for playerrow in playerrows.fetchall():
    ID=playerrow[0]
    name=playerrow[2]
    posstr=playerrow[4]
    #print name
    for positer in posstr.split(","):
      thispos=positer.strip()
      checkquery = "select * from PlayerValues where Player_ID='" + str(ID) + "' and Position='" + thispos + "';"
      #print checkquery
      checkcursor = dbconn.cursor(buffered=True)
      checkcursor.execute(checkquery)
      checkoutput=checkcursor.fetchall()
      #print checkoutput
      #print len(checkoutput)
      paba=0
        #add player to playervalues
      updatequery="update PlayerValues set "
      insertquery="insert into PlayerValues (Player_ID, League, Season, Position, CalcType, R, HR, RBI, SB, BAVG, K, W, SV, ERA, WHIP, PABA) values ('" + str(ID) + "', '" + str(league) + "', '" + str(season) + "', '" + thispos + "', '3', "
      for stat in sd:
        diff=0
        #print stat + ":      xx" + str(playerrow[statloc[stat]]) + "xx"
        #print "wardict[" + thispos + "," + stat + "]:" + str(wardict[thispos,stat])
        if str(playerrow[statloc[stat]]) == "None":
          diff=0-wardict[thispos,stat]
        else:
          diff=float(playerrow[statloc[stat]])-wardict[thispos,stat]
        if stat is "ERA" or stat is "WHIP":
          diff = (0-diff)
        #print diff
        updatequery += stat + "='" + str(diff) + "', "
        insertquery += "'" + str(diff) + "',"
        diff=(diff/ptspstat[stat])
        if stat is "BAVG":
          paba+=(diff/batters)
        else:
          if stat is "ERA" or stat is "WHIP":
            if positer is "RP":
              paba+=(diff/50)
            else:
              paba+=(diff/150)
          else:
            paba+=diff
      updatequery+=" PABA='" + str(paba) + "' where Player_ID=" + str(ID) + " and Position = '" + positer + "';"
      insertquery+="'" + str(paba) + "');"
      if len(checkoutput) == 0:
        query=insertquery 
      else:
        query=updatequery
      print query
      updatecursor = dbconn.cursor(buffered=True)
      updateoutput=updatecursor.execute(query)
      #print updateoutput
      dbconn.commit()
      updatecursor.close()

cursor.close()
dbconn.close()


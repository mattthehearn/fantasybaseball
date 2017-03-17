#!/ramdisk/bin/python

from subprocess import Popen, PIPE
from collections import OrderedDict
import math
import mysql.connector
from mysql.connector import errorcode

season=2016

def statmax(pd, stat):
  max=0.0
  for player in pd:
    #print stat + str(player[stat])
    if player[stat] is not None:
      if max < float(player[stat]):
        max = float(player[stat])
  return max

def statmin(pd, stat):
  min=1000.0
  for player in pd:
    #print stat + str(player[stat])
    if player[stat] is not None:
      if min > float(player[stat]):
        min = float(player[stat])
  return min
 
def posfilter(pd, pos):
  retlist=[]
  for player in pd:
    if pos in player["PosString"]:
      retlist.append(player)
  return retlist

# Column name dictionary
cnd = ( "ID", "Season", "Name", "RealTeam", "PosString", "FantTeam", "KeepPts", "R", "HR", "RBI", "SB", "BAVG", "K", "W", "SV", "ERA", "WHIP", "ESPNRank")

# Players dictionary
playdict = []

# position dictionary
#pcd = ['C', '1B', '2B', '3B', 'SS', 'OF', 'DH', 'SP', 'RP']
pcd = ['C']
sd = ( "R", "HR", "RBI", "SB", "BAVG", "K", "W", "SV", "ERA", "WHIP")


query=("select * from Players where Players.Season=" + str(season) + " and Players.id not in (select Player from DraftResult where season=" + str(season) + ") limit 20")
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

cursor = dbconn.cursor(buffered=True)

output = cursor.execute(query,multi = True)

for rows in output:
  for row in rows.fetchall():
    print row
    pdr = {}
    for colnum in range(0,len(cnd)-1):
      pdr[cnd[colnum]]=row[colnum]
    #print pdr
    playdict.append(pdr)  

#statmaxdict

for position in pcd:
  smxd=[]
  smnd=[]
  print "processing " + position + ":"
  poslist=posfilter(playdict, position)
  for player in poslist:
    print player
  for stat in sd:
    smxd.append(statmax(poslist,stat))
    smnd.append(statmin(poslist,stat))
  print smxd
  print smnd

#print playdict

cursor.close()
dbconn.close()

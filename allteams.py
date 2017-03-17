#!/ramdisk/bin/python

from subprocess import Popen, PIPE
from collections import OrderedDict

query="select * from FantasyTeams where (Season=2016)"
teamstable = Popen(["mysql","-N","-u","matthear_hearnmd","","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
#output, err = teamstable.communicate(b"")
#print output


statmax = OrderedDict([('R', 0.0), ('HR', 0.0), ('RBI', 0.0), ('SB', 0.0), ('BAVG', 0.0), ('K', 0.0), ('W', 0.0), ('SV', 0.0), ('ERA', 0.0), ('WHIP', 0.0)])
statmin = OrderedDict([('R', 1000.0), ('HR', 1000.0), ('RBI', 1000.0), ('SB', 1000.0), ('BAVG', 1000.0), ('K', 1000.0), ('W', 1000.0), ('SV', 1000.0), ('ERA', 1000.0), ('WHIP', 1000.0)])

statdict = OrderedDict() 
ownerdict = OrderedDict() 

for teamline in iter(teamstable.stdout.readline,''):
  #print teamline
  teamid=teamline.split("	")[0]
  #print teamid + "	",
  teamname=teamline.split("	")[2] 
  ownerdict[teamid]=teamname
  #print teamid
  query="select * from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016 and DraftResult.FantasyTeam=" + str(teamid) + ") order by DraftResult.Pick;"
  p = Popen(["mysql","-N","-u","matthear_hearnmd","","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)

  #statdict.update(OrderedDict([((teamid,'R'),0.0),((teamid,'HR'),0.0),((teamid,'RBI'),0.0),((teamid,'SB'),0.0),((teamid,'BAVG'),0.0),((teamid,'K'),0.0),((teamid,'W'),0.0),((teamid,'SV'),0.0),((teamid,'ERA'),0.0),((teamid,'WHIP'),0.0)]))
  totdict=OrderedDict([('R',0.0),('HR',0.0),('RBI',0.0),('SB',0.0),('BAVG',0.0),('K',0.0),('W',0.0),('SV',0.0),('ERA',0.0),('WHIP',0.0)])
  #print "totdict:"
  #print totdict
  #statdict.update(OrderedDict([((teamid,'R'), 0.0), ((teamid,'HR'), 0.0), ((teamid,'RBI'): 0.0, (teamid,'SB'): 0.0, (teamid,'BAVG'): 0.0, (teamid,'K'): 0.0, (teamid,'W'): 0.0, (teamid,'SV'): 0.0, (teamid,'ERA'): 0.0, (teamid,'WHIP'): 0.0]))
  pcount = 0
  bcount = 0

  for line in iter(p.stdout.readline,''):
    #print line
    name=line.split("	")[8]
    pos=line.split("	")[10].split(",")[0]
    #print name + " " + pos
    if pos == "SP" or pos == "RP":
      #print "He's a pitcher, shitass"
      totdict['K']+=float(line.split("	")[18])
      totdict['W']+=float(line.split("	")[19])
      totdict['SV']+=float(line.split("	")[20])
      totdict['ERA']+=float(line.split("	")[21])
      totdict['WHIP']+=float(line.split("	")[22])
      pcount+=1
    else:
      #print "He's a batter, dumbnut"
      totdict['R']+=float(line.split("	")[13])
      totdict['HR']+=float(line.split("	")[14])
      totdict['RBI']+=float(line.split("	")[15])
      totdict['SB']+=float(line.split("	")[16])
      totdict['BAVG']+=float(line.split("	")[17])
      bcount+=1

  #print totdict

  for item in ["R","HR","RBI","SB","BAVG"]:
    statdict[teamid,item]=round(totdict[item]/bcount,3)
    #print str(statdict[teamid,item]) + "	",
    if statdict[teamid,item] > statmax[item]:
      #print "Changing max for " + item + " from " + str(statmax[item]) + " to " + str(statdict[teamid,item])
      statmax[item]=statdict[teamid,item]
    if statdict[teamid,item] < statmin[item]:
      statmin[item]=statdict[teamid,item]

  for item in ["K","W","SV","ERA","WHIP"]:
    statdict[teamid,item]=round(totdict[item]/pcount,3)
    #print str(statdict[teamid,item]) + "	",
    if statdict[teamid,item] > statmax[item]:
      #print "Changing max for " + item + " from " + str(statmax[item]) + " to " + str(statdict[teamid,item])
      statmax[item]=statdict[teamid,item]
    if statdict[teamid,item] < statmin[item]:
      statmin[item]=statdict[teamid,item]

  #print

#for statkey in statmax.keys():
  #print str(statkey) + " " + str(statmax[statkey]) + " " + str(statmin[statkey])

ptsdict=OrderedDict()

for teamkey in statdict.keys():
  #print teamkey
  teamid=teamkey[0]
  statstr=teamkey[1]
  ptspstat=float((statmax[statstr]-statmin[statstr])/11)
  #print "teamid: " + str(teamid)
  #print "stat: " + statstr
  #print "statmax: " + str(statmax[statstr])
  #print "statdict: " + str(statdict[(teamid,statstr)])
  #print "statmin: " + str(statmin[statstr])
  #print "ptspstat: " + str(ptspstat)
  if statstr=="ERA" or statstr=="WHIP":
    ptsdict[(teamid,statstr)]=12-((statdict[(teamid,statstr)]-statmin[statstr])/ptspstat)
  else:
    ptsdict[(teamid,statstr)]=((statdict[(teamid,statstr)]-statmin[statstr])/ptspstat)+1

#print ptsdict

oldid=0
sumpts=0.0

print "ID	Owner	R		HR		RBI		SB		BAVG		K		W		SV		ERA		WHIP		Total"
for teamkey in statdict.keys():
  teamid=teamkey[0]
  statstr=teamkey[1]
  if not(teamid==oldid):
    sumpts=0.0
    print ownerdict[teamid] + "	",
    oldid=teamid
    print str(teamid) + "	",
  print str(round(statdict[teamid,statstr],3)) + "	",
  print str(round(ptsdict[teamid,statstr],1)) + "	",
  sumpts+=ptsdict[teamid,statstr]
  if statstr is "WHIP":
    print str(round(sumpts,1)) 
#print

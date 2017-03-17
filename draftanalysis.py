#!/ramdisk/bin/python

from subprocess import Popen, PIPE

query="select CONCAT_WS(':',Players.Name,Players.PosString) from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016) order by DraftResult.Pick;"

pcd = {'SP': 0, 'RP': 0, 'C': 0, '1B': 0, '2B': 0, '3B': 0, 'SS': 0, 'OF': 0, 'DH': 0}

p = Popen(["mysql","-s","-r","-N","-u","matthear_hearnmd","-p","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
#output, err = p.communicate(b"")

#print output

pcount=0

for line in iter(p.stdout.readline,''):
  pcount+=1
  name=line.split(":")[0]
  posstr=line.split(":")[1]
  print name,
  frag=(1.00/len(posstr.split(",")))
  #print "Frag: %f" % frag
  for pos in posstr.split(","):
    #print pos.strip()
    pcd[pos.strip()]+=frag

print
print "Number of players owned: %i" % pcount

#for pos in sorted(pcd):
for pos in (["C","1B","2B","3B","SS","DH","OF","SP","RP"]):
  print pos + "	",
print
#for pos in sorted(pcd):
for pos in (["C","1B","2B","3B","SS","DH","OF","SP","RP"]):
  print str(round(pcd[pos],1)) + "	", 
print
print "12	12	12	12	12		48	48	24"
print "Plus 12 MI, 12 CI, 12 Util, 3 P, 5 Bench, 2 DL"
print "Total possible players (excluding DL): 312"
#print "Total possible players (including DL): 336"
print "Percent of draft complete: " + str(round(pcount/312.0,4)*100) + "%"
print str(312-pcount) + " picks left to make"


#query="select CONCAT_WS(':',Players.ID,Players.Season,Players.Name,Players.RealTeam,Players.PosString,Players.FantTeam,Players.KeepPts,Players.R,Players.HR,Players.RBI,Players.SB,Players.BAVG,Players.K,Players.W,Players.SV,Players.ERA,Players.WHIP,Players.ESPNRank) from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016) order by DraftResult.Pick;"

query="select * from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016) order by DraftResult.Pick;"
#p = Popen(["mysql","-s","-r","-N","-u","matthear_hearnmd","-p","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
p = Popen(["mysql","-N","-u","matthear_hearnmd","-p","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
#output, err = p.communicate(b"")

statdict = {'R': 0.0, 'HR': 0.0, 'RBI': 0.0, 'SB': 0.0, 'BAVG': 0.0, 'K': 0.0, 'W': 0.0, 'SV': 0.0, 'ERA': 0.0, 'WHIP': 0.0}
pcount = 0
bcount = 0

#print output
for line in iter(p.stdout.readline,''):
  #print line
  name=line.split("	")[8]
  pos=line.split("	")[10].split(",")[0]
  #print name + " " + pos
  if pos == "SP" or pos == "RP":
    #print "He's a pitcher, shitass"
    statdict['K']+=float(line.split("	")[18])
    statdict['W']+=float(line.split("	")[19])
    statdict['SV']+=float(line.split("	")[20])
    statdict['ERA']+=float(line.split("	")[21])
    statdict['WHIP']+=float(line.split("	")[22])
    pcount+=1
  else:
    #print "He's a batter, dumbnut"
    statdict['R']+=float(line.split("	")[13])
    statdict['HR']+=float(line.split("	")[14])
    statdict['RBI']+=float(line.split("	")[15])
    statdict['SB']+=float(line.split("	")[16])
    statdict['BAVG']+=float(line.split("	")[17])
    bcount+=1

print "Current league averages: "

print "R	HR	RBI	SB	BAVG	K	W	SV	ERA	WHIP"

for item in ["R","HR","RBI","SB","BAVG"]:
  print str(round(statdict[item]/bcount,3)) + "	",

for item in ["K","W","SV","ERA","WHIP"]:
  print str(round(statdict[item]/pcount,3)) + "	",

print

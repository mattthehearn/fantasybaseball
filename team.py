#!/ramdisk/bin/python

from subprocess import Popen, PIPE
import sys

owner=sys.argv[1]

#print owner

query="select * from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016 and DraftResult.FantasyTeam=" + str(owner) + ") order by DraftResult.Pick;"
#query="select * from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016 and DraftResult.FantasyTeam=37) order by DraftResult.Pick;"
p = Popen(["mysql","-N","-u","matthear_hearnmd","-p","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
#output, err = p.communicate(b"")

statdict = {'R': 0.0, 'HR': 0.0, 'RBI': 0.0, 'SB': 0.0, 'BAVG': 0.0, 'K': 0.0, 'W': 0.0, 'SV': 0.0, 'ERA': 0.0, 'WHIP': 0.0}
pcount = 0
bcount = 0

#print output
for line in iter(p.stdout.readline,''):
  print line.strip()
  name=line.split("	")[8]
  pos=line.split("	")[10].split(",")[0]
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

print "This team averages: "

print "R	HR	RBI	SB	BAVG	K	W	SV	ERA	WHIP"

for item in ["R","HR","RBI","SB","BAVG"]:
  print str(round(statdict[item]/bcount,2)) + "	",

for item in ["K","W","SV","ERA","WHIP"]:
  print str(round(statdict[item]/pcount,2)) + "	",

print

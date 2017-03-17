#!/ramdisk/bin/python

from subprocess import Popen, PIPE
import sys

inputs=sys.argv

inputs.pop(0)

query=(' '.join(inputs)) + ";"

print query
#query="select CONCAT_WS(':',Players.Name,Players.PosString) from DraftResult,Players where (DraftResult.Player=Players.ID and DraftResult.Season=2016) order by DraftResult.Pick;"

p = Popen(["mysql","-s","-r","-N","-u","matthear_hearnmd","-p","-e",query,"matthear_fantasybaseball"], stdin=PIPE, stdout=PIPE, stderr=PIPE)
output, err = p.communicate(b"")

print output

#!/ramdisk/bin/bash

#query='select `WARPosition`.`ID` AS `ID`,`WARPosition`.`Season` AS `Season`,`WARPosition`.`Name` AS `Name`,`WARPosition`.`RealTeam` AS `RealTeam`,`WARPosition`.`PosString` AS `PosString`,`WARPosition`.`FantTeam` AS `FantTeam`,`WARPosition`.`KeepPts` AS `KeepPts`,`WARPosition`.`R` AS `R`,`WARPosition`.`HR` AS `HR`,`WARPosition`.`RBI` AS `RBI`,`WARPosition`.`SB` AS `SB`,`WARPosition`.`BAVG` AS `BAVG`,`WARPosition`.`K` AS `K`,`WARPosition`.`W` AS `W`,`WARPosition`.`SV` AS `SV`,`WARPosition`.`ERA` AS `ERA`,`WARPosition`.`WHIP` AS `WHIP`,`WARPosition`.`ESPNRank` AS `ESPNRank`,`WARPosition`.`RPts` AS `RPts`,`WARPosition`.`HRPts` AS `HRPts`,`WARPosition`.`RBIPts` AS `RBIPts`,`WARPosition`.`SBPts` AS `SBPts`,`WARPosition`.`BAVGPts` AS `BAVGPts`,`WARPosition`.`WPts` AS `WPts`,`WARPosition`.`SVPts` AS `SVPts`,`WARPosition`.`KPts` AS `KPts`,`WARPosition`.`ERAPts` AS `ERAPts`,`WARPosition`.`WHIPPts` AS `WHIPPts`,`WARPosition`.`PosPrim` AS `PosPrim`,`WARPosition`.`WAR` AS `WAR`,`WARPosition`.`WARP` AS `WARP` from `WARPosition` where ((not(`WARPosition`.`ID` in (select `DraftResult`.`Player` from `DraftResult` where ((`DraftResult`.`League` = 1) and (`DraftResult`.`Season` = 2016))))) and WARPosition.Season=2016) order by WARP DESC limit 10' 

query='select * from Players where (Season=2016 and (not(Players.ID in (select DraftResult.Player from DraftResult where ((DraftResult.League=1) and (DraftResult.Season=2016)))))) order by ESPNRank limit 10'

echo $query

mysql -u matthear_hearnmd -p"" -e "$query" matthear_fantasybaseball;

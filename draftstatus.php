<?php
  $starttime=time();
  //print $starttime;
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);

  start_html("Draft Results");
  print_header();
 
  if (isset($_GET['season'])) {
    $season=$_GET['season'];
  } else {
    $season=date('Y');
  } 

  #get number of teams:
  $team_query="select ID from FantasyTeams where Season=$season";
  #print $team_query."<P>\n";
  $team_result=mysql_query($team_query);
  $numteams=mysql_num_rows($team_result);

  #print "numteams: ".$numteams."<P>\n";

  # for testing: $player_query="select FantasyTeams.Owner as Owner,Players.Name as PlayerName,DraftResult.Pick as DraftPick from DraftResult,Players,FantasyTeams where DraftResult.Player=Players.ID and DraftResult.Season=$season and FantasyTeams.ID=DraftResult.FantasyTeam order by DraftResult.Pick desc limit 10";
  $player_query="select Players.RealTeam as RealTeam, Players.PosString as PosString,FantasyTeams.ID as TeamID, FantasyTeams.Owner as Owner,Players.Name as PlayerName,DraftResult.Pick as DraftPick from DraftResult,Players,FantasyTeams where DraftResult.Player=Players.ID and DraftResult.Season=$season and FantasyTeams.ID=DraftResult.FantasyTeam order by DraftResult.Pick desc";
  $player_result=mysql_query($player_query);

/*
array(66) { 
	[0]=> string(3) "531" 
	["ID"]=> string(2) "37" 
	[1]=> string(2) "37" 
	["FantasyTeam"]=> string(2) "37" 
	[2]=> string(4) "2016" 
	["Season"]=> string(4) "2016" 
	[3]=> string(1) "1" 
	["League"]=> string(1) "1" 
	[4]=> string(4) "1017" 
	["Player"]=> string(4) "1017" 
	[5]=> string(3) "104" 
	["Pick"]=> string(3) "104" 
	[6]=> string(4) "1017" 
	[7]=> string(4) "2016" 
	[8]=> string(15) "Yordano Ventura" 
	["Name"]=> string(19) "Aging Phillies Fans" 
	[9]=> string(2) "KC" 
	["RealTeam"]=> string(2) "KC" 
	[10]=> string(2) "SP" 
	["PosString"]=> string(2) "SP" 
	[11]=> NULL 
	["FantTeam"]=> NULL 
	[12]=> NULL 
	["KeepPts"]=> NULL 
	[13]=> NULL 
	["R"]=> string(1) "0" 
	[14]=> NULL 
	["HR"]=> string(1) "0" 
	[15]=> NULL 
	["RBI"]=> string(1) "0" 
	[16]=> NULL 
	["SB"]=> string(1) "0" 
	[17]=> NULL 
	["BAVG"]=> string(1) "0" 
	[18]=> string(3) "183" 
	["K"]=> string(1) "0" 
	[19]=> string(2) "12" 
	["W"]=> string(1) "0" 
	[20]=> string(1) "0" 
	["SV"]=> string(1) "0" 
	[21]=> string(3) "3.6" 
	["ERA"]=> string(1) "0" 
	[22]=> string(4) "1.28" 
	["WHIP"]=> string(1) "0" 
	[23]=> string(2) "71" 
	["ESPNRank"]=> string(2) "71" 
	[24]=> string(2) "37" 
	[25]=> string(19) "Aging Phillies Fans" 
	[26]=> string(4) "Matt" 
	["Owner"]=> string(4) "Matt" 
	[27]=> string(4) "2016" 
	[28]=> string(1) "1" 
	[29]=> string(1) "0" 
	["GamesBat"]=> string(1) "0" 
	[30]=> string(1) "0" 
	[31]=> string(1) "0" 
	[32]=> string(1) "0" 
	[33]=> string(1) "0" 
	[34]=> string(1) "0" 
	[35]=> string(1) "0" 
	["Innings"]=> string(1) "0" 
	[36]=> string(1) "0" 
	[37]=> string(1) "0" 
	[38]=> string(1) "0" 
	[39]=> string(1) "0" 
	[40]=> string(1) "0" }
*/

  print "<TABLE border=1>";
  while ($player_row=mysql_fetch_array($player_result)) {
    print "<TR>";
    #$player_keys=array_keys($player_row);
    $teamid=$player_row['TeamID'];
    print "<TD><A HREF=http://www.matthearn.com/fantasybaseball/team.php?teamid=$teamid>".$player_row['Owner']."</A></TD>";
    $pick=$player_row['DraftPick'];
    $round=floor($pick/$numteams)+1;
    $rpick=$pick % $numteams;
    if ($rpick==0) {
      $rpick=12;
      $round--;
    }
    if ($pick==0) print "<TD>Keeper</TD>";
    else print "<TD>".$round."-".$rpick." (#".$pick.")</TD>";
    print "<TD>".$player_row['PlayerName']."</TD>";
    print "<TD>".$player_row['PosString']."</TD>";
    print "<TD>".$player_row['RealTeam']."</TD>";
    print "</TR>\n";
    
    #print "<TR><TD colspan=8>";
    #var_dump($player_row);
    #print "</TD></TR>\n";
    #print "<P>\n";
    #print_r($player_keys);
    #print "<P>\n";
    #print count($player_row);
  }
  print "<TABLE>\n";
 
  print "Processing time: ".(time()-$starttime)."s<P>\n";
  end_html();
?>

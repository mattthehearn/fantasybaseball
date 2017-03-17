<?php
  /*
  This doesn't work so great.  It's supposed to total up the points allocated for each team's stat totals, and it sucks at it.




  */
  $starttime=time();
  //print $starttime;
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);

  print_header();

  /*print "<HR><B>Warning</B>: The actual stats are correct (I think) but all the calculations of points and results are shit. (You can tell 'cause Kas and Brian aren't projected to run away with the league.)  Ignore them until I can, you know, fix them.  You'll know that happened when I remove this warning.<HR>";*/

  $statarr=array("R","HR","RBI","SB","BAVG","K","W","SV","ERA","WHIP");

  /*function sort_by ($a, $b) {
    return $a['R'] - $b['R'];
  }*/

  if (isset($_GET['season'])) {
    $season=$_GET['season'];
  } else {
    $season=date('Y');
  } 

  #get number of teams:
  $team_query="select ID,Owner from FantasyTeams where Season=$season order by Owner";
  #print $team_query."<P>\n";
  $team_result=mysql_query($team_query);
  $numteams=mysql_num_rows($team_result);

  #print "numteams: ".$numteams."<P>\n";
   
  foreach ($statarr as $statstr) {
    $max[$statstr]=0;
    $min[$statstr]=10000;
  }
    
  while ($team_row=mysql_fetch_array($team_result)) {
    $teamid=$team_row[ID];
    #print $teamid;
    $player_query="select Players.PosString as PosString,FantasyTeams.ID as TeamID, FantasyTeams.Owner as Owner,Players.Name as PlayerName,DraftResult.Pick as DraftPick,";
    foreach ($statarr as $statstr) {
      $player_query=$player_query."Players.$statstr as $statstr";
      if ($statstr!="WHIP") $player_query=$player_query.", ";
    }
    #$teamstat['ID']=$teamid;
    $teamstat[$teamid]['Owner']=$team_row['Owner'];
    print $player_row['Owner'];
    $player_query=$player_query." from DraftResult,Players,FantasyTeams where DraftResult.FantasyTeam=$teamid and DraftResult.Player=Players.ID and DraftResult.Season=$season and FantasyTeams.ID=DraftResult.FantasyTeam order by DraftResult.Pick desc";
    #print $player_query;
    $player_result=mysql_query($player_query);
    $teamstat[$teamid]['numplayers']=mysql_num_rows($player_result); 
    while ($player_row=mysql_fetch_array($player_result)) {
      foreach ($statarr as $statstr) {
        if ($statstr=="BAVG" and $player_row['BAVG']>0) {
	  $teamstat[$teamid]['Hits']+=($player_row['BAVG']*500);
          $teamstat[$teamid]['AtBats']+=500; 
        } else {
          if ($statstr=="ERA" and $player_row['ERA']>0) {
            if ($player_row['W'] < 10) { #consider him a reliever
              $teamstat[$teamid]['RA']+=$player_row['ERA']*60;
	    } else { #consider him a starter
              $teamstat[$teamid]['RA']+=$player_row['ERA']*150;
            }
          } else {
            if ($statstr=="WHIP" and $player_row['WHIP']>0) {
              if ($player_row['W'] < 10) { #consider him a reliever
                $teamstat[$teamid]['WHA']+=$player_row['WHIP']*60;
                $teamstat[$teamid]['Innings']+=60;
              } else { #consider him a starter
                $teamstat[$teamid]['WHA']+=$player_row['WHIP']*150;
                $teamstat[$teamid]['Innings']+=150;
              }
            } else {
              $teamstat[$teamid][$statstr]+=$player_row[$statstr];
            }
          }
        }
      }
    }
    $teamstat[$teamid]['BAVG']=round($teamstat[$teamid]['Hits']/$teamstat[$teamid]['AtBats'],3);
    $teamstat[$teamid]['ERA']=round($teamstat[$teamid]['RA']/$teamstat[$teamid]['Innings'],2);
    $teamstat[$teamid]['WHIP']=round($teamstat[$teamid]['WHA']/$teamstat[$teamid]['Innings'],2);
    foreach ($statarr as $statstr) {
      $average[$statstr]+=$teamstat[$teamid][$statstr];
      if ($max[$statstr]<$teamstat[$teamid][$statstr]) $max[$statstr]=$teamstat[$teamid][$statstr];
      if ($min[$statstr]>$teamstat[$teamid][$statstr]) $min[$statstr]=$teamstat[$teamid][$statstr];
    }
  }
  foreach ($statarr as $statstr) {
    $average[$statstr]=round($average[$statstr]/$numteams,3);
    $diff[$statstr]=$max[$statstr]-$min[$statstr];
    $statppts[$statstr]=$diff[$statstr]/($numteams-1);
    #print $statstr." max: ".$max[$statstr]."<BR>\n";
    #print $statstr." min: ".$min[$statstr]."<BR>\n";
    #print $statstr." diff: ".$diff[$statstr]."<BR>\n";
    #print $statstr." statppts: ".$statppts[$statstr]."<BR>\n";
  }

  foreach ($teamstat as $teamid=>$teamiter) {
    foreach ($statarr as $statstr) {
      if ($statstr == "ERA" or $statstr == "WHIP") {
        $teamstat[$teamid]['Pts']+=($max[$statstr]-$teamstat[$teamid][$statstr])/$statppts[$statstr]+1;
        $teampts[$teamid][$statstr]=($max[$statstr]-$teamstat[$teamid][$statstr])/$statppts[$statstr]+1;
      } else {
        $teamstat[$teamid]['Pts']+=(($teamstat[$teamid][$statstr]-$min[$statstr])/$statppts[$statstr])+1;
        $teampts[$teamid][$statstr]=($teamstat[$teamid][$statstr]-$min[$statstr])/$statppts[$statstr]+1;
      }
    }
  }

  #usort($teamstat, 'sort_by ');

  #print "<pre>";
  #var_dump($teamstat);
  #print "</pre>";

  #var_dump($teamstat[$teamid]);
  #print "<PRE>";
  ##var_dump($teamstat);
  #var_dump($teampts);
  #print "</PRE>";
  function sort_by_Pts($a, $b)
  {
    return $b['Pts'] - $a['Pts'];
  }

  #print "<HR><HR><P>\n";
  uasort($teamstat, 'sort_by_Pts');
  #print "<PRE>";
  #var_dump($teamstat);
  #var_dump($teampts);
  #print "</PRE>";

  print "<TABLE BORDER=1>\n";
  print "<TR>";
  print "<TD>Owner</TD>";
  print "<TD>Players<BR>(of 26)</TD>";
  foreach ($statarr as $statitem) {
    print "<TD>".$statitem."</TD><TD>pts</TD>"; 
  }
  print "<TD>Points</TD></TR>\n";
  foreach ($teamstat as $teamid=>$teamiter) {
    print "<TR>";
    #print "<TD>";
    #var_dump($teamiter);
    #print $teamid;
    #print "</TD>";
    print "<TD><A HREF=http://www.matthearn.com/fantasybaseball/team.php?teamid=$teamid>".$teamiter['Owner']."</A></TD>";
    print "<TD>".$teamiter['numplayers']."</TD>";
    foreach ($statarr as $statitem) {
      print "<TD>".$teamiter[$statitem]."</TD>"; 
      print "<TD>".round($teampts[$teamid][$statitem],1)."</TD>"; 
    }
    print "<TD>".round($teamiter['Pts'],2)."</TD>"; 
    print "</TR>\n";
  }
  print "<TR><TD>Average</TD>";
  foreach ($statarr as $statitem) {
    print "<TD></TD>";
    print "<TD>".$average[$statitem]."</TD>"; 
  }
  print "</TR>\n";
  print "</TABLE>\n";

  print "Processing time: ".(time()-$starttime)."s<P>\n";
?>

<?php
  $starttime=time();
  //print $starttime;
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);
  

  $player_query="select * from Players where Season=2015";
  $player_result=mysql_query($player_query);
  
  //GET COUNTS OF POSITIONS
  $posct['1BDH']=20;
  $posct['2B']=15;
  $posct['3B']=15;
  $posct['SS']=15;
  $posct['OF']=45;
  $posct['C']=10;
  $posct['SP']=54;
  $posct['RP']=24;

  $hitstats = array("R","HR","RBI","SB","BAVG");
  $pitchstats = array("K","W","SV","ERA","WHIP");

  $numteams=12;
  $numhitters=12;

  foreach ($hitstats as $stat) {
    $lsmm_query="select min(".$stat.")/".$numteams." as ".$stat."_min,max(".$stat.")/".$numteams." as ".$stat."_max from FantasyTeams where Season=2014";
    $lsmm_result=mysql_query($lsmm_query);
    $lsmm_row=mysql_fetch_array($lsmm_result);
    $lsmi[$stat]=$lsmm_row[$stat."_min"];
    $lspp[$stat]=($lsmm_row[$stat."_max"]-$lsmi[$stat])/$numhitters;
    print "Min ".$stat.": ".$lsmi[$stat]." ".$stat." per point ".$lspp[$stat]."<BR>\n";
  }

  foreach ($posct as $pos => $poscount) {
    #print "$pos<P>\n";
    if ($pos=="SP"||$pos=="RP") {
      print "$pos: ";
      foreach ($pitchstats as $stat) {
        if ($stat=="ERA"||$stat=="WHIP") { //negatives
          $pos_query="select ".$stat." from Players where PosString like '".$pos."' order by ".$stat." limit ".($poscount-1).",1";
        } else {
          $pos_query="select ".$stat." from Players where PosString like '".$pos."' order by ".$stat." desc limit ".($poscount-1).",1";
        }
        #print $pos_query."<BR>\n";
        $pos_result=mysql_query($pos_query);
        $pos_row=mysql_fetch_array($pos_result);
        print $stat.": ".$pos_row[$stat]." ";
      }
      print "<BR>\n";
    } else {
      print "$pos: ";
      foreach ($hitstats as $stat) {
        if ($pos=="1BDH") {
          $pos_query="select ".$stat." from Players where PosString like '1B%' or PosString like 'DH%' order by ".
                     $stat." desc limit ".($poscount-1).",1";
        } else {
          $pos_query="select ".$stat." from Players where PosString like '".$pos."' order by ".$stat." desc limit ".($poscount-1).",1";
        }
        #print $pos_query."<BR>\n";
        $pos_result=mysql_query($pos_query);
        $pos_row=mysql_fetch_array($pos_result);
        print $stat.": ".$pos_row[$stat]." ";
      } //foreach $hitstats
      print "<BR>\n";
    }
  } //foreach $posct
    
  //PRINT FORM FOR COUNTS OF POSITIONS, INCLUDING "AVAILABLE" OPTION
  
  //Look up replacement value for each position, set array to store 'em
  //WarTMP field?  Set it for each player and then use the DB sorting functions instead of dealing with a shit ton of arrays?  That's a lot of fuckin' queries doe just to look up one list.
  
  
  while ($player_row=mysql_fetch_array($player_result))
  {
    //var_dump($player_row);
    print $player_row['Name']."<P>\n";
  }
  
  
  
  
  
  /*
    $last_pick=$draft_row['pick'];
    if ($num_picks==$last_pick) {
      $next_pick=$last_pick+1;
      $league_query="select * from fantasy_league where id=".$_GET['league'];
      //print $league_query."<BR>\n";
      $league_result=mysql_query($league_query,$db);
      $league_row=mysql_fetch_array($league_result);
      $team_count=$league_row['num_teams'];
      //var_dump($league_row);
      //print "team count: ".$team_count."<BR>";       
      $rpick=($next_pick % $team_count);
      if ($rpick==0) $rpick=$team_count;
      $round=floor(($next_pick-1)/$team_count)+1;
      if (($round % 2)==0) $next_pick_team=($team_count-$rpick);
      else $next_pick_team=$rpick;
      if ($next_pick_team==0) $next_pick_team=$team_count;
      $team_query="select * from fantasy_team where draft_pick=".$next_pick_team." and league=".$_GET['league']." and season=2014";
      //print $team_query."<P>\n";
      $team_result=mysql_query($team_query,$db);
      $team_row=mysql_fetch_array($team_result);
      $player_query="select * from fantasy_player where id=".$_GET['draft'];
      //print $player_query."<BR>\n";
      $player_result=mysql_query($player_query,$db);
      $player_row=mysql_fetch_array($player_result);
      print $player_row['name']."<BR>\n"; 
      print "Next pick: #".$next_pick." (Pick #".$rpick." of Round #".$round."), ".$team_row['name']." (Team #".$team_row['id'].")<BR>\n";
      print "<FORM name=xval action=http://www.matthearn.com/nfl/player_xvals.php?league=".$_GET['league']." method=post>\n";
      print "<INPUT type=HIDDEN name=draft_confirm value=".$_GET['draft'].">";
      print "<input type=submit value=Submit>\n";
      print "</FORM>\n";
    } else {
      print "INCONSISTENT PICK COUNTS<P>\n";
    }
  } else {
    //First, gotta process new picks from POST.
    if (isset($_POST['draft_confirm'])) {
      print "Gonna draft a player yay<BR>\n";
      #$player_id=$_POST['draft_confirm'];
      print "Player ID: ".$_POST['draft_confirm']."<BR>\n";
      //Check if the picks are consistent:
      $draft_query="select pick from fantasy_draft order by pick";
      $draft_result=mysql_query($draft_query);
      $num_picks=mysql_num_rows($draft_result); 
      $draft_query="select pick from fantasy_draft order by pick desc limit 1";
      $draft_result=mysql_query($draft_query);
      $draft_row=mysql_fetch_array($draft_result);
      $last_pick=$draft_row['pick'];
      if ($num_picks==$last_pick) {
        //Check if player is already drafted
        $draftck_query="select * from fantasy_draft where player=".$_POST['draft_confirm'];
        $draftck_result=mysql_query($draftck_query,$db);
        if (mysql_num_rows($draftck_result)==0) {
          print "draftck_query ".$draftck_query."<BR>\n";
          $next_pick=$last_pick+1;
          $league_query="select * from fantasy_league where id=".$_GET['league'];
          print $league_query."<BR>\n";
          $league_result=mysql_query($league_query,$db);
          $league_row=mysql_fetch_array($league_result);
          $team_count=$league_row['num_teams'];
          //var_dump($league_row);
          print "team count: ".$team_count."<BR>";       
          $rpick=($next_pick % $team_count);
          if ($rpick==0) $rpick=$team_count;
          $round=floor(($next_pick-1)/$team_count)+1;
          if (($round % 2)==0) $next_pick_team=($team_count-$rpick);
          else $next_pick_team=$rpick;
          if ($next_pick_team==0) $next_pick_team=$team_count;
          $team_query="select * from fantasy_team where draft_pick=".$next_pick_team." and league=".$_GET['league']." and season=2014";
          //print $team_query."<P>\n";
          $team_result=mysql_query($team_query,$db);
          $team_row=mysql_fetch_array($team_result);
          $player_query="select * from fantasy_player where id=".$_POST['draft_confirm'];
          print $player_query."<BR>\n";
          $player_result=mysql_query($player_query,$db);
          $player_row=mysql_fetch_array($player_result);
          print $player_row['name']."<BR>\n"; 
          //print "Next pick: #".$next_pick." (Pick #".$rpick." of Round #".$round."), ".$team_row['name']."<BR>\n";
          print "Next pick: #".$next_pick." (Pick #".$rpick." of Round #".$round."), ".$team_row['name']." (Team #".$team_row['id'].")<BR>\n";
          $update_query="insert into fantasy_draft (league, season, team, pick, player) values ('".
                      $_GET['league']."','2014','".$team_row['id']."','".$next_pick."','".$_POST['draft_confirm']."')";
          print $update_query."<P>\n";
          $update_result=mysql_query($update_query,$db);
        } else print "<B>That player is already drafted!</B><BR>\n";
      } else {
        print "INCONSISTENT PICK COUNTS<P>\n";
      }
    }
    //Gonna need a form up top, yo, and gotta get the gets.
    print "<HTML><HEAD><TITLE>Player Xvalues and Auction Prices</TITLE></HEAD><BODY>\n";
    $pos_array=array("QB","RB","WR","TE","D/ST","K");
    //If a valid league_id is provided, then skip the form and the gets and shit.
    if (isset($_GET['league'])) {
      $league_query="select * from fantasy_league where id=".$_GET['league'];
      #print $league_query;
      $league_result=mysql_query($league_query,$db);
      if (mysql_num_rows($league_result)>0) {
        $league_bool=1;
        $league_row=mysql_fetch_array($league_result);
        #var_dump($league_row);
        $team_count=$league_row['num_teams'];
        $player_count['QB']=$league_row['num_qb']*$team_count; 
        $player_count['RB']=$league_row['num_rb']*$team_count; 
        $player_count['WR']=$league_row['num_wr']*$team_count; 
        $player_count['TE']=$league_row['num_te']*$team_count;
        $player_count['FLEX']=$league_row['num_flex']*$team_count;
        $player_count['K']=$league_row['num_k']*$team_count;
        $player_count['D/ST']=$league_row['num_dst']*$team_count;
        $player_count['Bench']=$league_row['num_bench']*$team_count;
        $team_money=$league_row['salary'];
        $league_name=$league_row['name'];
        $total_money=($team_money-$league_row['num_bench'])*$team_count;
        print "League: ".$league_name."<P>\n";
        $flex_count=0;
        foreach ($pos_array as &$position) {
          $pos_query="select * from fantasy_player,fantasy_draft where fantasy_player.id=fantasy_draft.player and fantasy_player.position='".$position."'";
          $pos_result=mysql_query($pos_query,$db);
          $pos_drafted[$position]=mysql_num_rows($pos_result);
          $pos_needs[$position]=$player_count[$position]-$pos_drafted[$position];
          //print "pos_needs ".$pos_needs[$position]." ".$position."<BR>\n";
          if (($pos_needs[$position]<0)&&(($position=='RB')||($position=='WR')||($position='TE'))) $flex_count-=$pos_needs[$position];
          //print "number of ".$position."s drafted: ".mysql_num_rows($pos_result)."<BR>\n";
          //print $pos_query."<P>\n";
          print $position.": ".$pos_drafted[$position]."/".$player_count[$position]."<BR>\n";
          //print "flex_count: ".$flex_count."<BR>\n";
        } //foreach position
        print "FLEX: ".$flex_count."/".$player_count['FLEX']."<BR>\n";
        print "Bench: ".$player_count['Bench']."<BR>\n";
        $draft_query="select pick from fantasy_draft order by pick";
        $draft_result=mysql_query($draft_query);
        $num_picks=mysql_num_rows($draft_result); 
        $draft_query="select pick from fantasy_draft order by pick desc limit 1";
        $draft_result=mysql_query($draft_query);
        $draft_row=mysql_fetch_array($draft_result);
        $last_pick=$draft_row['pick'];
        //print "num_picks: ".$num_picks." last_pick: ".$last_pick."<P>\n";
        if ($num_picks==$last_pick) { //the picks match up, so let's just print the next one's info.
          $next_pick=$last_pick+1;
          $rpick=($next_pick % $team_count);
          if ($rpick==0) $rpick=$team_count;
          $round=floor(($next_pick-1)/$team_count)+1;
          if (($round % 2)==0) $next_pick_team=($team_count-$rpick);
          else $next_pick_team=$rpick;
          if ($next_pick_team==0) $next_pick_team=$team_count;
          //print "next_pick_team: ".$next_pick_team."<BR>\n";
          $team_query="select * from fantasy_team where draft_pick=".$next_pick_team." and league=".$_GET['league']." and season=2014";
          //print $team_query."<P>\n";
          $team_result=mysql_query($team_query,$db);
          $team_row=mysql_fetch_array($team_result);
          //print "Next pick: #".$next_pick." (Pick #".$rpick." of Round #".$round."), ".$team_row['name'];
          print "Next pick: #".$next_pick." (Pick #".$rpick." of Round #".$round."), ".$team_row['name']." (Team #".$team_row['id'].")<BR>\n";
        } else {
          print "The last pick and the number of picks don't match!  Fix yo shit ninja!<P>\n";
        } //confirm pick counts
      }    
    } else { //if check league is in the database, no valid league id provided, so just print the form and get the gets.####
      print "<TABLE border=1><TR><TD></TD>";
      foreach ($pos_array as &$position) {
        print "<TD>".$position."</TD>";
      }
      print "<TD>Flex</TD><TD>Bench</TD><TD>Salary<BR>Cap</TD><TD>Number<BR>of Teams</TD></TR>\n";
      print "<FORM name=xval action=http://www.matthearn.com/nfl/player_xvals.php method=get>\n";
      print "<TR><TD>League totals:</TD>";
      foreach ($pos_array as &$position) {
        if (isset($_GET[$position])) print "<TD><INPUT TYPE=text name=".$position." size=2 value=".$_GET[$position]."></INPUT></TD>";
        else if (($position=="RB")||($position=="WR")) print "<TD><INPUT TYPE=text name=".$position." size=2 value=2></INPUT></TD>";
             else print "<TD><INPUT TYPE=text name=".$position." size=2 value=1></INPUT></TD>";
      }
      
      if (isset($_GET['FLEX'])) print "<TD><INPUT TYPE=text name=FLEX size=2 value=".$_GET['FLEX']."></INPUT></TD>";
      else print "<TD><INPUT TYPE=text name=FLEX size=2 value=1></INPUT></TD>";
      if (isset($_GET['Bench'])) print "<TD><INPUT TYPE=text name=Bench size=2 value=".$_GET['Bench']."></INPUT></TD>";
      else print "<TD><INPUT TYPE=text name=Bench size=2 value=5></INPUT></TD>";
      if (isset($_GET['team_money'])) print "<TD><INPUT TYPE=text name=team_money size=3 value=".$_GET['team_money']."></INPUT></TD>";
      else print "<TD><INPUT TYPE=text name=team_money size=3 value=200></INPUT></TD>";
      if (isset($_GET['team_count'])) print "<TD><INPUT TYPE=text name=team_count size=2 value=".$_GET['team_count']."></INPUT></TD>";
      else print "<TD><INPUT TYPE=text name=team_count size=2 value=12></INPUT></TD>";
      print "<TD><input type=submit value=Submit></TD>";
      print "</TR></TABLE>\n";
        
      if (isset($_GET['team_count'])) $team_count=$_GET['team_count'];
      else $team_count=12;
      if (isset($_GET['FLEX'])) $player_count['FLEX']=$_GET['FLEX'];
      else $player_count['FLEX']=$team_count;
      if (isset($_GET['Bench'])) $player_count['Bench']=$_GET['Bench'];
      else $player_count['Bench']=$team_count*5;
      if (isset($_GET['team_money'])) $total_money=($_GET['team_money']-$_GET['Bench'])*$team_count;
      else $total_money=(200-($player_count['Bench']/$team_count))*$team_count;

    }//get league check 
    
    //$pos_array=array("QB","RB","WR","TE","D/ST","K"); //,"FLEX","Bench");
    $nonfbcount=0;
    foreach ($pos_array as &$position) {
      $count=0;
      if (isset($_GET[$position])) $player_count[$position]=$_GET[$position];
      else if (($position=="RB")||($position=="WR")) $player_count[$position]=2*$team_count;
           else $player_count[$position]=$team_count;
      
      $pos_count[$position]=$player_count[$position];
      //print "Checking ".$position.", ".$player_count[$position]." players<BR>\n";
      $nonfbcount+=$player_count[$position];
      $repval_query="select fantasy_points from fantasy_player where position='".$position."' order by fantasy_points desc";
      $repval_result=mysql_query($repval_query,$db);
      while ($repval_row=mysql_fetch_array($repval_result)) {
        $repval[$position][$count]=$repval_row['fantasy_points'];
        $count++;
      } //while
      //print $position." ".$player_count[$position]." ".$repval[$position][$count]."<P>\n";
    } //foreach position
    //print "FLEX ".$player_count['FLEX']."<P>\n";
    //print "Total non-FLEX, non-Bench players: ".$nonfbcount."<BR>\n";
    //foreach ($pos_array as &$position) {
      //print $position.": ".$pos_count[$position]." players<BR>\n";
    //} 
    //print "Adding FLEX Players...<BR>\n";
    for ($count=0;$count<$player_count['FLEX'];$count++) {
      $rbrep=$repval['RB'][$pos_count['RB']];
      $wrrep=$repval['WR'][$pos_count['WR']];
      $terep=$repval['TE'][$pos_count['TE']];
      //print "repvals: $RB: ".$rbrep." WR: ".$wrrep." TE: ".$terep."<BR>\n";
      if (($rbrep>=$wrrep)&&($rbrep>=$terep)) { //RB is the biggest, or tied for the biggest, so use RB. The tie in the pair should get used up next time anyway.
        $pos_count['RB']++;
      } 
      if (($wrrep>$rbrep)&&($wrrep>=$terep)) { //WR is bigger than RB, and at least tied with TE, so use WR.
        $pos_count['WR']++;    
      }
      if (($terep>$rbrep)&&($terep>$wrrep)) { //TE is definitely the biggest without any ties, so use TE.
        $pos_count['TE']++;
      }
    } //for number of flex positions
    
    //print "Processing time so far: ".(time()-$starttime)."s<P>\n";
    $player_query="select * from fantasy_player order by fantasy_points desc";
    $player_result=mysql_query($player_query,$db);
    $count=0;
    $xvalcount=0;
    while ($player_row=mysql_fetch_array($player_result)) {
      $player_name[$count]=$player_row['name'];
      $player_pos[$count]=$player_row['position'];
      $player_team[$count]=$player_row['team'];
      $player_pts[$count]=$player_row['fantasy_points'];
      $player_id[$count]=$player_row['id'];
      //print "count:".$count." player_pos[count]:".$player_pos[count]." position".$position
      $player_xval[$count]=$player_pts[$count]-$repval[$player_pos[$count]][$pos_count[$player_pos[$count]]];
      //print $repval[$player_pos[$count]][$pos_count[$position]];
      //print $count." ".$player_name[$count]." ".$player_pos[$count]." ".$player_team[$count]." ".$player_pts[$count]." ".$player_xval[$count]."<BR>\n";
      if ($player_xval[$count]>=0) $xvalcount+=$player_xval[$count];
      //print $player_xval[$count];
      $count++;
    } //while get all the players
    $money_per_xpt=floatval($total_money)/floatval($xvalcount);
    //print $total_money."/".$xvalcount."=".$money_per_xpt."<BR>\n";
    arsort($player_xval);
    print "<TABLE border=1><TR><TD>Rank</TD><TD>Player</TD><TD>Pos</TD><TD>Team</TD><TD>Points</TD><TD>Xval</TD><TD>Price</TD></TR>\n";
    $rank_count=1;
    foreach ($player_xval as $id => $xval)
    {
      print "<TR><TD>".$rank_count."</TD><TD>".$player_name[$id]."</TD><TD>".$player_pos[$id]."</TD><TD>".$player_team[$id]."</TD><TD>".$player_pts[$id]."</TD><TD>".$player_xval[$id]."</TD><TD>";
      print round($money_per_xpt*floatval($player_xval[$id]),0)."</TD>";
      $draft_query="select * from fantasy_draft where league='".$_GET['league']."' and player='".$player_id[$id]."'";
      //print "<TD>".$draft_query."</TD>";
      $draft_result=mysql_query($draft_query,$db);
      if (mysql_num_rows($draft_result)>0) {
      $draft_row=mysql_fetch_array($draft_result);
      print "<TD>Pick #".$draft_row['pick']." by Team #".$draft_row['team']."</TD>";
      } else {
        print "<TD><A HREF=http://www.matthearn.com/nfl/player_xvals.php?draft=".$player_id[$id]."&league=".$_GET['league'].">draft him</A></TD>";
      } //iterate the player arrays
      print "</TR>\n";
      $rank_count++;
    }//if isset get league
  print "</TABLE>";
  } //isset get_draft  
  print "Processing time: ".(time()-$starttime)."s<P>\n";
  */
?>

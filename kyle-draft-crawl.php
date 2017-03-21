<?php
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("matthearn.com", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);

  $season=date('Y');
  
  $url="http://sop.vtkyle.com/index.php/?load=drafts/recent";
  print $url."<P>\n";
  $html = file_get_html($url);
  foreach ($html->find('tr') as $tr) {
    #print $tr."<BR>\n";
    $fields=preg_split('/(<[^>]*>)+/', $tr);
    if ((strlen($fields[4])>0) && ($fields[4] != "Player")) {
      #print_r($fields);
      #print $fields[4]."\n";
      $round=$fields[1];
      $rpick=$fields[2];
      $owner=$fields[3];
      $playername=$fields[4];
      $playerteam=$fields[5];
      $query="select * from Players where Name='".$playername."' and Season='".$season."'";
      #print $query."\n";
      $result=mysql_query($query);
      #print mysql_num_rows($result)."\n";
      if (mysql_num_rows($result) != 1) {
        print "Looks like ".$playername." appears ".mysql_num_rows($result)." times for ".$season." in your Players table; should be 1.  Fix that, bae.\n";
      } else {
        $playerrow=mysql_fetch_array($result);
        $playerid=$playerrow['ID'];
        $query="select * from DraftResult where Player='".$playerid."' and Season='".$season."'";
        #print $query."\n"; 
        $result=mysql_query($query);
        if (mysql_num_rows($result) > 1) {
          print "Looks like ".$playername." appears ".mysql_num_rows($result)." times for ".$season." in your DraftResults table; should be 1.  Fix that, bae.\n";
        } else {
          if (mysql_num_rows($result) == 0) {
            print "Looks like you don't have draft pick ".$round.", ".$rpick.", ".$playername.", ".$owner." in your DraftResults table!  I guess I'll add it, then.\n";
            $teamquery="select * from FantasyTeams where Season='".$season."'";
            $teamresult=mysql_query($teamquery);
            $numteams=mysql_num_rows($teamresult);
            $teamquery="select * from FantasyTeams where Season='".$season."' and Owner='".$owner."'";
            $teamresult=mysql_query($teamquery);
            if (mysql_num_rows($teamresult) != 1) {
              print "Looks like ".$owner." doesn't appear in your FantasyTeams database for ".$season.".  Fix that, bae.\n";
            } else {
              $opick=(($round-1)*$numteams)+$rpick;
              $ownerrow=mysql_fetch_array($teamresult);
              $ownerid=$ownerrow['ID'];
              $updatequery="insert into DraftResult (FantasyTeam, Season, League, Player, Pick) values ('".$ownerid."', '".$season."', '1', '".$playerid."', '".$opick."')";
              print $updatequery."\n";
              $updateresult=mysql_query($updatequery) or die("wtf");
            }
          }
        }
          
      }
    }
  } //for each tr
?>

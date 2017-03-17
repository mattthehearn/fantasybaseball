<?php
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_nfllines",$db);
  
  switch ($_GET['position']) {
    case "QB":
      $position=0;
      break;
    case "RB":
      $position=2;
      break;
    case "WR":
      $position=4;
      break;
    case "TE":
      $position=6;
      break;
    case "D/ST":
      $position=16;
      break;
    case "K":
      $position=17;
      break;
  }
  $page=$_GET['page'];
  if ($page=="") $page=0;
  if (!isset($position)) {
    print "<HTML><HEAD><TITLE>Error: no position in URL</TITLE></HEAD><BODY>Need a position!</BODY></HTML>";
  } else {
    $url="http://games.espn.go.com/ffl/tools/projections?&proTeamId=null&slotCategoryId=".$position."&startIndex=".($page*40);
    print $url."<P>\n";
    $html = file_get_html($url);
    foreach ($html->find('tr') as $tr) {
      //print $tr->class."<BR>";
      if (($tr->class=="pncPlayerRow playerTableBgRow0")||($tr->class=="pncPlayerRow playerTableBgRow1")) {
        $tr_contents=str_get_html($tr->innertext);
        //print $tr_contents."<P>";
        foreach ($tr_contents->find('td') as $td) {
          switch ($td->class) {
            case "playertableData":
              //print "stripped contents of the rank TD: ".strip_tags($td->innertext)."<BR>\n";
              print "insert into fantasy_player (rank, name, team, position, injury_status, ";
              print "passing_comp, passing_att, passing_yd, passing_td, passing_int, ";
              print "rushing_att, rushing_yd, rushing_td, receiving_cat, receiving_yd, receiving_td, fantasy_points) values ('".$td->innertext."', ";
              break;
            case "playertablePlayerName":
              $td_name=strip_tags($td->innertext);
              //print "stripped contents of the name TD: ".$td_name."<BR>\n";
              if ($position=16) {
                $player_pos="D/ST";
                $td_namearr=explode(" ", $td_name);
                $player_name=trim($td_namearr[0]);
                $player_team=team_abbr($player_name);
              } else {
                $td_namearr=explode(",", $td_name);
                $player_name=trim($td_namearr[0]);
                //print "before the comma: ".$td_namearr[0]." after the comma: ".$td_namearr[1]."<BR>\n";
                $teampos_arr=explode("&nbsp;", trim($td_namearr[1]));
                $player_team=trim($teampos_arr[0]);
                $player_pos=trim($teampos_arr[1]);
                $player_inj=trim($teampos_arr[2]);
                if ($player_inj=="") $player_inj=trim($teampos_arr[3]);
              }
              print "\"".$player_name."\", '".$player_team."', '".$player_pos."', '".$player_inj."', ";
              break;
            case "playertableStat":
              $td_stat=strip_tags($td->innertext);
              $stat_arr=explode("/", $td_stat);
              print "'".$stat_arr[0]."', ";
              if ($stat_arr[1]!="") print "'".$stat_arr[1]."', ";
              break;
            case "playertableStat appliedPoints":
              $td_stat=strip_tags($td->innertext);
              print "'".$td_stat."');<BR>\n";
              break;
            default:
              print "TD class: ".$td->class."<BR>\n";
              print "TD contents: ".$td->innertext."<BR>\n";
              break;
          } //switch td->class
        } //foreach td
      } //if it's a player row
    } //for each tr
  } //check for position in url
?>

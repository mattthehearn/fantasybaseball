<?php
  include('simple_html_dom.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_nfllines",$db);
  $url="http://espn.go.com/nfl/powerrankings";
  $html = file_get_html($url);
  
  //Look for the form to see which week this is.
  foreach ($html->find('form') as $form) {
    $form_contents=str_get_html($form->innertext);
    foreach ($form_contents->find('select') as $select) {
      if ($select->class=="tablesm") {
        //print $select->innertext;
        $select_contents=str_get_html($select->innertext);
        foreach ($select_contents->find('option') as $option) {
          if ($option->selected==1) {
            $week=strip_tags($option->innertext);
            if ($week=="Pre") print "<HTML><HEAD><TITLE>Preseason Rankings</TITLE></HEAD><BODY><H2>Preseason Rankings</H2><P>\n";
            else print "<HTML><HEAD><TITLE>Week ".$option->innertext." Rankings</TITLE></HEAD><BODY><H2>Week ".$option->innertext." Rankings</H2><P>\n";
          }
        }
      }
    }
  }
 
  if ($week=="Pre") print "<A HREF=http://www.matthearn.com/nfl/weekly_matchups.php?week=1>Matchup Info</A>";
  else print "<A HREF=http://www.matthearn.com/nfl/weekly_matchups.php?week=".$week.">Matchup Info</A>";
 
  //Check and see if this week has ESPN rankings in the database; if not, offer the button to import them.
  $rank_query="select * from ranking where source=1 and week=".$week;
  //print $rank_query;
  $rank_result=mysql_query($rank_query,$db);
  if (mysql_num_rows($rank_result)<32) {
    print "<FORM name=import action=http://www.matthearn.com/nfl/espn_rankings.php method=post><INPUT type=hidden name=import value=yes><INPUT type=submit value='Import These Rankings'></FORM><BR>\n";
  }
  
  //print "<TABLE border=1><TR><TD>Rank</TD><TD>Team</TD><TD>Opponent</TD></TR>\n";
  foreach ($html->find('tr') as $tr) {
    if (($tr->class=="oddrow")||($tr->class=="evenrow")) {
      //print $tr->class.": ".$tr->outertext."<BR>";
      //print "<TR>";
      $tr_contents=str_get_html($tr->innertext);
      foreach ($tr_contents->find('td') as $td) {
        //Check for a class
        if ($td->class=="pr-rank") { //this is the td with the rank in it.
          //if (intval($td->innertext)==$td->innertext) 
          //print "<TD>".$td->innertext."</TD>";
          $currank=strip_tags($td->innertext);
          //print "<TD>rar".intval($td->innertext)."rar</TD>";
          //$lastrank=intval($td->innertext);
        } else { //It doesn't have the rank, and the TDs have no tags, so we have to parse further.
          //We need the team, which is contained in an <A> tag.
          $td_contents=str_get_html($td->innertext);
          foreach ($td_contents->find('a') as $a) {
            //print "<TD>".$a->href."</TD><TD>".$a->innertext."</TD>";
            //If the contents is just a stupid DIV tag, then ditch it.
            //$a_contents=str_get_html($a->innertext);
            //if (!$a->text="") 
            $ait_notag=strip_tags($a->innertext);
            if (!$ait_notag=="") {
              //print "<TD>".$ait_notag."</TD>";
              $teamrank_arr[$currank]=$ait_notag;
              
              //print "<TD>wetfg".strip_tags($td->innertext)."wtf</TD>";
             
	      //print "<TD>".$team_row['id']."</TD>";
            }
          }
          //print "<TD>".$td->innertext."</TD>";
        }
      }
      //print "</TR>\n";
    }
  }
  //print "</TABLE>\n";
  print "<TABLE border=1><TR><TD>Rank</TD><TD>Team</TD><TD>Projected<BR>Spread</TD><TD>Opponent</TD><TD>Rank</TD></TR>\n";
  for ($rank=1;$rank<=32;$rank++) {
    print "<TR><TD>".$rank."</TD>";
    //print "<TD>".$teamrank_arr[$rank]."</TD>";
    $team_query="select * from team where name='".$teamrank_arr[$rank]."'";
    $team_result=mysql_query($team_query,$db);
    $team_row=mysql_fetch_array($team_result);
    print "<TD>".$team_row['city']." ".$team_row['name']."</TD>";
    if ($week=="Pre") $matchup_query="select * from matchup where home='".$team_row['id']."' or away='".$team_row['id']."' and week='1'";
    else $matchup_query="select * from matchup where home='".$team_row['id']."' or away='".$team_row['id']."' and week='".$week."'";
    //print "<TD>".$matchup_query."</TD>";
    $matchup_result=mysql_query($matchup_query,$db);
    $matchup_row=mysql_fetch_array($matchup_result);
    //print "<TD>".$matchup_row['away']."</TD>";
    //print "<TD>";
    if ($matchup_row['away']==$team_row['id']) {
      //print "@";
      $away=1;
      $opp_query="select * from team where id='".$matchup_row['home']."'";
    } else {
      $away=0;
      $opp_query="select * from team where id='".$matchup_row['away']."'";
    }
    $opp_result=mysql_query($opp_query,$db);
    $opp_row=mysql_fetch_array($opp_result);
    
    print "</TD>";
    for ($opp_rankc=1;$opp_rankc<=32;$opp_rankc++) {
      if ($teamrank_arr[$opp_rankc]==$opp_row['name']) {
        //print "<TD>".$opp_rankc."</TD>";
        $opp_rank=$opp_rankc;
      }
    }
    if ($away==1) $proj_spread=((floatval($rank)-floatval($opp_rank))/2)+3;
    else $proj_spread=((floatval($rank)-floatval($opp_rank))/2)-3;
    print "<TD>".$proj_spread."</TD>";
    print "<TD>";
    if ($away==1) print "at ";
    else print "vs. ";
    print $opp_row['city']." ".$opp_row['name'];      
    print "</TD>";
    print "<TD>".$opp_rank."</TD>";
    print "</TR>\n";
    if ($_POST['import']=='yes') {
      $addquery="insert into ranking (week, source, team, rank) values ('".$week."','1','".$team_row['id']."','".$rank."')";
      $addresult=mysql_query($addquery,$db);
      //print $addquery;
    }
  }
  print "</TABLE>";
  print "</BODY></HTML>";
  /*$tags = $xpath->query('//td');
  foreach ($tags as $tag) {
    var_dump(trim($tag->nodeValue));
    var_dump($tag);
    print "<BR>";
  }*/
  
  /*foreach($doc->getElementsByTagName('td') as $td) {
    print $td->getAttribute('class');
    var_dump($td);
    echo "<br />";
  }*/

?>

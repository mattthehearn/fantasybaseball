<?php
  include('simple_html_dom.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);
  
  $poslookup = array(
    "C" => 0,
    "1B" => 1,
    "2B" => 2,
    "3B" => 3,
    "SS" => 4,
    "MI" => 6,
    "CI" => 7,
    "IF" => 19,
    "OF" => 5,
    "DH" => 11,
    "SP" => 14,
    "RP" => 15
  );
  //print var_dump($poslookup);

  if (isset($_GET['pos'])) {
    if ($_GET['pos']=="P")
      $url="http://games.espn.go.com/flb/tools/projections?slotCategoryGroup=2";
    else
      $url="http://games.espn.go.com/flb/tools/projections?slotCategoryId=".$poslookup[$_GET['pos']];
    if (isset($_GET['next'])) $url=$url."&startIndex=".$_GET['next'];
  } else {
    $url="http://games.espn.go.com/flb/tools/projections";
    if (isset($_GET['next'])) $url=$url."?startIndex=".$_GET['next'];
  }

  //print $url;
  
  $html = file_get_html($url);
  
  $nexturl="http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php";  
  print "<A HREF=http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php>Batters</A> ";
  if ($_GET['pos']=="P") { 
    print "<A HREF=http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=P><B>Pitchers</B></A> ";
    $nexturl="http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=P";  
  } else 
    print "<A HREF=http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=P>Pitchers</A> ";

  foreach($poslookup as $pos => $value)
  {
    if ($_GET['pos']==$pos) {
      print "<A HREF=http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=".$pos."><B>".$pos."</B></A> ";
      $nexturl="http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=".$pos;
    } else
      print "<A HREF=http://www.matthearn.com/fantasybaseball/espn_playerprojections_sql.php?pos=".$pos.">".$pos."</A> ";
  }
  if (isset($_GET['next'])) 
    if (isset($_GET['pos'])) $nexturl=$nexturl."&next=".($_GET['next']+40);
    else $nexturl=$nexturl."?next=".($_GET['next']+40);
  else 
    if (isset($_GET['pos'])) $nexturl=$nexturl."&next=40";
    else $nexturl=$nexturl."?next=40";
  
  print "<BR><A HREF=".$nexturl.">Next 40</A><P>";
  
  foreach ($html->find('tr') as $tr) {
    if (($tr->class=="pncPlayerRow playerTableBgRow0")||($tr->class=="pncPlayerRow playerTableBgRow1")) {
      //print $tr->class.": ".$tr->outertext."<BR>";
      $tr_contents=str_get_html($tr->innertext);
      $statcount=0;
      foreach ($tr_contents->find('td') as $td) {
        //Check for a class
         if ($td->class=="playertableStat") { //this is the td with the rank in it.
          $stat[$statcount]=$td->innertext;
          //print "stat ".$statcount.": ".$td->innertext;
          $statcount++;
           //print "<TD>".$td->innertext."</TD>";
           //print "<TD>".$td->outertext."</TD>";
        }  
              
        if ($td->class=="playertablePlayerName") { 
          //if (intval($td->innertext)==$td->innertext) 
          
          $gotname==0;
          $name_arr=explode("&nbsp;", $td->innertext);
          $name_team=$name_arr[0];
          $positions=strip_tags($name_arr[1]);
          $nt_arr=explode(",", $name_team);
          $name_html=$nt_arr[0];
          $team=trim($nt_arr[1]);
          $name_cont=str_get_html($name_html);

          foreach ($name_cont->find('a') as $namea) {
            if ($gotname==0) {
              $playername=$namea->innertext;
//              print "<TD>".$namea->innertext."</TD>";
              $gotname==1;
            }
          }
          //print "<TD>".$team."</TD><TD>".$positions."</TD>";
          //print "<TD>".$td->outtertext."</TD>";
        } 
        
        if ($td->class=="playertableData") { //RANK YOU MORON
          if (intval($td->innertext)==$td->innertext) 
            $espn_rank=strip_tags($td->innertext);
        } 

      }
      if (preg_match("/SP/i",$positions)||preg_match("/RP/i",$positions))
        print "insert into Players (Season, Name, RealTeam, PosString, K, W, SV, ERA, WHIP, ESPNRank) values ('2016',\"";
      else
        print "insert into Players (Season, Name, RealTeam, PosString, R, HR, RBI, SB, BAVG, ESPNRank) values ('2016',\"";
      print $playername."\",'".$team."','".$positions."','";
      for ($sc=0;$sc<5;$sc++) {
        print $stat[$sc]."','";
      }
      print $espn_rank."');<BR>\n";
    }
  }

?>

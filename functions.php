<?php


  function login_form($returnpage) {
    #print "$returnpage<BR>\n";
    print "<FORM ACTION='$returnpage' method='POST'>\n";
    print "Username: <INPUT TYPE='text' name='email'><BR>\n";
    print "Password: <INPUT TYPE='password' name='passwd'><BR>\n";
    print "<input type='submit' value='Login'>";
    print "</FORM>";
  }

  function process_login($postin, $returnpage) {
    include('db_pass.php');
    print "processing login yo";
    #var_dump($postin);
    $email=$postin['email'];
    $passwd=$postin['passwd'];
    $db = mysql_connect("matthearn.com", $mysql_user, $mysql_pass);
    #print "mysql user ".$mysql_user;
    mysql_select_db("matthear_fantasybaseball");
    $user_query="SELECT * from Users where email=\"$email\"";
    print $user_query;
    $user_result = mysql_query($user_query,$db);
    if (mysql_num_rows($user_result) != 1) {
      print "Either too many rows, or no rows, in the db for this user.  I'm bailing out.";
    } else {
      if ($user_row = mysql_fetch_array($user_result)) {
        print md5($passwd);
        if (md5($passwd)==$user_row['passwd']) {
          return $user_row; 
        } else {
          print "<FONT COLOR=#FF0000>Incorrect login.</FONT>";
          login_form($returnpage);  
        }  
      } else {
        print "Unknown error pulling what should be a good Users row from the db.  Bailing out.";
      } 
    }
  }

  function start_html($title) {
    if (!isset($title)) {
      print "<HTML><HEAD><TITLE>you didn't pass a title to me dummy</TITLE></HEAD><BODY>\n";
    } else {
      print "<HTML><HEAD><TITLE>$title</TITLE></HEAD><BODY>\n";
    }
  }
  
  function end_html() {
    print "</BODY></HTML>";
  }

  function print_header() {
    $url_prefix="http://www.matthearn.com/fantasybaseball";
    $item_array=array(
      "availableplayers.php" => "Available Players",
      "allteams.php" => "All Teams",
      "draftstatus.php" => "Draft Results",
      "remainingpicks.php" => "Remaining Picks",

    );

    foreach ($item_array as $url=>$title) {
      print "<A HREF=$url_prefix/$url>[$title]</A> ";
    }

    if (isset($_SESSION['user_id']) and $_SESSION['user_id']>0) {
      print "<A HREF=$url_prefix/logout.php>[Log out]</A>";
    }
    print "<P>\n";
  }

  function team_abbr($team_name) {
    switch ($team_name) {
      case "49ers":
        return "SF";
        break;
      case "Bears":
        return "Chi";
        break;
      case "Bengals":
        return "Cin";
        break;
      case "Bills":
        return "Buf";
        break;
      case "Broncos":
        return "Den";
        break;
      case "Browns":
        return "Cle";
        break;
      case "Buccaneers":
        return "TB";
        break;
      case "Cardinals":
        return "Ari";
        break;
      case "Chargers":
        return "SD";
        break;
      case "Chiefs":
        return "KC";
        break;
      case "Colts":
        return "Ind";
        break;
      case "Cowboys":
        return "Dal";
        break;
      case "Dolphins":
        return "Mia";
        break;
      case "Eagles":
        return "Phi";
        break;
      case "Falcons":
        return "Atl";
        break;
      case "Giants":
        return "NYG";
        break;
      case "Jaguars":
        return "Jac";
        break;
      case "Jets":
        return "NYJ";
        break;
      case "Lions":
        return "Det";
        break;
      case "Packers":
        return "GB";
        break;
      case "Panthers":
        return "Car";
        break;
      case "Patriots":
        return "NE";
        break;
      case "Raiders":
        return "Oak";
        break;
      case "Rams":
        return "StL";
        break;
      case "Ravens":
        return "Bal";
        break;
      case "Redskins":
        return "Was";
        break;
      case "Saints":
        return "NO";
        break;
      case "Seahawks":
        return "Sea";
        break;
      case "Steelers":
        return "Pit";
        break;
      case "Texans":
        return "Hou";
        break;
    }
 }

?>

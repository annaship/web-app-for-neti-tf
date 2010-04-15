<?php
session_name('herbarium_wu_taxamatch');
session_start();

include('inc/variables.php');
include('inc/connect.php');

if (empty($_SESSION['uid'])) {
    $_SESSION['uid']      = 0;
    $_SESSION['username'] = '';
}

if (!empty($_POST['username'])) {
    $result = db_query("SELECT uid, username FROM tbluser WHERE username = " . quoteString($_POST['username']));
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
        session_regenerate_id();  // prevent session fixation
        $_SESSION['uid']      = $row['uid'];
        $_SESSION['username'] = $row['username'];
    } else {
        do {
            $user = $_POST['username'] . sprintf("%05d", mt_rand(100, 99999));
            $result = db_query("SELECT uid FROM tbluser WHERE username = " . quoteString($user));
        } while (mysql_num_rows($result) > 0);
        db_query("INSERT INTO tbluser SET username = " . quoteString($user));
        $id = mysql_insert_id();
        $row = mysql_fetch_array(db_query("SELECT uid, username FROM tbluser WHERE uid = '$id'"));
        session_regenerate_id();  // prevent session fixation
        $_SESSION['uid']      = $row['uid'];
        $_SESSION['username'] = $row['username'];
    }
} elseif (!empty($_POST['logout'])) {
    $_SESSION = array();  // Unset all of the session variables.
    session_destroy();
    $_SESSION['uid']      = 0;
    $_SESSION['username'] = '';
} elseif (isset($_FILES['userfile']) && is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    $result = db_query("SELECT * FROM tbljobs WHERE finish IS NULL AND uid = '" . $_SESSION['uid'] . "'");
    if (mysql_num_rows($result) == 0) {
        db_query("INSERT INTO tbljobs SET uid = '" . $_SESSION['uid'] . "', filename = " . quoteString($_FILES['userfile']['name']));
        $jobID = mysql_insert_id();
        $oldIniSetting = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $handle = @fopen($_FILES['userfile']['tmp_name'], "r");
        if ($handle) {
            $ctr = 1;
            while (!feof($handle)) {
                $line = ucfirst(trim(fgets($handle)));
                if (substr($line, 0, 3) == chr(0xef) . chr(0xbb) . chr(0xbf)) $line = substr($line, 3);
                if ($line) {
                    db_query("INSERT INTO tblqueries SET
                               jobID  = '$jobID',
                               lineNr = '$ctr',
                               query  = " . quoteString($line));
                    $ctr++;
                }
            }
        }
        fclose($handle);
        ini_set('auto_detect_line_endings', $oldIniSetting);
        db_query("INSERT INTO tblschedule SET jobID = '$jobID'");
    }
    exec("./bulkprocessCmd.php > /dev/null 2>&1 &");
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
  <title>taxamatch - bulk upload</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="css/screen.css">
</head>

<body>

<?php
if (!$_SESSION['uid']) {
    echo "<form Action='" . $_SERVER['PHP_SELF'] . "' Method='POST' name='f'>\n"
       . "username: <input type='text' name='username'> \n"
       . "<input type='submit' value='login'>\n"
       . "</form>\n";
} else {
    echo "<form enctype='multipart/form-data' Action='" . $_SERVER['PHP_SELF'] . "' Method='POST' name='f'>\n"
       . "<big><b>username: " . $_SESSION['username'] . "</b></big> \n"
       . "<input type='submit' name='logout' value='logout'>\n"
       . "<p>\n";

    $result = db_query("SELECT * FROM tbljobs WHERE finish IS NULL AND uid = '" . $_SESSION['uid'] . "'");
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_array($result);
    } else {
        $row = array();
        echo "<input type='hidden' name='MAX_FILE_SIZE' value='8000000' />\n"
           . "upload this file: <input name='userfile' type='file' />\n"
           . "<input type='submit' value='upload' />\n";

    }
    echo "<div style='font-size:large; font-weight:bold;'><input type='submit' name='refresh' value='refresh list'></div>\n"
       . "</form>\n";

    echo "<table class='out'>\n"
       . "<tr class='out'>"
       . "<th class='out'>filename</th>"
       . "<th class='out'>start</th>"
       . "<th class='out'>finish</th>"
       . "<th class='out'>status</th>"
       . "<th class='out'>errors</th></tr>";

    if ($row) {
        echo "<tr class='out'>"
           . "<td class='outCenter'><a href='bulkshow.php?id=" . $row['jobID'] . "' target='_blank'>" . htmlspecialchars($row['filename']) . "</a></td>"
           . "<td class='outCenter'>" . htmlspecialchars(($row['start']) ? $row['start'] : '-') . "</td>"
           . "<td class='outCenter'>-</td>"
           . "<td class='outCenter'>" . (($row['start']) ? 'processing' : 'waiting') . "</td>"
           . "<td class='out'>" . nl2br(htmlspecialchars($row['errors'])) . "</td>"
           . "</tr>\n";
    }

    $result = db_query("SELECT * FROM tbljobs WHERE finish IS NOT NULL AND uid = '" . $_SESSION['uid'] . "' ORDER by start DESC");
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result)) {
            echo "<tr class='out'>"
               . "<td class='outCenter'><a href='bulkshow.php?id=" . $row['jobID'] . "' target='_blank'>" . htmlspecialchars($row['filename']) . "</a></td>"
               . "<td class='outCenter'>" . htmlspecialchars($row['start']) . "</td>"
               . "<td class='outCenter'>" . htmlspecialchars($row['finish']) . "</td>"
               . "<td class='outCenter'>finished</td>"
               . "<td class='out'>" . nl2br(htmlspecialchars($row['errors'])) . "</td>"
               . "</tr>\n";
        }
    }
}
?>

</body>
</html>
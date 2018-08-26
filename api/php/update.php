<?php
header("Content-type: text/plain");
$domain = $_REQUEST['domain'];
$addr = $_REQUEST['addr'];
$port = $_REQUEST['port'];
if (isset($_REQUEST['speedlimit'])) {
    $speedlimit = '--bwlimit=' . $_REQUEST['speedlimit'];
}
$srcpath = $_REQUEST['srcpath'];

#Check arugments
if (empty($domain)) {
    echo "error: domain not found";
    return 1;
} else {
    $sshKeyPath = $_SERVER['DOCUMENT_ROOT'] . '/../sshkey';
    $sshKey = "$sshKeyPath/" . $domain . '_rsync.pem';
    if (!file_exists($sshKey)) {
        echo "error: SSH Key not found";
        return 1;
    }
    if (empty($addr)) {
        $addr = $domain;
    }
    if (empty($port)) {
        $port = 22;
    }
}

#echo cmd
if ($_REQUEST['cmd']) {
    echo "rsync --safe-links --partial --compress-level=9 -rtDSzvuc $speedlimit --delete -T /tmp \
  -e \"ssh -p $port -o UserKnownHostsFile=$sshKeyPath/known_hosts -o StrictHostKeyChecking=no -i $sshKey\" \
  root@$addr:$srcpath/ " . $_SERVER['DOCUMENT_ROOT'] . "/$domain/";
    return 0;
}


#Check pid
$pidfile = "$sshKeyPath/$domain" . "_bash.pid";
if (file_exists($pidfile)) {
    $fd = fopen($pidfile, 'r');
    $pid = fread($fd, filesize($pidfile));
    fclose($fd);
    $psRet;
    exec("ps -q $pid > /dev/null", $output, $psRet);
    if ($psRet == 0) {
        echo "error: update is already running " . exec("ps -o etime --no-headers -q $pid");
        return 1;
    }
}

#Run rsync
unset($output);
$descriptorspec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("pipe", "w"),
);

$rsync = proc_open("rsync --safe-links --partial --compress-level=9 -rtDSzvuc $speedlimit --delete -T /tmp -z \
  -e \"ssh -p $port -o UserKnownHostsFile=$sshKeyPath/known_hosts -o StrictHostKeyChecking=no -i $sshKey\" \
  root@$addr:$srcpath/ " . $_SERVER['DOCUMENT_ROOT'] . "/$domain/", $descriptorspec, $pipes, null, null);
if (!is_resource($rsync)) {
    echo "error: can't start rsync";
    return 1;
}

#write pid
$fd = fopen($pidfile, 'w');
$pid = proc_get_status($rsync)['pid'];
fwrite($fd, $pid);
fclose($fd);

#pipe output
echo stream_get_contents($pipes[1]);
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
$rsyncRet = proc_get_status($rsync)['exitcode'];
proc_close($rsync);

#End
echo "\r\n";
if ($rsyncRet == 0) {
    echo "Successful";
} else {
    echo "error: rsync return code $rsyncRet";
}
unlink($pidfile);
?>

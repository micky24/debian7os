<?php
$dl = 90;
$wl = 75;
$memloc = '/proc/meminfo'; 
$fileloc = '/'; 
$uptimeloc = '/proc/uptime'; 
$loadtime = 0;
$post = array(); 
$internal = array();
$internal['uptime'] = explode('.', file_get_contents($uptimeloc), 2);
$internal['memraw'] = file_get_contents($memloc);
$internal['hddtotal'] = disk_total_space($fileloc);
$internal['hddfree'] = disk_free_space($fileloc);
$internal['load'] = sys_getloadavg();
$post['uptime'] = sec2human($internal['uptime'][0]); // Processing uptime and putting in post field!
preg_match_all('/MemTotal:(.*)kB/', $internal['memraw'], $internal['memtotal']); // Get Total Memory!
$internal['memtotal'] = trim($internal['memtotal'][1][0], " ");  // Make nice.
preg_match_all('/MemFree:(.*)kB/', $internal['memraw'], $internal['memfree']); // Get Free Memory!
$internal['memfree'] = trim($internal['memfree'][1][0], " "); // Make nice.
preg_match_all('/Cached:(.*)kB/', $internal['memraw'], $internal['memcache']); // Get Cached Memory!
$internal['memfree'] = trim($internal['memcache'][1][0], " ") + $internal['memfree']; // Making cache seen as Free Memory!
$internal['memperc'] = round((($internal['memtotal'] - $internal['memfree']) / $internal['memtotal']) * 100); // calculations
$post['memory'] = levels($internal['memperc'], $dl, $wl);  // adding to the post field!
$internal['hddperc'] = round((($internal['hddtotal'] - $internal['hddfree']) / $internal['hddtotal']) * 100); // calculations!
$post['hdd'] = levels($internal['hddperc'], $dl, $wl); // adding hdd to the post field!
$post['load'] = $internal['load'][$loadtime]; // posting load avg.
$post['online'] = '<div class="progress"><div class="bar bar-success" style="width: 100%;"><small>Up</small></div></div>';
echo json_encode($post); // Time to show the world what we are made of!
function levels($perc, $dl, $wl){
    // make nice green bars
    if($perc < 30) {
        $width = 30;
    } else {
        $width = $perc;
    }
    if($perc < $wl) { 
        $return = '<div class="progress progress-striped active"><div class="bar bar-success" style="width: ' . $width . '%;">' . $perc . '%</div</div>';
    }
    elseif($perc > $wl) {
        $return = '<div class="progress progress-striped active"><div class="bar bar-warning" style="width: ' . $width . '%;">' . $perc . '%</div></div>';
    }
    elseif($perc > $dl) {
        $return = '<div class="progress progress-striped active"><div class="bar bar-danger" style="width: ' . $width . '%;">' . $perc . '%</div></div>';
    }
    return $return;
    
}

function sec2human($time) {
  $seconds = $time%60;
	$mins = floor($time/60)%60;
	$hours = floor($time/60/60)%24;
	$days = floor($time/60/60/24);
	return $days > 0 ? $days . ' day'.($days > 1 ? 's' : '') : $hours.':'.$mins.':'.$seconds;
}

?>

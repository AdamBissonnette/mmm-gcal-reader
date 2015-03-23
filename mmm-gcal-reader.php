<?php 
include_once('coreylib-master/coreylib.php');
//https://www.google.com/calendar/feeds/vfsdjqv14ameva3c5mil7062ok%40group.calendar.google.com/public/basic

$output = "";

$api = new clApi('https://www.google.com/calendar/feeds/vfsdjqv14ameva3c5mil7062ok%40group.calendar.google.com/public/basic');
if ($feed = $api->parse()) {
  // now we have data...
    $output = "";

    foreach ($feed->get('entry') as $entry) {
        echo ($entry->title) . "<br />";
        echo str_replace(($entry->summary);
        echo "<br />";
    }
} else {
  // something went wrong
    $output = "Couldn't parse the given xml.";
}

echo $output;

?>
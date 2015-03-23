<?php 
/*
Plugin Name: Mmm GCal Reader
Plugin URI: http://www.mediamanifesto.com
Description: A simple shortcode for loading a google calendar feed
Version: 1.0
Author: Adam Bissonnette
Author URI: http://www.mediamanifesto.com
*/

include_once('lib/coreylib/coreylib.php');

function LoadGCalReader($atts, $content=null)
{

    extract( shortcode_atts( array(
        'src' => '',
        'class' => '',
        'count' => '5',
        'date_format' => 'd M Y g:ia'
        ), $atts ) );
    
    $output = "";

    if ($class != '')
    {
        $class = sprintf(' class="%s"', $class);
    }

    $entry_template = "<p" . $class . ">%s</p>";

    $api = new clApi($src);
    if ($feed = $api->parse()) {
      // now we have data...
        $output = "";

        $i = 0;
        foreach ($feed->get('entry') as $entry) {
            if ($content == null)
            {
                $cur_entry = ($entry->title) . "<br />";
                $cur_entry_date = _getEntryStartDate($entry->summary);
                $cur_entry .= $cur_entry_date->format($date_format);
                $output .= sprintf($entry_template, $cur_entry);
            }
            else
            {

                $output .= _doGCalTemplate($content, $entry, $date_format);
            }

            if (++$i == $count)
            {
                break;
            }
        }
    } else {
      // something went wrong
        $output = "Couldn't parse the given xml.";
    }

    return $output;
}

function _getEntryStartDate($entrySummary)
{
    return new DateTime(trim(preg_replace("/<br>Event Status: confirmed|When: |to .+?\n.+?\n+\s?/", "", $entrySummary)));
}

function _doGCalTemplate($content, $entry, $dateFormat)
{
    $entryDate = _getEntryStartDate($entry->summary);

    $entryAtts = array( "title" => $entry->title,
                        "date" => $entryDate->format($dateFormat),
                        "D" => $entryDate->format("D"),
                        "d" => $entryDate->format("d"),
                        "M" => $entryDate->format("M"),
                        "Y" => $entryDate->format("Y"),
                        "time" => $entryDate->format("g:ia"));

    return _AddEntryAttsToTemplate($content, $entryAtts);
}

function _AddEntryAttsToTemplate($template, $entryAtts)
{
    $output = $template;

    foreach ($entryAtts as $key => $value) {

        if (isset($value))
        {
            $output = str_replace(sprintf('{%s}', $key), $value, $output);
        }
    }

    return $output;
}

add_shortcode( 'MmmGCalReader', 'LoadGCalReader' );

?>
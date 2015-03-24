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

class MM_GCal_Reader
{
    public static $attsKeyTemplate = "{%s}";

    function MM_GCal_Reader()
    {
        add_shortcode( 'MMGCalReader', array(&$this, 'LoadGCalReader') );
    }

    function LoadGCalReader($atts, $content=null)
    {

        extract( shortcode_atts( array(
            'src' => '',
            'class' => '',
            'count' => '5',
            'date_format' => 'd M Y g:ia',
            'sortorder' => "ascend",
            'orderby' => "starttime",
            'start' => (new DateTime())->format("Y-m-d")
            ), $atts ) );
        $output = "";

        if ($class != '')
        {
            $class = sprintf(' class="%s"', $class);
        }

        $entry_template = "<p" . $class . ">%s</p>";

        $clFeed = new clApi($src);

        $clFeed->param("orderby", $orderby);
        $clFeed->param("sortorder", $sortorder);
        $clFeed->param("start-min", $start);

        if ($feed = $clFeed->parse()) {
          // now we have data...
            $output = "";

            $i = 0;

            foreach ($feed->get('entry') as $entry) {
                if (strpos($entry->summary, "When") !== false)
                {
                    if ($content == null)
                    {
                        $cur_entry = ($entry->title) . "<br />";
                        $cur_entry_date = $this->_getEntryStartDate($entry->summary);
                        $cur_entry .= $cur_entry_date->format($date_format);
                        $output .= sprintf($entry_template, $cur_entry);
                    }
                    else
                    {
                        $output .= $this->_doGCalTemplate($content, $entry, $date_format);
                    }

                    if (++$i == $count)
                    {
                        break;
                    }
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
        $replace_regex = "/Who.+?\n|<br>|Event Status: confirmed|When: |to .+?\n.+?\n+/";

        return new DateTime(trim(preg_replace($replace_regex, "", $entrySummary)));
    }

    function _doGCalTemplate($content, $entry, $dateFormat)
    {
        $entryDate = $this->_getEntryStartDate($entry->summary);

        $entryAtts = array( "title" => $entry->title,
                            "date" => $entryDate->format($dateFormat),
                            "D" => $entryDate->format("D"),
                            "d" => $entryDate->format("d"),
                            "M" => $entryDate->format("M"),
                            "Y" => $entryDate->format("Y"),
                            "time" => $entryDate->format("g:ia"));

        return $this->_AddEntryAttsToTemplate($content, $entryAtts);
    }

    function _AddEntryAttsToTemplate($template, $entryAtts)
    {
        $output = $template;

        foreach ($entryAtts as $key => $value) {

            if (isset($value))
            {
                $output = str_replace(sprintf(MM_GCal_Reader::$attsKeyTemplate, $key), $value, $output);
            }
        }

        return $output;
    }
}

add_action( 'init', 'MM_GCal_Reader_Init', 5 );
function MM_GCal_Reader_Init()
{
    global $MM_GCal_Reader;
    $MM_GCal_Reader = new MM_GCal_Reader();
}

?>
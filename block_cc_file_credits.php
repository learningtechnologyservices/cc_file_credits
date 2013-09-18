<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Show course's file on the current course page.
 * The block can only be used by teacher of the current course.
 * 
 * @package     block
 * @subpackage  cc_file_credits
 * @author      Shuai Zhang
 * @copyright   2013 Shuai Zhang <shuaizhang@lts.ie>
 */

class block_cc_file_credits extends block_base {
    /**
     * Block initialization
     */
    function init() {
        $this->title = get_string('cc_file_credits', 'block_cc_file_credits');
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('course-view' => true);
    }
    
    /**
     * Return contents of cc_file_credits block
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $DB,$USER,$OUTPUT,$CFG;
        $this->content = new stdClass;
        $courseid = $this->page->course->id;
        $coursecontext = context_course::instance($courseid);

        //Ensure only teacher can use this block
        if(has_capability('block/cc_file_credits:manage', $coursecontext)){
            $url = new moodle_url('/blocks/cc_file_credits/view_report.php',array('id'=>$courseid));
            $text = 'View the files in this course';
            $this->content->text .= html_writer::link($url,$text);
        } else {
            $this->content->text .= 'Course managers only.';
        }
        return $this->content;
    }
}
?>

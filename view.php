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
 * Show course's file on the current course page
 * 
 * @package      block
 * @subpackage   cc_file_credits
 * @copyright    2013 (c) Learning Technology Services
 * @contributor  Shuai Zhang <shuaizhang@lts.ie>
 */


//  Display the report page
require_once('../../config.php');
require_once('../../lib/filelib.php');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$courseid));
$coursecontext = context_course::instance($courseid);

if (!$course){
    print_error('invalidcourse', 'block_cc_file_credits', $courseid);
}
require_login($course);
$url = new moodle_url('/blocks/cc_file_credits/view.php',array('id'=>$courseid));

$PAGE->set_pagelayout('report');
$PAGE->set_url($url,array('id' => $courseid));
$PAGE->set_title($course->fullname);
$PAGE->set_heading('Files in '.$course->fullname);

if (!has_capability('block/cc_file_credits:manage', $coursecontext)){
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Error', 1);
    echo "Only teacher and manager are authorized to access this page.";
    echo $OUTPUT->footer();
    exit();
}


//Ensure only teacher can use this block
if(has_capability('block/cc_file_credits:manage', $coursecontext)){
    $table = new html_table();
    $table->head = array('Name','Size','Type','Author','License','Time Created');
    $tabledata = array();
    $result = $DB->get_records_sql(
       "SELECT id, filename, filesize, mimetype ,author, license, timecreated
          FROM {files}
         WHERE filesize >0
           AND {files}.contextid
            IN (   SELECT id
                     FROM {context}
                    WHERE path 
                     LIKE (   SELECT CONCAT('%/',id,'/%')
                                  AS contextquery
                                FROM {context}
                               WHERE instanceid = $courseid
                                 AND contextlevel = 50
                           )
                )",array());

    // Fetch the data and put them into table
    if($result){
        foreach ($result as $file) {
        	  $file_license = $file->license;
        	  if(is_cc($file_license)) {
            	  $file->filesize    = improve_filesize($file->filesize);
                $file->author      = improve_author($file->author);
                $file->timecreated = improve_timecreated($file->timecreated);
                $file->license     = improve_license($file_license);
        	      $file->mimetype    = improve_mimetype($file->mimetype, $OUTPUT);
                array_push($tabledata, array($file->filename,
                                             $file->filesize,
                                             $file->mimetype,
                                             $file->author,
                                             $file->license,
                                             $file->timecreated));
            }
        }
    }
    if(count($tabledata) == 0){ 
        $tabledata = array(array('No record','No record','No record','No record','No record','No record'));
    }
    $table->data = $tabledata;
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Files in Course: '.$course->fullname, 1);
echo html_writer::table($table); 
echo $OUTPUT->footer();







// Make file size more readable
function improve_filesize($filesize) {
    $i = 0;
    while($filesize > 1024){
        $filesize = round(($filesize / 1024),1);
        $i += 1;
    }
    if ($i = 0){
        $filesize .= ' bytes';
    } else if($i = 1) {
        $filesize .= ' KB';
    } else if($i = 2) {
        $filesize .= ' MB';
    }
return $filesize;
}

// Make author more readable
function improve_author($author) {
    if ($author != null){
        return $author;
    } else {
        $author = 'No record';
        return $author;
    }
}

// Make timecreated more readable
function improve_timecreated($timecreated) {
    $date = Date('Y-m-d H:i:s', $timecreated);
    return $date; 
}

// Make license more readable
function improve_license($license) {
    if($license == 'cc') {
        $license = 'Creative Commons';
    } else if ($license == 'cc-nd') {
        $license = 'Creative Commons - NoDerivs';
    } else if ($license == 'cc-nc-nd') {
        $license = 'Creative Commons - No Commercial NoDerivs';
    } else if ($license == 'cc-nc') {
        $license = 'Creative Commons - No Commercial';
    } else if ($license == 'cc-nc-sa') {
        $license = 'Creative Commons - No Commercial ShareAlike';
    } else if ($license == 'cc-sa') {
        $license = 'Creative Commons - ShareAlike';
    }
    return $license;
}

// Make mimetype more readable, add an relative icon
function improve_mimetype($mimetype, $OUTPUT) {
    $icon_src = $OUTPUT->pix_url(file_mimetype_icon($mimetype))->out();
    $mimetype = get_mimetype_description($mimetype);
    $result = html_writer::empty_tag('img', array('src' => $icon_src, 'alt' => $mimetype)).$mimetype;
    return $result;
}

// Determine if a file is with creative commons license
function is_cc($license) {
    if($license != NULL)
    {   $pos = strpos($license,"cc");
        if($pos !== false)
        {   return true;}
    }
    return false;
}

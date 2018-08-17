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
 * Question type class for the sassessment question type.
 *
 * @package    qtype
 * @subpackage sassessment
 * @copyright  2018 Kochi-Tech.ac.jp

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/sassessment/question.php');


/**
 * The sassessment question type.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sassessment extends question_type {
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_sassessment_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;
        $result = new stdClass();

        $this->save_question_answers($formdata);

        {
          $options = $DB->get_record('qtype_sassessment_options', array('questionid' => $formdata->id));
          if (!$options) {
              $options = new stdClass();
              $options->questionid = $formdata->id;
              $options->id = $DB->insert_record('qtype_sassessment_options', $options);
          }

          $options->show_transcript = (int)$formdata->show_transcript;
          $options->save_stud_audio = (int)$formdata->save_stud_audio;
          $options->show_analysis = (int)$formdata->show_analysis;

          $options->fb_type = $formdata->fb_type;

          $DB->update_record('qtype_sassessment_options', $options);
        }
    }

    protected function is_answer_empty($questiondata, $key) {
       return html_is_blank($questiondata->answer[$key]['text']) || trim($questiondata->answer[$key]) == '';
    }

    protected function fill_answer_fields($answer, $questiondata, $key, $context) {
        // $answer->answer = $this->import_or_save_files($questiondata->answer[$key],
        //         $context, 'question', 'answer', $answer->id);
        // $answer->answerformat = $questiondata->answer[$key]['format'];
        $answer->answer = trim($questiondata->answer[$key]);
        return $answer;
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_sassessment_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->questions = $questiondata->options->answers;
    }

    public function get_random_guess_score($questiondata) {
        // TODO.
        return 0;
    }

    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }

    public function feedback_types() {
        return array(
            'percent' => get_string('percent_score', 'qtype_sassessment'),
            'points' => get_string('points_score', 'qtype_sassessment'),
        );
    }
}

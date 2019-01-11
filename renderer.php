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
 * sassessment question renderer class.
 *
 * @package    qtype
 * @subpackage sassessment
 * @copyright  2018 Kochi-Tech.ac.jp

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

/**
 * Generates the output for sassessment questions.
 *
 * @copyright  2018 Kochi-Tech.ac.jp

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_sassessment_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $USER, $CFG;

        $question = $qa->get_question();

        $questiontext = $question->format_questiontext($qa);
        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if ($options->readonly) {
          $result .= html_writer::start_tag('ul');
          foreach ($question->questions as $q) {
            $result .= html_writer::start_tag('li');
            $result .= html_writer::tag('div', $question->format_text($q->answer, $q->answerformat,
                    $qa, 'question', 'answer', $q->id)); // , array('class' => 'qtext')
            $result .= html_writer::end_tag('li');
          }
          $result .= html_writer::end_tag('ul');
        }

        $answername = $qa->get_qt_field_name('answer');
        {
          $label = 'answer';
          $currentanswer = $qa->get_last_qt_var($label);
          $inputattributes = array(
              'type' => 'hidden',
              'name' => $answername,
              'value' => $currentanswer,
              'id' => $answername,
              'size' => 60,
              'class' => 'form-control d-inline',
              'readonly' => 'readonly',
              //'style' => 'border: 0px; background-color: transparent;',
          );

          $answerDiv = $qa->get_qt_field_name('answerDiv');

          $input = html_writer::div($currentanswer, $answerDiv, array("id" => $answerDiv));
          $input .= html_writer::empty_tag('input', $inputattributes);


          if ($question->show_transcript == 1) {
              $answerDisplayStatus = "none";
          } else {
              $answerDisplayStatus = "display:none";
          }

          if ($question->show_analysis == 1) {
              $gradeDisplayStatus = "none";
          } else {
              $gradeDisplayStatus = "display:none";
          }

          if ($question->save_stud_audio == 1) {
              $audioDisplayStatus = "none";
          } else {
              $audioDisplayStatus = "display:none";
          }

          $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
          $result .= html_writer::tag('label', get_string('answer', 'qtype_sassessment',
                  html_writer::tag('span', $input, array('class' => 'answer'))),
                  array('for' => $inputattributes['id'], 'style'=>$answerDisplayStatus));
          $result .= html_writer::end_tag('div');
        }

        $itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        if (!$options->readonly) {
            $gradename = $qa->get_qt_field_name('grade');
            $btnname = $qa->get_qt_field_name('rec');
            $audioname = $qa->get_qt_field_name('audio');
            $btnattributes = array(
              'name' => $btnname,
              'id' => $btnname,
              'size' => 80,
              'qid' => $question->id,
              'answername' => $answername,
              'answerDiv' => $answerDiv,
              'gradename' => $gradename,
              'onclick' => 'recBtn(event);',
              'type' => 'button',
              'options' => json_encode(array(
                'repo_id' => $this->get_repo_id(),
                'ctx_id' => $options->context->id,
                'itemid' => $itemid,
                'title' => 'audio.mp3',
              )),
              'audioname' => $audioname,
            );

            $btn = html_writer::tag('button', 'Start recording', $btnattributes);
            $audio = html_writer::empty_tag('audio', array('src' => ''));

            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            $result .= html_writer::tag('label', 'Answer recording ' . $btn,
                    array('for' => $btnattributes['id']));
            $result .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $qa->get_qt_field_name('attachments'), 'value' => $itemid));
            $result .= html_writer::end_tag('div');

            $result .= html_writer::start_tag('div', array('class' => 'ablock', 'style' => $audioDisplayStatus));
            $result .= html_writer::empty_tag('audio', array('id' => $audioname, 'name' => $audioname, 'controls' => ''));
            $result .= html_writer::end_tag('div');

            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/recorder.js'));
            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/main.js'));
            $result .= html_writer::script(null, new moodle_url('/question/type/sassessment/js/Mp3LameEncoder.min.js'));
        }
        else {
          $files = $qa->get_last_qt_files('attachments', $options->context->id);
          foreach ($files as $file) {
            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            // $result .= html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
            //         $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
            //         'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
            $result .= html_writer::tag('p', html_writer::empty_tag('audio', array('src' => $qa->get_response_file_url($file), 'controls' => '')));
            $result .= html_writer::end_tag('div');
          }
        }

        $gradename = $qa->get_qt_field_name('grade');
        {
            $label = 'grade';
            $currentanswer = $qa->get_last_qt_var($label);
            $inputattributes = array(
                'name' => $gradename,
                'value' => $currentanswer,
                'id' => $gradename,
                'size' => 10,
                'class' => 'form-control d-inline',
                'readonly' => 'readonly',
                'style' => 'border: 0px; background-color: transparent;',
            );

            $input = html_writer::empty_tag('input', $inputattributes);

            $result .= html_writer::start_tag('div', array('class' => 'ablock form-inline'));
            $result .= html_writer::tag('label', get_string('grade', 'qtype_sassessment',
                html_writer::tag('span', $input, array('class' => 'answer'))),
                array('for' => $inputattributes['id'], 'style'=>$gradeDisplayStatus));
            $result .= html_writer::end_tag('div');
        }


        /*
         * IOS app integration code START
         */
        if (function_exists("voiceshadow_is_ios"))
        if (voiceshadow_is_ios() && !$options->readonly && file_exists($CFG->dirroot.'/mod/voiceshadow/ajax-apprecord.php')) {
            $time = time();
            $result .= html_writer::start_tag("a", array("href" => 'voiceshadow://?link='.$CFG->wwwroot.'&id='.$options->context->id.'&uid='.$USER->id.'&time='.$time.'&fid='.$question->id.'&var=0&audioBtn=1&sstBtn=1&type=voiceshadow&mod=voiceshadow', "id" => "id_recoring_link",  //
                "onclick" => 'formsubmit(this.href)'));

            $result .= get_string('recordAudioIniosapp', 'qtype_sassessment');
            $result .= html_writer::end_tag('a');


            $result .= html_writer::script('

setInterval(function(){
    $.get( "'.$CFG->wwwroot.'/mod/voiceshadow/ajax-apprecord.php", { id: '.$question->id.', inst: '.$options->context->id.', uid: '.$USER->id.' }, function(json){
        var j = JSON.parse(json);
        var t = +new Date();

        if (j.status == "success") {

            $(":text").each(function() {
                if ($(this).attr("id") == "'.$answername.'") {
                    $(this).val(j.text);
                }
            });
            
            $("audio").each(function() {
                if ($(this).attr("id") == "'.$audioname.'") {
                    $(this).attr("src", "'.$CFG->wwwroot.'/mod/voiceshadow/file.php?file="+j.fileid);
                }
            });
            
            $("input").each(function() {
                if ($(this).attr("name") == "'.$qa->get_qt_field_name('attachments').'") {
                    $(this).val(j.itemid);
                }
            });
            
            $.post("'.$CFG->wwwroot.'/question/type/sassessment/ajax-score.php", { qid: '.$question->id.', ans: j.text },
               function (data) {
                  $("input").each(function() {
                     if ($(this).attr("name") == "'.$gradename.'") {
                        $(this).val(JSON.parse(data).gradePercent);
                     }
                  });
               });

            $.get( "'.$CFG->wwwroot.'/mod/voiceshadow/ajax-apprecord.php", { a: "delete", id: '.$question->id.', inst: '.$options->context->id.', uid: '.$USER->id.' });
        }
    } );
}, 1000);

                ');
            /*
             * IOS app integration code END
             */


        }


        return $result;
    }

  public function get_repo_id($type = 'upload') {
    global $CFG;
    require_once($CFG->dirroot . '/lib/form/filemanager.php');
    foreach (repository::get_instances() as $rep) {
      $meta = $rep->get_meta();
      if ($meta->type == $type)
        return $meta->id;
    }
    return null;
  }

    public function specific_feedback(question_attempt $qa) {
        $ans = $qa->get_last_qt_var('answer');
        $grade = qtype_sassessment_compare_answer($ans, $qa->get_question()->id);

        $result = '';
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('p', 'Target answer: ' . $grade['answer']);
        $result .= html_writer::tag('p', 'Your answer: ' . $ans);
        $result .= html_writer::tag('p', 'Graded: ' . $grade['gradePercent']);
        $result .= html_writer::end_tag('div');

        /*
         * TMP disabled Analises report
        $anl = qtype_sassessment_printanalizeform($ans);
        unset($anl['laters']);
        $table = new html_table();
        $table->head = array('Analysis', 'Result');
        $table->data = array();
        foreach ($anl as $k => $v)
          $table->data[] = array($k, $v);
        $result .= html_writer::table($table);
        */

        return $result;
    }

    public function correct_response(question_attempt $qa) {
        // TODO.
        return '';
    }
}

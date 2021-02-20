<head>
  <link rel="stylesheet" type="text/css"  href="C:/xampp7/htdocs/moodle/blocks/tutorvirtual/styles.css">
</head>
<body>
  <?php

  /*require(__DIR__.'../config.php');  C:\xampp7\htdocs\moodle
  style='color: blue; background-color: lightblue;'
  */

  defined('MOODLE_INTERNAL') || die();
  require_once('../config.php');

  class block_tutorvirtual extends block_list {

      public function init() {
          $this->title = get_string('Actividades pendientes ', 'block_simplehtml');
      }

      // The PHP tag and the curly bracket for the class definition
      // will only be closed after there is another function added in the next section.

      public function get_content() {
        global $DB;
        global $PAGE;
        if ($this->content !== null) {
          return $this->content;
        }
        $courseid = $PAGE->course->id;
        //$user = $DB->get_record('course', array('shortname' => 'curso 1'), '*', MUST_EXIST);
        //$idcurso = $user->id;


        $course = $this->page->cm;
        $this->content         =  new stdClass;
        $this->content->items = array();
        //$this->content->items[] = "<img src='https://cdn.discordapp.com/attachments/699813602328051765/705960260653023282/huellita.png' class='boton' draggable=true style='margin-top:15px; border-radius: 30px; position:center;' onclick=location.href='https://www.facebook.com/stories/1825005327519671/UzpfSVNDOjEwMjE5NTM2NTA3ODU2Mzk3/?source=story_tray%27%3E</img>";

        $assign = $DB->get_records('assign', array('course' => $courseid), '', 'name', 0, 0);
        $assigncourse = array_column($assign, 'name');

        foreach ($assigncourse as $assigncourse) {
            $this->content->items[] = $assigncourse;
        }

        //$this->content->items[] = $courseid;

        $this->content->items[] = '<br>';
        $this->content->footer = 'Curso ';
        return $this->content;
    }

    function has_config() {
      return true;
    }

    function instance_allow_config() {
      return true;
    }
  }
  ?>
</body>

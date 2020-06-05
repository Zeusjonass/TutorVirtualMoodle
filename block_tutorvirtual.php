<?php
class block_tutorvirtual extends block_list {

  public function init() {
    $this->title = get_string('Tutor Virtual');
  }
  // The PHP tag and the curly bracket for the class definition 
  // will only be closed after there is another function added in the next section.
  
  public function get_content() {

    if ($this->content !== null) {
      return $this->content;
    }

    $this->content        =  new stdClass;
    $this->content->items = array();
    $this->content->icons = array();

    $course = $this->page->course->id;
    $this->content->items[] = "Lista de Recursos:<br><br>";

    $this->page->requires->js_call_amd('block_tutorvirtual/script', 'init');

    $this->content->items = array();
    //$this->content->items[] = "<img class='imagen' src='/moodle/blocks/tutorvirtual/huellita.png' alt='imagen'</img>";
    //$this->enviarMensaje();
    //$this->imprimirActividades();
    //$this->content->items[] ="<br>Lista Actividades c:<br><br>";
    //$this->imprimirRecursos();
    //$this->content->items[] = "<br>Lista Recursoooos";

    return $this->content;
  }

  function has_config() {
    return true;
  }

  function instance_allow_config() {
    return true;
  }

  public function imprimirRecursos(){
    $modinfo = get_fast_modinfo($this->page->course);
    $activities = get_array_of_activities($this->page->course->id);
    $items[] = [];
    foreach ($activities as $activity) {
      if($activity->mod == "resource" || $activity->mod == "url" || $activity->mod == "page" ||
        $activity->mod == "label" || $activity->mod == "book" || $activity->mod == "folder"){
          $this->content->items[] = $activity->name;
        }
    }
  }

  public function enviarMensaje(){
    global $DB;
    global $PAGE;
    global $USER;
    $teachers = $this->get_course_teachers($DB, $PAGE);
    foreach ($teachers as $teacher) {
      $this->send_message_to_course_teacher($USER, $teacher, $PAGE);
      $this->content->items[] = "Se ha enviado su mensaje";
    }
  }

  function get_course_teachers(mariadb_native_moodle_database $DB, moodle_page $PAGE) {
    $courseid = $PAGE->course->id;
    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $teachers = get_role_users($role->id, $context);
    return $teachers;
  }

  function send_message_to_course_teacher(stdClass $USER, stdClass $teacher, moodle_page $PAGE) {
    //create message
    $message = new \core\message\message();
    $message->component = 'moodle';
    $message->name = 'instantmessage';
    $message->userfrom = $USER;
    $message->userto = $teacher;
    $message->fullmessage = 'Mesaje de Prueba al Profesor de Álgebra Intermedia';
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->notification = '0';
    $message->courseid = $PAGE->course->id;
    // Create a file instance.
    $usercontext = context_user::instance($teacher->id);
    $file = new stdClass;
    $file->contextid = $usercontext->id;
    $file->component = 'user';
    $file->filearea  = 'private';
    $file->itemid    = 0;
    $file->filepath  = '/';
    $file->filename  = $this->random_strings(5) . '.txt';
    $file->source    = 'test';
    //join file instance and message
    $fs = get_file_storage();
    $file = $fs->create_file_from_string($file, 'file1 content');
    $message->attachment = $file;
    //send message
    $messageid = message_send($message);
  }

  function random_strings($length_of_string) {   
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';   
    // Shuffle the $str_result and returns substring of specified length 
    return substr(str_shuffle($str_result),0, $length_of_string); 
  }

  function hide_header() {
    return true;
  }
  
  public function imprimirActividades(){
    global $DB;
    global $PAGE;

    $courseid = $PAGE->course->id;
    //$user = $DB->get_record('course', array('shortname' => 'curso 1'), '*', MUST_EXIST);
    //$idcurso = $user->id;


    $course = $this->page->cm;
    $this->content         =  new stdClass;
    $this->content->items = array();
    //$this->content->items[] = "<img src='https://cdn.discordapp.com/attachments/699813602328051765/705960260653023282/huellita.png' class='boton' draggable=true style='margin-top:15px; border-radius: 30px; position:center;' onclick=location.href='https://www.facebook.com/stories/1825005327519671/UzpfSVNDOjEwMjE5NTM2NTA3ODU2Mzk3/?source=story_tray%27%3E</img>";

    //Base de datos
    //$this->content->items[] = 'Base de datos:';
    $data = $DB->get_records('data', array('course' => $courseid), '', 'name', 0, 0);
    $datacourse = array_column($data, 'name');

    foreach ($datacourse as $datacourse) {
        $this->content->items[] = $datacourse;
    }
    //$this->content->items[] = '<br>';

    //Chat
    //$this->content->items[] = 'Chat:';
    $chat = $DB->get_records('chat', array('course' => $courseid), '', 'name', 0, 0);
    $chatcourse = array_column($chat, 'name');

    foreach ($chatcourse as $chatcourse) {
        $this->content->items[] = $chatcourse;
    }
    //$this->content->items[] = '<br>';

    //Elección
    //$this->content->items[] = 'Elección:';
    $choice = $DB->get_records('choice', array('course' => $courseid), '', 'name', 0, 0);
    $choicecourse = array_column($choice, 'name');

    foreach ($choicecourse as $choicecourse) {
        $this->content->items[] = $choicecourse;
    }
    //$this->content->items[] = '<br>';

    /*Encuesta predefinida
    $survey = $DB->get_records('survey', array('course' => $courseid), '', 'name', 0, 0);
    $choicecourse = array_column($choice, 'name');

    foreach ($choicecourse as $choicecourse) {
        $this->content->items[] = $choicecourse;
    }
    */

    //Examen
    //$this->content->items[] = 'Examen:';
    $quiz = $DB->get_records('quiz', array('course' => $courseid), '', 'name', 0, 0);
    $quizcourse = array_column($quiz, 'name');

    foreach ($quizcourse as $quizcourse) {
        $this->content->items[] = $quizcourse;
    }
    //$this->content->items[] = '<br>';

    //Foro
    //$this->content->items[] = 'Foro:';
    $forum = $DB->get_records('forum', array('course' => $courseid), '', 'name', 0, 0);
    $forumcourse = array_column($forum, 'name');

    foreach ($forumcourse as $forumcourse) {
        $this->content->items[] = $forumcourse;
    }
    //$this->content->items[] = '<br>';

    //Glosario
    //$this->content->items[] = 'Glosario:';
    $glossary = $DB->get_records('glossary', array('course' => $courseid), '', 'name', 0, 0);
    $glossarycourse = array_column($glossary, 'name');

    foreach ($glossarycourse as $glossarycourse) {
        $this->content->items[] = $glossarycourse;
    }
    //$this->content->items[] = '<br>';

    //Herramienta externa
    //$this->content->items[] = 'Herramientas externas:';
    $lti = $DB->get_records('lti', array('course' => $courseid), '', 'name', 0, 0);
    $lticourse = array_column($lti, 'name');

    foreach ($lticourse as $lticourse) {
        $this->content->items[] = $lticourse;
    }
    //$this->content->items[] = '<br>';

    //leccion
    //$this->content->items[] = 'Lecciones:';
    $lesson = $DB->get_records('lesson', array('course' => $courseid), '', 'name', 0, 0);
    $lessoncourse = array_column($lesson, 'name');

    foreach ($lessoncourse as $lessoncourse) {
        $this->content->items[] = $lessoncourse;
    }
    //$this->content->items[] = '<br>';

    //Paquete SCORM
    //$this->content->items[] = 'Paquetes SCORM:';
    $scorm = $DB->get_records('scorm', array('course' => $courseid), '', 'name', 0, 0);
    $scormcourse = array_column($scorm, 'name');

    foreach ($scormcourse as $scormcourse) {
        $this->content->items[] = $scormcourse;
    }
    //$this->content->items[] = '<br>';

    //Retroalimentación
    //$this->content->items[] = 'Retroalimentación:';
    $feedback = $DB->get_records('feedback', array('course' => $courseid), '', 'name', 0, 0);
    $feedbackcourse = array_column($feedback, 'name');

    foreach ($feedbackcourse as $feedbackcourse) {
        $this->content->items[] = $feedbackcourse;
    }
    //$this->content->items[] = '<br>';

    //Taller
    //$this->content->items[] = 'Talleres:';
    $workshop = $DB->get_records('workshop', array('course' => $courseid), '', 'name', 0, 0);
    $workshopcourse = array_column($workshop, 'name');

    foreach ($workshopcourse as $workshopcourse) {
        $this->content->items[] = $workshopcourse;
    }
    //$this->content->items[] = '<br>';

    //Tareas
    //$this->content->items[] = 'Tareas:';
    $assign = $DB->get_records('assign', array('course' => $courseid), '', 'name', 0, 0);
    $assigncourse = array_column($assign, 'name');

    foreach ($assigncourse as $assigncourse) {
        $this->content->items[] = $assigncourse;
    }
    //$this->content->items[] = '<br>';

    //wiki
    //$this->content->items[] = 'Wikis:';
    $wiki = $DB->get_records('wiki', array('course' => $courseid), '', 'name', 0, 0);
    $wikicourse = array_column($wiki, 'name');

    foreach ($wikicourse as $wikicourse) {
        $this->content->items[] = $wikicourse;
    }
    //$this->content->items[] = '<br>';
  }
}

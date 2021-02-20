<head>
  <link rel="stylesheet" type="text/css"  href="C:/xampp7/htdocs/moodle/blocks/tutorvirtual/styles.css">
</head>
<body>
  <?php
<<<<<<< HEAD

  /*require(__DIR__.'../config.php');  C:\xampp7\htdocs\moodle
  style='color: blue; background-color: lightblue;'
  */

  defined('MOODLE_INTERNAL') || die();
  require_once('../config.php');
  require_once("$CFG->libdir/formslib.php");
  class block_tutorvirtual extends block_list {

      public function init() {

      }

      // The PHP tag and the curly bracket for the class definition
      // will only be closed after there is another function added in the next section.

      public function get_content() {
        global $COURSE, $DB, $PAGE, $CFG;
        $PAGE->requires->jquery();
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/tutorvirtual/amd/src/scriptact.js') );
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/blocks/tutorvirtual/amd/src/DragAndDrop.js') );
        $PAGE->requires->css( new moodle_url($CFG->wwwroot . '/blocks/tutorvirtual/styles.css') );
        if ($this->content !== null) {
          return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        //Tomar input del usuario para enviarlo al profesor
        if (isset($_POST['textfield'])) {
          $message_content = $_POST['textfield'];
          $this->enviarMensaje($message_content);
          return;
        }

        //Menú
        $this->content->items[]  = 
        '
        <div id="div-arrastrable">
          <img id="imagen" src = "https://cdn.discordapp.com/attachments/699813602328051765/705960260653023282/huellita.png">
          <div id="menu" class="dropdown-content">
            <a id="menuActividades" class="opcion">Actividades</a>
            <a id="menuRecursos" class="opcion">Recursos</a>
            <a id="menuEnviarMensaje" class="opcion">Mensaje al profesor</a>
            <a id="menuNotificaciones" class="opcion">Notificaciones</a>
            <a id="menuPreguntasFrecuentes" class="opcion">Preguntas frecuentes</a>
          </div>
        </div>
        ';

        //Lista de Actividades
        $courseid = $PAGE->course->id;
        $course = $this->page->cm;

        $modules = $DB->get_records('grade_items', array('courseid' => $courseid,'itemtype' => 'mod' ), '', 'itemname, itemmodule,iteminstance', 0, 0);
        $moduleItem = array_column($modules, 'itemmodule');
        $moduleInstance = array_column($modules, 'iteminstance');
        $moduleName = array_column($modules, 'itemname');

        $n = count($moduleItem);

        $actividades = html_writer::start_tag('div', array('class'=>'dropdown-content', 'id'=>'imprimirActividades'));
        for ($i=0; $i <$n; $i++) {
          $id = $DB->get_field('course_modules', 'id', array('course' => $courseid, 'module' => $DB->get_field('modules', 'id', array('name' => $moduleItem[$i]), $strictness=IGNORE_MISSING),'instance' => $moduleInstance[$i] ), $strictness=IGNORE_MISSING);
          $actividades .= html_writer::link($CFG->wwwroot . "/mod/" . $moduleItem[$i]."/view.php?id=".$id, $moduleName[$i]);
          $actividades .= '<br>';
        }
        $this->content->items[] = $actividades;
        html_writer::end_tag('div');

        //Mensaje al Profesor
        $inputMensaje = html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'class'=>'dropdown-content', 'id'=>'inputMensaje'));
        $inputMensaje .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'textfield', 'id'=>'textfield'));
        $inputMensaje .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'button', 'value'=>'Enviar'));
        html_writer::end_tag('form');
        $this->content->items[] = $inputMensaje;

        //$this->imprimirActividades();
        //$this->imprimirRecursos();
        //$url = new moodle_url('/blocks/block_tutorvirtual/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        //$this->content->footer = html_writer::link($url, get_string('tutorVirtual', 'block_tutorvirtual'));

        //$this->content->items[] = html_writer::tag('div', html_writer::tag('span', "Actividades", array('class'=>'foo')), array('class'=>'blah','id'=>'imprimirActividades', 'style'=>'display:none;'));
        //$this->imprimirActividades();
        //$this->imprimirRecursos();
        //$url = new moodle_url('/blocks/block_tutorvirtual/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
        //$this->content->footer = html_writer::link($url, get_string('tutorVirtual', 'block_tutorvirtual'));

        return $this->content;
    }


=======

  /*require(__DIR__.'../config.php');  C:\xampp7\htdocs\moodle
  style='color: blue; background-color: lightblue;'
  */

  defined('MOODLE_INTERNAL') || die();
  require_once('../config.php');
  require_once("$CFG->libdir/formslib.php");
  class block_tutorvirtual extends block_list {


      public function init() {
      }

      // The PHP tag and the curly bracket for the class definition
      // will only be closed after there is another function added in the next section.

      public function get_content() {
        global $COURSE, $DB, $PAGE, $CFG;

        $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/tutorvirtual/styles.css'));

        if ($this->content !== null) {
          return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->items = array();
        $this->content->icons = array();

        $this->page->requires->js_call_amd('block_tutorvirtual/script', 'init');
        //tomar input del usuario para enviarlo al profesor
            if (isset($_POST['textfield'])) {
              $message_content = $_POST['textfield'];
              $this->enviarMensaje($message_content);
              return;
            }

            //Mensaje personalizado al profesor.

        //$this->content->items[]  = '<button><img src="/moodle/blocks/tutorvirtual/huellita.png" draggable=true width="100px" height="100px""/></button>';
        $this->content->items[]  = "<input id='imagen' type='image' src='https://cdn.discordapp.com/attachments/699813602328051765/705960260653023282/huellita.png' width='100px' height='100px' href='myFunction()' />";
        //$this->content->items[] = "<img class='imagen' src='https://cdn.discordapp.com/attachments/699813602328051765/705960260653023282/huellita.png' draggable=true style='margin-top:15px; border-radius: 30px; position:center;' onclick=location.href='https://www.facebook.com/stories/1825005327519671/UzpfSVNDOjEwMjE5NTM2NTA3ODU2Mzk3/?source=story_tray%27%3E</img>";
        $this->content->items[] = '<div id="menu" class="dropdown-content" style="display:none;right: 100%; bottom: 0;position: absolute;background-color: #f1f1f1;min-width: 160px;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);z-index: 1;">
                                      <button class="btn" id= "menuActividades" style="font-size: 17px;" >Actividades</button>
                                      <a href="#" style="color: black;padding: 12px 16px;text-decoration: none;display: block;">Notificaciones</a>
                                      <a id= "menuActividadess" href="#" style="color: black;padding: 12px 16px;text-decoration: none;display: block;">Actividades</a>
                                      <a id= "menuRecursos" href="#" style="color: black;padding: 12px 16px;text-decoration: none;display: block;">Recursos</a>
                                      <a href="#" style="color: black;padding: 12px 16px;text-decoration: none;display: block;">Enviar mensaje al profesor</a>
                                      <a href="#" style="color: black;padding: 12px 16px;text-decoration: none;display: block;">Otros</a>
                                   </div>';
          $courseid = $PAGE->course->id;
          $course = $this->page->cm;

           $modules = $DB->get_records('grade_items', array('courseid' => $courseid,'itemtype' => 'mod' ), '', 'itemname, itemmodule, iteminstance', 0, 0);
           $moduleItem = array_column($modules, 'itemmodule');
           $moduleInstance = array_column($modules, 'iteminstance');
           $moduleName = array_column($modules, 'itemname');
           $imprimirActividades =
           $n=count($moduleItem);

           $actividades = html_writer::start_tag('div', array('class'=>'dropdown-content','style'=>'display:none; right: 100%; bottom: 0; position: absolute;background-color: #f1f1f1;min-width: 160px;box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);z-index: 1;', 'id'=>'imprimirActividades'));
           for ($i=0; $i <$n; $i++) {
             $id = $DB->get_field('course_modules', 'id', array('course' => $courseid, 'module' => $DB->get_field('modules', 'id', array('name' => $moduleItem[$i]), $strictness=IGNORE_MISSING),'instance' => $moduleInstance[$i] ), $strictness=IGNORE_MISSING);
             $actividades .= html_writer::link($CFG->wwwroot."/mod/".$moduleItem[$i]."/view.php?id=".$id, $moduleName[$i]);
             $actividades .= '<br>';
           }
           html_writer::end_tag('div');
           $this->content->items[] = $actividades;

          //$this->content->items[] = html_writer::tag('div', html_writer::tag('span', "Actividades", array('class'=>'foo')), array('class'=>'blah','id'=>'imprimirActividades', 'style'=>'display:none;'));
          //$this->imprimirActividades();
          //$this->imprimirRecursos();
          $url = new moodle_url('/blocks/block_tutorvirtual/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
          $this->content->footer = html_writer::link($url, get_string('tutorVirtual', 'block_tutorvirtual'));

          //Mensaje personalizado al profesor.
          //$this->content->items[] = '<form method="post" action=""><input type="text" name="textfield" id="textfield"><input type="submit" name="button" id="button" value="Enviar"></form>';

          return $this->content;
    }
>>>>>>> cd4fa612ef2b7dc99097efbff43ccdebc83a50eb
    function definition() {
      global $CFG;

      $mform = $this->_form;
      $mform->addElement('footer','displayinfo', get_string('footerDescripcion', 'block_tutorvirtual'));
      $mform->addElement('button', 'intro', get_string("buttonlabel"));

    }
    function has_config() {
      return true;
    }

    function instance_allow_config() {
      return true;
    }
    function refresh_content() {
      // Nothing special here, depends on content()
      $this->content = NULL;
      return $this->get_content();
    }
    public function enviarMensaje($message_content){
        global $DB;
        global $PAGE;
        global $USER;
        $teachers = $this->get_course_teachers($DB, $PAGE);
        foreach ($teachers as $teacher) {
          $this->send_message_to_course_teacher($USER, $teacher, $PAGE, $message_content);
          $this->content->items[] = "Se ha enviado su mensaje";
        }
<<<<<<< HEAD
      }
      function get_course_teachers(mariadb_native_moodle_database $DB, moodle_page $PAGE) {
        $courseid = $PAGE->course->id;
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        $teachers = get_role_users($role->id, $context);
        return $teachers;
      }
=======
      }
      function get_course_teachers(mariadb_native_moodle_database $DB, moodle_page $PAGE) {
        $courseid = $PAGE->course->id;
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        $teachers = get_role_users($role->id, $context);
        return $teachers;
      }
>>>>>>> cd4fa612ef2b7dc99097efbff43ccdebc83a50eb
      function send_message_to_course_teacher(stdClass $USER, stdClass $teacher, moodle_page $PAGE, $message_content) {
        //create message
        $message = new \core\message\message();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $USER;
        $message->userto = $teacher;
        $message->fullmessage = $message_content;
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

     function imprimirActividades(){
      global $DB;
      global $PAGE;
      $courseid = $PAGE->course->id;
      //$user = $DB->get_record('course', array('shortname' => 'curso 1'), '*', MUST_EXIST);
      //$idcurso = $user->id;

      //$this->content = NULL;
      $course = $this->page->cm;
      //$this->content         =  new stdClass;
      //$this->content->items = array();

      //Base de datos
      $this->content->items[] = 'Base de datos:';
      $data = $DB->get_records('data', array('course' => $courseid), '', 'name', 0, 0);
      $datacourse = array_column($data, 'name');

      foreach ($datacourse as $datacourse) {
          $this->content->items[] = $datacourse;
      }
      $this->content->items[] = '<br>';

      //Chat
      $this->content->items[] = 'Chat:';
      $chat = $DB->get_records('chat', array('course' => $courseid), '', 'name', 0, 0);
      $chatcourse = array_column($chat, 'name');

      foreach ($chatcourse as $chatcourse) {
          $this->content->items[] = $chatcourse;
      }
      $this->content->items[] = '<br>';

      //Elección
      $this->content->items[] = 'Elección:';
      $choice = $DB->get_records('choice', array('course' => $courseid), '', 'name', 0, 0);
      $choicecourse = array_column($choice, 'name');

      foreach ($choicecourse as $choicecourse) {
          $this->content->items[] = $choicecourse;
      }
      $this->content->items[] = '<br>';

      /*Encuesta predefinida
      $survey = $DB->get_records('survey', array('course' => $courseid), '', 'name', 0, 0);
      $choicecourse = array_column($choice, 'name');

      foreach ($choicecourse as $choicecourse) {
          $this->content->items[] = $choicecourse;
      }
      */

      //Examen
      $this->content->items[] = 'Examen:';
      $quiz = $DB->get_records('quiz', array('course' => $courseid), '', 'name', 0, 0);
      $quizcourse = array_column($quiz, 'name');

      foreach ($quizcourse as $quizcourse) {
          $this->content->items[] = $quizcourse;
      }
      $this->content->items[] = '<br>';

      //Foro
      $this->content->items[] = 'Foro:';
      $forum = $DB->get_records('forum', array('course' => $courseid), '', 'name', 0, 0);
      $forumcourse = array_column($forum, 'name');

      foreach ($forumcourse as $forumcourse) {
          $this->content->items[] = $forumcourse;
      }
      $this->content->items[] = '<br>';

      //Glosario
      $this->content->items[] = 'Glosario:';
      $glossary = $DB->get_records('glossary', array('course' => $courseid), '', 'name', 0, 0);
      $glossarycourse = array_column($glossary, 'name');

      foreach ($glossarycourse as $glossarycourse) {
          $this->content->items[] = $glossarycourse;
      }
      $this->content->items[] = '<br>';

      //Herramienta externa
      $this->content->items[] = 'Herramientas externas:';
      $lti = $DB->get_records('lti', array('course' => $courseid), '', 'name', 0, 0);
      $lticourse = array_column($lti, 'name');

      foreach ($lticourse as $lticourse) {
          $this->content->items[] = $lticourse;
      }
      $this->content->items[] = '<br>';

      //leccion
      $this->content->items[] = 'Lecciones:';
      $lesson = $DB->get_records('lesson', array('course' => $courseid), '', 'name', 0, 0);
      $lessoncourse = array_column($lesson, 'name');

      foreach ($lessoncourse as $lessoncourse) {
          $this->content->items[] = $lessoncourse;
      }
      $this->content->items[] = '<br>';

      //Paquete SCORM
      $this->content->items[] = 'Paquetes SCORM:';
      $scorm = $DB->get_records('scorm', array('course' => $courseid), '', 'name', 0, 0);
      $scormcourse = array_column($scorm, 'name');

      foreach ($scormcourse as $scormcourse) {
          $this->content->items[] = $scormcourse;
      }
      $this->content->items[] = '<br>';

      //Retroalimentación
      $this->content->items[] = 'Retroalimentación:';
      $feedback = $DB->get_records('feedback', array('course' => $courseid), '', 'name', 0, 0);
      $feedbackcourse = array_column($feedback, 'name');

      foreach ($feedbackcourse as $feedbackcourse) {
          $this->content->items[] = $feedbackcourse;
      }
      $this->content->items[] = '<br>';

      //Taller
      $this->content->items[] = 'Talleres:';
      $workshop = $DB->get_records('workshop', array('course' => $courseid), '', 'name', 0, 0);
      $workshopcourse = array_column($workshop, 'name');

      foreach ($workshopcourse as $workshopcourse) {
          $this->content->items[] = $workshopcourse;
      }
      $this->content->items[] = '<br>';

      //Tareas
      $this->content->items[] = 'Tareas:';
      $assign = $DB->get_records('assign', array('course' => $courseid), '', 'name', 0, 0);
      $assigncourse = array_column($assign, 'name');

      foreach ($assigncourse as $assigncourse) {
          $this->content->items[] = $assigncourse;
      }
      $this->content->items[] = '<br>';

      //wiki
      $this->content->items[] = 'Wikis:';
      $wiki = $DB->get_records('wiki', array('course' => $courseid), '', 'name', 0, 0);
      $wikicourse = array_column($wiki, 'name');

      foreach ($wikicourse as $wikicourse) {
          $this->content->items[] = $wikicourse;
      }
      $this->content->items[] = '<br>';

      return $this->content;
    }

    public function imprimirRecursos(){
      global $DB;
      global $PAGE;
      $this->$content = NULL;
      $courseid = $PAGE->course->id;
      $course = $this->page->cm;
      $this->$content         =  new stdClass;
      $this->content->items = array();

      //Archivos
      $this->content->items[] = 'Archivos:';
      //$files = $DB->get_records('files', array('userid' => '2'), '', 'name', 0, 0);
      $filescourse = array_column($files, 'filename');

      foreach ($filescourse as $filescourse) {
          $this->content->items[] = $filescourse;
      }
      $this->content->items[] = '<br>';

      //Carpetas
      $this->content->items[] = 'Carpetas:';
      $folder = $DB->get_records('folder', array('course' => $courseid), '', 'name', 0, 0);
      $foldercourse = array_column($folder, 'name');

      foreach ($foldercourse as $foldercourse) {
          $this->content->items[] = $foldercourse;
      }
      $this->content->items[] = '<br>';

      //Etiqueta
      $this->content->items[] = 'Etiquetas:';
      $label = $DB->get_records('label', array('course' => $courseid), '', 'name', 0, 0);
      $labelcourse = array_column($label, 'name');

      foreach ($labelcourse as $labelcourse) {
          $this->content->items[] = $labelcourse;
      }
      $this->content->items[] = '<br>';

      //Libros
      $this->content->items[] = 'Libros:';
      $book = $DB->get_records('book', array('course' => $courseid), '', 'name', 0, 0);
      $bookcourse = array_column($book, 'name');

      foreach ($bookcourse as $bookcourse) {
          $this->content->items[] = $bookcourse;
      }
      $this->content->items[] = '<br>';

      //Páginas
      $this->content->items[] = 'Paquetes SCORM:';
      $page = $DB->get_records('page', array('course' => $courseid), '', 'name', 0, 0);
      $pagecourse = array_column($page, 'name');

      foreach ($pagecourse as $pagecourse) {
          $this->content->items[] = $pagecourse;
      }
      $this->content->items[] = '<br>';

      //Paquete de contenido IMS
      $this->content->items[] = 'Paquete de contenido IMS:';
      $imscp = $DB->get_records('imscp', array('course' => $courseid), '', 'name', 0, 0);
      $imscpcourse = array_column($imscp, 'name');

      foreach ($imscpcourse as $imscpcourse) {
          $this->content->items[] = $imscpcourse;
      }
      $this->content->items[] = '<br>';

      //Url
      $this->content->items[] = 'Url:';
      $url = $DB->get_records('url', array('course' => $courseid), '', 'name', 0, 0);
      $urlcourse = array_column($url, 'name');

      foreach ($urlcourse as $urlcourse) {
          $this->content->items[] = $urlcourse;
      }
      $this->content->items[] = '<br>';

    }
  }
  ?>
</body>

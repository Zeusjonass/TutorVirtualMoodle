<head>
  <link rel="stylesheet" type="text/css"  href="C:/xampp7/htdocs/moodle/blocks/tutorvirtual/styles.css">
</head>
<body>
  <?php

  defined('MOODLE_INTERNAL') || die();
  require_once('../config.php');
  require_once("$CFG->libdir/formslib.php");
  class block_tutorvirtual extends block_list {

      public function init() {

      }

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

        //Menú Principal
        $menu = html_writer::start_tag('div', array('id'=>'div-arrastrable'));
          $menu .= html_writer::empty_tag('img', array('id'=>'imagen', 'src'=>'https://media.discordapp.net/attachments/699813602328051765/812826296307548191/huellita.png?width=388&height=406'));
          $menu .= html_writer::start_tag('div', array('id'=>'menu', 'class'=>'dropdown-content'));
            $menu .= '<a id="menuActividades" class="opcion">Actividades</a>';
            $menu .= '<a id="menuRecursos" class="opcion">Recursos</a>';
            $menu .= '<a id="menuEnviarMensaje" class="opcion">Mensaje al profesor</a>';
            $menu .= '<a id="menuNotificaciones" class="opcion">Notificaciones</a>';
            $menu .= '<a id="menuPreguntasFrecPlataforma" class="opcion">Preguntas frecuentes de la Plataforma</a>';
            $menu .= '<a id="menuPreguntasFrecCurso" class="opcion">Preguntas frecuentes del Curso</a>';
          html_writer::end_tag('div');
        html_writer::end_tag('div');
        $this->content->items[] = $menu;

        //Lista de Actividades
        $courseid = $PAGE->course->id;
        $course = $this->page->cm;

        $modules = $DB->get_records('grade_items', array('courseid' => $courseid,'itemtype' => 'mod' ), '', 'itemname, itemmodule,iteminstance', 0, 0);
        $moduleItem = array_column($modules, 'itemmodule');
        $moduleInstance = array_column($modules, 'iteminstance');
        $moduleName = array_column($modules, 'itemname');

        $n = count($moduleItem);

        $actividades = html_writer::start_tag('div', array('class'=>'dropdown-content', 'id'=>'imprimirActividades'));
          for ($i=0; $i<$n; $i++) {
            $id = $DB->get_field('course_modules', 'id', array('course' => $courseid, 'module' => $DB->get_field('modules', 'id', array('name' => $moduleItem[$i]), $strictness=IGNORE_MISSING),'instance' => $moduleInstance[$i] ), $strictness=IGNORE_MISSING);
            $actividades .= html_writer::link($CFG->wwwroot . "/mod/" . $moduleItem[$i]."/view.php?id=".$id, $moduleName[$i]);
            $actividades .= html_writer::empty_tag('br');
          }
          $this->content->items[] = $actividades;
        html_writer::end_tag('div');

        //Mensaje al Profesor
        $inputMensaje = html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'class'=>'dropdown-content', 'id'=>'inputMensaje'));
          $inputMensaje .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'textfield', 'id'=>'textfield'));
          $inputMensaje .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'button', 'value'=>'Enviar'));
        html_writer::end_tag('form');
        $this->content->items[] = $inputMensaje;

        //Lista de Recursos
        $tiposRecursos = array('book','files','folder','imscp','label', 'page','url');
        $recursos = html_writer::start_tag('div', array('class'=>'dropdown-content', 'id'=>'imprimirRecursos'));   
        foreach($tiposRecursos as $tipoRecurso) {
          if ($tipoRecurso == 'files') {
            //$sql = '';
          }else {
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
            FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
            ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');

            for ($i=0; $i<count($moduleId); $i++) {
              $recursos .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
              $recursos .= html_writer::empty_tag('br');
            }
          }
        }
        html_writer::end_tag('div');
        $this->content->items[] = $recursos;

        return $this->content;
    }

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
      }

      function get_course_teachers(mariadb_native_moodle_database $DB, moodle_page $PAGE) {
        $courseid = $PAGE->course->id;
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        $teachers = get_role_users($role->id, $context);
        return $teachers;
      }
      
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

<?php

defined('MOODLE_INTERNAL') || die();
require_once('../config.php');
require_once("$CFG->libdir/formslib.php");
class block_tutorvirtual extends block_list {

  public function init() {

  }

  public function get_content() {
    global $COURSE, $DB, $PAGE, $CFG, $USER;
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

    $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    //Validamos que el usuario sea un estudiante
    $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    if (!has_capability('moodle/course:viewhiddensections', $coursecontext)) {
        //Menú Principal
        $menu .= html_writer::start_tag('div', array('id'=>'div-arrastrable'));
        $menu .= html_writer::start_tag('div', array('id'=>'img-wrapper'));
          $menu .= html_writer::empty_tag('img', array('id'=>'imagen', 'draggable'=>'true', 'clickable'=>'true', 'onclick'=>'toggleMenu()', 'src'=>'https://media.discordapp.net/attachments/699813602328051765/812826296307548191/huellita.png?width=388&height=406'));
        $menu .= html_writer::end_tag('div');
        $menu .= html_writer::start_tag('div', array());
          $menu .= html_writer::start_tag('ul', array('id'=>'menu', 'class'=>'ul-tutorvirtual'));

            // ACTIVIDADES
            $menu .= html_writer::start_tag('li', array('id'=>'actividades'));
              $menu .= '<a id="menuActividades">Actividades</a>';
              $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown scroll', 'id'=>'listaActs'));
                $tiposActividades = array('assign', 'chat', 'quiz', 'data', 'lti', 'feedback', 'forum', 'glossary', 'h5p', 'lesson', 'choice', 'scorm', 'survey', 'wiki', 'workshop');
                foreach($tiposActividades as $tipoActividad) {
                  if ($tipoActividad == 'h5p') {
                    //$sql = '';
                  } else {
                    if ($tipoActividad == 'forum') {
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name
                      FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      for ($i=0; $i<count($moduleId); $i++) {
                        if ($moduleName[$i] != 'Avisos') {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                    } else if($tipoActividad == 'assign'){     
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.duedate as duedate
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        $moduleDuedate = array_column($modules, 'duedate');
                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $fechaEntrega = getdate($moduleDuedate[$i]);
                          $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $fechaEntrega['minutes'] . '</p>';
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                      else if($tipoActividad == 'quiz'){
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.timeclose as timeclose
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        $moduleTimeclose = array_column($modules, 'timeclose');

                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $fechaEntrega = getdate($moduleTimeclose[$i]);
                          $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $fechaEntrega['minutes'] . '</p>';
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                      else if($tipoActividad == 'chat'){
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.chattime as chattime
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        $moduleChattime = array_column($modules, 'chattime');
                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $fechaEntrega = getdate($moduleChattime[$i]);
                          $menu .= '<p>Fecha de la sesión: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $fechaEntrega['minutes'] . '</p>';
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                      else if($tipoActividad == 'workshop'){
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.submissionend as submissionend
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        $moduleSubmissionend = array_column($modules, 'submissionend');
                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $fechaEntrega = getdate($moduleSubmissionend[$i]);
                          $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . '</p>';
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                      else if($tipoActividad == 'data'){
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.timeavailableto as timeavailableto
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        $moduleTimeavailableto = array_column($modules, 'timeavailableto');

                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $fechaEntrega = getdate($moduleTimeavailableto[$i]);
                          $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $fechaEntrega['minutes'] . '</p>';
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                      else{
                        $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name
                        FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoActividad.'
                        ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                        $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                        $moduleId = array_column($modules, 'id');
                        $moduleType = array_column($modules, 'type');
                        $moduleInstance = array_column($modules, 'instance');
                        $moduleName = array_column($modules, 'name');
                        for ($i=0; $i <count($moduleId); $i++) {
                          $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                          $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                          $menu .= html_writer::end_tag('li');
                        }
                      }
                  }
                }
              $menu .= html_writer::end_tag('ul');
            $menu .= html_writer::end_tag('li');

            // RECURSOS
            $menu .= html_writer::start_tag('li');
              $menu .= '<a id="opcion-recursos">Recursos</a>';
              $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown scroll'));
                $tiposRecursos = array('book','files','folder','imscp','label', 'page','url'); 
                foreach($tiposRecursos as $tipoRecurso) {
                  if ($tipoRecurso == 'files') {
                    //$sql = '';
                  }else {
                    $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
                    FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
                    ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
                    $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
                    $moduleId = array_column($modules, 'id');
                    $moduleType = array_column($modules, 'type');
                    $moduleInstance = array_column($modules, 'instance');
                    $moduleName = array_column($modules, 'name');

                    for ($i=0; $i<count($moduleId); $i++) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }
                  }
                }
              $menu .= html_writer::end_tag('ul');
            $menu .= html_writer::end_tag('li');

            //MENSAJE
            $menu .= html_writer::start_tag('li');
            $menu .= '<a>Mensaje al profesor</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown', 'id'=>'divMensaje'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a id="leyendaMensaje">¿Tienes alguna duda?<br>¡Envíale un mensaje a tu Profesor(a)!</a>';
                $menu .= html_writer::start_tag('form', array('method'=>'post', 'action'=>''));
                  $menu .= html_writer::start_tag('textarea', array('name'=>'textfield', 'id'=>'textfield', 'class'=>'form-control'));
                  $menu .= html_writer::end_tag('textarea');
                  $menu .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'button', 'value'=>'Enviar', 'class'=>'boton'));
                  $menu .= html_writer::end_tag('form');
                $menu .= html_writer::end_tag('li');
              $menu .= html_writer::end_tag('ul');
            $menu .= html_writer::end_tag('li');
            
            // PREGUNTAS PLATAFORMA
            $menu .= html_writer::start_tag('li');
              $menu .= '<a id="menuNotificaciones">Preguntas Frecuentes de la Plataforma</a>';
            $menu .= html_writer::end_tag('li');

            //PREGUNTAS CURSO
            $menu .= html_writer::start_tag('li');
              $menu .= '<a id="menuPreguntasFrecuentes">Preguntas Frecuentes del Curso</a>';
            $menu .= html_writer::end_tag('li');
      
          $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('div');
      $menu .= html_writer::end_tag('div');
      $this->content->items[] = $menu;

      //tomar input del usuario para enviarlo al profesor
      if (isset($_POST['textfield'])) {
        $message_content = $_POST['textfield'];
        $this->enviarMensaje($message_content);
        return;
      }
    }
    else{
      $formulario = html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'id'=>'formulario'));
        $formulario .= html_writer::div("Hola! Soy el tutor virtual",  array('id' => 'title'));
        $formulario .= html_writer::div("Mi propósito es ofrecer a lo estudiantes apoyo mientras cursan esta materia.",  array('id' => 'desc1', 'class' => 'desc'));
        $formulario .= html_writer::div("Ayúdame a lograrlo ingresando en los siguientes campos preguntas frecuentes que puedan encontrarse en este curso.",  array('id' => 'desc2', 'class' => 'decs'));
        $formulario .= html_writer::empty_tag('br');
        $formulario .= html_writer::div("Pregunta:",  array('id' => 'labelPregunta', 'class' => 'label'));
        $formulario .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'pregunta', 'id'=>'pregunta', 'required'=>'required', 'class'=>'inpurPregunta form-control'));
        $formulario .= html_writer::empty_tag('br');
        $formulario .= html_writer::div("Respuesta:",  array('id' => 'labelRespuesta', 'class' => 'label'));
        $formulario .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'respuesta', 'id'=>'respuesta', 'required'=>'required', 'class'=>'inpurPregunta form-control'));
        $formulario .= html_writer::empty_tag('br');
        $formulario .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'button', 'value'=>'Guardar', 'class'=>'boton'));
      $formulario .= html_writer::end_tag('form');
      $this->content->items[] = $formulario;
    }
    return $this->content;
  }

  function has_config() {
    return true;
  }

  function instance_allow_config() {
    return true;
  }

  public function enviarMensaje($message_content){
      global $DB;
      global $PAGE;
      global $USER;
      $teachers = $this->get_course_teachers($DB, $PAGE);
      foreach ($teachers as $teacher) {
        $this->send_message_to_course_teacher($USER, $teacher, $PAGE, $message_content);
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
}
?>

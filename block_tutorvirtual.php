<?php

defined('MOODLE_INTERNAL') || die();
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
    //$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    if(true){
    //if (!has_capability('moodle/course:viewhiddensections', $coursecontext)) {
        //Menú Principal
        $menu = html_writer::start_tag('div', array('id'=>'div-arrastrable'));
        $menu .= html_writer::start_tag('div', array('id'=>'img-wrapper'));
          $menu .= html_writer::empty_tag('img', array('id'=>'imagen', 'draggable'=>'true', 'clickable'=>'true', 'onclick'=>'toggleMenu()', 'src'=>'https://media.discordapp.net/attachments/699813602328051765/812826296307548191/huellita.png?width=388&height=406'));
        $menu .= html_writer::end_tag('div');
        $menu .= html_writer::start_tag('div', array());
          $menu .= html_writer::start_tag('ul', array('id'=>'menu', 'class'=>'ul-tutorvirtual cursor-default'));

            // Agregamos la sección de Actividades Pendientes
            $menu .= $this->listaActividades();

            // Agregamos la sección de Recursos
            $menu .= $this->listaRecursos();

            //Agregamos la sección de Enviar Mensaje al Profesor
            $menu .= $this->mensajeAlProfesor();
            
            // Agregamos la sección de Preguntas Frecuentes de la Plataforma
            $menu .= $this->preguntasPlataforma();

            // Agregamos la sección de Preguntas Frecuentes del Curso
            $menu .= $this->preguntasCurso();

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
      $formulario = $this->formularioProfesor();
      $this->content->items[] = $formulario;
      //Enviamos la pregunta a la base de datos
      if (isset($_POST['pregunta'])) {
        foreach($_POST['unidad'] as $selected) {
          $unidad = $selected;
        }
        $pregunta = $_POST['pregunta'];
        $respuesta = $_POST['respuesta'];
        $this->guardarPregunta($unidad, $pregunta, $respuesta);
        echo '<script type="text/javascript">'
          , 'alert("Muchas gracias por registrar su pregunta!");'
          , '</script>'
        ;
      }
    }

    return $this->content;
  }
  
  function guardarPregunta($unidad, $pregunta, $respuesta){
    global $DB, $CFG, $USER, $PAGE;
    $dataObj = new stdClass();
    $dataObj->unit = $unidad;
    $dataObj->question = $pregunta;
    $dataObj->answer = $respuesta;
    $dataObj->idcourse = $PAGE->course->id;
    $dataObj->profesorname = $USER->username;
    $DB->insert_record('course_question', $dataObj, true, false);
  }

  function listaActividades(){
    global $DB, $CFG, $PAGE;
    $idCourse = $PAGE->course->id;
    $timedActivities = array();
    $untimedActivities = array();
    $menu = html_writer::start_tag('li', array('id'=>'actividades'));
            $menu .= '<a id="menuActividades">Actividades</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown dropdown-tutorvirtual scroll', 'id'=>'listaActs'));

              $tiposActividades = array('assign', 'chat', 'quiz', 'data', 'lti', 'feedback', 'forum', 'glossary', 'h5p', 'lesson', 'choice', 'scorm', 'survey', 'wiki', 'workshop');
              foreach($tiposActividades as $tipoActividad) {
                if ($tipoActividad == 'h5p') {
                  //$sql = '';
                } else {
                  if ($tipoActividad == 'forum') {
                    $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS module, 
                    mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS element_name
                    FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = 
                    mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                    INNER JOIN mdl_'.$tipoActividad.' ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id 
                    AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                    $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'),0,0);
                    $moduleId = array_column($modules, 'id');
                    $moduleType = array_column($modules, 'type');
                    $moduleInstance = array_column($modules, 'instance');
                    $moduleName = array_column($modules, 'name');
                    for ($i=0; $i<count($moduleId); $i++) {
                      if ($moduleName[$i] != 'Avisos') {
                        $activity = array();
                        $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                        $activity['nombre'] = $moduleName[$i];
                        array_push($untimedActivities, $activity);
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
                        if($moduleDuedate[$i] == 0){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                        else if($moduleDuedate[$i] > time()){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          $activity['fecha'] = $moduleDuedate[$i];
                          array_push($timedActivities, $activity);
                        }
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
                        if($moduleTimeclose[$i] == 0){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                        else if($moduleTimeclose[$i] > time()){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          $activity['fecha'] = $moduleTimeclose[$i];
                          array_push($timedActivities, $activity);
                        }
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
                        if($moduleChattime[$i] == 0){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                        else if($moduleChattime[$i] > time()){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          $activity['fecha'] = $moduleChattime[$i];
                          array_push($timedActivities, $activity);
                        }
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
                        if($moduleSubmissionend[$i] == 0 || $moduleSubmissionend[$i] == 1616706660){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                        else if($moduleSubmissionend[$i] > time()){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          $activity['fecha'] = $moduleSubmissionend[$i];
                          array_push($timedActivities, $activity);
                        }
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
                        if($moduleTimeavailableto[$i] == 0){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                        else if($moduleTimeavailableto[$i] > time()){
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          $activity['fecha'] = $moduleTimeavailableto[$i];
                          array_push($timedActivities, $activity);
                        }
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
                        $activity = array();
                        $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                        $activity['nombre'] = $moduleName[$i];
                        array_push($untimedActivities, $activity);
                      }
                    }
                }
              }

              $timedActivitesSorted = $this->bubble_sort($timedActivities);
              
              for($i=0; $i<count($timedActivitesSorted); $i++){
                $fechaEntrega = getdate($timedActivitesSorted[$i]['fecha']);
                $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                $menu .= html_writer::link($timedActivitesSorted[$i]['link'], $timedActivitesSorted[$i]['nombre']);
                $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $this->add_minute_digits($fechaEntrega['minutes']) . '</p>';
                $menu .= html_writer::end_tag('li');
              }

              for($i=0; $i<count($untimedActivities); $i++){
                $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                $menu .= html_writer::link($untimedActivities[$i]['link'], $untimedActivities[$i]['nombre']);
                $menu .= html_writer::end_tag('li');
              }

            $menu .= html_writer::end_tag('ul');
          $menu .= html_writer::end_tag('li');
    return $menu;
  }

  function listaRecursos(){
    global $DB, $CFG;
    $menu = html_writer::start_tag('li');
      $menu .= '<a>Recursos</a>';
      $menu .= html_writer::start_tag('ul', array('id'=>'listaRecursos', 'class'=>'ul-tutorvirtual dropdown dropdown-tutorvirtual cursor-default'));
        
        $tiposRecursos = array('book','folder','files','page','url','imscp','label'); 

        foreach($tiposRecursos as $tipoRecurso){
          if($tipoRecurso == 'book'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>Libros</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'folder'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>Carpetas</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'page'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>Páginas</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'url'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>URLs</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'imscp'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>IMSCP</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'label'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON mdl_modules.id = mdl_course_modules.module INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>Etiquetas</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    $menu .= html_writer::start_tag('li');
                    $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                    $menu .= html_writer::end_tag('li');
                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }
        }
      $menu .= html_writer::end_tag('ul'); 
    $menu .= html_writer::end_tag('li');
    return $menu;
  }

  function mensajeAlProfesor(){
    $menu = html_writer::start_tag('li');
      $menu .= '<a>Mensaje al profesor</a>';
      $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown dropdown-tutorvirtual cursor-default', 'id'=>'divMensaje'));
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
    return $menu;
  }

  function preguntasPlataforma(){
   $menu = html_writer::start_tag('li');
      $menu .= '<a id="menuNotificaciones">Preguntas Frecuentes de la Plataforma</a>';
      $menu .= html_writer::start_tag('ul', array('id'=>'PregFrecPlat', 'class'=>'ul-tutorvirtual dropdown dropdown-tutorvirtual cursor-default'));
        
      $menu .= html_writer::start_tag('li');
        $menu .= '<a>Acceso y Navegación</a>';
          $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 cursor-default'));
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>¿Por qué no puedo acceder?</a>';
              $menu .= '<p>Podría haber muchas razones, pero la más probable es que simplemente haya olvidado su contraseña, 
              esté intentando acceder con una contraseña equivocada o la esté escribiendo incorrectamente. Algunas otras posibilidades son:<br><br>
              ¿Contienen su nombre de usuario o contraseña una mezcla de MAYÚSCULAS y minúsculas?. Si es así, deberán ser escritas en la forma exacta.<br>
              ¿Están habilitadas las cookies en su navegador?
              </p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>¿Cómo gano acceso a un curso?</a>';
              $menu .= '<p>Localice o busque el curso deseado (Usted puede elegir "Todos los cursos..." en el bloque de "Mis cursos") 
              y elija el nombre del curso. Si su profesor le ha dado una clave o contraseña para inscripción, escríbala en donde 
              corresponda y elija "Inscríbirme en este curso". Una vez que Usted esté insrito en el curso, aparecerá debajo de 
              "Mis cursos" cada vez que Usted ingrese al sitio Moodle.
              </p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>¿Cómo salto entre mis cursos?</a>';
              $menu .= '<p>Mediante su bloque de Mis Cursos si está disponible
              Vuelva a la página principal homepage (vea más adelante) y utilice el bloque de Mis Cursos del curso principal 
              (si está disponible!)
              </p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>¿Cómo regreso a la página principal del curso?</a>';
              $menu .= '<p>
              Utilice la Barra de navegación que está en la parte superior izquierda de la página, o use el botón en la parte 
              completamente inferior de la página del curso.
              </p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>¿Cómo puedo encontrar el curso X?</a>';
              $menu .= '<p>
              Si no está ya inscrito en un curso, puede buscarlo por el nombre y descripción.Si ya está inscrito le aparecerá 
              en su blog ( si está disponible).
              </p>';
            $menu .= html_writer::end_tag('li');
          $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>Contenido de Curso</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿A dónde se han ido todos los temas/semanas?</a>';
                $menu .= '<p>
                Probablemente pulsó en el icono One.gif. Para descubrir todos los otros temas/semanas necesita pulsar el icono 
                All.gif que verá en el margen derecho de temas/semanas. Si los temas están colapsados puede utilizar el combo 
                desplegable y moverse por los temas/semanas que se muestran para saltar a una sección oculta.
                </p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>Tareas y Calificaciones</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Por qué no hay botón de "subir" (o "grabar")?</a>';
                $menu .= '<p>
                Puede ser que:<br><br>

                  La tarea esté cerrada en este momento.<br>
                  La tarea no haya sido abierto aún.<br>
                  Ya ha subido/grabado algo antes y la configuración impida que repita las tareas.

                </p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Cómo puedo ver los comentarios del profesor a mis tareas recientes?</a>';
                $menu .= '<p>
                Hay muchas maneras de acceder a esos comentarios. El método más común consiste en ir al mismo sitio en 
                el que envió/subió el trabajo. Otro método consiste en pulsar en el vínculo que se encuentra en el bloque 
                "Actividades recientes", en el caso de que el profesor haya incluido ese bloque en su curso. Un método más 
                consistiría en accedere al libro de calificacionesy pinchar en el vínculo correspondiente a la tarea. 
                Dependiendo de cómo se haya configurado la tarea, puede recibir un correo electrónico si ha sido seleccionado 
                con un vínculo directo a los comentarios.
                </p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Por qué mi promedio del curso es tan baja?</a>';
                $menu .= '<p>
                No se asuste. El sistema de evaluación de Moodle tiene en cuenta los trabajos no calificados y los pendientes 
                de envío. Dicho de otro modo: Usted empieza con un cero y a medida que vaya avanzando a través del curso y 
                completando las actividades evaluables su nota subirá poco a poco.
                </p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
          $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>Exámenes</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Qué botón pulso cuando he terminado el examen?</a>';
                $menu .= '<p>
                Depende de lo que quiera hacer...
                </p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Porqué estoy obteniendo cero de calificación en mi examen?</a>';
                $menu .= '<p>
                Puede ser que haya excedido el tiempo límite para completar el examen. Esto nunca debería de suceder, porque 
                el cronómetro descendiente debería de enviar el examen automáticamente en cuanto se agote el tiempo, y después 
                el servidor Moodle debería de procesar su envío rápidamente. Sin embargo, si el servidor estuviera sobrecargado, 
                y corriendo lentamente, sus respuestas podrían no ser procesadas hasta después de un tiempo que sobrepasa el 
                margen permitido para envío del examen y a Usted no le estarán dando puntos para sus respuestas.
                </p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>Correos y Foros</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Por qué no tengo ningún correo y otros usuarios sí?</a>';
                $menu .= '<p>
                Es posible que su dirección de correo en su perfil esté errónea o deshabilitada. También pudiera ser que 
                no se haya suscrito a los foros que generan correos. Los usuarios AOL pueden no recibir correo tampoco 
                si el administrador ha bloqueado el uso de direcciones de correo AOL.
                </p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>¿Cómo puedo dejar de recibir todos estos correos?</a>';
                $menu .= '<p>
                El correo electrónico es esencial para el funcionamiento de Moodle. Se emplea para mantenerte informado de 
                las novedades. Si quieres reducir la cantidad de correos que recibes puedes:<br><br>

                  Editar su perfil y cambiar su configuración de e-mail para recibir resúmenes.<br>
                  Cancelar su subscripción a foros no esenciales (¡aunque existen por algo!).<br>
                  Inhabilitar su dirección de correo electrónico de su perfil, aunque esto no recomendable y puede ir en contra de las reglas de la casa.
                </p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

      $menu .= html_writer::end_tag('ul');
    $menu .= html_writer::end_tag('li');
    return $menu;
  }

  function preguntasCurso(){
    global $PAGE, $DB, $CFG;
    $courseid = $PAGE->course->id;
    $sql = 'SELECT section,name FROM mdl_course_sections WHERE course = '. $courseid .' AND section > 0';
    $sections = $DB->get_records_sql($sql, array('section', 'name'), 0, 0);
    $section_ids = array_column($sections, 'section');
    $section_names = array_column($sections, 'name');

    for($i=0; $i<count($section_names) ;$i++) {
      if(is_null($section_names[$i])) {
        $section_names[$i] = 'Tema ' . $section_ids[$i];
      }
    }

    if(count($section_names) > 0){
      $menu = html_writer::start_tag('li');
      $menu .= '<a id="menuPreguntasFrecuentes">Preguntas Frecuentes del Curso</a>';
        $menu .= html_writer::start_tag('ul', array('class'=>'ul-tutorvirtual dropdown dropdown-tutorvirtual scroll'));
          for ($i=0; $i<count($section_names); $i++) {
            $menu .= html_writer::start_tag('li');
            $menu .= html_writer::link($CFG->wwwroot . "/course/view.php?id=".$courseid."#section-".$section_ids[$i], $section_names[$i]);
            $menu .= html_writer::end_tag('li');
          }
        $menu .= html_writer::end_tag('ul');
      $menu .= html_writer::end_tag('li');
    }
    return $menu;
  }

  function formularioProfesor(){
    global $PAGE, $DB, $CFG;
    $courseid = $PAGE->course->id;
    $sql = 'SELECT section,name FROM mdl_course_sections WHERE course = '. $courseid .' AND section > 0';
    $sections = $DB->get_records_sql($sql, array('section', 'name'), 0, 0);
    $section_ids = array_column($sections, 'section');
    $section_names = array_column($sections, 'name');

    for($i=0; $i<count($section_names) ;$i++) {
      if(is_null($section_names[$i])) {
        $section_names[$i] = 'Tema ' . $section_ids[$i];
      }
    }

    $formulario = html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'id'=>'formulario'));
      $formulario .= html_writer::div("Hola! Soy el tutor virtual",  array('id' => 'title'));
      $formulario .= html_writer::div("Mi propósito es ofrecer a lo estudiantes apoyo mientras cursan esta materia.",  array('id' => 'desc1', 'class' => 'desc'));
      $formulario .= html_writer::div("Ayúdame a lograrlo ingresando en los siguientes campos preguntas frecuentes que puedan encontrarse en este curso.",  array('id' => 'desc2', 'class' => 'decs'));
      $formulario .= html_writer::empty_tag('br');
      if(count($section_ids)>1) {
        $formulario .= html_writer::div("Tema de la pregunta:", array('id'=>'labelTema','class'=>'label'));
        $formulario .= html_writer::start_tag('select', array('id'=>'unidad','name'=>'unidad[]','class'=>'form-select form-control'));
        for($i=0;$i<count($section_ids);$i++) {
          $formulario .= html_writer::start_tag('option',array('value'=>$section_ids[$i]));
          $formulario .= $section_names[$i];
          $formulario .= html_writer::end_tag('option');
        }
        $formulario .= html_writer::end_tag('select');
      }
      $formulario .= html_writer::empty_tag('br');
      $formulario .= html_writer::div("Pregunta:",  array('id' => 'labelPregunta', 'class' => 'label'));
      $formulario .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'pregunta', 'id'=>'pregunta', 'required'=>'required', 'class'=>'form-control'));
      $formulario .= html_writer::empty_tag('br');
      $formulario .= html_writer::div("Respuesta:",  array('id' => 'labelRespuesta', 'class' => 'label'));
      $formulario .= html_writer::start_tag('textarea', array('id'=>'respuesta', 'name'=>'respuesta', 'class'=>'form-control', 'required'=>'required',));
      $formulario .= html_writer::end_tag('textarea');
      $formulario .= html_writer::empty_tag('br');
      $formulario .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'button', 'value'=>'Guardar', 'class'=>'boton'));
    $formulario .= html_writer::end_tag('form');
    return $formulario;
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
  
  public function add_minute_digits($minutes){
    $finalMinutes = "";
    if($minutes < 10){
      $finalMinutes = "0" . $minutes;
      return $finalMinutes;
    }else{
      $finalMinutes .= $minutes;
      return $finalMinutes;
    }
  }
  
   function bubble_sort($timedActivities){
    $timedActivities = $timedActivities;
    if(count($timedActivities) > 1){
      $size = count($timedActivities) - 1;
      for($i=0; $i<$size; $i++){
        for ($j=0; $j<$size-$i; $j++) {
          $k = $j+1;
          if ($timedActivities[$k]['fecha'] < $timedActivities[$j]['fecha']) {
            // Swap elements at indices: $j, $k
            list($timedActivities[$j]['fecha'], $timedActivities[$k]['fecha']) = array($timedActivities[$k]['fecha'], $timedActivities[$j]['fecha']);
          }
        }

        return $timedActivities;
      }
    } else{
      return $timedActivities;
    }
    
  }
  
  function _self_test() {
    return true;
  }
  
}
?>


<?php
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
class block_tutorvirtual extends block_list {

  public function init() {
    $this->title = get_string('title', 'block_tutorvirtual');
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

    //Validamos que el usuario sea un estudiante
    $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    if (!has_capability('moodle/course:viewhiddensections', $coursecontext)) {
        //Menú Principal
        $menu = html_writer::start_tag('div', array('onclick'=>'arrastrable()', 'id'=>'div-arrastrable'));
        $menu .= html_writer::start_tag('div', array('id'=>'img-wrapper'));
          $menu .= html_writer::empty_tag('img', array('id'=>'btn-huellita', 'src'=>'https://media.discordapp.net/attachments/699813602328051765/812826296307548191/huellita.png?width=388&height=406'));
        $menu .= html_writer::end_tag('div');
        $menu .= html_writer::start_tag('div', array());
          $menu .= html_writer::start_tag('ul', array('id'=>'listaPrincipal', 'class'=>'lista-tutorVirtual cursor-default'));

            // Agregamos la sección de Actividades Pendientes
            $menu .= $this->listaActividades();

            // Agregamos la sección de Recursos
            $menu .= $this->listaRecursos();

            //Agregamos la sección de Ir a la cafeteria
            //$menu .= $this->linkCafeteria();

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
          , 'alert("'.get_string('savedQuestion', 'block_tutorvirtual').'");'
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
            $menu .= '<a>'.get_string('activities', 'block_tutorvirtual').'</a>';
            $menu .= html_writer::start_tag('ul', array('id'=>'listaActividades', 'class'=>'lista-tutorVirtual submenu-1 scroll'));

              $tiposActividades = array('assign', 'chat', 'quiz', 'data', 'lti', 'feedback', 'forum', 'glossary', 'h5p', 'lesson', 'choice', 'scorm', 'survey', 'wiki', 'workshop');
              foreach($tiposActividades as $tipoActividad) {
                if ($tipoActividad == 'h5p') {
                  //$sql = '';
                } else {
                  if ($tipoActividad == 'forum') {
                    $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress,
                    mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS element_name
                    FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id =
                    mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                    INNER JOIN mdl_'.$tipoActividad.' ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id
                    AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                    $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'element_name'),0,0);
                    $moduleId = array_column($modules, 'id');
                    $moduleType = array_column($modules, 'type');
                    $moduleDelete = array_column($modules, 'deletioninprogress');
                    $moduleInstance = array_column($modules, 'instance');
                    $moduleName = array_column($modules, 'element_name');

                    for ($i=0; $i<count($moduleId); $i++) {
                      if ($moduleDelete[$i] == 0) {
                        if ($moduleName[$i] != 'Avisos') {
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                      }
                    }
                  } else if($tipoActividad == 'assign'){
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance,
                      mdl_'.$tipoActividad.'.name AS name, mdl_'.$tipoActividad.'.duedate as duedate
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course
                      AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type', 'deletioninprogress','instance', 'name'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      $moduleDuedate = array_column($modules, 'duedate');


                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
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
                    }
                    else if($tipoActividad == 'quiz'){
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name,
                      mdl_'.$tipoActividad.'.timeclose as timeclose
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course
                      AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      $moduleTimeclose = array_column($modules, 'timeclose');

                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
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
                    }
                    else if($tipoActividad == 'chat'){
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name,
                      mdl_'.$tipoActividad.'.chattime as chattime
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id
                      AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name', 'chattime'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      $moduleChattime = array_column($modules, 'chattime');

                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
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
                    }
                    else if($tipoActividad == 'workshop'){
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name,
                      mdl_'.$tipoActividad.'.submissionend as submissionend
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type', 'deletioninprogress', 'instance', 'name'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      $moduleSubmissionend = array_column($modules, 'submissionend');


                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
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
                    }
                    else if($tipoActividad == 'data'){
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name,
                      mdl_'.$tipoActividad.'.timeavailableto as timeavailableto
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name', 'timeavailableto'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');
                      $moduleTimeavailableto = array_column($modules, 'timeavailableto');

                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
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
                    }
                    else{
                      $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoActividad.'.name AS name
                      FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
                      INNER JOIN mdl_'.$tipoActividad.'
                      ON (mdl_course_modules.instance = mdl_'.$tipoActividad.'.id AND mdl_course_modules.course = mdl_'.$tipoActividad.'.course AND mdl_modules.name = "'.$tipoActividad.'")';
                      $modules = $DB->get_records_sql($sql, array('id', 'type', 'deletioninprogress', 'instance', 'name'),0,0);
                      $moduleId = array_column($modules, 'id');
                      $moduleType = array_column($modules, 'type');
                      $moduleDelete = array_column($modules, 'deletioninprogress');
                      $moduleInstance = array_column($modules, 'instance');
                      $moduleName = array_column($modules, 'name');

                      for ($i=0; $i <count($moduleId); $i++) {
                        if ($moduleDelete[$i] == 0) {
                          $activity = array();
                          $activity['link'] = $CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i];
                          $activity['nombre'] = $moduleName[$i];
                          array_push($untimedActivities, $activity);
                        }
                      }
                    }
                }
              }

              $timedActivitesSorted = $this->bubble_sort($timedActivities);

              for($i=0; $i<count($timedActivitesSorted); $i++){
                if($timedActivitesSorted[$i]['nombre'] != null){
                  $fechaEntrega = getdate($timedActivitesSorted[$i]['fecha']);
                  $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                  $menu .= html_writer::link($timedActivitesSorted[$i]['link'], $timedActivitesSorted[$i]['nombre']);
                  $menu .= '<p>Entregable hasta: ' . $fechaEntrega['mday'] . '/' . $fechaEntrega['mon'] . '/' . $fechaEntrega['year'] . ' - ' . $fechaEntrega['hours'] . ':' . $this->add_minute_digits($fechaEntrega['minutes']) . '</p>';
                  $menu .= html_writer::end_tag('li');
                }
              }

              for($i=0; $i<count($untimedActivities); $i++){
                if($untimedActivities[$i]['nombre'] != null){
                  $menu .= html_writer::start_tag('li', array('class'=>'rowAct'));
                  $menu .= html_writer::link($untimedActivities[$i]['link'], $untimedActivities[$i]['nombre']);
                  $menu .= html_writer::end_tag('li');
                }
              }

            $menu .= html_writer::end_tag('ul');
          $menu .= html_writer::end_tag('li');
    return $menu;
  }

  function listaRecursos(){
    global $DB, $CFG, $PAGE;
    $idCourse = $PAGE->course->id;
    $menu = html_writer::start_tag('li');
      $menu .= '<a>'.get_string('resources', 'block_tutorvirtual').'</a>';
      $menu .= html_writer::start_tag('ul', array('id'=>'listaRecursos', 'class'=>'lista-tutorVirtual submenu-1 cursor-default'));

        $tiposRecursos = array('book','folder','files','page','url','imscp','label');

        foreach($tiposRecursos as $tipoRecurso){
          if($tipoRecurso == 'book'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('books', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                      }
                  }

                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'folder'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('folders', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }

                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'page'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type', 'deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('pages', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }

                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'url'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('urls', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }

                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'imscp'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('imscp', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }

                  }
                $menu .= html_writer::end_tag('ul');
              $menu .= html_writer::end_tag('li');
            }
          }

          if($tipoRecurso == 'label'){
            $sql = 'SELECT mdl_course_modules.id, mdl_modules.name AS type, mdl_course_modules.deletioninprogress, mdl_course_modules.instance, mdl_'.$tipoRecurso.'.name AS name
              FROM mdl_modules INNER JOIN mdl_course_modules ON (mdl_modules.id = mdl_course_modules.module AND mdl_course_modules.course = '.$idCourse.')
              INNER JOIN mdl_'.$tipoRecurso.'
              ON (mdl_course_modules.instance = mdl_'.$tipoRecurso.'.id AND mdl_course_modules.course = mdl_'.$tipoRecurso.'.course AND mdl_modules.name = "'.$tipoRecurso.'")';
            $modules = $DB->get_records_sql($sql, array('id', 'type','deletioninprogress', 'instance', 'name'), 0, 0);
            $moduleId = array_column($modules, 'id');
            $moduleType = array_column($modules, 'type');
            $moduleDelete = array_column($modules, 'deletioninprogress');
            $moduleInstance = array_column($modules, 'instance');
            $moduleName = array_column($modules, 'name');
            if(count($moduleId) > 0){
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('labels', 'block_tutorvirtual').'</a>';
                $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 scroll'));
                  for ($i=0; $i<count($moduleId); $i++) {
                    if ($moduleDelete[$i] == 0) {
                      $menu .= html_writer::start_tag('li');
                      $menu .= html_writer::link($CFG->wwwroot . "/mod/".$moduleType[$i]."/view.php?id=".$moduleId[$i], $moduleName[$i]);
                      $menu .= html_writer::end_tag('li');
                    }

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
      $menu .= '<a>'.get_string('msgTeacher', 'block_tutorvirtual').'</a>';
      $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-1 cursor-default', 'id'=>'mensajeProfesor'));
        $menu .= html_writer::start_tag('li');
          $menu .= '<a>'.get_string('msgCaption', 'block_tutorvirtual').'</a>';
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

  function linkCafeteria(){
    $menu = html_writer::start_tag('li');
      //Colocar el link en el href de abajo
      $menu .= '<a id="cafeteria" href="El link va aquí">'.get_string('cafeteria', 'block_tutorvirtual').'</a>';
    $menu .= html_writer::end_tag('li');
    return $menu;
  }

  function preguntasPlataforma(){
   $menu = html_writer::start_tag('li');
      $menu .= '<a>'.get_string('faqMoodle', 'block_tutorvirtual').'</a>';
      $menu .= html_writer::start_tag('ul', array('id'=>'preguntasFrecuentesPlataforma', 'class'=>'lista-tutorVirtual submenu-1 cursor-default'));

      $menu .= html_writer::start_tag('li');
        $menu .= '<a>'.get_string('faqMoodleT1', 'block_tutorvirtual').'</a>';
          $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>'.get_string('faqMoodleQ1', 'block_tutorvirtual').'</a>';
              $menu .= '<p>'.get_string('faqMoodleA1', 'block_tutorvirtual').'</p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>'.get_string('faqMoodleQ2', 'block_tutorvirtual').'</a>';
              $menu .= '<p>'.get_string('faqMoodleA2', 'block_tutorvirtual').'</p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>'.get_string('faqMoodleQ3', 'block_tutorvirtual').'</a>';
              $menu .= '<p>'.get_string('faqMoodleA3', 'block_tutorvirtual').'</p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>'.get_string('faqMoodleQ4', 'block_tutorvirtual').'</a>';
              $menu .= '<p>'.get_string('faqMoodleA4', 'block_tutorvirtual').'</p>';
            $menu .= html_writer::end_tag('li');
            $menu .= html_writer::start_tag('li');
              $menu .= '<a>'.get_string('faqMoodleQ5', 'block_tutorvirtual').'</a>';
              $menu .= '<p>'.get_string('faqMoodleA5', 'block_tutorvirtual').'</p>';
            $menu .= html_writer::end_tag('li');
          $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>'.get_string('faqMoodleT2', 'block_tutorvirtual').'</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ6', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA6', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>'.get_string('faqMoodleT3', 'block_tutorvirtual').'</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ7', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA7', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ8', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA8', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ9', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA9', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
          $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>'.get_string('faqMoodleT4', 'block_tutorvirtual').'</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ10', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA10', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ11', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA11', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
            $menu .= html_writer::end_tag('ul');
        $menu .= html_writer::end_tag('li');

        $menu .= html_writer::start_tag('li');
          $menu .= '<a>'.get_string('faqMoodleT5', 'block_tutorvirtual').'</a>';
            $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ12', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA12', 'block_tutorvirtual').'</p>';
              $menu .= html_writer::end_tag('li');
              $menu .= html_writer::start_tag('li');
                $menu .= '<a>'.get_string('faqMoodleQ13', 'block_tutorvirtual').'</a>';
                $menu .= '<p>'.get_string('faqMoodleA3', 'block_tutorvirtual').'</p>';
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

    //Secciones
    $sql = 'SELECT section,name FROM mdl_course_sections WHERE course = '. $courseid .' AND section > 0';
    $sections = $DB->get_records_sql($sql, array('section', 'name'), 0, 0);
    $section_ids = array_column($sections, 'section');
    $section_names = array_column($sections, 'name');

    //Preguntas
    $sql = 'SELECT question,answer, unit FROM mdl_course_question WHERE idcourse = '.$courseid.'';
    $questions_course = $DB->get_records_sql($sql, array('question', 'answer','unit'), 0, 0);
    $question = array_column($questions_course, 'question');
    $answer = array_column($questions_course, 'answer');
    $unit = array_column($questions_course, 'unit');

    for($i=0; $i<count($section_names) ;$i++) {
      if(is_null($section_names[$i])) {
        $section_names[$i] = 'Tema ' . $section_ids[$i];
      }
    }

    if(count($section_names) > 0){
      $menu = html_writer::start_tag('li');
        $menu .= '<a>Preguntas Frecuentes del Curso</a>';
        $menu .= html_writer::start_tag('ul', array('id'=>'preguntasFrecuentesCurso','class'=>'lista-tutorVirtual submenu-1'));
          for ($i=0; $i<count($section_names); $i++) {
            $menu .= html_writer::start_tag('li');
              $menu .= html_writer::link($CFG->wwwroot . "/course/view.php?id=".$courseid."#section-".$section_ids[$i], $section_names[$i]);
              $menu .= html_writer::start_tag('ul', array('class'=>'lista-tutorVirtual submenu-2 cursor-default'));
              for ($j=0; $j < count($question) ; $j++) {
                if ($section_ids[$i]== $unit[$j]) {
                  $menu .= html_writer::start_tag('li');
                    $menu .= '<a>'.$question[$j].'</a>';
                    $menu .= '<p>'.$answer[$j].'</p>';
                  $menu .= html_writer::end_tag('li');
                }
              }
              $menu .= html_writer::end_tag('ul');
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
        $section_names[$i] = get_string('topic', 'block_tutorvirtual') . ' ' . $section_ids[$i];
      }
    }
    $formulario = html_writer::start_tag('div', array('id'=>'divImgTutorVirtual'));
      $formulario .= html_writer::empty_tag('img', array('id'=>'imgTutorVirtual', 'src'=>'https://media.discordapp.net/attachments/699813602328051765/812826296307548191/huellita.png?width=388&height=406'));
    $formulario .= html_writer::end_tag('div');
    $formulario .= html_writer::start_tag('form', array('method'=>'post', 'action'=>'', 'id'=>'formulario'));
      $formulario .= html_writer::div(get_string('formTitle', 'block_tutorvirtual'),  array('id' => 'title'));
      $formulario .= html_writer::div(get_string('formDesc2', 'block_tutorvirtual'),  array('id' => 'desc1', 'class' => 'desc'));
      $formulario .= html_writer::div(get_string('formDesc3', 'block_tutorvirtual'),  array('id' => 'desc2', 'class' => 'desc'));
      $formulario .= html_writer::empty_tag('br');
      if(count($section_ids)>1) {
        $formulario .= html_writer::div(get_string('labelTopic', 'block_tutorvirtual'), array('id'=>'labelTema','class'=>'label'));
        $formulario .= html_writer::start_tag('select', array('id'=>'unidad','name'=>'unidad[]','class'=>'form-select form-control'));
        for($i=0;$i<count($section_ids);$i++) {
          $formulario .= html_writer::start_tag('option',array('value'=>$section_ids[$i]));
          $formulario .= $section_names[$i];
          $formulario .= html_writer::end_tag('option');
        }
        $formulario .= html_writer::end_tag('select');
      }
      $formulario .= html_writer::empty_tag('br');
      $formulario .= html_writer::div(get_string('labelQuestion', 'block_tutorvirtual'),  array('id' => 'labelPregunta', 'class' => 'label'));
      $formulario .= html_writer::empty_tag('input', array('type'=>'text', 'name'=>'pregunta', 'id'=>'pregunta', 'required'=>'required', 'class'=>'form-control'));
      $formulario .= html_writer::empty_tag('br');
      $formulario .= html_writer::div(get_string('labelAnswer', 'block_tutorvirtual'),  array('id' => 'labelRespuesta', 'class' => 'label'));
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

  function enviarMensaje($message_content){
    global $DB;
    global $PAGE;
    global $USER;
    $teachers = $this->get_course_teachers($DB, $PAGE);
    foreach ($teachers as $teacher) {
      $this->send_message_to_course_teacher($USER, $teacher, $PAGE, $message_content);
    }
  }

  function get_course_teachers($DB, $PAGE) {
    $courseid = $PAGE->course->id;
    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $teachers = get_role_users($role->id, $context);
    return $teachers;
  }

  function send_message_to_course_teacher(stdClass $USER, stdClass $teacher, $PAGE, $message_content) {
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
    //$timedActivities = $timedActivities;
    if(count($timedActivities) > 1){
      $size = count($timedActivities);
      for ($i = $size - 1; $i > 0; $i--){
        for($j = 0; $j<$i; $j++){
          if($timedActivities[$j]['fecha'] > $timedActivities[$j+1]['fecha']){
            $temp = $timedActivities[$j];
            $timedActivities[$j] = $timedActivities[$j+1];
            $timedActivities[$j + 1] = $temp;
          }
        }
      }

        return $timedActivities;
    }
    else{
      return $timedActivities;
    }

  }

  function _self_test() {
    return true;
  }

}
?>


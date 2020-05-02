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
  
    $this->content         =  new stdClass;
    $this->content->items = array();
    $this->content->icons = array();

    $this->content->icons[]  = '<img src="/moodle/blocks/tutorvirtual/imagen.png.jpeg" width="100px" height="100px" style="float: left"/>';
    $this->content->items[] = '<a href="/moodle/calendar/view.php?view=month">Ir al calendario</a>';
    $this->content->items[] = '<a href="/moodle/user/profile.php?id=2">Ir al perfil</a>';
    $this->content->items[] = '<a href="/moodle/calendar/view.php?view=upcoming">Ir a eventos próximos</a>';
    
    return $this->content;
  }

  function has_config() {
    return true;
  }

  function instance_allow_config() {
    return true;
  }
}

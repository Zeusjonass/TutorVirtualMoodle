<?php
class block_tutorvirtual extends block_base {

    public function init() {
        $this->title = get_string('Títulooooooooooo', 'block_simplehtml');
    }
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.

    public function get_content() {
      if ($this->content !== null) {
        return $this->content;
      }

      $course = $this->page->cm;
      $this->content         =  new stdClass;
      $this->content->text  = 'Ya funcionaaaaanaaaaa <br><br> aaaaaaaa <br><br> xxxxxxxxx';
      $this->content->footer = 'Holaaaaaaaa';

      return $this->content;
  }
}

<?php
function xmldb_block_tutorvirtual_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020041701) {

      // Define table course_question to be created.
      $table = new xmldb_table('course_question');

      // Adding fields to table course_question.
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('idcourse', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
      $table->add_field('profesorname', XMLDB_TYPE_TEXT, null, null, null, null, null);
      $table->add_field('question', XMLDB_TYPE_TEXT, null, null, null, null, null);
      $table->add_field('answer', XMLDB_TYPE_TEXT, null, null, null, null, null);
      $table->add_field('unit', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
      $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
      $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

      // Adding keys to table course_question.
      $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
      $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

      // Conditionally launch create table for course_question.
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      // Tutorvirtual savepoint reached.
      upgrade_block_savepoint(true, 2020041701, 'tutorvirtual');
    }

    return true;
}
?>
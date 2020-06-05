// Standard license block omitted.
/*
 * @package    block_overview
 * @copyright  2015 Someone cool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 /**
  * @module block_tutorvirtual/click
  */
define(['jquery'], function($) {
  return{
    init: function() {
      $('.imagen').click(function() {
        alert( "Esto está generado con JS");
      });
      $('.imagen').draggable();
    }
  };
});
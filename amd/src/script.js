// Standard license block omitted.
/*
* @package    block_overview
* @copyright  2015 Someone cool
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
/**
* @module block_tutorvirtual/click
*/
define(['jquery'], function() {
  return{
    init: function() {
      window.onclick = function(event) {
        var imagen = document.getElementById('imagen');
        var menu = document.getElementById('menu');
        var menuActividades = document.getElementById('menuActividades');
        var imprimirActividades = document.getElementById('imprimirActividades');
        if(event.target == menuActividades){
          menu.style.display='none';
          imprimirActividades.style.display='block';
        }
        else{
          imprimirActividades.style.display='none';
          if (event.target == imagen) {
            if (menu.style.display=='block') {
              menu.style.display='none';
            }else {
              menu.style.display='block';
            }
          }
          else{
            menu.style.display='none';
          }
        }
      };
    }
  };
});

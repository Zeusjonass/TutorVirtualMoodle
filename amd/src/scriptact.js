//alert("alerta de prueba");
window.onclick = function(event) {
  var imagen = document.getElementById('imagen');
  var menu = document.getElementById('menu');
  var menuActividades = document.getElementById('menuActividades');
  var imprimirActividades = document.getElementById('imprimirActividades');
  if(event.target == menuActividades){
    imprimirActividades.style.display='block';
    menu.style.display='none';
  }
  else{
    imprimirActividades.style.display='none';
    if (event.target == imagen) {
      if (menu.style.display=='block') {
        menu.style.display='none';
      }else {
        menu.style.display='block';
      }
    }else {
      menu.style.display='none';
    }
  }
};
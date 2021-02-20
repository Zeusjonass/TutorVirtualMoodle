//alert("alerta de prueba");
window.onclick = function(event) {
  var imagen = document.getElementById('imagen');
  var menu = document.getElementById('menu');
  var menuActividades = document.getElementById('menuActividades');
  var imprimirActividades = document.getElementById('imprimirActividades');
  var enviarMensaje = document.getElementById('menuEnviarMensaje');
  var inputMensaje = document.getElementById('inputMensaje');
  var textfield = document.getElementById('textfield');
  if(event.target == enviarMensaje || event.target == textfield){
    menu.style.display='none';
    inputMensaje.style.display='block';
  }
  else{
    inputMensaje.style.display='none';
    if(event.target == menuActividades){
      imprimirActividades.style.display='block';
      menu.style.display='none';
    }
    else{
      if(event.target != inputMensaje){
        imprimirActividades.style.display='none';
      }
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
  }
};
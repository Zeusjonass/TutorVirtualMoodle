window.onclick = function(event) {
  var menuPrincipal = document.getElementById('listaPrincipal');  
  var listas = document.querySelectorAll('.lista-tutorVirtual');
  var imagen = document.getElementById('btn-huellita'); 
  
  if(!imagen.contains(event.target)){
    var flag = true;
    listas.forEach(lista =>{
      if(lista.contains(event.target)){
        flag = false;
      }
    });
    if(flag){
      menuPrincipal.style.display = "none";
    }
  } else {
    if(menuPrincipal.style.display == "block"){
      menuPrincipal.style.display = "none";
    } else {
      menuPrincipal.style.display = "block";
      ubicacion = imagen.getBoundingClientRect();
      if(parseFloat(ubicacion.right) > parseFloat(window.innerWidth) / 2){
        if(window.innerWidth >= 800){
          menuPrincipal.style.removeProperty('left');
          menuPrincipal.style.right = "105%";
          subMenus = document.querySelectorAll('.submenu-1, .submenu-2');
          subMenus.forEach(subMenu => {
            subMenu.style.removeProperty('left');
            subMenu.style.right = "100%";
          });
        }
      } else {
        if(window.innerWidth >= 800){
          menuPrincipal.style.removeProperty('right');
          menuPrincipal.style.left = "105%";
          subMenus = document.querySelectorAll('.submenu-1, .submenu-2');
          subMenus.forEach(subMenu => {
            subMenu.style.removeProperty('right');
            subMenu.style.left = "100%";
          });
        }
      }
    }
  }
}


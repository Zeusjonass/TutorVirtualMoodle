window.onclick = function(event) {
  var menuBox = document.getElementById('menu');  
  var imagen = document.getElementById('imagen'); 
  var divMensaje = document.getElementById('divMensaje'); 
  var listaActs = document.getElementById('listaActs'); 
  if (event.target != imagen) {
    menuBox.style.display = "none";
  }
  else{
    if(menuBox.style.display == "block") {
      menuBox.style.display = "none";
    }
    else {
      ubicacion = imagen.getBoundingClientRect();
      if(parseFloat(ubicacion.right) > parseFloat(window.innerWidth) / 2){
        menuBox.style.left = "-190%"; 
        subMenus = document.querySelectorAll('.dropdown, .dropdown2');
        subMenus.forEach(subMenu => {
          subMenu.style.left = "-100%";
        });
        divMensaje.style.left = "-175%";
        listaActs.style.left = "-175%";
      }
      else{
        menuBox.style.left = "105%"; 
        subMenus = document.querySelectorAll('.dropdown, .dropdown2');
        subMenus.forEach(subMenu => {
          subMenu.style.left = "100%";
        });
      }   
      menuBox.style.display = "block";
    }
  }
}

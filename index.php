<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require("./logica/connection.php");
require("./logica/user2.php");
session_start();

if(!isset($_SESSION['email'])) {
    header('Location:./logica/cerrarsesion.php');
    exit();
}

$email=$_SESSION['email'];
$user = "SELECT * FROM usuarios INNER JOIN tipo_usuario ON usuarios.usuario_tipo = tipo_usuario.tipoUsuario_id WHERE usuarios.usuario_email='$email' AND usuarios.usuario_tipo!=1";
$resultUser = $connection->query($user);

$persona = null; // Inicializar la variable

if ($resultUser && mysqli_num_rows($resultUser) > 0) {
    while ($row = mysqli_fetch_array($resultUser)) {
        $persona = new usuario($row['usuario_id'], $row['usuario_nombre'], $row['usuario_edad'], $row['usuario_localidad'], $row['usuario_email'], $row['usuario_clave'], $row['usuario_descripcion'], $row['usuario_dateAct'], $row['usuario_fotoPerfil'], $row['usuario_portfolio'], $row['usuario_habilitado'], $row['tipoUsuario_nombre'], $row['usuario_estado']);
    }

    if ($persona) {
        $id = $persona->getId();
        $etiquetas = "SELECT * FROM usu_etiquetas INNER JOIN etiquetas ON usu_etiquetas.in_etiqueta = etiquetas.etiqueta_id WHERE usuario_id = $id";
        $resultEtiquetas = $connection->query($etiquetas);

        $chats = "SELECT * FROM mails_enviados INNER JOIN usuarios ON mails_enviados.mail_emisor = usuarios.usuario_id WHERE mail_receptor = $id ORDER BY mails_enviados.mail_id DESC";
        $resultChats = $connection->query($chats);

        $postulaciones = "SELECT * FROM postulaciones INNER JOIN usuarios ON postulaciones.postulacion_creador = usuarios.usuario_id ORDER BY postulaciones.postulaciones_id DESC";
        $resultPostulaciones = $connection->query($postulaciones);
    } else {
        // Redirigir si no se encuentra a la persona
        header("Location:./index-e.php");
        exit();
    }
} else {
    // Redirigir si la consulta no devuelve resultados
    header("Location:./index-e.php");
    exit();
}

if(date('Y-m-d') >= date('Y-m-d', strtotime($persona->getDateAct() . '+ 3 months'))){?>
  <script type="module">
    import { SendMail } from "./scripts/date_update.js"
    SendMail("<?php echo $_SESSION['email']; ?>")
  </script>
<?php }?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
  <title>inicio</title>
  <link rel="stylesheet" href="./estilos/index.css">
  <link rel="stylesheet" href="./estilos/estilo_footer.css">
  <link rel="icon" href="./imgs/logo_blanco.ico">
</head>
<body>
<script>
  function toggleInfo(item) {
    var info = item.nextElementSibling;
    console.log(info)
    if (info.style.display === "block") {
        info.style.display = "none";
    } else {
        info.style.display = "block";
    }
  }
  
  document.addEventListener("DOMContentLoaded", function() {
      const titles = document.querySelectorAll(".chat-item h3.title");

      titles.forEach(title => {
          title.addEventListener("click", function() {
              const parent = this.parentElement;
              parent.classList.toggle("active");
          });
      });
  });
</script>
<div class="contenedor">
    <header class="header">
      <nav class="navbar">
          <div class="menu-toggle">
              <label for="toggle" class="label">
                  <span>
                      <div class="dropdown">
                          <button class="dropbtn"><img style="width:27px; height:27px;"
                                  src="./imgs/user_icon.png" alt="user Icon"></button>
                          <div class="dropdown-content">
                              <a href="./vistas/edit-perfil-u.php">Editar Perfil</a>
                              <a href="./logica/cerrarsesion.php">Cerrar Sesion</a>
                          </div>
                      </div>
                  </span>
                  <span>
                      <div class="dropdown">
                          <button class="dropbtn"><img style="width:24px; height:24px;"
                                  src="./imgs/54206.png" alt="menu Icon"></button>
                          <div class="dropdown-content">
                              <a href="./index.php">Inicio</a>
                          </div>
                      </div>
                  </span>
              </label>
          </div>
      </nav>
    </header>
    <sidebar class="usuario">
        <h2><?php echo $persona->getTipo(); ?></h2>
        <img style="width:80px; height:80px; border-radius: 50%;" id="img-p" src="">
        <br>
        <p> <?php echo $persona->getNombre(); ?> </p>
        <ul class="eti">
            <?php while($row=mysqli_fetch_array($resultEtiquetas)){ ?>
              <li><a class="etiquetas"><?php echo $row['etiqueta_nombre']; ?> </a></li>
            <?php } ?>
        </ul>
    </sidebar>
    <sidebar class="chat">
      <h2>Notificaciones</h2>
      <?php while($row=mysqli_fetch_array($resultChats)){ ?>
        <div class="chat-item">
          <img src="./imgs/user_icon.png" alt="user Icon" width="30px">
          <h3 class="title"><?php echo $row['usuario_nombre']; ?></h3>
          <p><?php echo $row['mail_fechaEmision']; ?></p>
          <p class="info"><?php echo $row['mail_asunto']; ?> </p>
          <p class="info"><?php echo $row['mail_mensaje']; ?></p>
        </div>  
      <?php } ?>
    </sidebar>    
    <div class="postulacion">
      <h2>Ofertas de trabajo</h2>
      <?php while($row=mysqli_fetch_array($resultPostulaciones)){ 
                        if($row['postulaciones_estado']==1){?>
                          <div class="post">
                            <div class="item" onclick="toggleInfo(this)">
                              <img src="./imgs/user_icon.png" alt="user Icon" width="25px"> 
                              <h3><?php echo $row['postulaciones_titulo']; ?></h3>
                              <p><?php echo $row['postulaciones_fecha']; ?></p>
                            </div>
                          <div class="info">
                            <p><?php echo $row['usuario_nombre']; ?></p>
                            <p><?php echo $row['postulaciones_desc']; ?></p>
                            <ul clas="eti">
                              <?php
                              $posId=$row['postulaciones_id'];
                              $posteti="SELECT * FROM post_etiqueta p INNER JOIN etiquetas e ON p.in_etiqueta = e.etiqueta_id WHERE p.postulacion_id = $posId  ";
                              $ejeeti=$connection->query($posteti); 
                              while($row2=mysqli_fetch_array($ejeeti)){?>
                                  <li>
                                      <a class="etiquetas"><?php echo $row2['etiqueta_nombre']; ?></a>
                                  </li>      
                                      <?php };?>
                            </ul>
                            <br>
                            <?php
                            $pospost = "SELECT COUNT(*) as total FROM postulados WHERE postulacion_id=$posId AND usuario_id=$id";
                            $ejepospost = $connection->query($pospost);
                            $row = mysqli_fetch_assoc($ejepospost);
                            $count = $row['total'];
                            if ($count <= 0) { ?>
                                <a class="bot1" href="./logica/postularse.php?postulacion=<?php echo $posId; ?>&postulado=<?php echo $persona->getId(); ?>">Postularse</a>
                            <?php } else { ?>
                                <a class="bot1" href="./logica/despostularse.php?postulacion=<?php echo $posId; ?>&postulado=<?php echo $persona->getId(); ?>">Despostularse</a>
                            <?php } ?>
                          </div>
            </div>
              <?php }} ?>
  </div>
</div>
<?php 
include('./vistas/footer.html');

if (empty($persona->getFotoPerfil())) { ?>
    <script>
        document.getElementById('img-p').src = '../imgs/img_u/user.jpg';
    </script>
<?php } else { ?>
    <script>
        document.getElementById('img-p').src = '<?php echo $persona->getFotoPerfil(); ?>';
    </script>
<?php } ?>
</body>
</html>
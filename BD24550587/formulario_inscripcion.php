<?php
$con = mysqli_connect("localhost","root","");
$db = mysqli_select_db($con,"bd2_prac2");
// seleccion ayuntamiento
//nombre, apellido, usuario, contrasena, email, telefono
$consulta=" SELECT a.nombre as ayuntamiento
                FROM AYUNTAMIENTO a
            ";
$resultat = mysqli_query($con, $consulta);



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulario de Inscripción</title>

  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, sans-serif;
      background: linear-gradient(180deg, #eaf3ff, #ffffff);
      min-height: 100vh;
      color: #0f2a44;
    }

    header {
      background: #1e73be;
      color: #ffffff;
      text-align: center;
      padding: 26px 20px;
      font-size: 2rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    main {
      display: flex;
      justify-content: center;
      padding: 50px 20px;
    }

    .card {
      background: #ffffff;
      border-radius: 16px;
      padding: 40px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(30,115,190,0.2);
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    label {
      font-weight: 600;
      margin-top: 10px;
    }

    input, select {
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #cfdff1;
      font-size: 1rem;
    }

    input:focus, select:focus {
      outline: none;
      border-color: #1e73be;
      box-shadow: 0 0 0 2px rgba(30,115,190,0.15);
    }

    input[type="submit"] {
      margin-top: 20px;
      background: linear-gradient(135deg, #1e73be, #2f8be6);
      color: #ffffff;
      border: none;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    input[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(30,115,190,0.35);
    }
  </style>
</head>
<body>

<header>Formulario de Inscripción</header>

<main>
  <div class="card">
    <form action="anyade_voluntario.php">

      <label for="ayuntamiento">Selecciona tu ayuntamiento</label>
      <select name="ayuntamiento" id="ayuntamiento">
        <?php
          while ($registre = mysqli_fetch_array($resultat)) {
            echo '<option value="'.$registre['ayuntamiento'].'">'.$registre['ayuntamiento'].'</option>';
          }
        ?>
      </select>

      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" name="nombre">

      <label for="apellido">Apellido</label>
      <input type="text" id="apellido" name="apellido">

      <label for="usuario">Usuario</label>
      <input type="text" id="usuario" name="usuario">

      <label for="contrasena">Contraseña</label>
      <input type="password" id="contrasena" name="contrasena">

      <label for="email">Email</label>
      <input type="email" id="email" name="email">

      <label for="telefono">Teléfono</label>
      <input type="text" id="telefono" name="telefono">

      <input type="submit" value="Enviar">
    </form>
  </div>
</main>

</body>
</html>





<?php session_start();?>
<html>
<head>
  <title>Query Engine</title>
  <link href ="css/bootstrap.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div class="container" >
  <div class="panel panel-default" style="padding:5%; margin-top:2%">
    <form method="post" action="index.php" role="form" class="form">
      <div class="row form-group">
        <div class="col-lg-12">
          <label>Database Name: </label>
          <input type="text" name="database_name" id="database_name" required="required" class="form-control" />
      </div>
      </div>
      <div class="row">
        <div class="col-lg-6 form-group">
          <label>Username: </label>
          <input type="text" name="database_username" id="database_name" required="required" class="form-control" />
        </div>
        <div class="col-lg-6">
          <label>Database password: </label>
          <input type="password" name="database_password" id="database_name" required="required" class="form-control" />
        </div>
      </div>
      <p class="row text text-right">

        <input type="submit" name="database_submit" class=" btn btn-primary" value="Log in"/>
      </p>
    </form>

    <?php
        if(isset($_POST["database_submit"])){
          //since every request may need these parameters I need to persist them
          $_SESSION["database_name"]=$_POST["database_name"];
          $_SESSION["database_username"]=$_POST["database_username"];
          $_SESSION["database_password"]=$_POST["database_password"];
          $conn  = mysqli_connect("localhost",$_SESSION["database_username"],$_SESSION["database_password"],$_SESSION["database_name"]);
          if(!$conn){
              echo "<p class='text text-danger'> Cannot connet to the database </p>";
          }
          else{
              header("Location:queryBuilder.php");
          }

        }

     ?>
  </div>
</div>
</body>
</html>

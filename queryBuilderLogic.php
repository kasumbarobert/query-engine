<?php
session_start();
$conn  = mysqli_connect("localhost",$_SESSION["database_username"],$_SESSION["database_password"],$_SESSION["database_name"]);

if(isset($_POST["table_name"])){
  echo loadColumns($_POST["table_name"],$conn);
}
else if(isset($_POST["query_generated"])){
  echo getData($_POST["query_generated"],$conn);
}
else if(isset($_GET["table_name"])){
  echo loadColumns($_GET["table_name"],$conn);
}


function loadColumns($table,$conn){
  $columnsArray = array();
  $columns_query ="select column_name, data_type, character_maximum_length from INFORMATION_SCHEMA.COLUMNS where table_name = '$table'";
  $result = $conn->query($columns_query);
  $counter=0;
  while($column = mysqli_fetch_array($result)){
    $columnsArray[$counter++]=array("id"=>$column["column_name"],"label"=>$column["column_name"],"type"=>mapQueryBuilderType($column["data_type"]));
  }
  return json_encode($columnsArray);
}

function mapQueryBuilderType($type){
  if($type=="int"){
    return "integer";
  }
  else if($type=="varchar" || $type=="text" || $type=="enum"){
    return "string";
  }
  else if($type=="timestamp" || $type=="datetime" || $type="year"){
    return "date";
  }
  else {
    return $type;
  }
}

function getData($query,$conn){
  $result = $conn->query($query);
  $counter=0;
  $columnsArray=array();
  while($column = mysqli_fetch_assoc($result)){
    $columnsArray[$counter++]=$column;
  }
  return json_encode($columnsArray);
}

?>

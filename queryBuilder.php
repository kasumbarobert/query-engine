<?php
session_start();
if(!isset($_SESSION["database_name"]) || !isset($_SESSION["database_username"]) || !isset($_SESSION["database_password"])){
  header("Location:index.php");
}
$conn  = mysqli_connect("localhost",$_SESSION["database_username"],$_SESSION["database_password"],$_SESSION["database_name"]);
if(!$conn){
  $conn->error;
}
?>
<html>
<head>
  <title>Query Builder</title>
  <link href ="css/bootstrap.css" type="text/css" rel="stylesheet"/>
  <link href ="css/query-builder.default.min.css" type="text/css" rel="stylesheet"/>
  <link href ="css/custom.css" type="text/css" rel="stylesheet"/>




</head>
<body>
<div class="container">
  <div class="panel panel-default" style="padding:2%;">
    <form class="form" role="form form-inline">
      <div class="row form-group">
        <div class="col-lg-4 ">
          <label>Choose table (s)</label>
          <select class="form-control" id="tables_select">
            <?php
            //load all the tables in the database
            $query ="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='".$_SESSION["database_name"]."'";
            $results = $conn->query($query);
            echo mysqli_num_rows($results);
            while($table = mysqli_fetch_array($results)){
              echo "<option value=\"".$table["TABLE_NAME"]."\">".$table["TABLE_NAME"]."</option>";
            }
             ?>
          </select>
        </div>
        <div class="col-lg-4">
          <label>Choose Column(s)</label>
          <select class="form-control" id="columns_select" multiple="multiple" size="1">
          </select>
        </div>
    </div>
    </form>
    <div id="builder"></div>
    <div class="panel panel-primary table-wrapper-2">
      <table class="table table-stripped table-responsive-md" border="1" id="generated_table">
      </table>
    </div>
    <p class="row text text-right">
      <a download="table.csv" onclick="return ExcellentExport.csv(this, 'generated_table'); "><input type="submit" id="exportBtn" name="database_submit" class=" btn btn-primary" value="Export to CSV" style="display:none;"/></a>
    </p>
  </div>

    <script>

    </script>
</div>
<script src="js/vendor/jquery.js" type="text/javascript"></script>
  <script src="js/tooltip.js" type="text/javascript"></script>
<script type="text/javascript" src="js/vendor/doT.js"></script>
<script type="text/javascript" src="js/vendor/interact.js"></script>
<script type="text/javascript" src="js/vendor/jQuery.extendext.js"></script>
<script type="text/javascript" src="js/vendor/doT.js"></script>
<script type="text/javascript" src="js/vendor/moment.js"></script>
<script type="text/javascript" src="js/vendor/query-builder.min.js"></script>
<script type="text/javascript" src="js/vendor/sql-support/sql-parser.js"></script>
<script type="text/javascript" src="js/vendor/main.js"></script>
<script type="text/javascript" src="js/vendor/defaults.js"></script>
<script type="text/javascript" src="js/vendor/plugins.js"></script>
<script type="text/javascript" src="js/vendor/sql-support/plugin.js"></script>
<script type="text/javascript" src="js/vendor/excellentexport-1.4/excellentexport.js"></script>
<script type="text/javascript" src="js/vendor/jquery.dataTables.min.js"></script>

<script>
  $( document ).ready(function() {
    $('#builder').queryBuilder({
      filters: [{"id":"default"}],
         plugins: {
    'bt-tooltip-errors': { delay: 100 },
    'sortable': null
  }
    });
    $('#builder').on('afterAddRule.queryBuilder afterAddGroup.queryBuilder afterApplyGroupFlags.queryBuilder afterApplyRuleFlags.queryBuilder afterClear.queryBuilder afterCreateRuleFilters.queryBuilder afterCreateRuleInput.queryBuilder afterCreateRuleOperators.queryBuilder  afterDeleteGroup.queryBuilder afterDeleteRule.queryBuilder afterInit.queryBuilder afterReset.queryBuilder afterSetRules.queryBuilder afterUpdateGroupCondition.queryBuilder afterUpdateRuleFilter.queryBuilder afterUpdateRuleOperator.queryBuilder afterUpdateRuleValue.queryBuilder', function(e, rule, value) {
        reloadTable();
    });
    $('#columns_select').change(function(){
      reloadTable();
    });


    function reloadTable(){
      var sql =$('#builder').queryBuilder('getSQL', false, false)
      var query="";
      var where_clause ="";
      if(sql != null)
      {
        where_clause =" WHERE "+sql["sql"];
      }
      var selected_columns =$('#columns_select').val()
      var columns="";
      for(counter in selected_columns){
        columns+=selected_columns[counter]
        columns+=" , "
      }
      //if no column is selected
      if(columns ==""){
        columns=" * ";
      }
      else{
        //remove the last comma
        columns =columns.substring(0,columns.length-2);
      }
      if(sql !=null || selected_columns !=null){
       query= "SELECT "+columns+" FROM "+$("#tables_select").val()+where_clause;
        $.post("queryBuilderLogic.php", {query_generated:query},function(data){
          buildHtmlTable("#generated_table",JSON.parse(data));
        //  alert(" "+data);
      });
     }
    }
    // Builds the HTML Table out of myDataList.
    function buildHtmlTable(selector,myDataList) {
      $(selector).empty();
      $("#exportBtn").hide();
      var columns = addAllColumnHeaders(myDataList, selector);
      for (var i = 0; i < myDataList.length; i++) {
        var row$ = $('<tr/>');
        for (var colIndex = 0; colIndex < columns.length; colIndex++) {
          var cellValue = myDataList[i][columns[colIndex]];
          if (cellValue == null) cellValue = "";
          row$.append($('<td/>').html(cellValue));
        }

        $(selector).append(row$);
        if(myDataList.length>1){
          $("#exportBtn").show();
          $("#generated_table").DataTable();
        }
      }
}

// Adds a header row to the table and returns the set of columns.
// Need to do union of keys from all records as some records may not contain
// all records.
function addAllColumnHeaders(myList, selector) {
  var columnSet = [];
  var headerTr$ = $('<tr/>');

  for (var i = 0; i < myList.length; i++) {
    var rowHash = myList[i];
    for (var key in rowHash) {
      if ($.inArray(key, columnSet) == -1) {
        columnSet.push(key);
        headerTr$.append($('<th/>').html(key));
      }
    }
  }
  $(selector).append(headerTr$);

  return columnSet;
}
  function loadFilters(){
    var table_selected = $("#tables_select").val();
   $.post("queryBuilderLogic.php", {table_name:table_selected},function(data){

      var data = JSON.parse(data);
        $('#columns_select').empty()
      var string = "";
      var columns = data;
      for(column in columns){
        $('#columns_select').append($('<option>', {
            value: columns[column]["id"],
            text: columns[column]["id"]
        }));
      }
      $("#columns_select").attr( "size", 5);

      $('#builder').queryBuilder('setFilters',true, data);
    });
  }
  loadFilters();

    $("#tables_select").change(function(){
      loadFilters();
    });

    $("#columns_select").change(function(){
      var columns =$('#columns_select').val()
      var table_selected = $("#tables_select").val();


});
});
</script>


</body>
</html>

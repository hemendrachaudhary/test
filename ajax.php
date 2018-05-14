//C.I.
<script>
  function changeOrderStatus(orderID,val)
  {
    if(orderID != '')
    {
     $.ajax({
      type: "POST",
      url: "<?= base_url(); ?>web2/vendor/ChangeorderStatus",
      data: {'orderID': orderID,'oderStatus' : val},
      success: function (data2) {
        if(data2 == 1)
        {
          toastr.success('Order Status Changed');
        }
        else{
         toastr.error('Order Status could not be Changed','Sorry!!');
       }

     }
   });
   }
  }
  </script>
*******************************************************************************************************************************
//1053
  <script>
function getListMeber(desination) {


                            var desination_id = desination;
                            $.post('main.php?pid=-13&action=getMemberlist&applicant_type=' + $("#applicant_type").val() + '&app_id=' + $("#app_id").val() + '&file_id=' + $("#file_id").val() + '&tbl_id=' + $('#m_tbl_id').val(), $(this).serialize(), function (data) {


                                $('#' + desination_id).empty();

                                $('#' + desination_id).append(data);
                            }).fail(function () {
                                // just in case posting your form failed
                                alert("Posting failed.");
                            });
                            // to prevent refreshing the whole page page
                            return false;
                        }

</script>
  </script>
  if ($action == "get_school_list") {
    $inst_data = $loan->getInfo('institution_info', $app_id, $file_id, '', "", true);
    echo '<option value="">Please Select</option>  ';
    foreach ($inst_data as $data) {
        echo '<option value="' . $data['institute_code'] . '">' . $data['institution_name'] . '</option>';
    }
}


*************************************************************************************************************************************
core php Ajax

function getProduct(str) {
  var catid = document.getElementById('category_id').value;               
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("productfield").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "include/process.php?brandID=" + str +"&catId=" + catid, true);
        xmlhttp.send();
}
*****************************************************************************************************************************
Ajax form submit

<script>
                    $(document).ready(function(){
                      $('#user-register-form').submit(function(){
                        //disable the default form submission
                        event.preventDefault();
                        $.ajax({
                          url: 'include/process.php',
                          type: "POST",
                          data : new FormData($('#user-register-form')[0]),
                          processData: false,
                          contentType: false,
                          async:false
                        }).done(function(data){
                          $('#user-register-form')[0].reset();
                          $('#message1').html(data);
                        }).fail(function() {
                          alert( "Posting failed." );
                        });
                        // to prevent refreshing the whole page page
                        return false;
                      });
                    });
                  </script>
*******************************************************************************************************************************

     database connection


function dbConnect(){

    $hostName="localhost";
    $dbName="itsoluti_wedding";
    $userName="itsoluti_wedding";
    $passWord="hardwork123";

    $con=@mysql_connect($hostName,$userName,$passWord);
    $seldb=mysql_select_db($dbName);
}
******************************************************************************************************
function dbRowInsert($table_name, $form_data)
{
    // retrieve the keys of the array (column titles)
    $fields = array_keys($form_data);

    // build the query
    $sql = "INSERT INTO ".$table_name."
    (`".implode('`,`', $fields)."`)
    VALUES('".implode("','", $form_data)."')";

    // run and return the query result resource
    return mysql_query($sql) or die(mysql_error());
}

***************************************************************************************************************************************
function dbRowDelete($table_name, $where_clause='')
{
    // check for optional where clause
    $whereSQL = '';
    if(!empty($where_clause))
    {
        // check to see if the 'where' keyword exists
        if(substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE')
        {
            // not found, add keyword
            $whereSQL = " WHERE ".$where_clause;
        } else
        {
            $whereSQL = " ".trim($where_clause);
        }
    }
    // build the query
    $sql = "DELETE FROM ".$table_name.$whereSQL;

    // run and return the query result resource
    return mysql_query($sql);
}
**************************************************************************************************************************
function dbRowUpdate($table_name, $form_data, $where_clause='')
{
    // check for optional where clause
    $whereSQL = '';
    if(!empty($where_clause))
    {
        // check to see if the 'where' keyword exists
        if(substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE')
        {
            // not found, add key word
            $whereSQL = " WHERE ".$where_clause;
        } else
        {
            $whereSQL = " ".trim($where_clause);
        }
    }
    // start the actual SQL statement
    $sql = "UPDATE ".$table_name." SET ";

    // loop and build the column /
    $sets = array();
    foreach($form_data as $column => $value)
    {
         $sets[] = "`".$column."` = '".$value."'";
    }
    $sql .= implode(', ', $sets);

    // append the where statement
    $sql .= $whereSQL;

    // run and return the query result
    return mysql_query($sql);
}
    

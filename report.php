

<!-- ============================================================== -->
<!-- End Left Sidebar -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Page Content -->
<!-- ============================================================== -->
<div id="page-wrapper">
  <div class="container-fluid">
   <!-- .row -->
   <div class="row">
    <div class="row">
     <div class="col-md-12">
      <div class="panel">
       <div class="panel-heading">Report</div>

       <div class="row">
         <form method="post" id="form-filter">
          <div class="col-sm-3">             
           <div class="form-group">
            <label class="col-sm-4" for="user">Users</label> 
            <?php echo $form_user; ?> 
          </div>
        </div> 

        <div class="col-sm-3">             
         <div class="form-group">
          <label class="col-sm-4" for="call_status">Call Status</label> 
          <?php echo $form_status; ?> 
        </div>
      </div>

      <div class="col-sm-3">             
       <div class="form-group">
        <label class="col-sm-4" for="meeting_type">Meeting Type</label> 
        <?php echo $form_meeting; ?> 
      </div>
    </div>

    <div class="col-sm-3" style="margin-top: 20px;">             
     <div class="form-group">
       <label class="col-sm-4"></label>

       <button type="button" id="btn-filter" class="btn btn-primary">Filter</button>
       <button type="button" id="btn-reset" class="btn btn-default">Reset</button>

     </div>
   </div>

 </form>
</div>


<div id="example23_wrapper" class="dataTables_wrapper dt-bootstrap4">
  <table id="table" class="display nowrap table table-hover table-striped table-bordered dataTable" cellspacing="0" width="100%" role="grid" aria-describedby="example23_info" style="width: 100%;">    <thead>
    <tr>
     <th>Sno.</th>
     <th>Lead Id</th>
     <th>Name</th>
     <th>Email</th>
     <th>Mobile No.</th>
     <th>Whatsapp No.</th>
     <th>Address</th>
     <th>State</th>
     <th>city</th>
     <th class="text-center"><i class="fa fa-comments" aria-hidden="true"></i></th>
                     <!-- <th class="text-center">Call Feedback </th>
                       <th class="text-center">Meeting Feedback </th> -->
                    
                     </tr>
                   </thead>
                   <tbody>
                   </tbody>

                   <tfoot>
                    <tr>
                     <th> </th>
                     <th>Lead Id</th>
                     <th>Name</th>
                     <th>Email</th>
                     <th>Mobile No.</th>
                     <th>Whatsapp No.</th>
                     <th>Address</th>
                     <th>State</th>
                     <th>city</th>
                     <th class="text-center"><i class="fa fa-comments" aria-hidden="true"></i></th>
                     <!-- <th class="text-center">Call Feedback </th>
                       <th class="text-center">Meeting Feedback </th> -->
                     
                     </tr>
                   </tfoot>
                 </table>
               </div>
             </div>
           </div>
         </div>
       </div>
     </div>
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>  

     <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> 
     <script>
      function loadDoc(val) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {

          }
        };
        xhttp.open("GET", "<?php echo base_url();?>index.php/Admin/click2Call/"+val, true);
        xhttp.send();
      }
      var table;

      $(document).ready(function() {

    //datatables
    table = $('#table').DataTable({ 

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
        dom: 'Bflrtip',
        buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        // Load data for the table's content from an Ajax source
        "ajax": {
          "url": "<?php echo site_url('index.php/Admin/lead_list_report')?>",
          "type": "POST",
          "data": function ( data ) {
            data.user = $('#user').val();
            data.call_status = $('#call_status').val();
            data.meeting_type = $('#meeting_type').val(); 
          }
        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0 ], //first column / numbering column
            "orderable": false, //set not orderable
          },
          ],

        });
    $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
      });
    $('#btn-reset').click(function(){ //button reset event click
      $('#form-filter')[0].reset();
        table.ajax.reload();  //just reload table
      });

    $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary m-r-10');
  });
</script>


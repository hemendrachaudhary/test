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

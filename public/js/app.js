
  var incomesTable = $('#incomes-table').DataTable( {  
    "ajax": {
      "url": "/incomes/getAction",
      "dataSrc": "data",
      "error": function(jqXHR, textStatus, errorThrown) {
        console.error("Error loading data: ", textStatus, errorThrown);
        console.error("Response: ", jqXHR.responseText);
      }
    },
    columns: [
      { data : 'id'},
      { data : 'user_id'},
      { data : 'income_category_assigned_to_user_id'},
      { data : 'amount'},
      { data : 'date_of_income'},
      { data : 'income_comment'},
    ],     
  });


$('#incomes-table tbody').on('click', 'tr', function() {

  if ($(this).hasClass('selected')) {
    $(this).removeClass('selected');
  }
  else {
    incomesTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  }
});


/**
         * Add jQuery Validation plugin method for a valid password
         *
         * Valid passwords contain at least one letter and one number.
         */
$.validator.addMethod('validPassword',
  function (value, element, param) {

    if (value != '') {
      if (value.match(/.*[a-z]+.*/i) == null) {
        return false;
      }
      if (value.match(/.*\d+.*/) == null) {
        return false;
      }
    }

    return true;
  },
  'Must contain at least one letter and one number'
);


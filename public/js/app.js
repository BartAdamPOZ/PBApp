
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
      
      { data : 'income_category_assigned_to_user_id'},
      { data : 'amount'},
      { data : 'date_of_income'},
      { data : 'income_comment'},
    ],     
  });


$('#incomes-table tbody').on('click', 'tr', function() {
  if ($(this).hasClass('selected')) {
    $(this).removeClass('selected');
    $(this).removeAttr('data-bs-toggle data-bs-target');
  } else {
    incomesTable.$('tr.selected').removeClass('selected')
      .removeAttr('data-bs-toggle data-bs-target'); 
    $(this).addClass('selected');
    $(this).attr('data-bs-toggle', 'modal');
    $(this).attr('data-bs-target', '#exampleModal');
  }
});


$('#show-selected').on('click', function() {
  var selectedData = incomesTable.row('.selected').data();
  if (selectedData) {
    console.log("Selected row data:", selectedData);
    alert("Selected Income: " + JSON.stringify(selectedData));
  } else {
    console.log("No row selected");
    alert("No row selected");
  }
});

$('#add-income-button').on('click', function() {
    $(this).attr('data-bs-toggle', 'modal');
    $(this).attr('data-bs-target', '#exampleModal');
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


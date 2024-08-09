
  var incomesTable = $('#incomes-table').DataTable( {  
    processing: true,
    "ajax": {
      "url": "/incomes/getAction",
      "dataSrc": "data",
      "error": function(jqXHR, textStatus, errorThrown) {
        console.error("Error loading data: ", textStatus, errorThrown);
        console.error("Response: ", jqXHR.responseText);
      }
    },
    columns: [
      { data : 'id', visible: false},
      { data : 'income_category_assigned_to_user_id', visible: false},
      { data : 'category_name'},
      { data : 'amount'},
      { data : 'date_of_income'},
      { data : 'income_comment'},
    ],
    createdRow: function(row, data) {
      $(row).attr('data-id', data.id);
    }
  });

  function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0'); 
    const day = String(today.getDate()).padStart(2, '0'); 
    return `${year}-${month}-${day}`;
}

$('#add-income-button').on('click', function() {

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#incomeModal');

  $('#formAddIncome').attr('action', '/incomes/add');

  $('#incomeModalLabel').html('Add Income Details');
  $('#incomeModalSubmitButton').html('Add');

});

$('#edit-income-button').on('click', function() {
  var selectedRow = incomesTable.row('.selected');
  var selectedData = selectedRow.data();

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#incomeModal');

  $('#incomeModalLabel').html('Edit Income Details');
  $('#incomeModalSubmitButton').html('Save changes');
  $('#incomeModalSubmitButton');

  $('#formAddIncome').attr('action', '/incomes/edit');
  
  $('#incomeAmount').val(selectedData.amount);
  $('#incomeCategoryId').val(selectedData.income_category_assigned_to_user_id);

  $('#incomeCategoryId option').each(function() {
    if ($(this).val() == selectedData.income_category_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#dateOfIncome').val(selectedData.date_of_income);
  $('#incomeComment').val(selectedData.income_comment);

  if ($('#incomeId').length === 0) {
    $('<input>').attr({
        type: 'hidden',
        id: 'incomeId',
        name: 'id',
        value: selectedData.id
    }).appendTo('#formAddIncome');
  } else {
    $('#incomeId').val(selectedData.id);
  }

});



$('#delete-income-button').on('click',function() {

  var selectedRow = incomesTable.row('.selected');
  var selectedData = selectedRow.data();

  $('#formAddIncome').attr('action', '/incomes/delete');
  $('#incomeModalLabel').html('Delete Income');
  $('#incomeModalSubmitButton').html('Delete');

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#incomeModal');

  $('#incomeAmount').val(selectedData.amount).attr('disabled', true);
  $('#incomeCategoryId').val(selectedData.income_category_assigned_to_user_id).attr('disabled', true);

  $('#incomeCategoryId option').each(function() {
    if ($(this).val() == selectedData.income_category_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#dateOfIncome').val(selectedData.date_of_income).attr('disabled', true);
  $('#incomeComment').val(selectedData.income_comment).attr('disabled', true);

  if ($('#incomeId').length === 0) {
    $('<input>').attr({
        type: 'hidden',
        id: 'incomeId',
        name: 'id',
        value: selectedData.id
    }).appendTo('#formAddIncome');
  } else {
    $('#incomeId').val(selectedData.id);
  }
  
});


$('#incomeModal').on('hide.bs.modal', function() {

  $('#incomeAmount').val('').attr('disabled', false);
  $('#incomeCategoryId').val('').attr('disabled', false);
  $('#dateOfIncome').val(getTodayDate()).attr('disabled', false);
  $('#incomeComment').val('').attr('disabled', false);
  $('#incomeModalSubmitButton').html('').attr('disabled', false);

});


$('#incomes-table tbody').on('click', 'tr', function(event) {
  event.stopPropagation();

  if ($(this).hasClass('selected')) {
    $(this).removeAttr('data-bs-toggle data-bs-target');
    $('#edit-income-button').addClass('disabled');
    $('#delete-income-button').addClass('disabled');

  } else {

    incomesTable.$('tr.selected').removeClass('selected')
      .removeAttr('data-bs-toggle data-bs-target'); 
    $(this).addClass('selected');
    $('#edit-income-button').removeClass('disabled');
    $('#delete-income-button').removeClass('disabled');
    $(this).attr('data-bs-toggle', 'modal');
    $(this).attr('data-bs-target', '#incomeModal');
  }
});


$(document).on('click', function(event) {
  if (!$(event.target).closest('#incomes-table').length) {
    $('#incomes-table tbody').find('tr.selected').removeClass('selected')
      .removeAttr('data-bs-toggle data-bs-target');
    $('#edit-income-button').addClass('disabled');
    $('#delete-income-button').addClass('disabled');
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


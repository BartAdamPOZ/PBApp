/** INCOMES PAGE  */

/**Load data into incomes table using ajax request */
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

/**Return today's date - YYYY-MM-DD */
  function getTodayDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0'); 
    const day = String(today.getDate()).padStart(2, '0'); 
    return `${year}-${month}-${day}`;
}

/** Display modal when user click on the Add button */
$('#add-income-button').on('click', function() {

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#incomeModal');

  $('#formAddIncome').attr('action', '/incomes/add');

  $('#incomeModalLabel').html('Add Income Details');
  $('#incomeModalSubmitButton').html('Add');

});

/** Display modal when user click on the Edit button. Load data from selected row into displayed form. */
$('#edit-income-button').on('click', function() {
  var selectedRow = incomesTable.row('.selected');
  var selectedData = selectedRow.data();

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#incomeModal');

  $('#incomeModalLabel').html('Edit Income Details');
  $('#incomeModalSubmitButton').html('Save changes');


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


  /**Hide id of selected income row. It's neccessary only to identyfy selected income in case of updating it in the database. */
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

/** Display modal when user click on the Delete button. Load data from selected row into displayed form and set class on each element as disabled so there is no way to edit it. */
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

  /**Hide id of selected income row. It's neccessary only to identyfy selected income in case of deleting it in the database. */
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

/** Reset the form and some attributes of it when modal is closing. */
$('#incomeModal').on('hide.bs.modal', function() {

  $('#incomeAmount').attr('disabled', false);
  $('#incomeCategoryId').attr('disabled', false);
  $('#dateOfIncome').val(getTodayDate()).attr('disabled', false);
  $('#incomeComment').attr('disabled', false);
  $('#incomeModalSubmitButton').html('').attr('disabled', false);
  $('#formAddIncome')[0].reset();

});

/** Select row on click and add selected class to tr element. Also activate edit and delete buttons. */
$('#incomes-table tbody').on('click', 'tr', function(event) {
  event.stopPropagation();

  if($(this).attr('data-id')) {
    
    $('#incomes-table tbody tr').removeClass('selected');

    $(this).addClass('selected');
    $('#edit-income-button').removeClass('disabled');
    $('#delete-income-button').removeClass('disabled');

  }

});

/** This action works when row is selected and user click outside the table. Set edit and delete buttons disabled. Remove selection from row. */
$(document).on('click', function(event) {
  if (!$(event.target).closest('#incomes-table').length) {
    $('#incomes-table tbody').find('tr.selected').removeClass('selected')
      .removeAttr('data-bs-toggle data-bs-target');
    $('#edit-income-button').addClass('disabled');
    $('#delete-income-button').addClass('disabled');
  }
});

/** EXPENSES PAGE */

/** */
var expensesTable = $('#expenses-table').DataTable( {  
  processing: true,
  "ajax": {
    "url": "/expenses/getAction",
    "dataSrc": "data",
    "error": function(jqXHR, textStatus, errorThrown) {
      console.error("Error loading data: ", textStatus, errorThrown);
      console.error("Response: ", jqXHR.responseText);
    }
  },
  columns: [
    { data : 'id', visible: false},
    { data : 'expense_category_assigned_to_user_id', visible: false},
    { data : 'payment_method_assigned_to_user_id', visible: false},
    { data : 'category_name'},
    { data : 'payment_method_name'},
    { data : 'amount'},
    { data : 'date_of_expense'},
    { data : 'expense_comment'},
  ],
  createdRow: function(row, data) {
    $(row).attr('data-id', data.id);
  }
});

/** Display modal when user click on the Add button */
$('#add-expense-button').on('click', function() {

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#expenseModal');

  $('#formAddExpense').attr('action', '/expenses/add');

  $('#expenseModalLabel').html('Add Expense Details');
  $('#expenseModalSubmitButton').html('Add');

});

/** Display modal when user click on the Edit button. Load data from selected row into displayed form. */
$('#edit-expense-button').on('click', function() {
  var selectedRow = expensesTable.row('.selected');
  var selectedData = selectedRow.data();

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#expenseModal');

  $('#expenseModalLabel').html('Edit Expense Details');
  $('#expenseModalSubmitButton').html('Save changes');


  $('#formAddExpense').attr('action', '/expenses/edit');
  
  $('#expenseAmount').val(selectedData.amount);
  $('#expenseCategoryId').val(selectedData.expense_category_assigned_to_user_id);

  $('#expenseCategoryId option').each(function() {
    if ($(this).val() == selectedData.expense_category_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#paymentMethodId').val(selectedData.payment_method_assigned_to_user_id);

  $('#paymentMethodId option').each(function() {
    if ($(this).val() == selectedData.payment_method_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#dateOfExpense').val(selectedData.date_of_expense);
  $('#expenseComment').val(selectedData.expense_comment);


  /**Hide id of selected income row. It's neccessary only to identyfy selected income in case of updating it in the database. */
  if ($('#ExpenseId').length === 0) {
    $('<input>').attr({
        type: 'hidden',
        id: 'ExpenseId',
        name: 'id',
        value: selectedData.id
    }).appendTo('#formAddExpense');
  } else {
    $('#expenseId').val(selectedData.id);
  }

});

/** Display modal when user click on the Delete button. Load data from selected row into displayed form and set class on each element as disabled so there is no way to edit it. */
$('#delete-expense-button').on('click',function() {

  var selectedRow = expensesTable.row('.selected');
  var selectedData = selectedRow.data();

  $('#formAddExpense').attr('action', '/expenses/delete');
  $('#expenseModalLabel').html('Delete Expense');
  $('#expenseModalSubmitButton').html('Delete');

  $(this).attr('data-bs-toggle', 'modal');
  $(this).attr('data-bs-target', '#expenseModal');

  $('#expenseAmount').val(selectedData.amount).attr('disabled', true);
  $('#expenseCategoryId').val(selectedData.expense_category_assigned_to_user_id).attr('disabled', true);

  $('#expenseCategoryId option').each(function() {
    if ($(this).val() == selectedData.expense_category_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#paymentMethodId').val(selectedData.payment_method_assigned_to_user_id).attr('disabled', true);

  $('#paymentMethodId option').each(function() {
    if ($(this).val() == selectedData.payment_method_assigned_to_user_id) {
      $(this).prop('selected', true);
    } else {
      $(this).prop('selected', false);
    }
  });

  $('#dateOfExpense').val(selectedData.date_of_expense).attr('disabled', true);
  $('#expenseComment').val(selectedData.expense_comment).attr('disabled', true);

  /**Hide id of selected expense row. It's neccessary only to identyfy selected expense in case of deleting it in the database. */
  if ($('#expenseId').length === 0) {
    $('<input>').attr({
        type: 'hidden',
        id: 'expenseId',
        name: 'id',
        value: selectedData.id
    }).appendTo('#formAddExpense');
  } else {
    $('#expenseId').val(selectedData.id);
  }
  
});

/** Reset the form and some attributes of it when modal is closing. */
$('#expenseModal').on('hide.bs.modal', function() {

  $('#expenseAmount').attr('disabled', false);
  $('#expenseCategoryId').attr('disabled', false);
  $('#paymentMethodId').attr('disabled', false);
  $('#dateOfExpense').val(getTodayDate()).attr('disabled', false);
  $('#expenseComment').attr('disabled', false);
  $('#expenseModalSubmitButton').html('').attr('disabled', false);
  $('#formAddExpense')[0].reset();

});

/** Select row on click and add selected class to tr element. Also activate edit and delete buttons. */
$('#expenses-table tbody').on('click', 'tr', function(event) {
  event.stopPropagation();

  if($(this).attr('data-id')) {

    $('#expenses-table tbody tr').removeClass('selected');

    $(this).addClass('selected');
    $('#edit-expense-button').removeClass('disabled');
    $('#delete-expense-button').removeClass('disabled');

  }
    
});

/** This action works when row is selected and user click outside the table. Set edit and delete buttons disabled. Remove selection from row. */
$(document).on('click', function(event) {
  if (!$(event.target).closest('#expenses-table').length) {
    $('#expenses-table tbody').find('tr.selected').removeClass('selected')
      .removeAttr('data-bs-toggle data-bs-target');
    $('#edit-expense-button').addClass('disabled');
    $('#delete-expense-button').addClass('disabled');
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


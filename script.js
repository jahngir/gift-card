jQuery(document).ready(function ($) {

  
  // Function to show or hide the formfields div based on the gift card category selection
  function toggleFormFields() {
    var selectedCategory = $("#gift-card-post").val();
    if (selectedCategory) {
      $(".formfields").show();
      $(".live-preview").show();
    } else {
      $(".formfields").hide();
      $(".live-preview").hide();
    }
  }

  // Add event listener to the gift card category dropdown
  $("#gift-card-post").change(function () {
    toggleFormFields();
  });

  // Initialize formfields visibility on page load
  toggleFormFields();

  // Existing code for updating the second form's inputs and order summary
  var firstFormInputs = {
    yourName: $("#your-name"),
    recipientName: $("#recipient-name"),
    voucherValue: $("#voucher-value"),
    personalMessage: $("#personal-message"),
    recipientEmail: $("#recipient-email"),
    yourEmail: $("#your-email"),
  };

  var secondFormInputs = {
    yourName: $('form input[name="your-name"]'),
    recipientName: $('form input[name="recipient-name"]'),
    voucherValue: $('form input[name="voucher-value"]'),
    personalMessage: $("form #personal-message"),
    recipientEmail: $('form input[name="recipient-email"]'),
    yourEmail: $('form input[name="your-email"]'),
  };

  var orderSummary = {
    orderGiftName: $("#order-gift-name"),
    orderGiftItem: $("#order-gift-item"),
    orderTotalPrice: $("#order-total-price"),
  };

  function updateSecondForm() {
    secondFormInputs.yourName.val(firstFormInputs.yourName.val());
    secondFormInputs.recipientName.val(firstFormInputs.recipientName.val());
    secondFormInputs.voucherValue.val(firstFormInputs.voucherValue.val());
    secondFormInputs.personalMessage.text(firstFormInputs.personalMessage.val());
    secondFormInputs.recipientEmail.val(firstFormInputs.recipientEmail.val());
    secondFormInputs.yourEmail.val(firstFormInputs.yourEmail.val());
  }

  function updateOrderSummary() {
    // Debugging statements
    console.log('orderGiftName:', orderSummary.orderGiftName);
    console.log('orderGiftItem:', orderSummary.orderGiftItem);
    console.log('orderTotalPrice:', orderSummary.orderTotalPrice);


    orderSummary.orderGiftName.text(firstFormInputs.yourName.val());
    // Check if orderGiftItem is defined
    if (orderSummary.orderGiftItem.length) {
      orderSummary.orderGiftItem.text(firstFormInputs.recipientName.val()); // Assuming you meant to use recipientName here
    } else {
      console.error('orderGiftItem element is not found');
    }
    orderSummary.orderTotalPrice.text("$" + secondFormInputs.voucherValue.val());
  }

  $.each(firstFormInputs, function (key, input) {
    input.on("input", function () {
      updateSecondForm();
      updateOrderSummary();
    });
  });

  updateSecondForm();
  updateOrderSummary();
});

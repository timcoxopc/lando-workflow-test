(function ($, Drupal, window, document, undefined) {
  // Remove paypal options from alternative checkout
  removePaypal()
  $("input").change(function() {
    //setTimeout(removePaypal, 400);
  });
    
  function removePaypal() {
    //$("label:contains('PayPal')").parent().remove();
    console.log("Remove paypal 2");    
  }
})(jQuery, Drupal, this, this.document);
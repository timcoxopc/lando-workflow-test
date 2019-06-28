(function ($, Drupal, window, document, undefined) {
    var box = $('.language-box');
    box.onClick = function(e){
        console.log(":::" + e.target);
    }
    
    $("#block-mainnavigation-menu").text(function () {
        if ($(this).text().indexOf("help-support") === 0) {
            return $(this).text().replace("help-support", "help & support")
        }
    });
    
    // Modify payment form layout
    $("#paypal-buttons-container").before($("#edit-checkout"));
    $("#edit-checkout").html("Alternative payments*");
    
    $("#commerce-checkout-flow-paypal-checkout #edit-actions-next").html("Complete Purchase");
    
    //Move checkboxes inside the subscription panel in user edit form
    
    $('#edit-field-mailed-newsletter-wrapper').appendTo($('#edit-field-mailchimp-subscription-0'));
    $('.js-form-item-field_adult-brain-tumour-primer-value').appendTo($('#edit-field-mailchimp-subscription-0-value .panel-body'));
    $('.js-form-item-field-paediatric-handbook-value').appendTo($('#edit-field-mailchimp-subscription-0-value .panel-body'));
    $('.js-form-item-field-meningioma-booklet-value').appendTo($('#edit-field-mailchimp-subscription-0-value .panel-body'));
    $('.js-form-item-field-ibta-magazine-value').appendTo($('#edit-field-mailchimp-subscription-0-value .panel-body'));
     
})(jQuery, Drupal, this, this.document);
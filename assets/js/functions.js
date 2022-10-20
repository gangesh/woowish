/**
 * Created by Surfer on 17/06/19.
 */

function openNav() {
  document.getElementById("mySidenav").style.width = "450px";
}

function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
}

jQuery(document).ready(function($) {
  /*
    jQuery("body").on('click','#wcuc-add-new-collection', function (e) {
        e.preventDefault();
        var collection = jQuery('input#wcuc-new-collection').val();
        var product = jQuery('input#wcuc-add-to-collection-product').val();
        jQuery.ajax({
            type: "POST", // use $_POST method to submit data
            //dataType: 'html',
            url: wcuc.ajaxurl,
            data: {
                action: 'wcuc_action_add_collection', // load function hooked to: "wp_ajax_*" action hook
                collection_name: collection,
                product: product,
            },
            beforeSend: function (response) {
                jQuery('#wcuc-container').prepend('<div class="wcuc-overlay"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>');
            },
            complete: function (response) {
                response = JSON.parse(response.responseText);
                if(!response.error) {
                    jQuery('#wcuc-ajax-message').html('<div class="wcuc-success">New Collection Added!</div>');
                }else{
                    jQuery('#wcuc-ajax-message').html('<div class="wcuc-error">There\'s some error!</div>');
                }
                setTimeout(function(){
                    jQuery('#wcuc-ajax-message').html('');
                }, 5000);
                jQuery('#wcuc-container .wcuc-overlay').remove();

            },
            success: function (data) {
                data = JSON.parse(data);
                if(!data.error){
                    //alert('Added');
                    jQuery('#wcuc-collections').html(data.collection_html);
                	jQuery("#wcuc-container .wcuc-new-collection-field").slideUp();
				}else {
                    //alert('Error!');
                }
            },
            error: function (errorThrown) {
                //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
            }
        });
        //window.location.href = vxajax.homeurl + 'complete-registration/';
    });

    */
  jQuery("body").on("click", "a.wcuc-delete-collection", function(e) {
    e.preventDefault();
    var result = confirm("Are you sure you want to delete?");
    if (!result) {
      return false;
    }
    var el = jQuery(this);
    var collection = jQuery(this).data("delete-collection-id");
    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_delete_collection", // load function hooked to: "wp_ajax_*" action hook
        collection_key: collection
      },
      beforeSend: function(response) {},
      complete: function(response) {},
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          //alert('Deleted');
          el.closest(".card").hide(300);
          location.reload();
        } else {
          alert("Error!");
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });

  jQuery("body").on("click", ".loop-add-to-collection", function(e) {
    e.preventDefault();
    // console.log("Fired");
    let collection = null;

    //         if(jQuery('#wcuc-add-to-collection-check').is(':checked')){
    //             pid = jQuery('input#wcuc-add-to-collection-check').val();
    //             collection = jQuery('select#wcuc-collections').val();
    //         }else{
    //             alert('Please tick the checkbox.');
    //             return false;
    //         }

    let loop_item = jQuery(this);
    let pid = jQuery(this).data("product-id");
    collection = jQuery(this).data("col-key");
    if (!collection) {
      alert("Please select collection.");
      return false;
    }

    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_add_collection_product", // load function hooked to: "wp_ajax_*" action hook
        key: collection,
        product: pid
      },
      beforeSend: function(response) {
        loop_item
          .next(".wcuc-ajax-message")
          .html('<span class="wcuc-success">Processing...</span>');
      },
      complete: function(response) {
        //console.log(data.responseText);
        response = JSON.parse(response.responseText);
        if (!response.error) {
          jQuery(".no-product-class").hide();

          jQuery(".open-collection-sidebar .col-pro-count").text(
            response.product_count
          );

          let product_html =
            '<div class="wcuc-collection-single-block">' +
            '<div class="wcuc-collection-product-thumb"><a href="#" class="wcuc-delete-product" data-delete-id="' +
            pid +
            '" data-delete-collection-id="' +
            collection +
            '">X</a><a href="' +
            response.link +
            '"><img src="' +
            response.pro_image +
            '" alt="' +
            response.pro_name +
            '"></a></div>' +
            '<div class="wcuc-collection-product-name"><a href="' +
            response.link +
            '"><h3>' +
            response.pro_name +
            "</h3></a></div>" +
            "</div>";

          jQuery("div#mySidenav .card-body").append(product_html);

          loop_item
            .next(".wcuc-ajax-message")
            .html(
              '<span class="wcuc-success">Product Added To Collection!</span>'
            );
        } else {
          loop_item
            .next(".wcuc-ajax-message")
            .html('<span class="wcuc-error">' + response.message + "</span>");
        }
        setTimeout(function() {
          jQuery(".wcuc-ajax-message").html("");
        }, 5000);
      },
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          //alert('Added');
        } else {
          //alert('Error!');
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });

  jQuery("body").on("click", "#wcuc-add-to-collection", function(e) {
    e.preventDefault();
    var collection = null;

    //         if(jQuery('#wcuc-add-to-collection-check').is(':checked')){
    //             pid = jQuery('input#wcuc-add-to-collection-check').val();
    //             collection = jQuery('select#wcuc-collections').val();
    //         }else{
    //             alert('Please tick the checkbox.');
    //             return false;
    //         }
    var pid = jQuery("input#wcuc-add-to-collection-product").val();
    collection = jQuery("input#wcuc-add-to-collection-value").val();
    if (!collection) {
      alert("Please select collection.");
      return false;
    }

    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_add_collection_product", // load function hooked to: "wp_ajax_*" action hook
        key: collection,
        product: pid
      },
      beforeSend: function(response) {
        jQuery("#wcuc-container").prepend(
          '<div class="wcuc-overlay"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>'
        );
      },
      complete: function(response) {
        //console.log(data.responseText);
        response = JSON.parse(response.responseText);
        if (!response.error) {
          jQuery(".no-product-class").hide();
          jQuery(".open-collection-sidebar .col-pro-count").text(
            response.product_count
          );

          let product_html =
            '<div class="wcuc-collection-single-block">' +
            '<div class="wcuc-collection-product-thumb"><a href="#" class="wcuc-delete-product" data-delete-id="' +
            pid +
            '" data-delete-collection-id="' +
            collection +
            '">X</a><a href="' +
            response.link +
            '"><img src="' +
            response.pro_image +
            '" alt="' +
            response.pro_name +
            '"></a></div>' +
            '<div class="wcuc-collection-product-name"><a href="' +
            response.link +
            '"><h3>' +
            response.pro_name +
            "</h3></a></div>" +
            "</div>";

          jQuery("div#mySidenav .card-body").append(product_html);

          jQuery("#wcuc-ajax-message").html(
            '<div class="wcuc-success">Product Added To Collection!</div>'
          );
        } else {
          jQuery("#wcuc-ajax-message").html(
            '<div class="wcuc-error">' + response.message + "</div>"
          );
        }
        setTimeout(function() {
          jQuery("#wcuc-ajax-message").html("");
        }, 5000);
        jQuery("#wcuc-container .wcuc-overlay").remove();
      },
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          //alert('Added');
        } else {
          //alert('Error!');
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });
  jQuery("body").on("click", "a.wcuc-delete-product", function(e) {
    e.preventDefault();
    var result = confirm("Are you sure you want to delete?");
    if (!result) {
      return false;
    }
    var el = jQuery(this);
    var collection = null;

    var pid = jQuery(this).data("delete-id");
    var collection = jQuery(this).data("delete-collection-id");

    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_delete_collection_product", // load function hooked to: "wp_ajax_*" action hook
        key: collection,
        product: pid
      },
      beforeSend: function(response) {},
      complete: function(response) {},
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          if (data.product_count > 0) {
            jQuery(".no-product-class").hide();
          } else {
            jQuery(".no-product-class").show();
          }
          //alert('Deleted');
          jQuery(".open-collection-sidebar .col-pro-count").text(
            data.product_count
          );
          el.parent()
            .parent(".wcuc-collection-single-block")
            .slideUp();
        } else {
          alert("Error!");
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });

  jQuery("body").on("click", "#wcuc_clear_all_pro", function(e) {
    e.preventDefault();
    var result = confirm("Are you sure you want to delete all products?");
    if (!result) {
      return false;
    }
    var el = jQuery(this);
    var collection = null;

    var collection = jQuery(this).data("collection-key");

    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_delete_all_collection_products", // load function hooked to: "wp_ajax_*" action hook
        key: collection
      },
      beforeSend: function(response) {},
      complete: function(response) {},
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          jQuery(".no-product-class").show();
          console.log(data.product_count);
          //alert('Deleted');
          jQuery(".open-collection-sidebar .col-pro-count").text(
            data.product_count
          );
          jQuery(".wcuc-collection-single-block").remove();

          alert(data.message);
        } else {
          alert("Error!");
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });

  jQuery("body").on("change", "#wcuc-collections", function(e) {
    e.preventDefault();
    var collection = jQuery(this).val();
    var pid = jQuery("#wcuc-add-to-collection-product").val();
    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: {
        action: "wcuc_action_check_collection", // load function hooked to: "wp_ajax_*" action hook
        collection_key: collection,
        product: pid
      },
      beforeSend: function(response) {
        jQuery("#wcuc-container").prepend(
          '<div class="wcuc-overlay"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>'
        );
      },
      complete: function(response) {
        jQuery("#wcuc-container .wcuc-overlay").remove();
      },
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          //alert('Added');
          jQuery(".wcuc-checkbox").html(data.collection_html);
          jQuery(
            ".wcuc-collection-dropdown-container #wcuc-add-to-collection"
          ).attr("disabled", "disabled");
        } else {
          //alert('Error!');
          jQuery(".wcuc-checkbox").html(data.collection_html);
          jQuery(
            ".wcuc-collection-dropdown-container #wcuc-add-to-collection"
          ).removeAttr("disabled");
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });

  jQuery("form#wcuc-share-form").on("submit", function(e) {
    var form = jQuery(this);
    e.preventDefault();

    var check = null;
    var dataArray = form.serializeArray();
    jQuery(dataArray).each(function(i, field) {
      console.log(field.name);
      if (field.value === "" && field.name !== "wcuc-share-message") {
        console.log(field.value);
        check++;
      }
    });
    if (check) {
      return false;
    }

    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: form.serialize() + "&action=wcuc_action_send_share_email",
      beforeSend: function(response) {},
      complete: function(response) {},
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          jQuery("#wcuc-share-form .wcuc-email-response").html(data.message);
        } else {
          jQuery("#wcuc-share-form .wcuc-email-response").html(data.message);
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });
  jQuery("form#wcuc-inquire-form").on("submit", function(e) {
    var form = jQuery(this);
    e.preventDefault();

    var check = null;
    var dataArray = form.serializeArray();
    jQuery(dataArray).each(function(i, field) {
      console.log(field.name);
      if (field.value === "") {
        console.log(field.value);
        check++;
        //e.stopPropagation();
      }
    });
    if (check) {
      return false;
    }
    jQuery.ajax({
      type: "POST", // use $_POST method to submit data
      //dataType: 'html',
      url: wcuc.ajaxurl,
      data: form.serialize() + "&action=wcuc_action_send_inquire_email",
      beforeSend: function(response) {},
      complete: function(response) {},
      success: function(data) {
        data = JSON.parse(data);
        if (!data.error) {
          jQuery("#wcuc-inquire-form .wcuc-email-response").html(data.message);
        } else {
          jQuery("#wcuc-inquire-form .wcuc-email-response").html(data.message);
        }
      },
      error: function(errorThrown) {
        //$('#vx_info_hidden').html('<p>Error retrieving data. Please try again.</p>');
      }
    });
    //window.location.href = vxajax.homeurl + 'complete-registration/';
  });
  //jQuery('select').materialSelect();

  jQuery("a.wcuc-show-hide-add-new-collection-field").click(function() {
    jQuery(".wcuc-new-collection-field").toggle();
    return false;
  });
});

// MDB Form Validations
(function() {
  "use strict";
  window.addEventListener(
    "load",
    function() {
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.getElementsByClassName("wcuc-needs-validation");
      // Loop over them and prevent submission
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener(
          "submit",
          function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add("was-validated");
          },
          false
        );
      });
    },
    false
  );
})();

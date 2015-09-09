$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.onblur = 'ignore'; 
    
    $('a[href="#"]').click(function(event) {
        event.preventDefault();
    });
    
    $('a[data-toggle="tooltip"]').tooltip();
    
    $('.datatable').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [ 1, 'asc' ]
        ],
        "columnDefs": [
            { "orderable": false, "targets": 0 }
        ]
    });
    
    $('input, textarea').attr('autocomplete', 'off');
    
    jQuery.fn.extend({
        disable: function(state) {
            return this.each(function() {
                var $this = $(this);
                $this.toggleClass('disabled', state);
            });
        }
    });
});

var Base = {
    showErrorMessage: function(message) {
        swal({
            title: "Error",
            text: message,
            type: "error",
            confirmButtonText: "OK"
        }); 
    },
    showSuccessMessage: function(message) {
        swal({
            title: "Success",
            text: message,
            type: "success",
            confirmButtonText: "OK"
        }); 
    },
    showInfoMessage: function(message) {
        swal({
            title: "Info",
            text: message,
            type: "info",
            confirmButtonText: "OK"
        }); 
    }
};

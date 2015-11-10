$(document).ready(function() {
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editable.defaults.onblur = 'ignore'; 
    
    $('a[href="#"]').click(function(event) {
        event.preventDefault();
    });
    
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });
    
    $('a[data-toggle="tooltip"]').tooltip();
    
    $('input, textarea').attr('autocomplete', 'off');
    
    jQuery.fn.extend({
        disable: function(state) {
            return this.each(function() {
                var $this = $(this);
                $this.toggleClass('disabled', state);
            });
        }
    });
    
    $(".modal").on('show.bs.modal', function() {
        $(".container, .affix").addClass("blur");
    });
    
    $(".modal").on('hide.bs.modal', function() {
        $(".container, .affix").removeClass("blur");
    });
    
    $("#refresh-sidebar").click(function() {
        Base.refreshSidebar(false);
    });
});

var Base = {
    loadBackground: function(background) {
        $("body").vegas({
            slides: [
                { src: background }
            ]
        });
    },
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
    },
    openSidebar: function() {
        $('#sidebar').offcanvas();
    },
    refreshSidebar: function(open) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('application_refresh_sidebar')
        }).done(function(data) {
            $("#sidebar-content").html(data.sidebar);
            if (open) {
                Base.openSidebar();
            }
        });
    }
};

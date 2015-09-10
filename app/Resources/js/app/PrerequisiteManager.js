var PrerequisiteManager = {
    init: function() {
        $("#modal-prerequisite").modal({
            backdrop: 'static',
            show: false
        });
        $("#add-prerequisite").click(function() {
            var testId = $(this).data('test-id');
            PrerequisiteManager.openAddFormModal(testId);
        });
        $("#save-prerequisite").click(function() {
            var testId = $(this).data('test-id');
            PrerequisiteManager.save(testId);
        });
        $("[id^=delete-prerequisite-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            PrerequisiteManager.delete(id, name);
        });
    },
    initItem: function(id) {
        $("#delete-prerequisite-" + id ).click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            PrerequisiteManager.delete(id, name);
        }).tooltip();
    },
    resetAddForm: function() {
        $("#form-prerequisite")[0].reset();
        $("#prerequisite_test").val($("#prerequisite_test option:first").val());
    },
    openAddFormModal: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_prerequisite_form_ajax', {
                'id': testId
            })
        }).done(function(data) {
            $('#save-prerequisite').data('test-id', testId);
            $("#modal-prerequisite-body").html(data.form);
            $("#modal-prerequisite").modal('show');
        });
    },
    closeAddFormModal: function() {
        $("#modal-prerequisite").modal('hide');
    },
    refreshSummary: function() {
        var count = $("[id^=item-prerequisite-]").length;
        $("#prerequisites-count").html(count);
        if (count === 0) {
            TestEditor.enableStartingPageSelection();
        }
    },
    add: function(id, li) {
        $(li).insertBefore($('#add-prerequisite').parent());
        PrerequisiteManager.initItem(id);
    },
    remove: function(id, testId, startingPage, resetStartingPage) {
        $('#item-prerequisite-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            $(this).remove();
            PrerequisiteManager.refreshSummary();
            updatePrerequisitesOrders(testId);
            if (startingPage) {
                $("#starting-page-name").html(startingPage.name);
            } else {
                if (resetStartingPage) {
                    TestEditor.resetStartingPage();
                }
            }
            TestEditor.refreshBehatScenario(testId);
        });
    },
    save: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_test_prerequisite_ajax', {
                'id': testId
            }),
            data: $("#form-prerequisite").serialize()
        }).done(function(data) {
            if (data.form) {
                $("#form-prerequisite").replaceWith($(data.form));
            } else {
                PrerequisiteManager.add(data.id, data.li);
                PrerequisiteManager.resetAddForm();
                PrerequisiteManager.closeAddFormModal();
                PrerequisiteManager.refreshSummary();
                if (data.resetStartingPage) {
                    TestEditor.resetStartingPage();
                } else {
                    $("#starting-page-name").html(data.startingPage.name);
                    TestEditor.disableStartingPageSelection();
                }
                TestEditor.refreshBehatScenario(testId);
            }
        });
    },
    delete: function(id, name) {
        swal({
            title: "Delete prerequisite " + name + " ?",
            text: "You will not be able to recover this prerequisite !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it !",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_delete_application_test_prerequisite_ajax', {
                    'id': id
                })
            }).done(function(data) {
                if (data.error) {
                    var message = "Prerequisite " + name + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    var testId = data.testId;
                    var startingPage = data.startingPage;
                    var resetStartingPage = data.resetStartingPage;
                    PrerequisiteManager.remove(id, testId, startingPage, resetStartingPage);
                    Base.showSuccessMessage("Prerequisite deleted with success !");
                }
            });
        });   
    }
};
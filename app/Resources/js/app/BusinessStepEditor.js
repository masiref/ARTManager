var BusinessStepEditor = {
    init: function() {
        BusinessStepExecuteStepManager.init();
        BusinessStepControlStepManager.init();
        BusinessStepEditor.initTranslations();
    },
    initItem: function(businessStepId) {
        $("#form_startingPage").change(function() {
            var pageId = $(this).val();
            if (pageId !== "") {
                var pageName = $(this).find("option:selected").text();
                $("#add-execute-step").data('starting-page-id', pageId);
                BusinessStepEditor.updateStartingPage(businessStepId, pageId, pageName);
            }
        });    
        $("#sentences-collapse").on('shown.bs.collapse', function() {
            $(".sentences-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                    .addClass("fontello-icon-up-open");
        });
        $("#sentences-collapse").on('hidden.bs.collapse', function() {
            $(".sentences-collapse-toggle-icon").removeClass("fontello-icon-up-open")
                    .addClass("fontello-icon-down-open");
        }); 
        $("#starting-page-collapse").on('shown.bs.collapse', function() {
            $(".starting-page-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                    .addClass("fontello-icon-up-open");
        });
        $("#starting-page-collapse").on('hidden.bs.collapse', function() {
            $(".starting-page-collapse-toggle-icon").removeClass("fontello-icon-up-open")
                    .addClass("fontello-icon-down-open");
        });
        $("#scenario-collapse").on('shown.bs.collapse', function() {
            $(".scenario-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                    .addClass("fontello-icon-up-open");
        });
        $("#scenario-collapse").on('hidden.bs.collapse', function() {
            $(".scenario-collapse-toggle-icon").removeClass("fontello-icon-up-open")
                    .addClass("fontello-icon-down-open");
        }); 
        $("#step-rows").sortable({
            update: function(event, ui) {
                var steps = $(this).sortable('toArray', { 'attribute': 'data-id' }).toString();
                BusinessStepExecuteStepManager.updateOrders(testId, steps);
            }
        }).disableSelection();
        $("[id^=control-step-rows-]").sortable({
            update: function(event, ui) {
                var steps = $(this).sortable('toArray', { 'attribute': 'data-id' }).toString();
                BusinessStepControlStepManager.updateOrders(testId, steps);
            }
        }).disableSelection();
    },
    initEditableData: function() {
        $("[id^=sentence-content-]").editable({
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
    },
    initTranslations: function() {
        $("#save-step-sentence").click(function(event) {
            event.preventDefault();
            var id = $(this).data("business-step-id");
            BusinessStepEditor.saveSentence(id);
        });
        $("[id^=delete-step-sentence-]").click(function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            var businessStepId = $(this).data('business-step-id');
            BusinessStepEditor.deleteSentence(businessStepId, id);
        }).tooltip();
    },
    resetStartingPage: function() {
        $("#starting-page-name").text("");
        $("#form_startingPage").val($("#form_startingPage option:first").val());
    },
    updateStartingPage: function(businessStepId, pageId, pageName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_business_step_update_starting_page_ajax', {
                id: businessStepId
            }),
            data: {
                pageId: pageId
            }
        }).done(function(data) {
            if (data.error) {
                var message = "Starting page not updated !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                $("#starting-page-name").html(pageName);
            }
        });
    },
    resetAddSentenceForm: function() {
        $("#form-add-step-sentence")[0].reset();
    },
    saveSentence: function(businessStepId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_business_step_sentence_ajax', {
                'id': businessStepId
            }),
            data: $("#form-add-step-sentence").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Translation not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                BusinessStepEditor.refreshTranslations(data.sentencesContent);
                BusinessStepEditor.resetAddSentenceForm();
                var message = "Translation added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    deleteSentence: function(businessStepId, id) {
        swal({
            title: "Delete tranlation ?",
            text: "You will not be able to recover this translation !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it !",
            closeOnConfirm: false
        },
        function(){
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_delete_application_business_step_sentence_ajax', {
                    'id': businessStepId,
                    'sentenceId': id
                })
            }).done(function(data) {
                BusinessStepEditor.refreshTranslations(data.sentencesContent);
                var message = "Translation deleted !";
                Base.showSuccessMessage(message);
            });
        });
    },
    refreshTranslations: function(content) {
        $("#sentences-collapse").html(content);
        BusinessStepEditor.initEditableData();
        BusinessStepEditor.initTranslations();
    }
};
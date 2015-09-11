var TestEditor = {
    init: function() {
        ExecuteStepManager.init();
        ControlStepManager.init();
        PrerequisiteManager.init();
    },
    initItem: function(testId, prerequisiteCount) {
        $("#form_startingPage").change(function() {
            var pageId = $(this).val();
            var pageName = $(this).find("option:selected").text();
            TestEditor.updateStartingPage(testId, pageId, pageName);
        });
        $("#prerequisites-collapse").on('shown.bs.collapse', function() {
            $(".prerequisites-collapse-toggle-icon").removeClass("fontello-icon-down-open")
                    .addClass("fontello-icon-up-open");
        });
        $("#prerequisites-collapse").on('hidden.bs.collapse', function() {
            $(".prerequisites-collapse-toggle-icon").removeClass("fontello-icon-up-open")
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
        if (prerequisiteCount > 0) {
            TestEditor.disableStartingPageSelection();
        }     
        $("#step-rows").sortable({
            update: function(event, ui) {
                var steps = $(this).sortable('toArray', { 'attribute': 'data-id' }).toString();
                ExecuteStepManager.updateOrders(testId, steps);
            }
        }).disableSelection();
        $("[id^=control-step-rows-]").sortable({
            update: function(event, ui) {
                var steps = $(this).sortable('toArray', { 'attribute': 'data-id' }).toString();
                ControlStepManager.updateOrders(testId, steps);
            }
        }).disableSelection();
    },
    resetStartingPage: function() {
        $("#starting-page-name").text("");
        $("#form_startingPage").val($("#form_startingPage option:first").val());
    },
    updateStartingPage: function(testId, pageId, pageName) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_update_starting_page_ajax', {
                id: testId
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
                TestEditor.refreshBehatScenario(testId);
            }
        });
    },
    enableStartingPageSelection: function() {
        $("#form_startingPage").show();
        $(".starting-page-collapse-toggle-icon").show();
    },
    disableStartingPageSelection: function() {
        $("#form_startingPage").hide();
        $(".starting-page-collapse-toggle-icon").hide();
    },
    refreshBehatScenario: function(testId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_get_application_test_behat_scenario_ajax', {
                'id': testId
            })
        }).done(function(data) {
            $("#behat-scenario").html(data.scenario);
        });
    }
};
$( "#modal-add-step" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-execute-step" ).click(function() {
    var id = $(this).data('test-id');
    var name = $(this).data('test-name');
    var description = $(this).data('test-description');
    showAddStepForm(id, name, description);
});

$( "#save-execute-step" ).click(function() {
    var testId = $(this).data('test-id');
    var testName = $(this).data('test-name');
    saveStep(testId, testName);
});

$( "[id^=add-control-step-step-]").click(function() {
    var id = $(this).data('step-id');
    var name = $(this).data('step-name');
    showAddControlStepForm(id, name);
});

$( "#save-control-step" ).click(function() {
    var stepId = $(this).data('step-id');
    saveControlStep(stepId);
});

$( "[id^=delete-execute-step-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var order = $(this).data('order');
    var testId = $(this).data('test-id');
    deleteExecuteStep(id, name, order, testId);
});

$( "[id^=delete-control-step-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var order = $(this).data('order');
    var stepId = $(this).data('step-id');
    deleteControlStep(id, name, order, stepId);
});

function showAddStepForm(id, name, description) {
    updateAddStepModalTitle(name, description);
    $('#save-execute-step').data('test-id', id);
    $('#save-execute-step').data('test-name', name);
    $("#modal-add-execute-step").modal('show');
}

function updateAddStepModalTitle(name, description) {
    $('#new-step-test-name').html(name);
    $('#new-step-test-description').html(description);
}

function saveStep(testId, testName) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_execute_step_ajax', {
            'id': testId
        }),
        data: $("#form-add-execute-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not added !", data.error, "error");
        } else {
            var id = data.id;
            var row = data.row;
            $(row).insertBefore($('#step-footer'));
            $( "#delete-execute-step-" + id ).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var order = $(this).data('order');
                var testId = $(this).data('test-id');
                deleteExecuteStep(id, name, order, testId);
            });
            $( "#add-control-step-step-" + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('step-id');
                var name = $(this).data('step-name');
                showAddControlStepForm(id, name);
            });
            swal("Step added with success !", "", "success");
            $("#form-add-execute-step")[0].reset();
            $("#modal-add-execute-step").modal('hide');
        }
    });
}

function showAddControlStepForm(id, name) {
    $('#new-step-step-name').html(name);
    $('#save-control-step').data('step-id', id);
    $("#modal-add-control-step").modal('show');
}

function saveControlStep(stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_step_control_step_ajax', {
            'id': stepId
        }),
        data: $("#form-add-control-step").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Step not added !", data.error, "error");
        } else {
            var id = data.id;
            var row = data.row;
            $(row).insertBefore($('#control-step-footer-' + stepId));
            $( "#delete-control-step-" + id).click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');
                var order = $(this).data('order');
                var stepId = $(this).data('step-id');
                deleteControlStep(id, name, order, stepId);
            });
            swal("Step added with success !", "", "success");
            $("#form-add-control-step")[0].reset();
            $("#modal-add-control-step").modal('hide');
        }
    });
}

function deleteExecuteStep(id, name, order, testId) {  
    swal({
        title: "Delete #" + order + " " + name + " ?",
        text: "You will not be able to recover this step !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_test_execute_step_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal("#" + order + " " + name + " not deleted !", data.error, "error");
            } else {
                $('#step-row-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    $(this).remove();
                    // TODO: update execute steps orders
                    updateExecuteStepsOrders(testId);
                });
                swal("Step deleted with success !", "", "success");
            }
        });
    });
}

function updateExecuteStepsOrders(testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_execute_step_orders_ajax', {
            'id': testId
        })
    }).done(function(data) {
        jQuery.each(data, function(id, order) {
            $("#step-order-" + id).html(order);
            $("#delete-execute-step-" + id).data("order", order);
        });
    });
}

function deleteControlStep(id, name, order, stepId) {  
    swal({
        title: "Delete #" + order + " " + name + " ?",
        text: "You will not be able to recover this step !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_test_step_control_step_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal("#" + order + " " + name + " not deleted !", data.error, "error");
            } else {
                $('#control-step-row-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    $(this).remove();
                    // TODO: update execute steps orders
                    updateControlStepsOrders(stepId);
                });
                swal("Step deleted with success !", "", "success");
            }
        });
    });
}

function updateControlStepsOrders(stepId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_get_application_test_step_control_step_orders_ajax', {
            'id': stepId
        })
    }).done(function(data) {
        jQuery.each(data, function(id, order) {
            $("#control-step-order-" + id).html(order);
            $("#delete-control-step-" + id).data("order", order);
        });
    });
}

/*

$( "#delete-checked-entities").click(function() {
    var applicationId = $(this).data('application-id');
    deleteEntities(applicationId);
});

function refreshTestFolderSubtitle(count) {
    var subtitle = '';
    if (count <= 1) {
        subtitle += 'There is ';
    } else {
        subtitle += 'There are ';
    }
    if (count === 0) {
        subtitle += 'no ';
    } else {
        subtitle += '<span class="badge">' + count + '</span>';
    }
    subtitle += ' test folder' + (count > 1 ? "s" : "");
    $('#test-folders-count').html(subtitle);
}

function refreshTestSubtitle(count) {
    var subtitle = '';
    if (count <= 1) {
        subtitle += 'There is ';
    } else {
        subtitle += 'There are ';
    }
    if (count === 0) {
        subtitle += 'no ';
    } else {
        subtitle += '<span class="badge">' + count + '</span>';
    }
    subtitle += ' test' + (count > 1 ? "s" : "");
    $('#tests-count').html(subtitle);
}

function saveTestFolder(applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    var selectedFolder = $(testsTreeHtmlId).treeview('getSelected');
    var parentFolderId = -1;
    if (selectedFolder.length === 1) {
        var parentFolder = selectedFolder[0];
        var href = parentFolder.href;
        var id = href.substring(href.lastIndexOf("-") + 1);
        var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
        if (type === "folder") {
            parentFolderId = id;
        }
    }
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_folder_ajax', {
            'id': applicationId,
            'parentId': parentFolderId
        }),
        data: $("#form-add-test-folder").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Test folder not added !", data.error, "error");
        } else {
            var id = data.id;
            var name = data.name;
            var testFoldersCount = data.testFoldersCount;
            hideTestsPanel(applicationId);
            $(testsTreeHtmlId).treeview({
                data: data.treeTests,
                showBorder: false,
                showCheckbox: true,
                onNodeSelected: function(event, data) {
                    showEntityProperties(data);
                },
                onNodeUnselected: function(event, data) {
                    hideEntityPropertiesPanelBodyAndFooter();
                    clearAddTestDataAttributes();
                    clearActionsHref();
                },
                onNodeChecked: function(event, data) {
                    checkTreeChildNodes(testsTreeHtmlId, data);
                    refreshCheckedTestsTreeEntitiesCount(applicationId);
                },
                onNodeUnchecked: function(event, data) {
                    uncheckTreeChildNodes(testsTreeHtmlId, data);
                    uncheckTreeParentNode(testsTreeHtmlId, data);
                    refreshCheckedTestsTreeEntitiesCount(applicationId);
                }                 
            });
            $(testsTreeHtmlId).treeview('collapseAll', { silent: true });
            showTestsPanel(applicationId);
            var selectedNode = $(testsTreeHtmlId).treeview("getSelected")[0];
            //showObjectProperties(selectedNode);
            $(testsTreeHtmlId).treeview("revealNode", selectedNode);
            refreshTestFolderSubtitle(testFoldersCount, applicationId);
            swal(name + " added !", "Your folder has been added.", "success");
            $("#form-add-test-folder")[0].reset();
            $("#modal-add-test-folder").modal('hide');
        }
    });
}

function refreshTestsTree(id, collapse, checkbox) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_tests_tree_ajax', {
            'id': id
        })
    }).done(function(data) {
        showTestsTree(data, collapse, checkbox);
        revealTestsTreeSelectedNode(id);
    });
}

function showTestsTree(data, collapse, checkbox) {
    jQuery.each(data, function(i, val) {
        var id = i.substring(i.lastIndexOf("-") + 1);
        var htmlId = "#" + i;
        $(htmlId).treeview({
            data: val,
            showBorder: false,
            showCheckbox: checkbox,
            onNodeSelected: function(event, data) {
                showEntityProperties(data);
            },
            onNodeUnselected: function(event, data) {
                hideEntityPropertiesPanelBodyAndFooter();
                clearAddTestDataAttributes();
                clearActionsHref();
            },
            onNodeChecked: function(event, data) {
                checkTreeChildNodes(htmlId, data);
                refreshCheckedTestsTreeEntitiesCount(id);
            },
            onNodeUnchecked: function(event, data) {
                uncheckTreeChildNodes(htmlId, data);
                uncheckTreeParentNode(htmlId, data);
                refreshCheckedTestsTreeEntitiesCount(id);
            }
        });
        if (collapse) {
            $(htmlId).treeview('collapseAll', { silent: true });
        }
        revealTestsTreeSelectedNode(id);
        showTestsPanel(id);
    });
}

function showTestsTreeWithSelectedFolder(testFolderId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_tests_tree_with_selected_folder_ajax', {
            'id': testFolderId
        })
    }).done(function(data) {
        showTestsTree(data, true, true);
    });
}

function showTestsTreeWithSelectedTest(testId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_tests_tree_with_selected_test_ajax', {
            'id': testId
        })
    }).done(function(data) {
        showTestsTree(data, true, true);
    });
}

function revealTestsTreeSelectedNode(applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    var selectedNode = $(testsTreeHtmlId).treeview("getSelected")[0];
    if (selectedNode) {
        $(testsTreeHtmlId).treeview("revealNode", selectedNode);
    }
}

function hideTestsPanel(applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    $(testsTreeHtmlId + "-loader").addClass("three-quarters-loader");
    $(testsTreeHtmlId).hide();
}

function showTestsPanel(applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    $(testsTreeHtmlId + "-loader").removeClass("three-quarters-loader");
    $(testsTreeHtmlId).show();
}

function showEntityProperties(treeNode) {
    if (treeNode) {
        $("#panel-body-entity-properties").show();
        $("#panel-footer-entity-properties").show();
        $("#entity-properties-loader").show();
        var href = treeNode.href;
        var id = href.substring(href.lastIndexOf("-") + 1);
        var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
        $('#entity-icon').removeClass("fontello-icon-puzzle");
        switch(type) {
            case "folder":
                hideEntityActions();
                $('#entity-icon').removeClass().addClass("fontello-icon-folder");
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_application_get_test_folder_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#entity-properties-loader").hide();
                    var folder = data.testFolder;
                    var name = folder.name;
                    var description = folder.description;
                    var createdAt = folder.createdAt;
                    $('#entity-name').editable('option', 'pk', id);
                    $('#entity-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_folder_update_name_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-description').editable('option', 'pk', id);
                    $('#entity-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_folder_update_description_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-name').editable('setValue', name, false);
                    $('#entity-description').editable('setValue', description, false);
                    $('#entity-creation-date').html(createdAt);
                    setAddTestDataAttributes(id, name, description);
                });
                break;
            case "test":
                showEntityActions();
                $('#edit-entity').attr("href",
                    Routing.generate('app_index_application_test_editor', {
                        'id': id
                    })
                );
                $('#entity-icon').removeClass().addClass('fontello-icon-tasks');
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_application_get_test_folder_test_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#entity-properties-loader").hide();
                    var test = data.test;
                    var name = test.name;
                    var description = test.description;
                    var createdAt = test.createdAt;
                    $('#entity-name').editable('option', 'pk', id);
                    $('#entity-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_folder_test_update_name_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-description').editable('option', 'pk', id);
                    $('#entity-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_folder_test_update_description_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-name').editable('setValue', name, false);
                    $('#entity-description').editable('setValue', description, false);
                    $('#entity-creation-date').html(createdAt);
                });
                break;
            default:
                $('#entity-icon').removeClass().addClass("fontello-icon-puzzle");
        }
    } else {
        hideEntityPropertiesPanelBodyAndFooter();
    }
}

function setAddTestDataAttributes(id, name, description) {
    $('#add-test').data('test-folder-id', id);
    $('#add-test').data('test-folder-name', name);
    $('#add-test').data('test-folder-description', description);
}

function clearAddTestDataAttributes() {
    $('#add-test').removeData('test-folder-id');
    $('#add-test').removeData('test-folder-name');
    $('#add-test').removeData('test-folder-description');
}

function hideEntityPropertiesPanelBodyAndFooter() {
    $('#panel-body-entity-properties').hide();
    $('#panel-footer-entity-properties').hide();
}

function refreshCheckedTestsTreeEntitiesCount(applicationId) {
    var checkedEntities = $("#tree-tests-" + applicationId).treeview('getChecked');
    var checkedEntitiesCount = checkedEntities.length;
    $("#checked-entities-count").html(checkedEntitiesCount);
    if (checkedEntitiesCount > 0) {
        $("#delete-checked-entities").removeClass("disabled");
    } else {
        $("#delete-checked-entities").addClass("disabled");
    }
}

function deleteEntities(applicationId) {
    var testsTreeHtmlId = "#tree-tests-" + applicationId;
    swal({
        title: "Delete selected entities ?",
        text: "You will not be able to recover them !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete them !",
        closeOnConfirm: false
    }, function() {
        var objects = $(testsTreeHtmlId).treeview('getChecked');
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_entities_delete_ajax', {
                id: applicationId
            }),
            data: {
                objects: objects,
                selectedNode: $(testsTreeHtmlId).treeview("getSelected")[0]
            }
        }).done(function(data) {
            if (data.error) {
                swal("Selected entities were not deleted !", data.error, "error");
            } else {
                var count = data.count;
                var testFoldersCount = data.testFoldersCount;
                var testsCount = data.testsCount;
                var applicationId = data.applicationId;
                hideTestsPanel(applicationId);
                $(testsTreeHtmlId).treeview({
                    data: data.treeTests,
                    showBorder: false,
                    showCheckbox: true,
                    onNodeSelected: function(event, data) {
                        showEntityProperties(data);
                    },
                    onNodeUnselected: function(event, data) {
                        hideEntityPropertiesPanelBodyAndFooter();
                        clearAddTestDataAttributes();
                        clearActionsHref();
                    },
                    onNodeChecked: function(event, data) {
                        checkTreeChildNodes(testsTreeHtmlId, data);
                        refreshCheckedTestsTreeEntitiesCount(applicationId);
                    },
                    onNodeUnchecked: function(event, data) {
                        uncheckTreeChildNodes(testsTreeHtmlId, data);
                        uncheckTreeParentNode(testsTreeHtmlId, data);
                        refreshCheckedTestsTreeEntitiesCount(applicationId);
                    }             
                });
                $(testsTreeHtmlId).treeview('collapseAll', { silent: true });
                showTestsPanel(applicationId);
                refreshTestSubtitle(testsCount, applicationId);
                refreshTestFolderSubtitle(testFoldersCount, applicationId);
                refreshCheckedTestsTreeEntitiesCount(applicationId);
                swal("Selected entities deleted !", "You have deleted " + count + " entit" + (count > 1 ? "ies" : "y"), "success");
            }
        });
    });
}

function hideEntityActions() {
    $("#panel-body-content-entity-actions").hide();
}

function showEntityActions() {
    $("#panel-body-content-entity-actions").show();
}

function clearActionsHref() {
    $("#edit-entity").removeAttr("href");
}*/
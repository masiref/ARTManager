/* test set folder triggers */
$( "#modal-add-test-set-folder" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-test-set-folder" ).click(function() {
    $("#modal-add-test-set-folder").modal('show');
});

$( "#save-test-set-folder" ).click(function() {
    var applicationId = $(this).data('application-id');
    saveTestSetFolder(applicationId);
});

/* test set triggers */
$( "#modal-add-test-set" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-test-set" ).click(function() {
    var id = $(this).data('test-set-folder-id');
    if (id) {
        var name = $(this).data('test-set-folder-name');
        var description = $(this).data('test-set-folder-description');
        showAddTestSetForm(id, name, description);
    } else {
        swal({
            title: "Error",
            text: "Please select a folder !",
            type: "error",
            confirmButtonText: "OK"
        });
    }
});

$( "#save-test-set" ).click(function() {
    var testSetFolderId = $(this).data('test-set-folder-id');
    var testSetFolderName = $(this).data('test-set-folder-name');
    saveTestSet(testSetFolderId, testSetFolderName);
});

/* hybrid triggers */
$( "#delete-checked-entities").click(function() {
    var applicationId = $(this).data('application-id');
    deleteEntities(applicationId);
});

/* test set folder methods */
function refreshTestSetFolderSubtitle(count) {
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
    subtitle += ' folder' + (count > 1 ? "s" : "");
    $('#test-set-folders-count').html(subtitle);
}

function saveTestSetFolder(applicationId) {
    var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
    var selectedFolder = $(testSetsTreeHtmlId).treeview('getSelected');
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
        url: Routing.generate('app_add_application_test_set_folder_ajax', {
            'id': applicationId,
            'parentId': parentFolderId
        }),
        data: $("#form-add-test-set-folder").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Test set folder not added !", data.error, "error");
        } else {
            var name = data.name;
            var testSetFoldersCount = data.testSetFoldersCount;
            hideTestSetsPanel(applicationId);
            $(testSetsTreeHtmlId).treeview({
                data: data.treeTestSets,
                showBorder: false,
                showCheckbox: true,
                onNodeSelected: function(event, data) {
                    showTestSetEntityProperties(data);
                },
                onNodeUnselected: function(event, data) {
                    hideEntityPropertiesPanelBodyAndFooter();
                    clearAddTestSetDataAttributes();
                    clearActionsHref();
                },
                onNodeChecked: function(event, data) {
                    checkTreeChildNodes(testSetsTreeHtmlId, data);
                    refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                },
                onNodeUnchecked: function(event, data) {
                    uncheckTreeChildNodes(testSetsTreeHtmlId, data);
                    uncheckTreeParentNode(testSetsTreeHtmlId, data);
                    refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                }                 
            });
            $(testSetsTreeHtmlId).treeview('collapseAll', { silent: true });
            showTestSetsPanel(applicationId);
            var selectedNode = $(testSetsTreeHtmlId).treeview("getSelected")[0];
            showTestSetEntityProperties(selectedNode);
            $(testSetsTreeHtmlId).treeview("revealNode", selectedNode);
            refreshTestSetFolderSubtitle(testSetFoldersCount, applicationId);
            swal(name + " added !", "Your folder has been added.", "success");
            $("#form-add-test-set-folder")[0].reset();
            $("#modal-add-test-set-folder").modal('hide');
        }
    });
}

/* test set methods */
function refreshTestSetSubtitle(count) {
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
    subtitle += ' test set' + (count > 1 ? "s" : "");
    $('#test-sets-count').html(subtitle);
}

function refreshTestSetsTree(id, collapse, checkbox) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_test_sets_tree_ajax', {
            'id': id
        })
    }).done(function(data) {
        showTestSetsTree(data, collapse, checkbox);
        revealTestSetsTreeSelectedNode(id);
    });
}

function showTestSetsTree(data, collapse, checkbox) {
    jQuery.each(data, function(i, val) {
        var id = i.substring(i.lastIndexOf("-") + 1);
        var htmlId = "#" + i;
        $(htmlId).treeview({
            data: val,
            showBorder: false,
            showCheckbox: checkbox,
            onNodeSelected: function(event, data) {
                showTestSetEntityProperties(data);
            },
            onNodeUnselected: function(event, data) {
                hideEntityPropertiesPanelBodyAndFooter();
                clearAddTestSetDataAttributes();
                clearActionsHref();
            },
            onNodeChecked: function(event, data) {
                checkTreeChildNodes(htmlId, data);
                refreshCheckedTestSetsTreeEntitiesCount(id);
            },
            onNodeUnchecked: function(event, data) {
                uncheckTreeChildNodes(htmlId, data);
                uncheckTreeParentNode(htmlId, data);
                refreshCheckedTestSetsTreeEntitiesCount(id);
            }
        });
        if (collapse) {
            $(htmlId).treeview('collapseAll', { silent: true });
        }
        revealTestSetsTreeSelectedNode(id);
        showTestSetsPanel(id);
    });
}

function showTestSetsTreeWithSelectedFolder(testSetFolderId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_test_sets_tree_with_selected_folder_ajax', {
            'id': testSetFolderId
        })
    }).done(function(data) {
        showTestSetsTree(data, true, true);
    });
}

function showTestSetsTreeWithSelectedTestSet(testSetId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_test_sets_tree_with_selected_test_set_ajax', {
            'id': testSetId
        })
    }).done(function(data) {
        showTestSetsTree(data, true, true);
    });
}

function revealTestSetsTreeSelectedNode(applicationId) {
    var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
    var selectedNode = $(testSetsTreeHtmlId).treeview("getSelected")[0];
    if (selectedNode) {
        $(testSetsTreeHtmlId).treeview("revealNode", selectedNode);
    }
}

function hideTestSetsPanel(applicationId) {
    var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
    $(testSetsTreeHtmlId + "-loader").addClass("three-quarters-loader");
    $(testSetsTreeHtmlId).hide();
}

function showTestSetsPanel(applicationId) {
    var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
    $(testSetsTreeHtmlId + "-loader").removeClass("three-quarters-loader");
    $(testSetsTreeHtmlId).show();
}

function setAddTestSetDataAttributes(id, name, description) {
    $('#add-test-set').data('test-set-folder-id', id);
    $('#add-test-set').data('test-set-folder-name', name);
    $('#add-test-set').data('test-set-folder-description', description);
}

function clearAddTestSetDataAttributes() {
    $('#add-test-set').removeData('test-set-folder-id');
    $('#add-test-set').removeData('test-set-folder-name');
    $('#add-test-set').removeData('test-set-folder-description');
}

function showAddTestSetForm(id, name, description) {
    updateAddTestSetModalTitle(name, description);
    $('#save-test-set').data('test-set-folder-id', id);
    $('#save-test-set').data('test-set-folder-name', name);
    $("#modal-add-test-set").modal('show');
}

function saveTestSet(testSetFolderId, testSetFolderName) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_test_set_folder_test_set_ajax', {
            'id': testSetFolderId
        }),
        data: $("#form-add-test-set").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Test set not added !", data.error, "error");
        } else {
            var name = data.name;
            var testSetsCount = data.testSetsCount;
            var applicationId = data.applicationId;
            var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
            hideTestSetsPanel(applicationId);
            $(testSetsTreeHtmlId).treeview({
                data: data.treeTestSets,
                showBorder: false,
                showCheckbox: true,
                onNodeSelected: function(event, data) {
                    showTestSetEntityProperties(data);
                },
                onNodeUnselected: function(event, data) {
                    hideEntityPropertiesPanelBodyAndFooter();
                    clearAddTestSetDataAttributes();
                    clearActionsHref();
                },
                onNodeChecked: function(event, data) {
                    checkTreeChildNodes(testSetsTreeHtmlId, data);
                    refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                },
                onNodeUnchecked: function(event, data) {
                    uncheckTreeChildNodes(testSetsTreeHtmlId, data);
                    uncheckTreeParentNode(testSetsTreeHtmlId, data);
                    refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                }             
            });
            $(testSetsTreeHtmlId).treeview('collapseAll', { silent: true });
            showTestSetsPanel(applicationId);
            var selectedNode = $(testSetsTreeHtmlId).treeview("getSelected")[0];
            showTestSetEntityProperties(selectedNode);
            $(testSetsTreeHtmlId).treeview("revealNode", selectedNode);
            $(testSetsTreeHtmlId).treeview("expandNode", selectedNode);
            refreshTestSetSubtitle(testSetsCount, applicationId);
            swal(name + " added to " + testSetFolderName + " !", "Your test set has been added.", "success");
            $("#form-add-test-set")[0].reset();
            $("#modal-add-test-set").modal('hide');
        }
    });
}

function updateAddTestSetModalTitle(name, description) {
    $('#new-test-set-test-set-folder-name').html(name);
    $('#new-test-set-test-set-folder-description').html(description);
}

/* hybrid methods */
function showTestSetEntityProperties(treeNode) {
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
                    url: Routing.generate('app_application_get_test_set_folder_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#entity-properties-loader").hide();
                    var folder = data.testSetFolder;
                    var name = folder.name;
                    var description = folder.description;
                    var createdAt = folder.createdAt;
                    var chart = folder.chart;
                    $('#entity-name').editable('option', 'pk', id);
                    $('#entity-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_set_folder_update_name_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-description').editable('option', 'pk', id);
                    $('#entity-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_set_folder_update_description_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-name').editable('setValue', name, false);
                    $('#entity-description').editable('setValue', description, false);
                    $('#entity-creation-date').html(createdAt);
                    setAddTestSetDataAttributes(id, name, description);
                    refreshEntityChart(chart);
                });
                break;
            case "test-set":
                showEntityActions();
                $('#edit-entity').attr("href",
                    Routing.generate('app_index_application_test_set_editor', {
                        'id': id
                    })
                );
                $('#entity-icon').removeClass().addClass('fontello-icon-beaker');
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_application_get_test_set_folder_test_set_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#entity-properties-loader").hide();
                    var testSet = data.testSet;
                    var name = testSet.name;
                    var description = testSet.description;
                    var createdAt = testSet.createdAt;
                    var chart = testSet.chart;
                    $('#entity-name').editable('option', 'pk', id);
                    $('#entity-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_set_folder_test_set_update_name_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-description').editable('option', 'pk', id);
                    $('#entity-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_test_set_folder_test_set_update_description_ajax', {
                            'id': id
                        })
                    );
                    $('#entity-name').editable('setValue', name, false);
                    $('#entity-description').editable('setValue', description, false);
                    $('#entity-creation-date').html(createdAt);
                    refreshEntityChart(chart);
                });
                break;
            default:
                $('#entity-icon').removeClass().addClass("fontello-icon-puzzle");
        }
    } else {
        hideEntityPropertiesPanelBodyAndFooter();
    }
}

function hideEntityPropertiesPanelBodyAndFooter() {
    $('#panel-body-entity-properties').hide();
    $('#panel-footer-entity-properties').hide();
}

function refreshCheckedTestSetsTreeEntitiesCount(applicationId) {
    var checkedEntities = $("#tree-test-sets-" + applicationId).treeview('getChecked');
    var checkedEntitiesCount = checkedEntities.length;
    $("#checked-entities-count").html(checkedEntitiesCount);
    if (checkedEntitiesCount > 0) {
        $("#delete-checked-entities").removeClass("disabled");
    } else {
        $("#delete-checked-entities").addClass("disabled");
    }
}

function deleteEntities(applicationId) {
    var testSetsTreeHtmlId = "#tree-test-sets-" + applicationId;
    swal({
        title: "Delete selected entities ?",
        text: "You will not be able to recover them !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete them !",
        closeOnConfirm: false
    }, function() {
        var objects = $(testSetsTreeHtmlId).treeview('getChecked');
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_test_set_entities_delete_ajax', {
                id: applicationId
            }),
            data: {
                objects: objects,
                selectedNode: $(testSetsTreeHtmlId).treeview("getSelected")[0]
            }
        }).done(function(data) {
            if (data.error) {
                swal("Selected entities were not deleted !", data.error, "error");
            } else {
                var count = data.count;
                var testSetFoldersCount = data.testSetFoldersCount;
                var testSetsCount = data.testSetsCount;
                var applicationId = data.applicationId;
                hideTestSetsPanel(applicationId);
                hideEntityPropertiesPanelBodyAndFooter();
                clearAddTestDataAttributes();
                clearActionsHref();
                $(testSetsTreeHtmlId).treeview({
                    data: data.treeTestSets,
                    showBorder: false,
                    showCheckbox: true,
                    onNodeSelected: function(event, data) {
                        showTestSetEntityProperties(data);
                    },
                    onNodeUnselected: function(event, data) {
                        hideEntityPropertiesPanelBodyAndFooter();
                        clearAddTestSetDataAttributes();
                        clearActionsHref();
                    },
                    onNodeChecked: function(event, data) {
                        checkTreeChildNodes(testSetsTreeHtmlId, data);
                        refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                    },
                    onNodeUnchecked: function(event, data) {
                        uncheckTreeChildNodes(testSetsTreeHtmlId, data);
                        uncheckTreeParentNode(testSetsTreeHtmlId, data);
                        refreshCheckedTestSetsTreeEntitiesCount(applicationId);
                    }             
                });
                $(testSetsTreeHtmlId).treeview('collapseAll', { silent: true });
                showTestSetsPanel(applicationId);
                refreshTestSetSubtitle(testSetsCount, applicationId);
                refreshTestSetFolderSubtitle(testSetFoldersCount, applicationId);
                refreshCheckedTestSetsTreeEntitiesCount(applicationId);
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

function hideEntityStatus() {
    $("#panel-body-content-entity-status").hide();
}

function showEntityStatus() {
    $("#panel-body-content-entity-status").show();
}

function clearActionsHref() {
    $("#edit-entity").removeAttr("href");
}

function refreshEntityChart(data) {
    var chartContext = $("#entity-chart").get(0).getContext("2d");
    new Chart(chartContext).Doughnut(data, {
        scaleFontSize: 8,
        tooltipFontSize: 10,
        percentageInnerCutout : 70
    });
}
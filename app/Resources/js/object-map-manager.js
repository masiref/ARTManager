/* object map trigger */
$( "#modal-add-object-map" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-object-map" ).click(function() {
    $("#modal-add-object-map").modal('show');
});

$( "#save-object-map" ).click(function() {
    var applicationId = $(this).data('application-id');
    saveObjectMap(applicationId);
});

$( "[id^=delete-object-map-]" ).click(function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    deleteObjectMap(id, name);
});

/* page triggers */
$( "#modal-add-page" ).modal({
    backdrop: 'static',
    show: false
});

$( "[id^=add-page-]" ).click(function() {
    var id = $(this).data('object-map-id');
    var name = $(this).data('object-map-name');
    var description = $(this).data('object-map-description');
    showAddPageForm(id, name, description);
});

$( "#save-page" ).click(function() {
    var objectMapId = $(this).data('object-map-id');
    var objectMapName = $(this).data('object-map-name');
    savePage(objectMapId, objectMapName);
});

/* object triggers */
$( "#modal-add-object" ).modal({
    backdrop: 'static',
    show: false
});

$( "#add-object" ).click(function() {
    var id = $(this).data('page-id');
    if (id) {
        var name = $(this).data('page-name');
        var description = $(this).data('page-description');
        showAddObjectForm(id, name, description);
    } else {
        swal({
            title: "Error",
            text: "Please select a page !",
            type: "error",
            confirmButtonText: "OK"
        });
    }
});

$( "#save-object" ).click(function() {
    var pageId = $(this).data('page-id');
    var pageName = $(this).data('page-name');
    saveObject(pageId, pageName);
});

/* hybrid triggers */
$( "#delete-checked-objects").click(function() {
    var objectMapId = $(this).data('object-map-id');
    deleteObjects(objectMapId);
});

/* object map methods */
function refreshObjectMapSubtitle(count) {
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
    subtitle += ' object map' + (count > 1 ? "s" : "");
    $('#object-maps-count').html(subtitle);;
}

function saveObjectMap(applicationId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_object_map_ajax', {
            'id': applicationId
        }),
        data: $("#form-add-object-map").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Object map not added !", data.error, "error");
        } else {
            var objectMap = JSON.parse(data.objectMap);
            var id = objectMap.id;
            var name = objectMap.name;
            var panel = data.panel;
            $("#object-maps-row").prepend(panel);
            $('#delete-object-map-' + id).click(function(event) {
                event.preventDefault();
                id = $(this).data('id');
                name = $(this).data('name');
                deleteObjectMap(id, name);
            }).tooltip();
            $('#open-object-map-' + id).tooltip();
            refreshPageSubtitle(0, id);
            var objectMapsCount = $("[id^=panel-object-map-]").length;
            refreshObjectMapSubtitle(objectMapsCount);
            swal(name + " added !", "Your object map has been added.", "success");
            $("#tree-object-map-" + id + "-loader").removeClass("three-quarters-loader");
            $("#form-add-object-map")[0].reset();
            $("#modal-add-object-map").modal('hide');
        }
    });
}

function deleteObjectMap(id, name) {
    swal({
        title: "Delete " + name + " ?",
        text: "You will not be able to recover this object map !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it !",
        closeOnConfirm: false
    },
    function(){
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_delete_application_object_map_ajax', {
                'id': id
            })
        }).done(function(data) {
            if (data.error) {
                swal(name + " not deleted !", data.error, "error");
            } else {
                $('#panel-object-map-' + id).css('visibility', 'hidden').animate({
                    height: 0,
                    width: 0
                }, 300, function () {
                    var container = $(this).parent();
                    $(this).remove();
                    container.remove();
                    var objectPropertiesPanel = $('#panel-object-properties');
                    if (objectPropertiesPanel !== undefined) {
                        objectPropertiesPanel.remove();
                    }
                    var objectMapsCount = $("[id^=panel-object-map-]").length;
                    refreshObjectMapSubtitle(objectMapsCount);
                });
                swal(name + " deleted !", "Your object map has been deleted.", "success");
            }
        });
    });
}

function refreshObjectMap(id, collapse, checkbox) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_object_map_tree_ajax', {
            'id': id
        })
    }).done(function(data) {
        showObjectMapTree(data, collapse, checkbox);
        revealObjectMapSelectedNode(id);
    });
}

function refreshObjectMaps(applicationId, collapse) {
    $(document).ready(function() {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_trees_ajax', {
                'id': applicationId
            })
        }).done(function(data) {
            showObjectMapTrees(data, collapse, false);
        });
    });
}

function hideObjectMapPanel(objectMapId) {
    var objectMapHtmlId = "#tree-object-map-" + objectMapId;
    $(objectMapHtmlId + "-loader").addClass("three-quarters-loader");
    $(objectMapHtmlId).hide();
}

function showObjectMapPanel(objectMapId) {
    var objectMapHtmlId = "#tree-object-map-" + objectMapId;
    $(objectMapHtmlId + "-loader").removeClass("three-quarters-loader");
    $(objectMapHtmlId).show();
}

function showObjectMapTrees(data, collapse, checkbox) {
    jQuery.each(data, function(i, val) {
        var id = i.substring(i.lastIndexOf("-") + 1);
        var htmlId = "#" + i;
        $(htmlId).treeview({
            data: val,
            showBorder: false,
            showCheckbox: checkbox,
            onNodeSelected: function(event, data) {
                showObjectProperties(data);
            },
            onNodeUnselected: function(event, data) {
                hideObjectPropertiesPanelBodyAndFooter();
                clearAddObjectDataAttributes();
            },
            onNodeChecked: function(event, data) {
                checkTreeChildNodes(htmlId, data);
                refreshCheckedObjectMapObjectsCount(id);
            },
            onNodeUnchecked: function(event, data) {
                uncheckTreeChildNodes(htmlId, data);
                uncheckTreeParentNode(htmlId, data);
                refreshCheckedObjectMapObjectsCount(id);
            }
        });
        if (collapse) {
            $(htmlId).treeview('collapseAll', { silent: true });
        }
        revealObjectMapSelectedNode(id);
        showObjectMapPanel(id);
    });
}

function showObjectMapTree(data, collapse, checkbox) {
    showObjectMapTrees(data, collapse, checkbox);
}

function showObjectMapTreeWithSelectedPage(pageId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_object_map_tree_with_selected_page_ajax', {
            'id': pageId
        })
    }).done(function(data) {
        showObjectMapTree(data, true, true);
    });
}

function showObjectMapTreeWithSelectedObject(objectId) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_application_get_object_map_tree_with_selected_object_ajax', {
            'id': objectId
        })
    }).done(function(data) {
        showObjectMapTree(data, true, true);
    });
}

function revealObjectMapSelectedNode(objectMapId) {
    var objectMapHtmlId = "#tree-object-map-" + objectMapId;
    var selectedNode = $(objectMapHtmlId).treeview("getSelected")[0];
    if (selectedNode) {
        $(objectMapHtmlId).treeview("revealNode", selectedNode);
    }
}

/* page methods */
function refreshPageSubtitle(count, objectMapId) {
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
    subtitle += ' page' + (count > 1 ? "s" : "") + ' in the object map';
    $('#page-count-' + objectMapId).html(subtitle);
}

function showAddPageForm(id, name, description) {
    $('#new-page-object-map-name').html(name);
    $('#save-page').data('object-map-id', id);
    $('#save-page').data('object-map-name', name);
    $('#new-page-object-map-description').html(description);
    $('#save-page').data('object-map-description', description);
    $("#modal-add-page").modal('show');
}

function savePage(objectMapId, objectMapName) {
    var objectMapHtmlId = "#tree-object-map-" + objectMapId;
    var selectedPage = $(objectMapHtmlId).treeview('getSelected');
    var parentPageId = -1;
    if (selectedPage.length === 1) {
        var parentPage = selectedPage[0];
        var href = parentPage.href;
        var id = href.substring(href.lastIndexOf("-") + 1);
        var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
        if (type === "page") {
            parentPageId = id;
        }
    }
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_object_map_page_ajax', {
            'id': objectMapId,
            'parentId': parentPageId
        }),
        data: $("#form-add-page").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Page not added !", data.error, "error");
        } else {
            var id = data.id;
            var name = data.name;
            var pagesCount = data.pagesCount;
            hideObjectMapPanel(objectMapId);
            $(objectMapHtmlId).treeview({
                data: data.treeObjectMap,
                showBorder: false,
                showCheckbox: true,
                onNodeSelected: function(event, data) {
                    showObjectProperties(data);
                },
                onNodeUnselected: function(event, data) {
                    hideObjectPropertiesPanelBodyAndFooter();
                    clearAddObjectDataAttributes();
                },
                onNodeChecked: function(event, data) {
                    checkTreeChildNodes(objectMapHtmlId, data);
                    refreshCheckedObjectMapObjectsCount(objectMapId);
                },
                onNodeUnchecked: function(event, data) {
                    uncheckTreeChildNodes(objectMapHtmlId, data);
                    uncheckTreeParentNode(objectMapHtmlId, data);
                    refreshCheckedObjectMapObjectsCount(objectMapId);
                }                 
            });
            $(objectMapHtmlId).treeview('collapseAll', { silent: true });
            showObjectMapPanel(objectMapId);
            var selectedNode = $(objectMapHtmlId).treeview("getSelected")[0];
            showObjectProperties(selectedNode);
            $(objectMapHtmlId).treeview("revealNode", selectedNode);
            refreshPageSubtitle(pagesCount, objectMapId);
            swal(name + " added to " + objectMapName + " !", "Your page has been added.", "success");
            $("#form-add-page")[0].reset();
            $("#modal-add-page").modal('hide');
        }
    });
}

function showPageTypeBlock() {
    $('#page-type-block').show();
}

function hidePageTypeBlock() {
    $('#page-type-block').hide();
}

function showPagePathBlock() {
    $('#page-path-block').show();
}

function hidePagePathBlock() {
    $('#page-path-block').hide();
}

/* object methods */
function refreshObjectSubtitle(count, objectMapId) {
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
    subtitle += ' object' + (count > 1 ? "s" : "") + ' in the object map';
    $('#object-count-' + objectMapId).html(subtitle);
}

function showAddObjectForm(id, name, description) {
    $('#new-object-page-name').html(name);
    $('#save-object').data('page-id', id);
    $('#save-object').data('page-name', name);
    $('#new-object-page-description').html(description);
    $('#save-object').data('page-description', description);
    $("#modal-add-object").modal('show');
}

function saveObject(pageId, pageName) {
    $.ajax({
        type: 'POST',
        url: Routing.generate('app_add_application_object_map_page_object_ajax', {
            'id': pageId
        }),
        data: $("#form-add-object").serialize()
    }).done(function(data) {
        if (data.error) {
            swal("Object not added !", data.error, "error");
        } else {
            var id = data.id;
            var name = data.name;
            var objectsCount = data.objectsCount;
            var objectMapId = data.objectMapId;
            var objectMapHtmlId = "#tree-object-map-" + objectMapId;
            hideObjectMapPanel(objectMapId);
            $(objectMapHtmlId).treeview({
                data: data.treeObjectMap,
                showBorder: false,
                showCheckbox: true,
                onNodeSelected: function(event, data) {
                    showObjectProperties(data);
                },
                onNodeUnselected: function(event, data) {
                    hideObjectPropertiesPanelBodyAndFooter();
                    clearAddObjectDataAttributes();
                },
                onNodeChecked: function(event, data) {
                    checkTreeChildNodes(objectMapHtmlId, data);
                    refreshCheckedObjectMapObjectsCount(objectMapId);
                },
                onNodeUnchecked: function(event, data) {
                    uncheckTreeChildNodes(objectMapHtmlId, data);
                    uncheckTreeParentNode(objectMapHtmlId, data);
                    refreshCheckedObjectMapObjectsCount(objectMapId);
                }                 
            });
            $(objectMapHtmlId).treeview('collapseAll', { silent: true });
            showObjectMapPanel(objectMapId);
            var selectedNode = $(objectMapHtmlId).treeview("getSelected")[0];
            showObjectProperties(selectedNode);
            $(objectMapHtmlId).treeview("revealNode", selectedNode);
            $(objectMapHtmlId).treeview("expandNode", selectedNode);
            refreshObjectSubtitle(objectsCount, objectMapId);
            swal(name + " added to " + pageName + " !", "Your object has been added.", "success");
            $("#form-add-object")[0].reset();
            $("#modal-add-object").modal('hide');
        }
    });
}

function setAddObjectDataAttributes(id, name, description) {
    $('#add-object').data('page-id', id);
    $('#add-object').data('page-name', name);
    $('#add-object').data('page-description', description);
}

function clearAddObjectDataAttributes() {
    $('#add-object').removeData('page-id');
    $('#add-object').removeData('page-name');
    $('#add-object').removeData('page-description');
}

function showObjectTypeBlock() {
    $('#object-type-block').show();
}

function hideObjectTypeBlock() {
    $('#object-type-block').hide();
}

/* hybrid methods */
function deleteObjects(objectMapId) {
    var objectMapHtmlId = "#tree-object-map-" + objectMapId;
    swal({
        title: "Delete selected objects ?",
        text: "You will not be able to recover them !",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete them !",
        closeOnConfirm: false
    }, function() {
        var objects = $(objectMapHtmlId).treeview('getChecked');
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_object_map_objects_delete_ajax', {
                id: objectMapId
            }),
            data: {
                objects: objects,
                selectedNode: $(objectMapHtmlId).treeview("getSelected")[0]
            }
        }).done(function(data) {
            if (data.error) {
                swal("Selected objects were not deleted !", data.error, "error");
            } else {
                var count = data.count;
                var objectsCount = data.objectsCount;
                var pagesCount = data.pagesCount;
                var objectMapId = data.objectMapId;
                hideObjectMapPanel(objectMapId);
                $(objectMapHtmlId).treeview({
                    data: data.treeObjectMap,
                    showBorder: false,
                    showCheckbox: true,
                    onNodeSelected: function(event, data) {
                        showObjectProperties(data);
                    },
                    onNodeUnselected: function(event, data) {
                        hideObjectPropertiesPanelBodyAndFooter();
                        clearAddObjectDataAttributes();
                    },
                    onNodeChecked: function(event, data) {
                        checkTreeChildNodes(objectMapHtmlId, data);
                        refreshCheckedObjectMapObjectsCount(objectMapId);
                    },
                    onNodeUnchecked: function(event, data) {
                        uncheckTreeChildNodes(objectMapHtmlId, data);
                        uncheckTreeParentNode(objectMapHtmlId, data);
                        refreshCheckedObjectMapObjectsCount(objectMapId);
                    }                 
                });
                $(objectMapHtmlId).treeview('collapseAll', { silent: true });
                showObjectMapPanel(objectMapId);
                hideObjectPropertiesPanelBodyAndFooter();
                /*var selectedNode = $(objectMapHtmlId).treeview("getSelected")[0];
                showObjectProperties(selectedNode);
                $(objectMapHtmlId).treeview("revealNode", selectedNode);
                $(objectMapHtmlId).treeview("expandNode", selectedNode);*/
                refreshObjectSubtitle(objectsCount, objectMapId);
                refreshPageSubtitle(pagesCount, objectMapId);
                refreshCheckedObjectMapObjectsCount(objectMapId);
                swal("Selected objects deleted !", "You have deleted " + count + " object" + (count > 1 ? "s" : ""), "success");
            }
        });
    });
}

function showObjectProperties(treeNode) {
    if (treeNode) {
        $("#panel-body-object-properties").show();
        $("#panel-footer-object-properties").show();
        $("#object-properties-loader").show();
        var href = treeNode.href;
        var id = href.substring(href.lastIndexOf("-") + 1);
        var type = href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
        $('#object-icon').removeClass("fontello-icon-puzzle");
        switch(type) {
            case "page":
                showPageTypeBlock();
                showPagePathBlock();
                hideObjectTypeBlock();
                hideObjectIdentifierBlock();
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_application_get_object_map_page_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#object-properties-loader").hide();
                    var page = data.page;
                    var name = page.name;
                    var description = page.description;
                    var pageType = page.pageType;
                    var path = page.path;
                    //var createdAt = page.createdAt;
                    $('#object-icon').removeClass().addClass(pageType.icon);
                    $('#object-name').editable('option', 'pk', id);
                    $('#object-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_update_page_name_ajax', {
                            'id': id
                        })
                    );
                    $('#object-description').editable('option', 'pk', id);
                    $('#object-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_update_page_description_ajax', {
                            'id': id
                        })
                    );
                    $('#page-type').editable('option', 'pk', id);
                    $('#page-type').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_update_page_type_ajax', {
                            'id': id
                        })
                    );
                    $('#page-path').editable('option', 'pk', id);
                    $('#page-path').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_update_page_path_ajax', {
                            'id': id
                        })
                    );
                    $('#object-name').editable('setValue', name, false);
                    $('#object-description').editable('setValue', description, false);
                    $('#page-type').editable('setValue', pageType.id, false);
                    $('#page-path').editable('setValue', path, false);
                    //$('#object-creation-date').html(createdAt);
                    setAddObjectDataAttributes(id, name, description);
                });
                break;
            case "object":
                hidePageTypeBlock();
                hidePagePathBlock();
                showObjectTypeBlock();
                showObjectIdentifierBlock();
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_application_get_object_map_page_object_ajax', {
                        'id': id
                    })
                }).done(function(data) {
                    $("#object-properties-loader").hide();
                    var object = data.object;
                    var name = object.name;
                    var description = object.description;
                    //var createdAt = object.createdAt;
                    var objectType = object.objectType;
                    var objectIdentifier = object.objectIdentifier;
                    var objectIdentifierType = null;
                    if (objectIdentifier !== null) {
                        objectIdentifierType = objectIdentifier.objectIdentifierType;
                    }
                    $('#object-icon').removeClass().addClass(object.objectType.icon);
                    $('#object-name').editable('option', 'pk', id);
                    $('#object-name').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_page_update_object_name_ajax', {
                            'id': id
                        })
                    );
                    $('#object-description').editable('option', 'pk', id);
                    $('#object-description').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_page_update_object_description_ajax', {
                            'id': id
                        })
                    );
                    $('#object-type').editable('option', 'pk', id);
                    $('#object-type').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_page_update_object_type_ajax', {
                            'id': id
                        })
                    );
                    $('#object-identifier-type').editable('option', 'pk', id);
                    $('#object-identifier-type').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_page_update_object_identifier_type_ajax', {
                            'id': id
                        })
                    );
                    $('#object-identifier-value').editable('option', 'pk', id);
                    $('#object-identifier-value').editable(
                        'option',
                        'url',
                        Routing.generate('app_application_object_map_page_update_object_identifier_value_ajax', {
                            'id': id
                        })
                    );
                    $('#object-name').editable('setValue', name, false);
                    $('#object-description').editable('setValue', description, false);
                    $('#object-type').editable('setValue', objectType.id, false);
                    if (objectIdentifier !== null) {
                        $('#object-identifier-value').editable('setValue', objectIdentifier.value, false);
                        if (objectIdentifierType !== null) {
                            $('#object-identifier-type').editable('setValue', objectIdentifierType.id, false);
                        } else {
                            $('#object-identifier-type').editable('setValue', '', false);
                        }
                    } else {
                        $('#object-identifier-type').editable('setValue', '', false);
                        $('#object-identifier-value').editable('setValue', '', false);
                    }
                    //$('#object-creation-date').html(createdAt);
                });
                break;
            default:
                $('#object-icon').removeClass().addClass("fontello-icon-puzzle");
        }
    } else {
        hideObjectPropertiesPanelBodyAndFooter();
    }
}

function hideObjectPropertiesPanelBodyAndFooter() {
    $('#panel-body-object-properties').hide();
    $('#panel-footer-object-properties').hide();
}

function refreshCheckedObjectMapObjectsCount(objectMapId) {
    var checkedObjects = $("#tree-object-map-" + objectMapId).treeview('getChecked');
    var checkedObjectsCount = checkedObjects.length;
    $("#checked-objects-count").html(checkedObjectsCount);
    if (checkedObjectsCount > 0) {
        $("#delete-checked-objects").removeClass("disabled");
    } else {
        $("#delete-checked-objects").addClass("disabled");
    }
}

function showObjectIdentifierBlock() {
    $('#object-identifier-block').show();
}

function hideObjectIdentifierBlock() {
    $('#object-identifier-block').hide();
}

function refreshObjectTypeIcon(icon) {
    $('#object-icon').removeClass().addClass(icon);
}
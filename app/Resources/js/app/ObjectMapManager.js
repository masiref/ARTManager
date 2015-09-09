var ObjectMapManager = {
    init: function() {
        $("#modal-add-object-map").modal({
            backdrop: 'static',
            show: false
        });

        $("#add-object-map").click(function() {
            ObjectMapManager.openAddFormModal();
        });

        $("#save-object-map").click(function() {
            var applicationId = $(this).data('application-id');
            ObjectMapManager.save(applicationId);
        });

        $("[id^=delete-object-map-]").click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            ObjectMapManager.delete(id, name);
        });
        $("#delete-checked-objects").click(function() {
            var objectMapId = $(this).data('object-map-id');
            ObjectMapManager.deleteCheckedObjects(objectMapId);
        });
    },
    initItem: function(id, name) {
        $('#open-object-map-' + id).tooltip();
        $('#delete-object-map-' + id).click(function(event) {
            event.preventDefault();
            id = $(this).data('id');
            name = $(this).data('name');
            ObjectMapManager.delete(id, name);
        }).tooltip();
    },
    initEditableData: function(id) {
        $("#name").editable({
            success: function(response, newValue) {
                $("#breadcrumb-active-item").html(response);
            },
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            }
        });
        $("#description").editable({
            emptytext: 'Add description'
        });
        $("#object-name").editable({
            error: function(response, newValue) {
                Base.showErrorMessage(response.responseText);
            },
            success: function(response, newValue) {
                ObjectMapManager.hideTree(id);
                switch(response.objectType) {
                    case "page":
                        ObjectMapManager.refreshTreeWithSelectedPage(response.pageId);
                        $('#add-object').data('page-name', newValue);
                        break;
                    case "object":
                        ObjectMapManager.refreshTreeWithSelectedObject(response.objectId);
                        break;
                }
            }
        });
        $("#object-description").editable({
            emptytext: 'Add description',
            defaultValue: '',
            success: function(response, newValue) {
                ObjectMapManager.hideTree(id);
                switch(response.objectType) {
                    case "page":
                        ObjectMapManager.refreshTreeWithSelectedPage(response.pageId);
                        $('#add-object').data('page-description', newValue);
                        break;
                    case "object":
                        ObjectMapManager.refreshTreeWithSelectedObject(response.objectId);
                        break;
                }
            }
        });
    },
    refresh: function(id, collapse, checkbox) { 
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_tree_ajax', {
                'id': id
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                ObjectMapManager.refreshTree(id, "#" + treeCssSelector, treeData, collapse, checkbox);
            });
        });
    },
    refreshAll: function(applicationId, collapse, checkbox) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_trees_ajax', {
                'id': applicationId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                ObjectMapManager.refreshTree(id, "#" + treeCssSelector, treeData, collapse, checkbox);
            });
        });
    },
    resetAddForm: function() {
        $("#form-add-object-map")[0].reset();
    },
    openAddFormModal: function() {
        $("#modal-add-object-map").modal('show');
    },
    closeAddFormModal: function() {
        $("#modal-add-object-map").modal('hide');
    },
    refreshSummary: function() {
        var count = ObjectMapManager.getCount();
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' object map' + (count > 1 ? "s" : "");
        $('#object-maps-count').html(subtitle);
    },
    refreshObjectMapPageSummary: function(id, count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' page' + (count > 1 ? "s" : "") + ' in the object map';
        $('#page-count-' + id).html(subtitle);
    },
    refreshObjectMapObjectSummary: function(id, count) {
        var subtitle = 'There ';
        subtitle += (count <= 1 ? 'is ' : 'are ');
        subtitle += (count === 0 ? 'no ' : '<span class="badge">' + count + '</span>');
        subtitle += ' object' + (count > 1 ? "s" : "") + ' in the object map';
        $('#object-count-' + id).html(subtitle);
    },
    getCount: function() {
        return $("[id^=panel-object-map-]").length;
    },
    add: function(panel) {
        $("#object-maps-row").prepend(panel);
    },
    remove: function(id) {
        $('#panel-object-map-' + id).css('visibility', 'hidden').animate({
            height: 0,
            width: 0
        }, 300, function () {
            var container = $(this).parent();
            $(this).remove();
            container.remove();
            ObjectMapManager.refreshSummary();
        });
    },
    save: function(applicationId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_add_application_object_map_ajax', {
                'id': applicationId
            }),
            data: $("#form-add-object-map").serialize()
        }).done(function(data) {
            if (data.error) {
                var message = "Object map not added !\n" + data.error;
                Base.showErrorMessage(message);
            } else {
                var objectMap = data.objectMap;
                var id = objectMap.id;
                var name = objectMap.name;
                var panel = data.panel;
                ObjectMapManager.add(panel);
                ObjectMapManager.hideTreeLoader(id);
                ObjectMapManager.init(id, name);
                ObjectMapManager.refreshSummary();
                ObjectMapManager.refreshObjectMapPageSummary(id, 0);
                ObjectMapManager.resetAddForm();
                ObjectMapManager.closeAddFormModal();
                var message = name + " added !";
                Base.showSuccessMessage(message);
            }
        });
    },
    delete: function(id, name) {
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
                    var message = name + " not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    ObjectMapManager.remove(id);
                    var message = name + " deleted !";
                    Base.showSuccessMessage(message);
                }
            });
        });
    },
    showObjectPropertiesPanel: function() {
        $("#panel-body-object-properties").show();
        $("#panel-footer-object-properties").show();
    },
    hideObjectPropertiesPanel: function() {
        $("#panel-body-object-properties").hide();
        $("#panel-footer-object-properties").hide();
    },
    showObjectPropertiesLoader: function() {
        $("#object-properties-loader").show();
    },
    hideObjectPropertiesLoader: function() {
        $("#object-properties-loader").hide();
    },
    showObjectProperties: function(node) {
        if (node) {
            ObjectMapManager.showObjectPropertiesPanel();
            ObjectMapManager.showObjectPropertiesLoader();
            var href = node.href;
            var id = ObjectMapManager.getIdFromNodeHref(href);
            var type = ObjectMapManager.getTypeFromNodeHref(href);
            $('#object-icon').removeClass();
            switch(type) {
                case "page":
                    PageManager.showProperties(id);
                    break;
                case "object":
                    ObjectManager.showProperties(id);
                    break;
                default:
                    $('#object-icon').removeClass().addClass("fontello-icon-puzzle");
            }
        } else {
            ObjectMapManager.hideObjectPropertiesPanel();
        }
    },
    refreshObjectTypeIcon: function(icon) {
        $('#object-icon').removeClass().addClass(icon);
    },
    refreshCheckedObjectCount: function(id) {
        var treeCssSelector = ObjectMapManager.getTreeCssSelector(id);
        var checkedObjects = $(treeCssSelector).treeview('getChecked');
        var checkedObjectsCount = checkedObjects.length;
        $("#checked-objects-count").html(checkedObjectsCount);
        if (checkedObjectsCount > 0) {
            $("#delete-checked-objects").removeClass("disabled");
        } else {
            $("#delete-checked-objects").addClass("disabled");
        }
    },
    deleteCheckedObjects: function(id) {
        var treeCssSelector = ObjectMapManager.getTreeCssSelector(id);
        swal({
            title: "Delete selected objects ?",
            text: "You will not be able to recover them !",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete them !",
            closeOnConfirm: false
        }, function() {
            var objects = $(treeCssSelector).treeview('getChecked');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_application_object_map_objects_delete_ajax', {
                    id: id
                }),
                data: {
                    objects: objects,
                    selectedNode: $(treeCssSelector).treeview("getSelected")[0]
                }
            }).done(function(data) {
                if (data.error) {
                    var message = "Selected objects were not deleted !\n" + data.error;
                    Base.showErrorMessage(message);
                } else {
                    var count = data.count;
                    var objectsCount = data.objectsCount;
                    var pagesCount = data.pagesCount;
                    ObjectMapManager.hideTree(id);
                    ObjectMapManager.tree(id, treeCssSelector, data.treeObjectMap, true);
                    TreeManager.collapse(treeCssSelector);
                    ObjectMapManager.showTree(id);
                    ObjectMapManager.hideObjectPropertiesPanel();
                    ObjectMapManager.refreshObjectMapPageSummary(id, pagesCount);
                    ObjectMapManager.refreshObjectMapObjectSummary(id, objectsCount);
                    ObjectMapManager.refreshCheckedObjectCount(id);
                    var message = "You have deleted " + count + " object" + (count > 1 ? "s" : "");
                    Base.showSuccessMessage(message);
                }
            });
        });
    },
    tree: function(id, treeCssSelector, data, checkbox) {
        $(treeCssSelector).treeview({
            data: data,
            showBorder: false,
            showCheckbox: checkbox,
            onNodeSelected: function(event, node) {
                ObjectMapManager.showObjectProperties(node);
            },
            onNodeUnselected: function(event, node) {
                ObjectMapManager.hideObjectPropertiesPanel();
                ObjectManager.unsetAddButtonDataAttributes();
            },
            onNodeChecked: function(event, node) {
                TreeManager.checkChildNodes(treeCssSelector, node);
                ObjectMapManager.refreshCheckedObjectCount(id);
            },
            onNodeUnchecked: function(event, node) {
                TreeManager.uncheckChildNodes(treeCssSelector, node);
                TreeManager.uncheckParentNode(treeCssSelector, node);
                ObjectMapManager.refreshCheckedObjectCount(id);
            }                 
        });
    },
    refreshTree: function(id, treeCssSelector, data, collapse, checkbox) {
        ObjectMapManager.tree(id, treeCssSelector, data, checkbox);
        if (collapse) {
            TreeManager.collapse(treeCssSelector);
        }
        var selectedNode = $(treeCssSelector).treeview("getSelected")[0];
        TreeManager.revealNode(treeCssSelector, selectedNode);
        ObjectMapManager.showTree(id);
    },
    refreshTreeWithSelectedPage: function(pageId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_tree_with_selected_page_ajax', {
                'id': pageId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                ObjectMapManager.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    refreshTreeWithSelectedObject: function(objectId) {
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_application_get_object_map_tree_with_selected_object_ajax', {
                'id': objectId
            })
        }).done(function(data) {
            jQuery.each(data, function(treeCssSelector, treeData) {
                var id = treeCssSelector.substring(treeCssSelector.lastIndexOf("-") + 1);
                ObjectMapManager.refreshTree(id, "#" + treeCssSelector, treeData, true, true);
            });
        });
    },
    getTreeCssSelector: function(id) {
        return "#tree-object-map-" + id;
    },
    showTreeLoader: function(id) {
        $("#tree-object-map-" + id + "-loader").addClass("three-quarters-loader");
    },
    hideTreeLoader: function(id) {
        $("#tree-object-map-" + id + "-loader").removeClass("three-quarters-loader");
    },
    showTree: function(id) {
        var treeCssSelector = ObjectMapManager.getTreeCssSelector(id);
        ObjectMapManager.hideTreeLoader(id);
        $(treeCssSelector).show();
    },
    hideTree: function(id) {
        var treeCssSelector = ObjectMapManager.getTreeCssSelector(id);
        ObjectMapManager.showTreeLoader(id);
        $(treeCssSelector).hide();
    },
    getIdFromNodeHref: function(href) {
        return href.substring(href.lastIndexOf("-") + 1);
    },
    getTypeFromNodeHref: function(href) {
        return href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
    }
};
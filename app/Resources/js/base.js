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
    
    jQuery.fn.extend({
        disable: function(state) {
            return this.each(function() {
                var $this = $(this);
                $this.toggleClass('disabled', state);
            });
        }
    });
});

/* trees */
function checkTreeChildNodes(id, node) {
    var children = node.nodes;
    if (children) {
        for (var i = 0; i < children.length; i++) {
            var childNode = children[i];
            var nodeId = childNode['nodeId'];
            $(id).treeview('checkNode', nodeId);
        }
    }
}

function uncheckTreeChildNodes(id, node) {
    var children = node.nodes;
    if (children) {
        for (var i = 0; i < children.length; i++) {
            var childNode = children[i];
            var nodeId = childNode['nodeId'];
            $(id).treeview('uncheckNode', nodeId);
        }
    }
}

function uncheckTreeParentNode(id, node) {
    var parent = $(id).treeview('getParent', node);
    var nodeId = parent['nodeId'];
    if (nodeId !== undefined) {
        $(id).treeview('uncheckNode', [ nodeId, { silent: true } ]);
        uncheckTreeParentNode(id, parent);
    }
}
/* end */

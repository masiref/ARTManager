var TreeManager = {
    checkChildNodes: function(cssSelector, node) {
        var children = node.nodes;
        if (children) {
            for (var i = 0; i < children.length; i++) {
                var childNode = children[i];
                var nodeId = childNode['nodeId'];
                $(cssSelector).treeview('checkNode', nodeId);
            }
        }
    },
    uncheckChildNodes: function(cssSelector, node) {
        var children = node.nodes;
        if (children) {
            for (var i = 0; i < children.length; i++) {
                var childNode = children[i];
                var nodeId = childNode['nodeId'];
                $(cssSelector).treeview('uncheckNode', nodeId);
            }
        }
    },
    uncheckParentNode: function(cssSelector, node) {
        var parent = $(cssSelector).treeview('getParent', node);
        var nodeId = parent['nodeId'];
        if (nodeId !== undefined) {
            $(cssSelector).treeview('uncheckNode', [ nodeId, { silent: true } ]);
            TreeManager.uncheckParentNode(cssSelector, parent);
        }
    },
    revealNode: function(cssSelector, node) {
        if (node) {
            $(cssSelector).treeview("revealNode", node);
        }
    },
    expandNode: function(cssSelector, node) {
        if (node) {
            $(cssSelector).treeview("expandNode", node);
        }
    },
    collapse: function(cssSelector) {
        $(cssSelector).treeview('collapseAll', { silent: true });
    },
    getIdFromNodeHref: function(href) {
        return href.substring(href.lastIndexOf("-") + 1);
    },
    getTypeFromNodeHref: function(href) {
        return href.substring(href.indexOf("-") + 1, href.lastIndexOf("-"));
    }
};
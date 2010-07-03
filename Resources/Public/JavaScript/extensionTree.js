Ext.onReady(function(){

	var children = [{
		text:'Model',
		children:[{
			text:'Object 1',
			children:[{
				text: 'Properties',
				leaf: true
			}, {
				text: 'TCA',
				leaf: true
			}, {
				text: 'Localization',
				leaf: true
			}]
		}, {
			text:'Object 2',
			children:[{
				text: 'Properties',
				leaf: true
			}, {
				text: 'TCA',
				leaf: true
			}, {
				text: 'Localization',
				leaf: true
			}]
		}]
	}, {
		text: 'Controllers',
		children: [{
			text: 'IndexController',
			leaf: true
		}, {
			text: 'ObjectController',
			leaf: true
		}]
	},{
		text:'Frontend Plugins',
		children:[{
			text:'Plugin 1',
			leaf:true
		}, {
			text:'Plugin 2',
			leaf:true
		}]
	}, {
		text: 'Backend Modules',
		children:[{
			text: 'Module 1',
			leaf: true
		}]
	}, {
		text: 'Scheduler tasks',
		children:[{
			text: 'Task 1',
			leaf: true
		}]
	}];

	var tree = new Ext.tree.TreePanel({
		applyTo: 'extension-tree',
		useArrows: true,
		autoScroll: true,
		animate: true,
		enableDD: false,
		containerScroll: true,
		selModel: new Ext.tree.MultiSelectionModel(),
		border: false,
		loader: new Ext.tree.TreeLoader(),

		root:new Ext.tree.AsyncTreeNode({
             expanded:true,
            leaf:false,
            text:'Tree Root',
            children:children
        })
	});

	tree.getRootNode().expand();
	tree.expandAll();
});
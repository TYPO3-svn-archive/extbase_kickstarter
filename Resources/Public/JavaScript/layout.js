Ext.ns("T3.ExtbaseKickstarter");
//T3.ExtbaseKickstarter.DocumentHeader = Ext.extend(Ext.Panel, {});
//Ext.reg('T3.ExtbaseKickstarter.DocumentHeader', T3.ExtbaseKickstarter.DocumentHeader);

T3.ExtbaseKickstarter.ViewPort = Ext.extend(Ext.Viewport, {
initComponent: function() {
	Ext.Viewport.superclass.initComponent.call(this);
		this.el = Ext.get(this.el) || Ext.getBody();

		if (this.el.dom === document.body) {
			this.el.dom.parentNode.className += ' x-viewport';
		}

		this.el.setHeight = Ext.emptyFn;
		this.el.setWidth = Ext.emptyFn;
		this.el.setSize = Ext.emptyFn;
		this.el.dom.scroll = 'no';
		this.allowDomMove = false;
		this.autoWidth = true;
		this.autoHeight = true;
		Ext.EventManager.onWindowResize(this.fireResize, this);
		this.renderTo = this.el;
	}
});
Ext.reg('T3.ExtbaseKickstarter.ViewPort', T3.ExtbaseKickstarter.ViewPort);

Ext.onReady(function(){
//	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	/** Quick and dirty :-) **/
	var docbody = Ext.get('typo3-inner-docbody');
	docbody.setWidth('100%').setHeight('100%');
	docbody.setHeight(docbody.getHeight() - 7).setWidth(docbody.getWidth() - 6);

	new Ext.Panel({
		tbar: [{
			text: 'Action Menu',
			handler: function() {
			}
		}, {
			text: 'Action Menu item 2',
			handler: function() {
			}
		}],
		renderTo: Ext.get('docheader-row2-left')
	});


	var viewPort = new T3.ExtbaseKickstarter.ViewPort({
		layout: 'border',
		el: 'typo3-inner-docbody',
		defaults: {
			collapsible:	false,
			split:			true,
			margins:		'0 0 0 0'
		},
		items: [{
//			region:			'north',
//			xtype:			'panel',
//
//		}, {
			region:			'west',
			collapseMode:	'mini',
			width:			280,
			minWidth:		175,
			maxWidth:		300,
			items: [{
				id:			'extension-tree'
			}]
		}, {
			region:			'center',
			id:				'content'
		}, {
			region:			'east',
			collapseMode:	'mini',
			split:			true,
			width:			225,
			layout: {
				type:		'accordion',
				animate:	true
			},
			items:			[{
				title: 'General',
				border: false,
				iconCls: 'nav'
			}, {
				title: 'Settings',
				border: false,
				iconCls: 'settings'
			}]
		}]
	});
});
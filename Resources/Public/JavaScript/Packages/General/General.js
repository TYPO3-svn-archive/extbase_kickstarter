Ext.ns("Tx.ExtbaseKickstarter.Packages.General.General");

Tx.ExtbaseKickstarter.Packages.General.General = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: 'General Info'
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.General.General.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.General.General', Tx.ExtbaseKickstarter.Packages.General.General);
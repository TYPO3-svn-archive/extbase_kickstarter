Ext.ns("Tx.ExtbaseKickstarter.UserInterface");

Tx.ExtbaseKickstarter.UserInterface.TabLayout = Ext.extend(Ext.TabPanel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			activeItem: 0
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.UserInterface.TabLayout.superclass.initComponent.call(this);
		Tx.ExtbaseKickstarter.Application.fireEvent('Tx.ExtbaseKickstarter.UserInterface.TabLayout.afterInitialize', this);
	}
});
Ext.reg('Tx.ExtbaseKickstarter.UserInterface.TabLayout', Tx.ExtbaseKickstarter.UserInterface.TabLayout);
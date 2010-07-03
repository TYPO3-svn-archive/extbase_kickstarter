Ext.ns("Tx.ExtbaseKickstarter.UserInterface");

Tx.ExtbaseKickstarter.UserInterface.Bootstrap = Ext.apply(new Tx.ExtbaseKickstarter.Application.AbstractBootstrap, {
	// TODO: document the afterInitialize event
	
	initialize: function() {
		Tx.ExtbaseKickstarter.Application.on('Tx.ExtbaseKickstarter.Application.afterBootstrap', this._initViewport, this);
	},
	/**
	 * Create the main viewport for layouting all components in a full
	 * width and height browser window.
	 */
	_initViewport: function() {
		Tx.ExtbaseKickstarter.UserInterface.viewport = new Tx.ExtbaseKickstarter.UserInterface.Layout();
		Tx.ExtbaseKickstarter.Application.fireEvent('Tx.ExtbaseKickstarter.UserInterface.afterInitialize');
	}

});
Tx.ExtbaseKickstarter.Application.registerBootstrap(Tx.ExtbaseKickstarter.UserInterface.Bootstrap);

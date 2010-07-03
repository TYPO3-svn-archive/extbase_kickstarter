Ext.ns("Tx.ExtbaseKickstarter.DomainModelling.DomainModeller");

Tx.ExtbaseKickstarter.DomainModelling.DomainModeller = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: 'Domain modelling'
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.DomainModelling.DomainModeller.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.DomainModelling.DomainModeller', Tx.ExtbaseKickstarter.DomainModelling.DomainModeller);
Ext.ns("Tx.ExtbaseKickstarter.Packages.General.General");

Tx.ExtbaseKickstarter.Packages.General.General = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {
		var config = {
			title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.general,
			autoLoad: {
				url: TYPO3.settings.extbase_kickstarter.controllers.General.index
			}
		};
		Ext.apply(this, config);
		Tx.ExtbaseKickstarter.Packages.General.General.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.General.General', Tx.ExtbaseKickstarter.Packages.General.General);
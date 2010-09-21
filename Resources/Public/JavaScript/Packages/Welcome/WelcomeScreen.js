Ext.ns("Tx.ExtbaseKickstarter.Packages.Welcome");

Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen = Ext.extend(Ext.Panel, {
	// TODO: document event
	initComponent: function() {

		var columnConfigDefaults = {
			collapsible: true
		};

		var config = {
			title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.welcome,
			layout: 'column',
			autoScroll: true,
			defaults: {
				border: false,
				baseCls:'x-plain',
				bodyStyle:'padding:5px 0 5px 5px'
			},
			items: [{
				columnWidth: .6,
				defaults: columnConfigDefaults,
				items: [{
					xtype: 'panel',
					title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.welcome,
					autoLoad: {
						url: TYPO3.settings.extbase_kickstarter.controllers.Welcome.welcome
					}
				}]
			}, {
				columnWidth: .4,
				defaults: columnConfigDefaults,
				items: [{
					xtype: 'panel',
					title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.openProjects,
					html: ''
				}, {
					xtype: 'panel',
					title: TYPO3.settings.extbase_kickstarter._LOCAL_LANG.help,
					autoLoad: {
						url: TYPO3.settings.extbase_kickstarter.controllers.Welcome.help
					}
				}]
			}]
		};
		Ext.apply(this, config);
		
		Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen.superclass.initComponent.call(this);
		
	}
});
Ext.reg('Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen', Tx.ExtbaseKickstarter.Packages.Welcome.WelcomeScreen);
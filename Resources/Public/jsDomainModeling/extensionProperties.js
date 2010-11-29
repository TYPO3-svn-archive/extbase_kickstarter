extbaseModeling_wiringEditorLanguage.propertiesFields =
[
	{
		type: "string",
		inputParams: {
			name: "name",
			label: "Name",
			typeInvite: "Extension title"
		}
	},
	{
		type: "string",
		inputParams: {
			name: "extensionKey",
			label: "Key",
			typeInvite: "Extension Key",
			cols: 30
		}
	},
	{
		inputParams: {
			name: "originalExtensionKey", 
			className:'hiddenField'
		}
	},
	{
		type: "text",
		inputParams: {
			name: "description",
			label: "Descr.",
			typeInvite: "Description",
			cols: 30
		}
	},
	{
		type: "string",
		inputParams: {
			name: "version", 
			label: "Version",
			required: false,
			size: 5
		}
	},
	{
		type: "select",
		inputParams: {
			label: "State",
			name: "state",
			selectValues: ["alpha","beta","stable","experimental","test"]
		}
	},
	{
		type: "list",
		inputParams: {
			label: "Persons",
			name: "persons",
			sortable: true,
			elementType: {
				type: "group",
				inputParams: {
					name: "property",
					fields: [
						{
							inputParams: {
								label: "Name",
								name: "name",
								required: false
							}
						},
						{
							type: "select",
							inputParams: {
								label: "Role",
								name: "role",
								selectValues: ["Developer", "Product Manager"]
							}
						},
						{
							inputParams: {
								label: "Email",
								name: "email",
								required: false
							}
						},
						{
							inputParams: {
								label: "Company",
								name: "company",
								required: false
							}
						},
					]
				}
			}
		}
	},
	{
		type: "list",
		inputParams: {
			label: "Plugins",
			name: "plugins",
			sortable: true,
			elementType: {
				type: "group",
				inputParams: {
					name: "property",
					fields: [
						{
							inputParams: {
								label: "Name",
								name: "name",
								required: true
							}
						},
						{
							inputParams: {
								label: "Key",
								name: "key",
								required: true
							}
//						},
//						{
//							type: "select",
//							inputParams: {
//								label: "Type",
//								name: "type",
//								selectValues: ["list_type", "CType"],
//								selectOptions: ["Frontend plugin", "Content type"],
//							}
						}
					]
				}
			}
		}
	},
	{
		type: "group",
		inputParams: {
			collapsible: true,
			collapsed: true,
			legend: "Overwrite settings",
			name: "advancedSettings",
			className:'overwriteSettings',
			fields: [
					{
						type: "text", 
						inputParams: {
							label: "You can configure the settings for each file separately: 0 => always overwrite, 1 => merge (if possible), 2 => never overwrite", 
							name: "overwriteSettings", 
							value: "Classes {\n\tController = 1\n\tModel = 1\n\tRepository = 1\n}\nConfiguration{\n\tTypoScript{\n\t\tconstants.txt = 2\n\t\tsetup.txt \t  = 2\n\t}\n}\nResources {\n\tPrivate {\n\t\tLanguage {\n\t\t\tlocallang.xml = 2\n\t\t}\n\t\tLayouts {\n\t\t\tlist.html = 0\n\t\t\tindex.html = 1\n\t\t}\n\t}\n}\next_localconf.php = 1\next_icon.gif = 2",
							cols:35,
							rows:22
						}
					},
			]
		}
	}
	
];
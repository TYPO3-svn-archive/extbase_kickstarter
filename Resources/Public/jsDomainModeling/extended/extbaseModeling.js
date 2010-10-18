Array.prototype.remove = function(from, to) {
	  var rest = this.slice((to || from) + 1 || this.length);
	  this.length = from < 0 ? this.length + from : from;
	  return this.push.apply(this, rest);
	};

var advancedFields = {
		type: "group",
		inputParams: {
			collapsible: true,
			collapsed: true,
			legend: "More",
			name: "advancedSettings",
			fields: [
					{
						type: "select", 
						inputParams: {
							label: "Type", 
							name: "relationType",
							selectValues: ["zeroToOne", "zeroToMany", "manyToMany"],
							selectOptions: ["0 .. 1","0 .. * (foreign Key)", "0 .. * (association table)"]
						}
					},
					{
						type: "text", 
						inputParams: {
							label: "Description", 
							name: "relationDescription", 
							cols:20,
							rows:1
						}
					},
					{
						type: "boolean",
						inputParams: {
							label: "Is ExcludeField?", 
							name: "propertyIsExcludeField",
							value: false
						}
					}
			]
		}
	};

var relationFieldSet = extbaseModeling_wiringEditorLanguage.modules[0].container.fields[4].inputParams.fields[0].inputParams.elementType.inputParams.fields;
relationFieldSet[5] = advancedFields;
// remove excludeField in first level form
relationFieldSet.remove(2);
// remove Description in first level form
relationFieldSet.remove(2);
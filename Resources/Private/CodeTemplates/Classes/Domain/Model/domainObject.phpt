<?php
{namespace k=Tx_ExtbaseKickstarter_ViewHelpers}
{classSchema.docComment}

<f:for each="{classSchema.modifierNames}" as="modifierName">{modifierName} </f:for>class {classSchema.name} <k:class classSchemaObject="{classSchema}"  renderElement="extend" />  <k:class classSchemaObject="{classSchema}"  renderElement="implement" /> {
<f:for each="{classSchema.constants}" as="constant">
	/**
	* <f:for each="{constant.docComment.getDescriptionLines}" as="descriptionLine">
	* {descriptionLine}</f:for>
	*<f:for each="{constant.tags}" as="tag">
	* {tag}</f:for>
	*/
	const {constant.name} = {constant.value};
</f:for>
<f:for each="{classSchema.properties}" as="property">
	/**
	* <f:for each="{property.descriptionLines}" as="descriptionLine">
	* {descriptionLine}</f:for>
	* <f:for each="{property.annotations}" as="annotation">
	* @{annotation}</f:for>
	*/
	<f:for each="{property.modifierNames}" as="modifierName">{modifierName} </f:for>${property.name}{property.defaultDeclaration};
</f:for>

<f:for each="{classSchema.methods}" as="method">
	/**
	* <f:for each="{method.descriptionLines}" as="descriptionLine">
	* {descriptionLine}</f:for>
	* <f:for each="{method.annotations}" as="annotation">
	* @{annotation}</f:for>
	*/
	<f:for each="{method.modifierNames}" as="modifierName">{modifierName} </f:for>function {method.name}(<k:method methodSchemaObject="{method}"  renderElement="parameter" /> )<![CDATA[{]]>
{method.body} 
	<![CDATA[}]]>
</f:for>
}
{classSchema.appendedBlock}
?>
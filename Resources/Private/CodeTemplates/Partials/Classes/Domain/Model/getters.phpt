{namespace k=Tx_ExtbaseKickstarter_ViewHelpers}
<f:for each="{getters}" as="getter">
	/**
	 * Getter for {getter.property.name}
	 *
	 * @param {getter.parameters.0.typeForComment} ${getter.property.name} {getter.property.description}
	 * @return void
	 */
	public function get{getter.property.name -> k:uppercaseFirst()}({getter.parameters.0.typeHint} ${getter.property.name}) {
		$this->{getter.property.name} = ${getter.property.name};
	}
</f:for>
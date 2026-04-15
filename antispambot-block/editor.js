( function ( blocks, element, blockEditor ) {
	var el = element.createElement;
	var RichText = blockEditor.RichText;
	var useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType( 'ironprogrammer/antispambot', {
		edit: function ( props ) {
			return el(
				RichText,
				Object.assign( {}, useBlockProps(), {
					tagName: 'p',
					value: props.attributes.content,
					onChange: function ( content ) {
						props.setAttributes( { content: content } );
					},
					allowedFormats: [],
					placeholder: 'Enter email to obfuscate\u2026',
				} )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );

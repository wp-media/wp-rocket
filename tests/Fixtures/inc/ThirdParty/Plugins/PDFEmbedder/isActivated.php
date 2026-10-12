<?php

return [
	'shouldReturnFalseWhenPDFEmbedderNotPresent' => [
		'config'   => [ 'define_core_pdf_embedder' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenPDFEmbedderPresent'     => [
		'config'   => [ 'define_core_pdf_embedder' => true ],
		'expected' => true,
	],
];

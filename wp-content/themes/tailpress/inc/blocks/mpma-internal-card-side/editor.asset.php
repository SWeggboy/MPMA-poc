<?php
return array(
	'dependencies' => array(
		'wp-blocks',
		'wp-element',
		'wp-block-editor',
		'wp-components',
		'wp-i18n',
	),
	'version' => file_exists( __DIR__ . '/editor.js' ) ? (string) filemtime( __DIR__ . '/editor.js' ) : '1.0.0',
);

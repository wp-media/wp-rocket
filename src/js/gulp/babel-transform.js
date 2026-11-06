const path = require( 'path' );
const { PassThrough, Transform } = require( 'stream' );

/**
 * Browserify transform running Babel 8 (replaces babelify, which only supports Babel 7).
 * Browserify only bundles CommonJS, so the caller declares no ES module support
 * and @babel/preset-env converts import/export to require().
 */
module.exports = function babelTransform( filename, opts = {} ) {
	if ( ! /\.(js|mjs|es6?|jsx)$/i.test( filename ) ) {
		return new PassThrough();
	}

	const chunks = [];
	const basedir = path.resolve( ( opts._flags && opts._flags.basedir ) || '.' );

	return new Transform( {
		transform( chunk, enc, callback ) {
			chunks.push( chunk );
			callback();
		},
		flush( callback ) {
			const code = Buffer.concat( chunks ).toString();
			import( '@babel/core' )
				.then( ( babel ) => babel.transformAsync( code, {
					cwd: basedir,
					filename: path.resolve( basedir, filename ),
					caller: { name: 'wpr-browserify', supportsStaticESM: false, supportsDynamicImport: false },
					sourceMaps: 'inline',
				} ) )
				.then( ( result ) => callback( null, result ? result.code : code ) )
				.catch( callback );
		},
	} );
};

<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'capa' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '@bZ$vJ7:QA)GxgY<Q_&Uf3vTEzo%+=q.W,zMa,D,r0c#T$Bxg06sY@Xu[0p0]otD' );
define( 'SECURE_AUTH_KEY',   ']Spe|KoR:PWaTObr`SNVbZu+SOF.Pw~2&Ln-;G2lSI5!p35M> h/KtQb|K2nSnZo' );
define( 'LOGGED_IN_KEY',     's_;+K8Jy9O7ltEIVQLfkz,,Zk2kB5t)Z.7;9}SDUwb(iK,JjETmN4`4Qdv/180{A' );
define( 'NONCE_KEY',         '7&,7.lfxxF|@8h?|RwZ)WOkJ7cd^X@,__>=XL6pOh_*,4d:pp=ToZM`)r_<=uM0e' );
define( 'AUTH_SALT',         'bqVsF!H&X9_2ZxJwiN!N[?Du]4oBG|C/K,!T@`H9IWL=:w$GX#P`J{mE3#d6Hb=M' );
define( 'SECURE_AUTH_SALT',  '8G:6H9y!RI-0NPp2gA;}kb(YV:I!X$vqF{3L.Kah37r:yb28]{&V+/a>Ky(uy96k' );
define( 'LOGGED_IN_SALT',    '7~+jXo@K)ye?~ACS[0^|dz)/N9L)+Q$Un?^CFSBlf8n+jAV .KBk,.JIq>3XKJCQ' );
define( 'NONCE_SALT',        'V0LKScUBp+ni2cUx9lT8Jgv}.hRc,@3wKl^Ja3$)Ky=uN<Ba|e4V^O)0kz<unyJS' );
define( 'WP_CACHE_KEY_SALT', '.v~QMvn0:cbMRz#5M)R|E]]]1~<yAAs(p]iB_KqVp}i`!jo`SXj0IVOH*=(;dw|=' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';


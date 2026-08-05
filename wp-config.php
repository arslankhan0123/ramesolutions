<?php
define( 'WP_CACHE', true );

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
define( 'DB_NAME', 'ramesolutions' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',          '*XQS<+Rc(txw@j+UZrmOYu2yD6&x_;>#qeDh4J`W>4j=lX`BC*o(*$ln<~cEL.WS' );
define( 'SECURE_AUTH_KEY',   '6uM?JbO:_j7B#T<9HKiH+|^G&bIUoM6{Q*k >Xsq&$uy70I2?;C<3J2,GWYi@{>G' );
define( 'LOGGED_IN_KEY',     'Y$rQTA6EO)-g$4Ue*^7$)%u0: *>i_;h~wHtUuQSe?/FPM`P/?9}OsWw!/]GDVer' );
define( 'NONCE_KEY',         'yj*Q,%n-E-,m.fgv0KH|(ge5v)iX37&<q+{Mi/l_ ) aCtK-W-yY==Bo,2N1=qiO' );
define( 'AUTH_SALT',         '{u-pUk]OHwY,`dt:7Y_LMk+5nanTlzanH#}#r0thrQssZk[*AFz&W6aj4eR{BjP?' );
define( 'SECURE_AUTH_SALT',  '~0:.<o{V=*Q!i^A`1HKn@$&w:kG7a6;8xgT>Y@Ufu^-q4(eld$2Rf6EmaS;R6A8:' );
define( 'LOGGED_IN_SALT',    'oe?RRPpE?DLxQsF-*_MvPJ6D,=q46]qv0mm3Ic]mHXl5YoO}FW=&B5ivG+8xNIzl' );
define( 'NONCE_SALT',        'Q(szo)fcC[mkze-7w8w5O_f))abI$bq)3{0!?U0^ns6)8%C2QpL-n57JJ?(@)!yL' );
define( 'WP_CACHE_KEY_SALT', 'Fk-gICWXS&hdFN/I|qV[r]IyZsl$d:?~N(/e0^?//6unt{u4!!2Bk<VPibmjA_=_' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_HOME', 'http://ramesolutions.test' );
define( 'WP_SITEURL', 'http://ramesolutions.test' );



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

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '9fdcaf714833c342dfbfa151fd2312ce' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

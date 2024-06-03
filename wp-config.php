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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wm_crm' );

/** Database username */
define( 'DB_USER', 'wm_crm' );

/** Database password */
define( 'DB_PASSWORD', 'sK5mM6jG4f' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'V_/Y?@cyfyDJ=K!~Si)pJs3R2&XP8ZS%W}P*p0>!49HEe8Ed;{yW]rR~D_HBsmro' );
define( 'SECURE_AUTH_KEY',  'mY_Me/-Z%{3n 3$ZR6kI.3:qB2<sl#Uy2drJ)~*Na|::8#V]Br:P#([}bHlzmyeW' );
define( 'LOGGED_IN_KEY',    'BYDMQiMIU-e03`nQa;4A56?w:xn?>c=-}h~zjM1*33Hs47[-E=Kn2DZ{F=iT(fZL' );
define( 'NONCE_KEY',        'YL6ga7#/[,%F(H/aj?6ENv:j28+Y.bC3[o2H{VM?tg4kjxv,7Q7cwj[%91gA,S2:' );
define( 'AUTH_SALT',        'B)?L8Vu!8(<J?(&unME)/M:;roR# D[k}0F,ll`- 3Dt5`X?Y/;!|%={mte)74V{' );
define( 'SECURE_AUTH_SALT', '`QuGb#SAVAjt[x9Y8Eb$&5dajz~)E#+tIi~a&zy+eLHtZU_X&s?zl[`j7}:hm9nu' );
define( 'LOGGED_IN_SALT',   '#`*m6ce*.S-?^XR)>=Zs)SDXh16D(yf#|xk#bwNz^S-zfLZ[37vg&m}O=|B]ua#1' );
define( 'NONCE_SALT',       '2,gC*/0E|SKvG0cBfJzL:pgnP1.z~+XopI4y:#a2E~60e:C+X]@gky&5dcZZ).M(' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', 0 );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

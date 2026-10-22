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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'c3i9ry9ur_maxify_digitalwordpress' );

/** Database username */
define( 'DB_USER', 'c3i9ry9ur_maxify_digitalwordpress' );

/** Database password */
define( 'DB_PASSWORD', '8B6TMT6FvMguuUYAA6R(' );

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
define( 'AUTH_KEY',         'qj;E0zTTTS=XBi9;X](,Sm:^j2ed#spWk*%g.BGu#[ >WJ&3}VM@ici`?5QsHS)c' );
define( 'SECURE_AUTH_KEY',  '#g1YuT]!8#ka+}An|F)<l+~QYztp;U,l5!5(uaUd)H)aw~C&[,|6F%ZI RZrL~iK' );
define( 'LOGGED_IN_KEY',    'f<UDm4s3%ym1c5`0qj[5B;P1LwgbltPZVT@aG5[G,aq*PY $$B)r?r%M)@M>p/3n' );
define( 'NONCE_KEY',        'U?2Y]h5FPC8SW-IiCQU8w*)~j$+H[(MZXsdzKC_SvdLcuOKl&z`3yTSf=fO%j;jT' );
define( 'AUTH_SALT',        '|yceoP-?#Msrmzml7UC9z[r*DKnd&FXtLV%U#`<yW7,kSMw3{P?}rOKYxEb4H 4|' );
define( 'SECURE_AUTH_SALT', 'd$Qb)3%}YwA[0B=WULw@$n[Rl#Sa<p^ET^=J2<rmQ-uoI^K?(l+ruo0,P1+-MK?A' );
define( 'LOGGED_IN_SALT',   '~h@!~ziNxeZ%DSJXX^ve;>Em02C*`gntt!^HN]a*<V6B25UI:n#bhd8QlVs[*$bF' );
define( 'NONCE_SALT',       '-0?r_!_ZAu4ojmV)<{=4G}?oy56Xj)gQdHM&+r>|@e5D=%apmmS=+feXDDv#]gA?' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

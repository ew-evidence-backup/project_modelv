<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'econline_mv');

/** MySQL database username */
define('DB_USER', 'econline_mv');

/** MySQL database password */
define('DB_PASSWORD', 'KeHG9.C9,n0b');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '[RcrjSHN!:Z@nr*0l&HrzIxIZPcd[->eyT[W-P=-wXEt7PX2C8OkIVrvPJ0.ZSg[');
define('SECURE_AUTH_KEY',  '*3|8c^?:v5Jy F$(#|nb>Sm.Q4X8ixSv~}SuOz1|BE_D!kHpZR&.I-XY6|p `i+_');
define('LOGGED_IN_KEY',    'dJTu[=$vZ*M&|[;r - AfjwA]//w*#kgK/(S*NB5y|pRI6_zC/2mPPGs:STHsC1>');
define('NONCE_KEY',        'k<?Ww9i}FYOE,`GGD+!A/Y+de|@l:=~GU+BK{! wf(=;n,6+F<N41|r57/BJ<r9o');
define('AUTH_SALT',        'mA BC*j[q5sMwbjyty5l>4E|rC0|DF~|Ov#DptD#m[r~558{RgX7@^p9+ym@4Rlx');
define('SECURE_AUTH_SALT', '&O}FyN)*R6Z-IdeiXK6]S(V0L7r]+dyQ7SLTSZt8U+:~FrfLudU.71gB>So-M(-J');
define('LOGGED_IN_SALT',   'VE.}*ZFxgv6}LW)3;v@Uq4&0[LVOq8+cH[FO|yZXLH])`kC$kvR*#/>+f#?UX_JB');
define('NONCE_SALT',       '+pPt}vH@fQnyG#a.OH93l9QAlZG+1k0GQSIeCMA1!-@1GdJEJo9T%J1drkz2x~/#');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'mv_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');
define('EMPTY_TRASH_DAYS', 0);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

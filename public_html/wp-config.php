<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'amzaclup_wp793');

/** MySQL database username */
define('DB_USER', 'amzaclup_wp793');

/** MySQL database password */
define('DB_PASSWORD', 'X]p511SOg)');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'z9kghodz1o0hjfsa8lk6ay8cdkmlj15xifhtnnr6wtxd8lndyy1l6neusubcikil');
define('SECURE_AUTH_KEY',  'oholx7z68d045f4inx5pi71vby6dicw1e2yeckh93x06lkkrhxkp5sczkvurq2m2');
define('LOGGED_IN_KEY',    '1tvtor0dtt36d5aquct2w9waladzoecsievafmtv9ikigjgth5tlclnvmrfiqznp');
define('NONCE_KEY',        '3idkdn24bb1zaewclvmkeqyumyf6k53i1oex08fyrnl7xcu58iksj9yulwxeuuix');
define('AUTH_SALT',        '0wwybu92qnbtkinzou1rzzdwj5irsbcvevwghpn1cblzuz9d0v8wmepqsuetacq6');
define('SECURE_AUTH_SALT', 'v99ymbs4ltkjlnwxoetvakzud77dzbhmgsjmsen1qwzsnmhwtbyic8jjohsrirrp');
define('LOGGED_IN_SALT',   'ykhfgzryjdxdlrpexeao4bwg4saa9tdwwonjrxmqhkofx6d4fc5gxtadygscl5ti');
define('NONCE_SALT',       'bwedilnftxsqt54fqszx5sp4jtwrdzgaiw3kdrjhgvn5qqoiehcupavrxc1s7kcc');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpck_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

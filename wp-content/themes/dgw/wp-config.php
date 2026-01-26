<?php

if (!defined('CONFIG_LOAD_COUNT')) {
    define('CONFIG_LOAD_COUNT', 1);
    error_log('Config loaded first time');
} else {
    $count = CONFIG_LOAD_COUNT + 1;
    define('CONFIG_LOAD_COUNT', $count);
    error_log('Config loaded multiple times: ' . $count);
}

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2

//if (!defined('DISALLOW_FILE_EDIT')) {
  //  @define('DISALLOW_FILE_EDIT', true);
//}
//define( 'DISALLOW_FILE_EDIT', true ); 
// Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

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

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */

// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define('DB_NAME', 'digiwinc_data');



/** MySQL database username */

define('DB_USER', 'digiwinc_user');



/** MySQL database password */

define('DB_PASSWORD', 'Digiwin@246');



/** MySQL hostname */

define('DB_HOST', 'localhost');



/** Database Charset to use in creating database tables. */

define('DB_CHARSET', 'utf8mb4');



/** The Database Collate type. Don't change this if in doubt. */

define('DB_COLLATE', '');



/* * #@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY', 'P0LM;$?4*8`?&$YK6c|6I8H,J~$QLhNY+&v2^b:21+-U+lc_ X>;J9t~k<vp=_R?');

define('SECURE_AUTH_KEY', 'I *v7iUQ;T%0fmL5=;U`>9`a@8+MF_wIhcDLny~-7=ghs,Y$V`l(0`RUuXBig]r.');

define('LOGGED_IN_KEY', ';SYDo~/)ib3pi2e/PPDE28>gKNT`15<G8]Y;WVcGX{VOe}#Y^)~J(Q:i!V{gFTvr');

define('NONCE_KEY', 'H=zw=mi-]{a^y83}{@U^kE[)Y!)[UipauXjrW9tb^._sICv0>pA}KjSkpJ~@Cf){');

define('AUTH_SALT', '8W,3Z3VR64]zm`:5FyiHPOT`l0PQZ]I?n0$G2@xM5G;y_OIz.J7D{Dj;9U3|4!5Z');

define('SECURE_AUTH_SALT', 'b4jwOn{z p1$+xyJHC|0&5evjW[JG2`s}2U=T<lTeg@o*0{SRyfb]@IbK-<;e%O,');

define('LOGGED_IN_SALT', 'i)<;sR,K(EJX9iSA1G}^_*GXwdZCT9/Q /&C!`F=GkA=5dzKXDGq0VKK=vLpYlDs');

define('NONCE_SALT', 'o=`&,jN}Ll+s8*H4dw%s<*34Pk70CdQ0X<[;X4:fdCJ$&d[{%Hylo$rOn=!a9R@=');



/* * #@- */



/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = 'v12c9q_';



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

/*define('WP_DEBUG', FALSE);*/
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);



/* That's all, stop editing! Happy publishing. */



/** Absolute path to the WordPress directory. */

if (!defined('ABSPATH')) {

    define('ABSPATH', __DIR__ . '/');

}



/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';


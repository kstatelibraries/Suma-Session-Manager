<?php
/*
 |--------------------------------------------------------------
 | Application Configuration
 |--------------------------------------------------------------
 | This file is intended to be committed to the repository. 
 | Sensitive values are read from environment variables (set via Docker, .env, or 
 | server configuration). 
 | If an environment variable is missing, a default value is used instead.
 |
 | Update environment variables rather than editing this file
 | whenever possible.
 */

// Helper to fetch env var with optional fallback
function env_or_default(string $key, $default = '') {
    $val = getenv($key);
    return $val === false ? $default : $val;
}

// -----------------------------------------------------------------------------
// Core constants (populate from ENV with fallback)
// -----------------------------------------------------------------------------

define('DEBUG', filter_var(env_or_default('DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));

define('SUMASERVER_URL', env_or_default('SUMASERVER_URL', ''));

define('SUMA_REPORTS_URL', env_or_default('SUMA_REPORTS_URL', ''));

define('MYSQL_HOST', env_or_default('MYSQL_HOST', 'localhost'));

define('MYSQL_DATABASE', env_or_default('MYSQL_DATABASE', 'suma'));

define('MYSQL_USER', env_or_default('MYSQL_USER', 'suma_user'));

define('MYSQL_PASSWORD', env_or_default('MYSQL_PASSWORD', 'suma_pass'));

// -----------------------------------------------------------------------------
// UI settings (leave these as-is or override via ENV if desired)
// -----------------------------------------------------------------------------

// Available JQuery UI themes: cupertino, flick, hot-sneaks, humanity, overcast,
// pepper-grinder, redmond, smoothness, south-street, start, sunny, ui-lightness
$ui_theme = env_or_default('UI_THEME', 'pepper-grinder');

$default_init      = env_or_default('DEFAULT_INIT', '');
$entries_per_page  = intval(env_or_default('ENTRIES_PER_PAGE', 100));

// If true, the datepicker will not allow future dates
$prevent_datepicker_future = filter_var(env_or_default('PREVENT_DATEPICKER_FUTURE', 'true'), FILTER_VALIDATE_BOOLEAN);

/*
 | If an initiative usually only has one count per hour, include it in the array
 | below (by ID) to allow searching for hours with multiple entries.
 | Example: $one_per_hour_inits = array(1,4);
 */
$one_per_hour_inits = array();

/*
  You can use Suma Session Manager to adjust the time of previously-entered
  sessions. The following array controls what options you are given for 
  adjusting the time. You may add, delete or comment-out lines as you wish.
  
  The array values (e.g. "subtime 04:00:00" are given as arguments sent to
  MySQL and are formatted to give a MySQL command. The amount of time to be
  changed is given in HH:MM:SS format. 
  
  Time-adjustment shortcuts for the Suma Session Manager UI.
  Keys are the display labels and values are the MySQL time commands.
 */
$adjust_time_options = array(
    '-4 hrs'  => 'subtime 04:00:00',
    '-3 hrs'  => 'subtime 03:00:00',
    '-2 hrs'  => 'subtime 02:00:00',
    '-60 min' => 'subtime 01:00:00',
    '-30 min' => 'subtime 00:30:00',
    '-15 min' => 'subtime 00:15:00',
    '-10 min' => 'subtime 00:10:00',
    '-5 min'  => 'subtime 00:05:00',
    '+5 min'  => 'addtime 00:05:00',
    '+10 min' => 'addtime 00:10:00',
    '+15 min' => 'addtime 00:15:00',
    '+30 min' => 'addtime 00:30:00',
    '+60 min' => 'addtime 01:00:00',
);

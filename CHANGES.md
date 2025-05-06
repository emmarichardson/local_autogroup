## v2.8.2
  * Solves issue #44 - postgresql compatibility issue during upgrade process, previous fix was still buggy.

## v2.8.1
  * Solves issue #44 - postgresql compatibility issue during upgrade process

## v2.8
  * Solves Issue #38 - thanks to Giorgio Riva for the fix.

## v2.7
  * Solves Issue #37 - thanks to nrosenquist for fix

## v2.5
  * Solves https://github.com/emmarichardson/local_autogroup/issues/16 default group by
  * This change requires checking your global config field uses correct key now
  
## v2.4
  * Fixed unnecessary processing on profile update. Thanks to Luuk Verhoeven for this addition.

## v2.3
  * Added support for custom profile fields.  Thanks to Arnaud Trouvé for this addition.

## v2.2
  * Performance enhancement: Event listeners now check to see whether triggered by AutoGroup

## v2.1
  * Switched to individual toggles for event listeners
  * Minor change to settings structure for improved usability
  * Fixed compatibility with Moodle 3.1 and onwards

## v2.0
  * Added support for defining multiple grouping sets on one course
  * Sort modules are now fully modular
  * Added new sort module for Totara positional assignments

## v1.1
  * Fixed a bug which would have resulted in roles being removed from all groupsets across a site
  * Changed default permissions for plugin files 

## v1.01
  * Added event handler for course_restored and related setting

## v1.0
  * Stable release. Tested for compatibility with Moodle 2.7 and 2.8

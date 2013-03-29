<?php
/**
 * SimpleAntiSpamReg MediaWiki extension
 * Adds a simple spambot check to registration form. Does not affect real users.
 * (c) Vitaliy Filippov, 2013
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOM
		This is an extension to the MediaWiki software and cannot be used standalone.\n
		To install this on the wiki, add the following line to LocalSettings.php:\n
			<tt>require_once( "\$IP/extensions/SimpleAntiSpamReg/SimpleAntiSpamReg.php" );</tt>\n
		To verify the installation, browse to the Special:Version page on your wiki.\n
EOM;
	die( 1 );
}

$wgExtensionCredits['antispam'][] = array(
	'path' => __FILE__,
	'name' => 'SimpleAntiSpamReg',
	'description' => 'Adds a simple spambot check to registration form',
	'author' => 'Vitaliy Filippov',
	'url' => 'http://wiki.4intra.net/SimpleAntiSpamReg',
	'version' => '1.0',
);

$wgHooks['UserCreateForm'][] = 'efSimpleAntiSpamRegField';
$wgHooks['AbortNewAccount'][] = 'efSimpleAntiSpamRegCheck';

/**
 * Add a field
 */
function efSimpleAntiSpamRegField( &$template ) {
	$template->set(
		'header', $template->data['header'] .
		'<div style="display: none;"><label for="wpASR">Password</label>'.
		'<input type="password" name="wpASR" id="wpASR" value="" /></div>'
	);
	return true;
}

/**
 * Check for the field and send 403 Forbidden if it isn't empty
 */
function efSimpleAntiSpamRegCheck( $u, &$abortError ) {
	global $wgRequest, $wgUser;
	$spam = $wgRequest->getVal( 'wpASR' );
	if ( $spam !== '' ) {
		wfDebugLog( 'SimpleAntiSpamReg', wfGetIP() . ': registration denied' );
		wfHttpError( 403, 'Forbidden', 'Registration denied.' );
		exit;
	}
	return true;
}

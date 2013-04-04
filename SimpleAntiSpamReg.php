<?php
/**
 * SimpleAntiSpamReg MediaWiki extension
 * Adds a stylesheet link to registration form and requires that
 * the client requests it before allowing registration.
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
$wgAjaxExportList[] = 'efAsrCss';

/**
 * Add a fake stylesheet link
 */
function efSimpleAntiSpamRegField( $template ) {
	global $wgOut, $wgServer, $wgScriptPath;
	if ( session_id() === '' ) {
		wfSetupSession();
	}
	$wgOut->addScript(
		'<link rel="stylesheet" type="text/css" href="'.
		$wgServer.$wgScriptPath.'?action=ajax&rs=efAsrCss"></script>'
	);
	return true;
}

/**
 * Remember link visit in the session
 */
function efAsrCss() {
	if ( session_id() === '' ) {
		wfSetupSession();
	}
	$_SESSION['asr_visit'] = 1;
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Content-Type: text/css');
	print "body { }";
	exit;
}

/**
 * Check for visit and send 403 Forbidden if it isn't empty
 */
function efSimpleAntiSpamRegCheck( $u, &$abortError ) {
	if ( session_id() === '' ) {
		wfSetupSession();
	}
	if ( empty( $_SESSION['asr_visit'] ) ) {
		wfDebugLog( 'SimpleAntiSpamReg', wfGetIP() . ': registration denied' );
		wfHttpError( 403, 'Forbidden', 'Registration denied. You must use a modern browser for registration.' );
		exit;
	}
	$_SESSION['asr_visit'] = 0;
	return true;
}

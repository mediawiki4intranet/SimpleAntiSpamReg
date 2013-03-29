<?php
/**
 * SimpleAntiSpamReg MediaWiki extension
 * Adds a simple spambot check to registration form. Does not affect real users.
 * (c) Vitaliy Filippov, 2013
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

$wgHooks['UserCreateForm'] = 'efSimpleAntiSpamRegField';
$wgHooks['AbortNewAccount'] = 'efSimpleAntiSpamRegCheck';

$wgHooks['EditPage::showEditForm:fields'][] = 'efSimpleAntiSpamField';
$wgHooks['EditPage::attemptSave'][] = 'efSimpleAntiSpamCheck';

/**
 * Add a field
 */
function efSimpleAntiSpamRegField( &$template ) {
	$template->set(
		'header', $template->get( 'header' ) .
		'<div style="display: none;"><label for="wpASR">Password</label>'.
		'<input type=\"password\" name=\"wpASR\" id=\"wpASR\" value=\"\" /></div>'
	);
	return true;
}

/**
 * Check for the field and send 403 Forbidden if it isn't empty
 */
function efSimpleAntiSpamRegCheck( $u, &$abortError ) {
	global $wgRequest, $wgUser;
	$spam = $wgRequest->getText( 'wpASR' );
	if ( $spam !== '' ) {
		wfDebugLog( 'SimpleAntiSpamReg', wfGetIP() . ': registration denied' );
		wfHttpError( 403, 'Forbidden', 'Registration denied.' );
		return false;
	}
	return true;
}

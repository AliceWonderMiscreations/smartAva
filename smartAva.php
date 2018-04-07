<?php
/**
 * Protects users from tracking by obfuscating the e-mail hash used for gravatar.com avatars.
 * Supports white-lists of domains and e-mail addresses you do not want obfuscated.
 *
 * @package smartAva
 * @author  Alice Wonder <paypal@domblogger.net>
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 * @link    https://github.com/AliceWonderMiscreations/smartAva
 *
 * Plugin Name: smartAva
 * Description: Protects users from tracking by obfuscating the e-mail hash used for gravatar.com avatars.
 * Plugin URI: http://wordpress.org/plugins/smartava/
 * Version: 0.5
 */
 
 /* Copyright 2013,2018  Alice Wonder  (email : paypal@domblogger.net)

    Licensed under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    ---

    The function get_avatar() is lifted from the pluggable.php file in the
    3.7.1 version of WordPress and is (c) it's respective authors. One
    minor modification was made to that function.
*/

/**
 * Converts domain name to punycode if idn_to_ascii is available.
 *
 * @param string $domain The domain to put into puny code.
 *
 * @return string
 */
function smartAvaIDN2Puny($domain)
{
    if (function_exists('idn_to_ascii')) {
        $domain=idn_to_ascii($domain);
    }
    return($domain);
}//end smartAvaIDN2Puny()

/**
 * Converts domain name from punycode if idn_to_utf8 is available.
 *
 * @param string $domain The domain to put into utf8.
 *
 * @return string
 */
function smartAvaPuny2IDN($domain)
{
    if (function_exists('idn_to_utf8')) {
        $domain=idn_to_utf8($domain);
    }
    return($domain);
}//end smartAvaPuny2IDN()

/**
 * Converts an e-mail address to punycode.
 *
 * @param string $email The e-mail address to convert.
 *
 * @return string The converted e-mail address.
 */
function smartAvaEmail2Puny($email)
{
    //warning - if argument does not have exactly 1 @ it will return argument.
    if (substr_count($email, '@') == 1) {
        $tmp=explode('@', $email);
        $email=$tmp[0] . '@' . smartAvaIDN2Puny($tmp[1]);
    }
    return($email);
}//end smartAvaEmail2Puny()

 
/**
 * Creates a blog footer message.
 *
 * @return void
 */
function smartAvaFooter()
{
    // @codingStandardsIgnoreLine
    echo('<div style="text-align: center;">' . __('Anonymity protected with') . ' <a href="https://wordpress.org/plugins/smartava/" target="_blank">smartAva</a></div>');
    return;
}//end smartAvaFooter()

/**
 * Adds domains to white-list of domains that will not be obfuscated.
 *
 * @param string $input The domain to add to white list.
 *
 * @return array $error An array of error messages.
 */
function smartAvaAddDomains($input)
{
    $error=array();
    if (! $domains=get_option('smartAvaDomains')) {
        $domains=array();
    }
    $n=sizeof($domains);
    $newDomains=explode(';', $input);
    $j=sizeof($newDomains);
    for ($i=0; $i<$j; $i++) {
        $domain=trim(strtolower($newDomains[$i]));
        $domain=smartAvaIDN2Puny($domain);
        $test='user@' . $domain;
        if (filter_var($test, FILTER_VALIDATE_EMAIL)) {
            $domains[]=$domain;
        } else {
            $error[]='The domain <code>' . $domain . '</code> is not a valid domain name.';
        }
    }
    $domains=array_values(array_unique($domains));
    $m=sizeof($domains);
    if ($m > $n) {
        update_option('smartAvaDomains', $domains);
    }
    return($error);
}//end smartAvaAddDomains()

/**
 * Adds e-mail address to white-list that will not be obfuscated.
 *
 * @param string $input The e-mail address to add to white list.
 *
 * @return array $error An array of error messages.
 */
function smartAvaAddAddresses($input)
{
    $error=array();
    if (! $addys=get_option('smartAvaAddys')) {
        $addys=array();
    }
    $n=sizeof($addys);
    $newAddys=explode(';', $input);
    $j=sizeof($newAddys);
    for ($i=0; $i<$j; $i++) {
        $address=trim(strtolower($newAddys[$i]));
        $paddress=smartAvaEmail2Puny($address);
        if (filter_var($paddress, FILTER_VALIDATE_EMAIL)) {
            $addys[]=$paddress;
        } else {
            $error[]='The e-mail address <code>' . $address . '</code> is not a valid e-mail address.';
        }
    }
    $addys=array_values(array_unique($addys));
    $m=sizeof($addys);
    if ($m > $n) {
        update_option('smartAvaAddys', $addys);
    }
    return($error);
}//end smartAvaAddAddresses()

/**
 * Removes a domain from the white-list of what won't be obfuscated.
 *
 * @param string $input The domain to be removed from the white list.
 *
 * @return void
 */
function smartAvaRemoveDomains($input)
{
    if (! $domains=get_option('smartAvaDomains')) {
        $domains=array();
    }
    $n=sizeof($domains);
    $remove=array();
    $remList=explode(';', $input);
    $j=sizeof($remList);
    for ($i=0; $i<$j; $i++) {
        $domain=trim(strtolower($remList[$i]));
        if (strlen($domain) > 0) {
            $remove[]=$domain;
        }
    }
    $domains=array_values(array_diff($domains, $remove));
    $m=sizeof($domains);
    if ($n > $m) {
        update_option('smartAvaDomains', $domains);
    }
}//end smartAvaRemoveDomains()

/**
 * Removes an e-mail from the white-list of what won't be obfuscated.
 *
 * @param string $input The e-mail address to be removed from the white list.
 *
 * @return void
 */
function smartAvaRemoveAddresses($input)
{
    if (! $addys=get_option('smartAvaAddys')) {
        $addys=array();
    }
    $n=sizeof($addys);
    $remove=array();
    $remList=explode(';', $input);
    $j=sizeof($remList);
    for ($i=0; $i<$j; $i++) {
        $addy=trim(strtolower($remList[$i]));
        if (strlen($addy) > 0) {
            $remove[]=$addy;
        }
    }
    $addys=array_values(array_diff($addys, $remove));
    $m=sizeof($addys);
    if ($n > $m) {
        update_option('smartAvaAddys', $addys);
    }
}//end smartAvaRemoveAddresses()

/**
 * Generates a string from 256 bits of random data, suitable for a salt or a nonce
 * if php 7 is used or if php 5.6 is used and openssl_random_pseudo_bytes is available.
 *
 * For older versions of PHP or php 5.6 without openssl_random_pseudo_bytes then what is
 * generated is not cryptographically secure but should be safe for the hash obfuscation
 * salt but is not as safe as it should be for the admin form CSRF nonce.
 *
 * @return string
 */
function smartAvaSaltShaker()
{
    if (function_exists('random_bytes')) {
        $raw = random_bytes(32);
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        // WARNING - this is not always crypto usable on deprecated versions of PHP < 5.6
        $raw = openssl_random_pseudo_bytes(32);
    }
    if (! isset($raw)) {
        // not suitable for cryptography but probably good enough for our purposes, and only needed with
        // PHP < 7 that also do not have openssl_random_pseudo_bytes()
        $alphabet='abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*(-_=+{}[]:;,./<>?|';
        $alphLength=strlen($alphabet);
        $alphabet=str_shuffle($alphabet);
        $tmp='';
        $j=384;
        $max=$alphLength - 1;
        for ($i=0; $i<$j; $i++) {
            $pos=rand(0, $max);
            $tmp=$tmp . $alphabet[$pos];
        }
        $raw = hash('sha256', $tmp, true);
    }
    $salt = base64_encode($raw);
    return($salt);
}//end smartAvaSaltShaker()

/**
 * Generates the md5 mimic hash to use with the gravatar system
 *
 * @param string $email The e-mail address to generate a hash for.
 *
 * @return The md5sum mimic string
 */
function smartAvaHash($email)
{
    if (! $salt=get_option('smartAvaSalt')) {
        $salt=array();
        $salt[]=smartAvaSaltShaker();
        $salt[]=smartAvaSaltShaker();
        update_option('smartAvaSalt', $salt);
    }
    if (! $domains=get_option('smartAvaDomains')) {
        $domains=array();
    }
    if (! $addys=get_option('smartAvaAddys')) {
        $addys=array();
    }
    $addys[]='unknown@gravatar.com'; //no need to obfuscate that address
    
    $email=trim(strtolower($email));
    $pemail=smartAvaEmail2Puny($email);
    //validate email
    if (! filter_var($pemail, FILTER_VALIDATE_EMAIL)) { //hopefully wordpress already has validated this but...
        $pemail='unknown@gravatar.com';
    }
    $foo=explode('@', $pemail);
    $domino=$foo[1]; //this is domain part of @domain
    $qq=0;
    //check for white-listed domain
    $j=sizeof($domains);
    for ($i=0; $i<$j; $i++) {
        $test=trim(strtolower($domains[$i]));
        $dummy='user@' . $test;
        if (filter_var($dummy, FILTER_VALIDATE_EMAIL)) {
            //check for exact match first
            if (strcasecmp($domino, $test) == 0) {
                $qq++;
            } else {
                $domino='.' . $domino; //for testing if $test is subdomain
                $qq = $qq + substr_count($domino, $test); //any matches and $qq is no longer 0
            }
        }
    }
    
    //check for white-listed address
    if ($qq === 0) {
        $j=sizeof($addys);
        for ($i=0; $i<$j; $i++) {
            $test=trim(strtolower($addys[$i]));
            if (strcasecmp($test, $pemail) == 0) {
                $qq++; //any match and $qq is no longer 0
            }
        }
    }
    
    if ($qq === 0) {
        // obfuscate
        $obf=hash('sha256', $salt[0] . $email);
        $obf=hash('sha256', $salt[1] . $obf);
        return substr($obf, 4, 32);
    } else {
        // there was a white-list match, do not obfuscate
        return(md5($email));
    }
}//end smartAvaHash()

    
// admin interface functions

/**
 * The admin interface domain menu.
 *
 * @return void
 */
function smartAvaAdmDomainMenu()
{
    echo("<div>\n");
    echo('<h2 style="font-variant: small-caps;">Domain White-List Management</h2>' . "\n");
    // @codingStandardsIgnoreLine
    echo("<p>E-Mail addresses at domains in your white-list will not have their MD5 hash of their e-mail address obfuscated. If these users at white-listed domains have gr*vatar.com accounts, their custom avatars will be used with their comments. Whether or not they have gr*vatar.com accounts, the MD5 hash of their e-mail address will be public information. Please do not white-list domains you or the company you work for do not control.</p>\n");
    echo("<h3>Add Domain to White-List</h3>\n");
    echo("<p>If entering more than one domain, separate domains with a semi-colon ; character.</p>\n");
    
    echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n");
    echo('<th scope="row"><label for="addDomains">Domains to White-List</label></th>' . "\n");
    // @codingStandardsIgnoreLine
    echo('<td><input type="text" id="addDomains" name="addDomains" size="64" title="Enter domains to white-list" autocomplete="off" /></td>' . "\n");
    echo('</tr></table>' . "\n");
    
    if (! $domains=get_option('smartAvaDomains')) {
        $domains=array();
    }
    $j=sizeof($domains);
    if ($j != 0) {
        echo("<h3>Remove Existing Domains</h3>\n");
        // @codingStandardsIgnoreLine
        echo("<p>If you wish to remove an existing domain from the white-list, check the box next to the domain name.</p>\n");
        
        echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n");
        echo('<th scope="row">Domains to Remove</th>' . "\n");
        echo('<td>' . "\n" . '<fieldset><legend class="screen-reader-text"><span>Domains to Remove</span></legend>');
        
        sort($domains);
        $search=array();
        $replace=array();
        $search[]='/\./';
        $replace[]='_DOT_';
        for ($i=0; $i<$j; $i++) {
            $name='del_' . preg_replace($search, $replace, $domains[$i]);
            $label=smartAvaPuny2IDN($domains[$i]);
            // @codingStandardsIgnoreLine
            echo('   <input type="checkbox" name="' . $name . '" value="T" id="' . $name . '" /><label for="' . $name . '" title="' . $domains[$i] . '"> ' . $label . "</label><br />\n");
        }
        echo("</fieldset>\n</td></tr></table>");
    }
    echo("</div>\n");
    return;
}//end smartAvaAdmDomainMenu()

/**
 * The admin interface address menu.
 *
 * @return void
 */
function smartAvaAdmAddressMenu()
{
    echo("<div>\n");
    echo('<h2 style="font-variant: small-caps;">E-Mail White-List Management</h2>' . "\n");
    // @codingStandardsIgnoreLine
    echo("<p>E-Mail addresses in your white-list will not have their MD5 hash of their e-mail address obfuscated. If users with white-listed e-mail addresses have gr*vatar.com accounts, their custom avatars will be used with their comments. Whether or not they have gr*vatar.com accounts, the MD5 hash of their e-mail address will be public information. Please do not white-list e-mail addresses without consent of the user.</p>");
    echo("<h3>Add E-Mail Address to White-List</h3>\n");
    echo("<p>If entering more than one e-mail address, separate them with a semi-colon ; character.</p>\n");
    
    echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n");
    echo('<th scope="row"><label for="addEmails">New Addresses to White-List</label></th>' . "\n");
    // @codingStandardsIgnoreLine
    echo('<td><input type="text" id="addEmails" name="addEmails" size="64" title="Enter e-mail addresses to white-list" autocomplete="off" /></td>' . "\n");
    echo('</tr></table>' . "\n");
    
    if (! $addys=get_option('smartAvaAddys')) {
        $addys=array();
    }
    $j=sizeof($addys);
    if ($j != 0) {
        echo("<h3>Remove Existing E-Mail Addresses</h3>\n");
        // @codingStandardsIgnoreLine
        echo("<p>If you wish to remove an e-mail address from the white-list, check the box next to the e-mail address.</p>\n");
        
        echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n");
        echo('<th scope="row">Addresses to Remove</th>' . "\n");
        echo('<td>' . "\n" . '<fieldset><legend class="screen-reader-text"><span>Addresses to Remove</span></legend>');
        
        sort($addys);
        $search=array();
        $replace=array();
        $search[]='/@/';
        $replace[]='_AT_';
        $search[]='/\./';
        $replace[]='_DOT_';
        for ($i=0; $i<$j; $i++) {
            $name='del_' . preg_replace($search, $replace, $addys[$i]);
            $tmp=explode('@', $addys[$i]);
            $label=$tmp[0] . '@' . smartAvaPuny2IDN($tmp[1]);
            // @codingStandardsIgnoreLine
            echo('   <input type="checkbox" name="' . $name . '" value="T" id="' . $name . '" /><label for="' . $name . '" title="' . $addys[$i] . '"> ' . $label . "</label><br />\n");
        }
        echo("</fieldset>\n</td></tr></table>");
    }
    echo("</div>\n");
    return;
}//end smartAvaAdmAddressMenu()

/**
 * The admin interface salt setting.
 *
 * @return void
 */
function smartAvaAdmSalty()
{
    echo("<div>\n");
    echo('<h2 style="font-variant: small-caps;">Obfuscation Salts</h2>' . "\n");
    // @codingStandardsIgnoreLine
    echo("<p>A salt is a randomized string of gibberish that is often used when obfuscating a hash to thwart <a href=\"https://en.wikipedia.org/wiki/Rainbow_table\" target=\"_blank\">Rainbow Table</a> attacks. If the attacker does not know the value of the salt, the attacker can not generate a table of hashes that will correspond to the hash you use. The smartAva plugin uses two salts in the obfuscation of e-mail address hashes.</p>\n");
    // @codingStandardsIgnoreLine
    echo("<p>It is suggested you allow smartAva to generate the salts for you. If you run multiple blogs and you want your users to have the same obfuscated hash between blogs, then you can manually create the salts. If you do so, make sure they are at least 18 characters long and are made up using an arrangement of many different characters.</p>\n");
    // @codingStandardsIgnoreLine
    echo('<p>The salts currently being used by your install of smartAva:</p>' . "\n" . '<div style="background-color: #cccccc; padding: 1em;">');
    echo('<ol style="font-family: monospace;">' . "\n");
    if (! $salt=get_option('smartAvaSalt')) {
        $salt=array();
        $salt[]=smartAvaSaltShaker();
        $salt[]=smartAvaSaltShaker();
        update_option('smartAvaSalt', $salt);
    }
    $search=array();
    $replace=array();
    $search[]='/&/';
    $replace[]='&amp;';
    $search[]='/</';
    $replace[]='&lt;';
    $search[]='/>/';
    $replace[]='&gt;';
    $aa=preg_replace($search, $replace, $salt[0]);
    $bb=preg_replace($search, $replace, $salt[1]);
    echo('<li>' . $aa . '</li>' . "\n" . '<li>' . $bb . '</li>' . "\n</ol>\n</div>");
    
    echo("<h3>Regenerate Salts</h3>\n");
    echo("<p>If for some reason you wish to regenerate the salts, check the box below:</p>\n");
    
    echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n" . '<th scope="row">Random Salts</th>' . "\n");
    
    // @codingStandardsIgnoreLine
    echo('<td><input type="checkbox" name="smartAvaRegenSalts" id="smartAvaRegenSalts" value="T" /><label for="smartAvaRegenSalts"> Regenerate Salts</label></td>' . "\n");
    echo("</tr>\n</table>");
    
    echo("<h3>Custom Salts</h3>\n");
    // @codingStandardsIgnoreLine
    echo("<p>If you wish to manually create your own salts, place your salt strings in the two input fields below. They must be at least 18 characters in length.</p>\n");
    
    echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n" . '<th scope="row">Custom Salts</th>' . "\n");
    echo('<td><fieldset><legend class="screen-reader-text"><span>Custom Salts</span></legend>');
    // @codingStandardsIgnoreLine
    echo('<input type="text" id="smartAvaSaltOne" name="smartAvaSaltOne" size="64" title="Enter a salt string at least 18 characters long." autocomplete="off" /><br />' . "\n");
    echo('<label for="smartAvaSaltOne">First Custom Salt</label><br />&#160;<br />' . "\n");
    // @codingStandardsIgnoreLine
    echo('<input type="text" id="smartAvaSaltTwo" name="smartAvaSaltTwo" size="64" title="Enter another salt string at least 18 characters long." autocomplete="off" /><br />' . "\n");
    echo('<label for="smartAvaSaltTwo">Second Custom Salt</label>' . "\n");
    echo("</fieldset>\n</td>\n</tr>\n</table>");
    
    echo("</div>\n");
}//end smartAvaAdmSalty()

/**
 * The admin interface add footer control.
 *
 * @return void
 */
function smartAvaAdmNotify()
{
    echo("<div>\n");
    echo('<h2 style="font-variant: small-caps;">User Notification</h2>' . "\n");
    // @codingStandardsIgnoreLine
    echo("<p>You can notify users of your blog that you are using smartAva by checking the box below. I would greatly appreciate this, and it will also let your users know that you care about their privacy and that their e-mail hash will be obfuscated when they post a comment so that they can not be tracked.</p>\n");
    echo("<p>If you check the box, the following notice will appear in the footer of your pages:</p>");
    smartAvaFooter();
    echo("\n");
    // @codingStandardsIgnoreLine
    echo('<table class="form-table">' . "\n" . '<tr valign="top">' . "\n" . '<th scope="row">User Notification</th>' . "\n");
    if ($footerPermission=get_option('smartAvaFooter')) {
        $checked=' checked="checked"';
    } else {
        $checked='';
    }
    // @codingStandardsIgnoreLine
    echo('<td><input type="checkbox" name="avaSmartNotify" value="T" id="avaSmartNotify"' . $checked . ' /><label for="avaSmartNotify"> Allow Notification to Users of smartAva usage</label></td>' . "\n");
    echo("</tr>\n</table>");
    
    echo("</div>\n");
}//end smartAvaAdmNotify()



/**
 * Retrieves default data about the avatar.
 *
 * @since 4.2.0
 *
 * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
 *                            user email, WP_User object, WP_Post object, or WP_Comment object.
 * @param array $args {
 *     Optional. Arguments to return instead of the default arguments.
 *
 *     @type   int    $size           Height and width of the avatar image file in pixels. Default 96.
 *     @type   int    $height         Display height of the avatar in pixels. Defaults to $size.
 *     @type   int    $width          Display width of the avatar in pixels. Defaults to $size.
 *     @type   string $default        URL for the default image or a default type. Accepts '404' (return
 *                                  a 404 instead of a default image), 'retro' (8bit), 'monsterid' (monster),
 *                                  'wavatar' (cartoon face), 'indenticon' (the "quilt"), 'mystery', 'mm',
 *                                  or 'mysteryman' (The Oyster Man), 'blank' (transparent GIF), or
 *                                  'gravatar_default' (the Gravatar logo). Default is the value of the
 *                                  'avatar_default' option, with a fallback of 'mystery'.
 *     @type   bool   $force_default  Whether to always show the default image, never the Gravatar. Default false.
 *     @type   string $rating         What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are
 *                                  judged in that order. Default is the value of the 'avatar_rating' option.
 *     @type   string $scheme         URL scheme to use. See set_url_scheme() for accepted values.
 *                                  Default null.
 *     @type   array  $processed_args When the function returns, the value will be the processed/sanitized $args
 *                                  plus a "found_avatar" guess. Pass as a reference. Default null.
 *     @type   string $extra_attr     HTML attributes to insert in the IMG element. Is not sanitized. Default empty.
 * }
 * @return array $processed_args {
 *     Along with the arguments passed in `$args`, this will contain a couple of extra arguments.
 *
 *     @type bool   $found_avatar True if we were able to find an avatar for this user,
 *                                false or not set if we couldn't.
 *     @type string $url          The URL of the avatar we found.
 * }
 */
function smart_ava_get_avatar_data($id_or_email, $args = null)
{
    $args = wp_parse_args(
        $args,
        array(
            'size'           => 96,
            'height'         => null,
            'width'          => null,
            'default'        => get_option('avatar_default', 'mystery'),
            'force_default'  => false,
            'rating'         => get_option('avatar_rating'),
            'scheme'         => null,
            'processed_args' => null, // if used, should be a reference
            'extra_attr'     => '',
        )
    );
    if (is_numeric($args['size'])) {
        $args['size'] = absint($args['size']);
        if (! $args['size']) {
            $args['size'] = 96;
        }
    } else {
        $args['size'] = 96;
    }
    if (is_numeric($args['height'])) {
        $args['height'] = absint($args['height']);
        if (! $args['height']) {
            $args['height'] = $args['size'];
        }
    } else {
        $args['height'] = $args['size'];
    }
    if (is_numeric($args['width'])) {
        $args['width'] = absint($args['width']);
        if (! $args['width']) {
            $args['width'] = $args['size'];
        }
    } else {
        $args['width'] = $args['size'];
    }
    if (empty($args['default'])) {
        $args['default'] = get_option('avatar_default', 'mystery');
    }
    switch ($args['default']) {
        case 'mm':
        case 'mystery':
        case 'mysteryman':
            $args['default'] = 'mm';
            break;
        case 'gravatar_default':
            $args['default'] = false;
            break;
    }
    $args['force_default'] = (bool) $args['force_default'];
    $args['rating'] = strtolower($args['rating']);
    $args['found_avatar'] = false;
    /**
     * Filters whether to retrieve the avatar URL early.
     *
     * Passing a non-null value in the 'url' member of the return array will
     * effectively short circuit get_avatar_data(), passing the value through
     * the {@see 'get_avatar_data'} filter and returning early.
     *
     * @since 4.2.0
     *
     * @param array  $args        Arguments passed to get_avatar_data(), after processing.
     * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
     *                            user email, WP_User object, WP_Post object, or WP_Comment object.
     */
    $args = apply_filters('pre_get_avatar_data', $args, $id_or_email);
    if (isset($args['url']) && ! is_null($args['url'])) {
        /** This filter is documented in wp-includes/link-template.php */
        return apply_filters('smart_ava_get_avatar_data', $args, $id_or_email);
    }
    $email_hash = '';
    $user       = $email = false;
    if (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
        $id_or_email = get_comment($id_or_email);
    }
    // Process the user identifier.
    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', absint($id_or_email));
    } elseif (is_string($id_or_email)) {
        if (strpos($id_or_email, '@md5.gravatar.com')) {
            // md5 hash
            list( $email_hash ) = explode('@', $id_or_email);
        } else {
            // email address
            $email = $id_or_email;
        }
    } elseif ($id_or_email instanceof WP_User) {
        // User Object
        $user = $id_or_email;
    } elseif ($id_or_email instanceof WP_Post) {
        // Post Object
        $user = get_user_by('id', (int) $id_or_email->post_author);
    } elseif ($id_or_email instanceof WP_Comment) {
        /**
         * Filters the list of allowed comment types for retrieving avatars.
         *
         * @since 3.0.0
         *
         * @param array $types An array of content types. Default only contains 'comment'.
         */
        $allowed_comment_types = apply_filters('get_avatar_comment_types', array( 'comment' ));
        // @codingStandardsIgnoreLine
        if (! empty($id_or_email->comment_type) && ! in_array($id_or_email->comment_type, (array) $allowed_comment_types)) {
            $args['url'] = false;
            /** This filter is documented in wp-includes/link-template.php */
            return apply_filters('smart_ava_get_avatar_data', $args, $id_or_email);
        }
        if (! empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);
        }
        if (( ! $user || is_wp_error($user) ) && ! empty($id_or_email->comment_author_email)) {
            $email = $id_or_email->comment_author_email;
        }
    }
    if (! $email_hash) {
        if ($user) {
            $email = $user->user_email;
        }
        if ($email) {
          // THIS IS WHAT I CHANGED
            //$email_hash = md5( strtolower( trim( $email ) ) );
            $email_hash = smartAvaHash($email);
        }
    }
    if ($email_hash) {
        $args['found_avatar'] = true;
        $gravatar_server      = hexdec($email_hash[0]) % 3;
    } else {
        $gravatar_server = rand(0, 2);
    }
    $url_args = array(
        's' => $args['size'],
        'd' => $args['default'],
        'f' => $args['force_default'] ? 'y' : false,
        'r' => $args['rating'],
    );
    if (is_ssl()) {
        $url = 'https://secure.gravatar.com/avatar/' . $email_hash;
    } else {
        $url = sprintf('http://%d.gravatar.com/avatar/%s', $gravatar_server, $email_hash);
    }
    $url = add_query_arg(
        rawurlencode_deep(array_filter($url_args)),
        set_url_scheme($url, $args['scheme'])
    );
    /**
     * Filters the avatar URL.
     *
     * @since 4.2.0
     *
     * @param string $url         The URL of the avatar.
     * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
     *                            user email, WP_User object, WP_Post object, or WP_Comment object.
     * @param array  $args        Arguments passed to get_avatar_data(), after processing.
     */
    $args['url'] = apply_filters('smart_ava_get_avatar_url', $url, $id_or_email, $args);
    /**
     * Filters the avatar data.
     *
     * @since 4.2.0
     *
     * @param array  $args        Arguments passed to get_avatar_data(), after processing.
     * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
     *                            user email, WP_User object, WP_Post object, or WP_Comment object.
     */
    return apply_filters('smart_ava_get_avatar_data', $args, $id_or_email);
}//end smart_ava_get_avatar_data()

/**
 * Retrieves the avatar URL.
 *
 * @since 4.2.0
 *
 * @param mixed $id_or_email The Gravatar to retrieve a URL for. Accepts a user_id, gravatar md5 hash,
 *                           user email, WP_User object, WP_Post object, or WP_Comment object.
 * @param array $args {
 *     Optional. Arguments to return instead of the default arguments.
 *
 *     @type   int    $size           Height and width of the avatar in pixels. Default 96.
 *     @type   string $default        URL for the default image or a default type. Accepts '404' (return
 *                                  a 404 instead of a default image), 'retro' (8bit), 'monsterid' (monster),
 *                                  'wavatar' (cartoon face), 'indenticon' (the "quilt"), 'mystery', 'mm',
 *                                  or 'mysteryman' (The Oyster Man), 'blank' (transparent GIF), or
 *                                  'gravatar_default' (the Gravatar logo). Default is the value of the
 *                                  'avatar_default' option, with a fallback of 'mystery'.
 *     @type   bool   $force_default  Whether to always show the default image, never the Gravatar. Default false.
 *     @type   string $rating         What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are
 *                                  judged in that order. Default is the value of the 'avatar_rating' option.
 *     @type   string $scheme         URL scheme to use. See set_url_scheme() for accepted values.
 *                                  Default null.
 *     @type   array  $processed_args When the function returns, the value will be the processed/sanitized $args
 *                                  plus a "found_avatar" guess. Pass as a reference. Default null.
 * }
 * @return false|string The URL of the avatar we found, or false if we couldn't find an avatar.
 */
function smart_ava_get_avatar_url($id_or_email, $args = null)
{
    $args = smart_ava_get_avatar_data($id_or_email, $args);
    return $args['url'];
}//end smart_ava_get_avatar_url()





/**
 * The admin interface form processing.
 *
 * @return void
 */
function smartAvaProcessForm()
{
    $error=array();
    //remove white-list domains
    if (! $domains=get_option('smartAvaDomains')) {
        $domains=array();
    }
    $j=sizeof($domains);
    $remove=array();
    $search=array();
    $replace=array();
    $search[]='/\./';
    $replace[]='_DOT_';
    for ($i=0; $i<$j; $i++) {
        $test='del_' . preg_replace($search, $replace, $domains[$i]);
        if (isset($_POST[$test])) {
            $remove[]=$domains[$i];
        }
    }
    if (sizeof($remove) > 0) {
        //should have just had the function take array argument - fix before 1.0
        $reList=implode(';', $remove);
        smartAvaRemoveDomains($reList);
    }
    
    //add white-list domains
    if (isset($_POST['addDomains'])) {
        $domainsToAdd=trim(urldecode($_POST['addDomains']));
        if (strlen($domainsToAdd) > 0) {
            $ee=smartAvaAddDomains($domainsToAdd);
            if ((sizeof($ee)) > 0) {
                $error = array_merge($error, $ee);
            }
        }
    }
        
    //remove white-list e-mail addresses
    if (! $addys=get_option('smartAvaAddys')) {
        $addys=array();
    }
    $j=sizeof($addys);
    $remove=array();
    $search=array();
    $replace=array();
    $search[]='/@/';
    $replace[]='_AT_';
    $search[]='/\./';
    $replace[]='_DOT_';
    for ($i=0; $i<$j; $i++) {
        $test='del_' . preg_replace($search, $replace, $addys[$i]);
        if (isset($_POST[$test])) {
            $remove[]=$addys[$i];
        }
    }
    if (sizeof($remove) > 0) {
        $reList=implode(';', $remove);
        smartAvaRemoveAddresses($reList);
    }
    
    //add white-listed e-mails
    if (isset($_POST['addEmails'])) {
        $emailsToAdd=trim(urldecode($_POST['addEmails']));
        if (strlen($emailsToAdd) > 0) {
            $ee=smartAvaAddAddresses($emailsToAdd);
            if ((sizeof($ee)) > 0) {
                $error = array_merge($error, $ee);
            }
        }
    }
        
    //salts
    $nsalt=array();
    if (isset($_POST['smartAvaRegenSalts'])) {
        delete_option('smartAvaSalt');
    }

    if (isset($_POST['smartAvaSaltOne'])) {
        $sone=trim($_POST['smartAvaSaltOne']);
    } else {
        $sone='';
    }
    if (isset($_POST['smartAvaSaltTwo'])) {
        $stwo=trim($_POST['smartAvaSaltTwo']);
    } else {
        $stwo='';
    }
        
    if (strlen($sone) > 0) {
        if (strlen($sone) > 17) {
            $nsalt[]=$sone;
        } else {
            $error[]='First custom salt is too short. It must be at least 18 characters long.';
        }
    }
        
    if (strlen($stwo) > 0) {
        if (strlen($stwo) > 17) {
            $nsalt[]=$stwo;
        } else {
            $error[]='Second custom salt is too short. It must be at least 18 characters long.';
        }
    }
    if (sizeof($nsalt) == 1) {
        $error[]='If using custom salts, you need two custom salts, each at least 18 characters long.';
    }
    if (sizeof($nsalt) == 2) {
        update_option('smartAvaSalt', $nsalt);
    }
        
    //notify
    if (isset($_POST['avaSmartNotify'])) {
        update_option('smartAvaFooter', 't');
    } else {
        delete_option('smartAvaFooter');
    }
    $j=sizeof($error);
    if ($j > 0) {
        if ($j == 1) {
            echo('<div class="error">' . "\n<p>The following error occurred:</p><ol>");
        } else {
            echo('<div class="error">' . "\n<p>The following errors occurred:</p><ol>");
        }
        for ($i=0; $i<$j; $i++) {
            echo('<li>' . $error[$i] . '</li>' . "\n");
        }
        echo("</ol>\n</div>\n");
    }
    // @codingStandardsIgnoreLine
    echo('<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>' . "\n");
}//end smartAvaProcessForm()
 //end of form processing function

/**
 * The admin interface options menu.
 *
 * @return void
 */
function smartAvaAdminOptions()
{
    if (! current_user_can('manage_options')) {
        wp_die(__('What does the fox say?'));
    }
    if (isset($_POST['smartAvaAuthKey'])) {
        $chk=trim($_POST['smartAvaAuthKey']);
        $key=trim(get_option('smartAvaAuthKey'));
        if (strcmp($chk, $key) == 0) {
            smartAvaProcessForm();
        }
    }
    echo('<div class="wrap">' . "\n");
    // @codingStandardsIgnoreLine
    echo('<div id="icon-options-general" class="icon32"><br /></div><h2>Gr*vatar Obfuscation Administration</h2>' . "\n");
    echo('<form id="smartAvaForm" method="post" action="options-general.php?page=smartAva">' . "\n");
    $key=smartAvaSaltShaker();
    update_option('smartAvaAuthKey', $key);
    echo('<input type="hidden" name="smartAvaAuthKey" id="smartAvaAuthKey" value="' . $key . '" />' . "\n");
    
    smartAvaAdmDomainMenu();
    smartAvaAdmAddressMenu();
    smartAvaAdmSalty();
    smartAvaAdmNotify();
    
    // @codingStandardsIgnoreLine
    echo('<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" /></p>');
    echo("</form>\n");
    echo("</div>\n");
}//end smartAvaAdminOptions()

/**
 * The admin interface.
 *
 * @return void
 */
function smartAvaAdminMenu()
{
    add_options_page('smartAva Administration', 'smartAva', 'manage_options', 'smartAva', 'smartAvaAdminOptions');
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
}//end smartAvaAdminMenu()


add_action('admin_menu', 'smartAvaAdminMenu');

////////////////////////

if (!function_exists('get_avatar')) {
    :
    if ($footerPermission=get_option('smartAvaFooter')) {
        add_action('wp_footer', 'smartAvaFooter');
    }
}

//below is direct from wordpress core pluggable.php with lines changed to use smart_ava_* functions

if (! function_exists('get_avatar')) :
    /**
     * Retrieve the avatar `<img>` tag for a user, email address, MD5 hash, comment, or post.
     *
     * @since 2.5.0
     * @since 4.2.0 Optional `$args` parameter added.
     *
     * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
     *                           user email, WP_User object, WP_Post object, or WP_Comment object.
     * @param int    $size       Optional. Height and width of the avatar image file in pixels. Default 96.
     * @param string $default    Optional. URL for the default image or a default type. Accepts '404'
     *                           (return a 404 instead of a default image), 'retro' (8bit), 'monsterid'
     *                           (monster), 'wavatar' (cartoon face), 'indenticon' (the "quilt"),
     *                           'mystery', 'mm', or 'mysteryman' (The Oyster Man), 'blank' (transparent GIF),
     *                           or 'gravatar_default' (the Gravatar logo). Default is the value of the
     *                           'avatar_default' option, with a fallback of 'mystery'.
     * @param string $alt        Optional. Alternative text to use in &lt;img&gt; tag. Default empty.
     * @param array  $args       {
     *     Optional. Extra arguments to retrieve the avatar.
     *
     *     @type   int          $height        Display height of the avatar in pixels. Defaults to $size.
     *     @type   int          $width         Display width of the avatar in pixels. Defaults to $size.
     *     @type   bool         $force_default Whether to always show the default image, never the Gravatar. Default
     *                                         false.
     *     @type   string       $rating        What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and
     *                                         are judged in that order. Default is the value of the 'avatar_rating'
     *                                         option.
     *     @type   string       $scheme        URL scheme to use. See set_url_scheme() for accepted values.
     *                                         Default null.
     *     @type   array|string $class         Array or string of additional classes to add to the &lt;img&gt; element.
     *                                         Default null.
     *     @type   bool         $force_display Whether to always show the avatar - ignores the show_avatars option.
     *                                         Default false.
     *     @type   string       $extra_attr    HTML attributes to insert in the IMG element. Is not sanitized. Default
     *                                         empty.
     * }
     * @return false|string `<img>` tag for the user's avatar. False on failure.
     */
    function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null)
    {
        $defaults = array(
        // get_avatar_data() args.
        'size'          => 96,
        'height'        => null,
        'width'         => null,
        'default'       => get_option('avatar_default', 'mystery'),
        'force_default' => false,
        'rating'        => get_option('avatar_rating'),
        'scheme'        => null,
        'alt'           => '',
        'class'         => null,
        'force_display' => false,
        'extra_attr'    => '',
        );
        if (empty($args)) {
            $args = array();
        }
        $args['size']    = (int) $size;
        $args['default'] = $default;
        $args['alt']     = $alt;
        $args = wp_parse_args($args, $defaults);
        if (empty($args['height'])) {
            $args['height'] = $args['size'];
        }
        if (empty($args['width'])) {
            $args['width'] = $args['size'];
        }
        if (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
            $id_or_email = get_comment($id_or_email);
        }
        /**
         * Filters whether to retrieve the avatar URL early.
         *
         * Passing a non-null value will effectively short-circuit get_avatar(), passing
         * the value through the {@see 'get_avatar'} filter and returning early.
         *
         * @since 4.2.0
         *
         * @param string $avatar      HTML for the user's avatar. Default null.
         * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
         *                            user email, WP_User object, WP_Post object, or WP_Comment object.
         * @param array  $args        Arguments passed to get_avatar_url(), after processing.
         */
        $avatar = apply_filters('pre_get_avatar', null, $id_or_email, $args);
        if (! is_null($avatar)) {
            /** This filter is documented in wp-includes/pluggable.php */
            // @codingStandardsIgnoreLine
            return apply_filters('get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args);
        }
        if (! $args['force_display'] && ! get_option('show_avatars')) {
            return false;
        }
        $url2x = smart_ava_get_avatar_url($id_or_email, array_merge($args, array( 'size' => $args['size'] * 2 )));
        $args = smart_ava_get_avatar_data($id_or_email, $args);
        $url = $args['url'];
        if (! $url || is_wp_error($url)) {
            return false;
        }
        $class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );
        if (! $args['found_avatar'] || $args['force_default']) {
            $class[] = 'avatar-default';
        }
        if ($args['class']) {
            if (is_array($args['class'])) {
                $class = array_merge($class, $args['class']);
            } else {
                $class[] = $args['class'];
            }
        }
        $avatar = sprintf(
            "<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
            esc_attr($args['alt']),
            esc_url($url),
            esc_url($url2x) . ' 2x',
            esc_attr(join(' ', $class)),
            (int) $args['height'],
            (int) $args['width'],
            $args['extra_attr']
        );
            /**
             * Filters the avatar to retrieve.
             *
             * @since 2.5.0
             * @since 4.2.0 The `$args` parameter was added.
             *
             * @param string $avatar      &lt;img&gt; tag for the user's avatar.
             * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
             *                            user email, WP_User object, WP_Post object, or WP_Comment object.
             * @param int    $size        Square avatar width and height in pixels to retrieve.
             * @param string $default     URL for the default image or a default type. Accepts '404', 'retro',
             *                            'monsterid', 'wavatar', 'indenticon','mystery' (or 'mm', or 'mysteryman'),
             *                            'blank', or 'gravatar_default'. Default is the value of the 'avatar_default'
             *                            option, with a fallback of 'mystery'.
             * @param string $alt         Alternative text to use in the avatar image tag. Default empty.
             * @param array  $args        Arguments passed to get_avatar_data(), after processing.
             */
            // @codingStandardsIgnoreLine
            return apply_filters('smart_ava_get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args);
    }//end get_avatar()

endif;
?>
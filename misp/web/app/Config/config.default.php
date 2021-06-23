<?php
$config = array(
	'debug'            => 0,
	'Security'         =>
		array(
			'level'      => 'medium',
			'salt'       => '',
			'cipherSeed' => '',
            'require_password_confirmation' => true
			//'auth'=>array('CertAuth.Certificate'), // additional authentication methods
			//'auth'=>array('ShibbAuth.ApacheShibb'),
			//'auth'=>array('AadAuth.AadAuthenticate'),
		),
	'MISP'             =>
		array(
			'baseurl'                        => '',
			'footermidleft'                  => '',
			'footermidright'                 => '',
			'org'                            => 'ORGNAME',
			'showorg'                        => true,
			'threatlevel_in_email_subject'   => true,
            'email_subject_TLP_string'       => 'tlp:amber',
			'email_subject_tag'              => 'tlp',
			'email_subject_include_tag_name' => true,
			'background_jobs'                => true,
			'cached_attachments'             => true,
			'osuser'                         => 'www-data',
            'email'                          => 'email@example.com',
            'contact'                        => 'email@example.com',
			'cveurl'                         => 'https://cve.circl.lu/cve/',
			'cweurl'                         => 'https://cve.circl.lu/cwe/',
			'disablerestalert'               => false,
			'default_event_distribution'     => '1',
			'default_attribute_distribution' => 'event',
			'tagging'                        => true,
			'full_tags_on_event_index'       => true,
			'attribute_tagging'              => true,
			'full_tags_on_attribute_index'   => true,
			'footer_logo'                    => '',
			'take_ownership_xml_import'      => false,
			'unpublishedprivate'             => false,
			'disable_emailing'               => false,
			'manage_workers'                 => true,
			'Attributes_Values_Filter_In_Event' => 'id, uuid, value, comment, type, category, Tag.name',
		),
	'GnuPG'            =>
		array(
			'onlyencrypted'     => false,
			'email'             => '',
			'homedir'           => '',
			'password'          => '',
            'bodyonlyencrypted' => false,
            'sign'              => true,
            'obscure_subject'   => false,
		),
	'SMIME'            =>
		array(
			'enabled'          => false,
			'email'            => '',
			'cert_public_sign' => '',
			'key_sign'         => '',
			'password'         => '',
		),
	'Proxy'            =>
		array(
			'host'     => '',
			'port'     => '',
			'method'   => '',
			'user'     => '',
			'password' => '',
		),
	'SecureAuth'       =>
		array(
			'amount' => 5,
			'expire' => 300,
		),
	// Uncomment the following to enable client SSL certificate authentication
	/*
	'CertAuth'         =>
		array(

			// CA
			'ca'           => array('FIRST.Org'), // List of CAs authorized
			'caId'         => 'O',          // Certificate field used to verify the CA. In this example, the field O (organization) of the client certificate has to equal to 'FIRST.Org' in order to validate the CA

			// User/client configuration
			'userModel'    => 'User',       // name of the User class (MISP class) to check if the user exists
			'userModelKey' => 'email',      // User field that will be used for querying. In this example, the field email of the MISP accounts will be used to search if the user exists.
			'map'          => array(        // maps client certificate attributes to User properties. This map will be used as conditions to find if the user exists. In this example, the client certificate fields 'O' (organization) and 'emailAddress' have to match with the MISP fields 'org' and 'email' to validate the user.
				'O'            => 'org',
				'emailAddress' => 'email',
			),

			// Synchronization/RestAPI
			'syncUser'     => true,         // should the User be synchronized with an external REST API
			'userDefaults' => array(          // default user attributes, only used when creating new users. By default, new users are "Read only" users (role_id: 6).
				'role_id' => 6,
			),
			'restApi'      => array(        // API parameters
				'url'     => 'https://example.com/data/users',  // URL to query
				'headers' => array(),                           // additional headers, used for authentication
				'param'   => array('email' => 'email'),       // query parameters to add to the URL, mapped to User properties
				'map'     => array(                            // maps REST result to the User properties
					'uid'        => 'nids_sid',
					'team'       => 'org',
					'email'      => 'email',
					'pgp_public' => 'gpgkey',
				),
			),
			'userDefaults' => array('role_id' => 6),          // default attributes for new users. By default, new users are "Read only" users (role_id: 6).
		),
	*/
	/*
	'ApacheShibbAuth'  =>                      // Configuration for shibboleth authentication
		array(
			'apacheEnv'         => 'REMOTE_USER',        // If proxy variable = HTTP_REMOTE_USER
			'MailTag'           => 'EMAIL_TAG',
			'OrgTag'            => 'FEDERATION_TAG',
			'GroupTag'          => 'GROUP_TAG',
			'GroupSeparator'    => ';',
			'GroupRoleMatching' => array(                // 3:User, 1:admin. May be good to set "1" for the first user
				'group_three' => 3,
				'group_two'   => 2,
				'group_one'   => 1,
			),
			'DefaultOrg'        => 'DEFAULT_ORG',
		),
	*/
	/*
	'LinOTPAuth' => // Configuration for the LinOTP authentication
	    array(
	        'baseUrl' => 'https://linotp', // The base URL of LinOTP
	        'realm' => 'lino', // the (default) realm of all the users logging in through this system
	        'userModel' => 'User', // name of the User class (MISP class) to check if the user exists
            'userModelKey' => 'email', // User field that will be used for querying.
        ),
	*/
	// Warning: The following is a 3rd party contribution and still untested (including security) by the MISP-project team.
	// Feel free to enable it and report back to us if you run into any issues.
	//
	// Uncomment the following to enable Kerberos authentication
	// needs PHP LDAP support enabled (e.g. compile flag --with-ldap or Debian package php5-ldap)
	/*
	'ApacheSecureAuth' => // Configuration for kerberos authentication
		array(
			'apacheEnv'          => 'REMOTE_USER',           // If proxy variable = HTTP_REMOTE_USER, If BasicAuth ldap = PHP_AUTH_USER
			'ldapServer'         => 'ldap://example.com',   // FQDN or IP
			'ldapProtocol'       => 3,
			'ldapNetworkTimeout' => -1,  // use -1 for unlimited network timeout
			'ldapReaderUser'     => 'cn=userWithReadAccess,ou=users,dc=example,dc=com', // DN ou RDN LDAP with reader user right
			'ldapReaderPassword' => 'UserPassword', // the LDAP reader user password
			'ldapDN'             => 'dc=example,dc=com',
			'ldapSearchFilter'   => '', // Search filter to limit results from ldapsearh fx to specific group. FX
	 		//'ldapSearchFilter'   => '(objectclass=InetOrgPerson)(!(nsaccountlock=True))(memberOf=cn=misp,cn=groups,cn=accounts,dc=example,dc=com)',
			'ldapSearchAttribut' => 'uid',          // filter for search
			'ldapFilter'         => array(
				'mail',
			//	'memberOf', //Needed filter if roles should be added depending on group membership.
			),
			'ldapDefaultRoleId'  => 3,               // 3:User, 1:admin. May be good to set "1" for the first user
			//ldapDefaultRoleId can also be set as an array to support creating users into different group, depending on ldap membership.
			//This will only work if the ldap server supports memberOf
			//'ldapDefaultRoleId'  => array(
			//                         'misp_admin' => 1,
			//                         'misp_orgadmin' => 2,
			//                         'misp_user' => 3,
			//                         'misp_publisher' => 4,
			//                         'misp_syncuser' => 5,
			//                         'misp_readonly' => 6,
			//                         ),
			//
			'ldapDefaultOrg'     => '1',      // uses 1st local org in MISP if undefined,
			'ldapAllowReferrals' => true,   // allow or disallow chasing LDAP referrals
			//'ldapEmailField' => array('emailAddress, 'mail'), // Optional : fields from which the email address should be retrieved. Default to 'mail' only. If more than one field is set (e.g. 'emailAddress' and 'mail' in this example), only the first one will be used.
			//'updateUser' => true, // Optional : Will update user on LDAP login to update user fields (e.g. role)
		),
	*/
	
	// Warning: The following is a 3rd party contribution and still untested (including security) by the MISP-project team.
	// Feel free to enable it and report back to us if you run into any issues.
	//
	// Uncomment the following to enable Azure AD authentication
	/*
	'AadAuth'         =>
        array(
			'client_id' => '', // Client ID (see Azure AD)
			'ad_tenant' => '', // Directory ID (see Azure AD)
			'client_secret' => '', // Client secret (see Azure AD)
			'redirect_uri' => '', // Your MISP URI, must be the same as in Azure AD
			'auth_provider' => 'https://login.microsoftonline.com/',	// Can be left to this default
			'auth_provider_user' => 'https://graph.microsoft.com/',		// Can be left to this default
			'misp_user' => 'MISP Users',	// The AD group for MISP users
			'misp_orgadmin' => 'MISP Administrators',	// The AD group for MISP administrators
			'misp_siteadmin' => 'MISP Site Administrators', 	// The AD group for MISP site administrators
			'check_ad_groups' => true	// Should we check if the user belongs to one of the above AD groups?
		),
	*/
);

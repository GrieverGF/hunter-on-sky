<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');
App::uses('LogableBehavior', 'Assets.models/behaviors');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');
App::uses('RandomTool', 'Tools');
class AppModel extends Model
{
    public $name;

    /**
     * @var PubSubTool
     */
    private $loadedPubSubTool;

    public $loadedKafkaPubTool = false;

    public $start = 0;

    public $assetCache = [];

    public $inserted_ids = array();

    /** @var null|Redis */
    private static $__redisConnection = null;

    private $__profiler = array();

    public $elasticSearchClient = false;

    /** @var AttachmentTool|null */
    private $attachmentTool;

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);

        $this->name = get_class($this);
        $this->findMethods['column'] = true;
    }

    // deprecated, use $db_changes
    // major -> minor -> hotfix -> requires_logout
    public $old_db_changes = array(
        2 => array(
            4 => array(
                18 => false, 19 => false, 20 => false, 25 => false, 27 => false,
                32 => false, 33 => true, 38 => true, 39 => true, 40 => false,
                42 => false, 44 => false, 45 => false, 49 => true, 50 => false,
                51 => false, 52 => false, 55 => true, 56 => true, 57 => true,
                58 => false, 59 => false, 60 => false, 61 => false, 62 => false,
                63 => false, 64 => false, 65 => false, 66 => false, 67 => true,
                68 => false, 69 => false, 71 => false, 72 => false, 73 => false,
                75 => false, 77 => false, 78 => false, 79 => false, 80 => false,
                81 => false, 82 => false, 83 => false, 84 => false, 85 => false,
                86 => false, 87 => false
            )
        )
    );

    public $db_changes = array(
        1 => false, 2 => false, 3 => false, 4 => true, 5 => false, 6 => false,
        7 => false, 8 => false, 9 => false, 10 => false, 11 => false, 12 => false,
        13 => false, 14 => false, 15 => false, 18 => false, 19 => false, 20 => false,
        21 => false, 22 => false, 23 => false, 24 => false, 25 => false, 26 => false,
        27 => false, 28 => false, 29 => false, 30 => false, 31 => false, 32 => false,
        33 => false, 34 => false, 35 => false, 36 => false, 37 => false, 38 => false,
        39 => false, 40 => false, 41 => false, 42 => false, 43 => false, 44 => false,
        45 => false, 46 => false, 47 => false, 48 => false, 49 => false, 50 => false,
        51 => false, 52 => false, 53 => false, 54 => false, 55 => false, 56 => false,
        57 => false, 58 => false, 59 => false, 60 => false, 61 => false, 62 => false,
        63 => true, 64 => false, 65 => false, 66 => false, 67 => false, 68 => false,
        69 => false,
    );

    public $advanced_updates_description = array(
        'seenOnAttributeAndObject' => array(
            'title' => 'First seen/Last seen Attribute table',
            'description' => 'Update the Attribute table to support first_seen and last_seen feature, with a microsecond resolution.',
            'liveOff' => true, # should the instance be offline for users other than site_admin
            'recommendBackup' => true, # should the update recommend backup
            'exitOnError' => false, # should the update exit on error
            'requirements' => 'MySQL version must be >= 5.6', # message stating the requirements necessary for the update
            'record' => false, # should the update success be saved in the admin_table
            // 'preUpdate' => 'seenOnAttributeAndObjectPreUpdate', # Function to execute before the update. If it throws an error, it cancels the update
            'url' => '/servers/updateDatabase/seenOnAttributeAndObject/' # url pointing to the funcion performing the update
        ),
    );
    public $actions_description = array(
        'verifyGnuPGkeys' => array(
            'title' => 'Verify GnuPG keys',
            'description' => "Run a full validation of all GnuPG keys within this instance's userbase. The script will try to identify possible issues with each key and report back on the results.",
            'url' => '/users/verifyGPG/'
        ),
        'databaseCleanupScripts' => array(
            'title' => 'Database Cleanup Scripts',
            'description' => 'If you run into an issue with an infinite upgrade loop (when upgrading from version ~2.4.50) that ends up filling your database with upgrade script log messages, run the following script.',
            'url' => '/logs/pruneUpdateLogs/'
        ),
        'releaseUpdateLock' => array(
            'title' => 'Release update lock',
            'description' => 'If your your database is locked and is not updating, unlock it here.',
            'ignore_disabled' => true,
            'url' => '/servers/releaseUpdateLock/'
        )
    );

    public function afterSave($created, $options = array())
    {
        if ($created) {
            $this->inserted_ids[] = $this->getInsertID();
        }
        return true;
    }

    public function isAcceptedDatabaseError($errorMessage, $dataSource)
    {
        $isAccepted = false;
        if ($dataSource == 'Database/Mysql' || $dataSource == 'Database/MysqlObserver') {
            $errorDuplicateColumn = 'SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name';
            $errorDuplicateIndex = 'SQLSTATE[42000]: Syntax error or access violation: 1061 Duplicate key name';
            $errorDropIndex = "/SQLSTATE\[42000\]: Syntax error or access violation: 1091 Can't DROP '[\w]+'; check that column\/key exists/";
            $isAccepted = substr($errorMessage, 0, strlen($errorDuplicateColumn)) === $errorDuplicateColumn ||
                            substr($errorMessage, 0, strlen($errorDuplicateIndex)) === $errorDuplicateIndex ||
                            preg_match($errorDropIndex, $errorMessage) !== 0;
        } elseif ($dataSource == 'Database/Postgres') {
            $errorDuplicateColumn = '/ERROR:  column "[\w]+" specified more than once/';
            $errorDuplicateIndex = '/ERROR: relation "[\w]+" already exists/';
            $errorDropIndex = '/ERROR: index "[\w]+" does not exist/';
            $isAccepted = preg_match($errorDuplicateColumn, $errorMessage) !== 0 ||
                            preg_match($errorDuplicateIndex, $errorMessage) !== 0 ||
                            preg_match($errorDropIndex, $errorMessage) !== 0;
        }
        return $isAccepted;
    }

    // Generic update script
    // add special cases where the upgrade does more than just update the DB
    // this could become useful in the future
    public function updateMISP($command)
    {
        $dbUpdateSuccess = false;
        switch ($command) {
            case '2.4.20':
                $dbUpdateSuccess = $this->updateDatabase($command);
                $this->ShadowAttribute = ClassRegistry::init('ShadowAttribute');
                $this->ShadowAttribute->upgradeToProposalCorrelation();
                break;
            case '2.4.25':
                $dbUpdateSuccess = $this->updateDatabase($command);
                $newFeeds = array(
                    array('provider' => 'CIRCL', 'name' => 'CIRCL OSINT Feed', 'url' => 'https://www.circl.lu/doc/misp/feed-osint', 'enabled' => 0),
                );
                $this->__addNewFeeds($newFeeds);
                break;
            case '2.4.27':
                $newFeeds = array(
                    array('provider' => 'Botvrij.eu', 'name' => 'The Botvrij.eu Data','url' => 'https://www.botvrij.eu/data/feed-osint', 'enabled' => 0)
                );
                $this->__addNewFeeds($newFeeds);
                break;
            case '2.4.49':
                $dbUpdateSuccess = $this->updateDatabase($command);
                $this->SharingGroup = ClassRegistry::init('SharingGroup');
                $this->SharingGroup->correctSyncedSharingGroups();
                $this->SharingGroup->updateRoaming();
                break;
            case '2.4.55':
                $dbUpdateSuccess = $this->updateDatabase('addSightings');
                break;
            case '2.4.66':
                $dbUpdateSuccess = $this->updateDatabase('2.4.66');
                $this->cleanCacheFiles();
                $this->Sighting = Classregistry::init('Sighting');
                $this->Sighting->addUuids();
                break;
            case '2.4.67':
                $dbUpdateSuccess = $this->updateDatabase('2.4.67');
                $this->Sighting = Classregistry::init('Sighting');
                $this->Sighting->addUuids();
                $this->Sighting->deleteAll(array('NOT' => array('Sighting.type' => array(0, 1, 2))));
                break;
            case '2.4.71':
                $this->OrgBlocklist = Classregistry::init('OrgBlocklist');
                $values = array(
                    array('org_uuid' => '58d38339-7b24-4386-b4b4-4c0f950d210f', 'org_name' => 'Setec Astrononomy', 'comment' => 'default example'),
                    array('org_uuid' => '58d38326-eda8-443a-9fa8-4e12950d210f', 'org_name' => 'Acme Finance', 'comment' => 'default example')
                );
                foreach ($values as $value) {
                    $found = $this->OrgBlocklist->find('first', array('conditions' => array('org_uuid' => $value['org_uuid']), 'recursive' => -1));
                    if (empty($found)) {
                        $this->OrgBlocklist->create();
                        $this->OrgBlocklist->save($value);
                    }
                }
                $dbUpdateSuccess = $this->updateDatabase($command);
                break;
            case '2.4.86':
                $this->MispObject = Classregistry::init('MispObject');
                $this->MispObject->removeOrphanedObjects();
                $dbUpdateSuccess = $this->updateDatabase($command);
                break;
            case 5:
                $dbUpdateSuccess = $this->updateDatabase($command);
                $this->Feed = Classregistry::init('Feed');
                $this->Feed->setEnableFeedCachingDefaults();
                break;
            case 8:
                $this->Server = Classregistry::init('Server');
                $this->Server->restartWorkers();
                break;
            case 10:
                $dbUpdateSuccess = $this->updateDatabase($command);
                $this->Role = Classregistry::init('Role');
                $this->Role->setPublishZmq();
                break;
            case 12:
                $this->__forceSettings();
                break;
            case 23:
                $this->__bumpReferences();
                break;
            case 34:
                $this->__fixServerPullPushRules();
                break;
            case 38:
                $dbUpdateSuccess = $this->updateDatabase($command);
                $this->__addServerPriority();
                break;
            case 46:
                $dbUpdateSuccess = $this->updateDatabase('seenOnAttributeAndObject');
                break;
            case 48:
                $dbUpdateSuccess = $this->__generateCorrelations();
                break;
            default:
                $dbUpdateSuccess = $this->updateDatabase($command);
                break;
        }
        return $dbUpdateSuccess;
    }

    private function __addServerPriority()
    {
        $this->Server = ClassRegistry::init('Server');
        $this->Server->reprioritise();
        return true;
    }

    private function __addNewFeeds($feeds)
    {
        $this->Feed = ClassRegistry::init('Feed');
        $this->Log = ClassRegistry::init('Log');
        $feedNames = array();
        foreach ($feeds as $feed) {
            $feedNames[] = $feed['name'];
        }
        $feedNames = implode(', ', $feedNames);
        $result = $this->Feed->addDefaultFeeds($feeds);
        $this->Log->create();
        $entry = array(
                'org' => 'SYSTEM',
                'model' => 'Server',
                'model_id' => 0,
                'email' => 'SYSTEM',
                'action' => 'update_database',
                'user_id' => 0,
                'title' => 'Added new default feeds.'
        );
        if ($result) {
            $entry['change'] = 'Feeds added: ' . $feedNames;
        } else {
            $entry['change'] = 'Tried adding new feeds but something went wrong.';
        }
        $this->Log->save($entry);
    }

    // SQL scripts for updates
    public function updateDatabase($command)
    {
        $this->Log = ClassRegistry::init('Log');

        $liveOff = false;
        $exitOnError = false;
        if (isset($this->advanced_updates_description[$command])) {
            $liveOff = isset($this->advanced_updates_description[$command]['liveOff']) ? $this->advanced_updates_description[$command]['liveOff'] : $liveOff;
            $exitOnError = isset($this->advanced_updates_description[$command]['exitOnError']) ? $this->advanced_updates_description[$command]['exitOnError'] : $exitOnError;
        }

        $dataSourceConfig = ConnectionManager::getDataSource('default')->config;
        $dataSource = $dataSourceConfig['datasource'];
        $sqlArray = array();
        $indexArray = array();
        $clean = true;
        switch ($command) {
            case 'extendServerOrganizationLength':
                $sqlArray[] = 'ALTER TABLE `servers` MODIFY COLUMN `organization` varchar(255) NOT NULL;';
                break;
            case 'convertLogFieldsToText':
                $sqlArray[] = 'ALTER TABLE `logs` MODIFY COLUMN `title` text, MODIFY COLUMN `change` text;';
                break;
            case 'addEventBlacklists':
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `event_blacklists` ( `id` int(11) NOT NULL AUTO_INCREMENT, `event_uuid` varchar(40) COLLATE utf8_bin NOT NULL, `created` datetime NOT NULL, PRIMARY KEY (`id`), `event_info` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `event_orgc` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
                break;
            case 'addOrgBlacklists':
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `org_blacklists` ( `id` int(11) NOT NULL AUTO_INCREMENT, `org_uuid` varchar(40) COLLATE utf8_bin NOT NULL, `created` datetime NOT NULL, PRIMARY KEY (`id`), `org_name` varchar(255) COLLATE utf8_bin NOT NULL, `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
                break;
            case 'addEventBlacklistsContext':
                $sqlArray[] = 'ALTER TABLE  `event_blacklists` ADD  `event_orgc` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , ADD  `event_info` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, ADD `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;';
                break;
            case 'addSightings':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS sightings (
                id int(11) NOT NULL AUTO_INCREMENT,
                attribute_id int(11) NOT NULL,
                event_id int(11) NOT NULL,
                org_id int(11) NOT NULL,
                date_sighting bigint(20) NOT NULL,
                PRIMARY KEY (id),
                INDEX attribute_id (attribute_id),
                INDEX event_id (event_id),
                INDEX org_id (org_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
                break;
            case 'makeAttributeUUIDsUnique':
                $this->__dropIndex('attributes', 'uuid');
                $sqlArray[] = 'ALTER TABLE `attributes` ADD UNIQUE (uuid);';
                break;
            case 'makeEventUUIDsUnique':
                $this->__dropIndex('events', 'uuid');
                $sqlArray[] = 'ALTER TABLE `events` ADD UNIQUE (uuid);';
                break;
            case 'cleanSessionTable':
                $sqlArray[] = 'DELETE FROM cake_sessions WHERE expires < ' . time() . ';';
                $clean = false;
                break;
            case 'destroyAllSessions':
                $sqlArray[] = 'DELETE FROM cake_sessions;';
                $clean = false;
                break;
            case 'addIPLogging':
                $sqlArray[] = 'ALTER TABLE `logs` ADD  `ip` varchar(45) COLLATE utf8_bin DEFAULT NULL;';
                break;
            case 'addCustomAuth':
                $sqlArray[] = "ALTER TABLE `users` ADD `external_auth_required` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'ALTER TABLE `users` ADD `external_auth_key` text COLLATE utf8_bin;';
                break;
            case 'x24betaupdates':
                $sqlArray = array();
                $sqlArray[] = "ALTER TABLE `shadow_attributes` ADD  `proposal_to_delete` tinyint(1) NOT NULL DEFAULT 0;";

                $sqlArray[] = 'ALTER TABLE `logs` MODIFY  `change` text COLLATE utf8_bin NOT NULL;';

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `taxonomies` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `namespace` varchar(255) COLLATE utf8_bin NOT NULL,
                    `description` text COLLATE utf8_bin NOT NULL,
                    `version` int(11) NOT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `taxonomy_entries` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `taxonomy_predicate_id` int(11) NOT NULL,
                    `value` text COLLATE utf8_bin NOT NULL,
                    `expanded` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `taxonomy_predicate_id` (`taxonomy_predicate_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `taxonomy_predicates` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `taxonomy_id` int(11) NOT NULL,
                    `value` text COLLATE utf8_bin NOT NULL,
                    `expanded` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `taxonomy_id` (`taxonomy_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $sqlArray[] = 'ALTER TABLE `jobs` ADD  `org` text COLLATE utf8_bin NOT NULL;';

                $sqlArray[] = 'ALTER TABLE  `servers` ADD  `name` varchar(255) NOT NULL;';

                $sqlArray[] = 'ALTER TABLE  `sharing_groups` ADD  `sync_user_id` INT( 11 ) NOT NULL DEFAULT \'0\' AFTER `org_id`;';

                $sqlArray[] = 'ALTER TABLE `users` ADD  `disabled` BOOLEAN NOT NULL;';
                $sqlArray[] = 'ALTER TABLE `users` ADD  `expiration` datetime DEFAULT NULL;';

                $sqlArray[] = 'UPDATE `roles` SET `perm_template` = 1 WHERE `perm_site_admin` = 1 OR `perm_admin` = 1;';
                $sqlArray[] = 'UPDATE `roles` SET `perm_sharing_group` = 1 WHERE `perm_site_admin` = 1 OR `perm_sync` = 1;';

                //create indexes
                break;
            case 'indexTables':
                $fieldsToIndex = array(
                    'attributes' => array(array('value1', 'INDEX', '255'), array('value2', 'INDEX', '255'), array('event_id', 'INDEX'), array('sharing_group_id', 'INDEX'), array('uuid', 'INDEX')),
                    'correlations' =>  array(array('org_id', 'INDEX'), array('event_id', 'INDEX'), array('attribute_id', 'INDEX'), array('sharing_group_id', 'INDEX'), array('1_event_id', 'INDEX'), array('1_attribute_id', 'INDEX'), array('a_sharing_group_id', 'INDEX'), array('value', 'FULLTEXT')),
                    'events' => array(array('info', 'FULLTEXT'), array('sharing_group_id', 'INDEX'), array('org_id', 'INDEX'), array('orgc_id', 'INDEX'), array('uuid', 'INDEX')),
                    'event_tags' => array(array('event_id', 'INDEX'), array('tag_id', 'INDEX')),
                    'organisations' => array(array('uuid', 'INDEX'), array('name', 'FULLTEXT')),
                    'posts' => array(array('post_id', 'INDEX'), array('thread_id', 'INDEX')),
                    'shadow_attributes' => array(array('value1', 'INDEX', '255'), array('value2', 'INDEX', '255'), array('old_id', 'INDEX'), array('event_id', 'INDEX'), array('uuid', 'INDEX'), array('event_org_id', 'INDEX'), array('event_uuid', 'INDEX')),
                    'sharing_groups' => array(array('org_id', 'INDEX'), array('sync_user_id', 'INDEX'), array('uuid', 'INDEX'), array('organisation_uuid', 'INDEX')),
                    'sharing_group_orgs' => array(array('sharing_group_id', 'INDEX'), array('org_id', 'INDEX')),
                    'sharing_group_servers' => array(array('sharing_group_id', 'INDEX'), array('server_id', 'INDEX')),
                    'servers' => array(array('org_id', 'INDEX'), array('remote_org_id', 'INDEX')),
                    'tags' => array(array('name', 'FULLTEXT')),
                    'threads' => array(array('user_id', 'INDEX'), array('event_id', 'INDEX'), array('org_id', 'INDEX'), array('sharing_group_id', 'INDEX')),
                    'users' => array(array('org_id', 'INDEX'), array('server_id', 'INDEX'), array('email', 'INDEX')),
                );

                $version = $this->query('select version();');
                $version = $version[0][0]['version()'];
                $version = explode('.', $version);
                $version[0] = intval($version[0]);
                $version[1] = intval($version[1]);
                $downgrade = true;
                if ($version[0] > 5 || ($version[0] == 5 && $version[1] > 5)) {
                    $downgrade = false;
                }

                // keep the fulltext for now, we can change it later to actually use it once we require MySQL 5.6 / or if we decide to move some tables to MyISAM

                foreach ($fieldsToIndex as $table => $fields) {
                    $downgradeThis = false;
                    $table_data = $this->query("SHOW TABLE STATUS WHERE Name = '" . $table . "'");
                    if ($downgrade && $table_data[0]['TABLES']['Engine'] !== 'MyISAM') {
                        $downgradeThis = true;
                    }
                    foreach ($fields as $field) {
                        $extra = '';
                        $this->__dropIndex($table, $field[0]);
                        if (isset($field[2])) {
                            $extra = ' (' . $field[2] . ')';
                        }
                        $sqlArray[] = 'ALTER TABLE `' . $table . '` ADD ' . ($downgradeThis ? 'INDEX' : $field[1]) . ' `' . $field[0] . '` (`' . $field[0] . '`' . $extra . ');';
                    }
                }
                break;
            case 'adminTable':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `admin_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `setting` varchar(255) COLLATE utf8_bin NOT NULL,
                    `value` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "INSERT INTO `admin_settings` (`setting`, `value`) VALUES ('db_version', '2.4.0');";
                break;
            case '2.4.18':
                $sqlArray[] = "ALTER TABLE `users` ADD `current_login` INT(11) DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ADD `last_login` INT(11) DEFAULT 0;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `event_delegations` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `org_id` int(11) NOT NULL,
                    `requester_org_id` int(11) NOT NULL,
                    `event_id` int(11) NOT NULL,
                    `message` text,
                    `distribution` tinyint(4) NOT NULL DEFAULT  '-1',
                    `sharing_group_id` int(11),
                    PRIMARY KEY (`id`),
                    KEY `org_id` (`org_id`),
                    KEY `event_id` (`event_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.19':
                $sqlArray[] = "DELETE FROM `shadow_attributes` WHERE `event_uuid` = '';";
                break;
            case '2.4.20':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `shadow_attribute_correlations` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `org_id` int(11) NOT NULL,
                    `value` text NOT NULL,
                    `distribution` tinyint(4) NOT NULL,
                    `a_distribution` tinyint(4) NOT NULL,
                    `sharing_group_id` int(11),
                    `a_sharing_group_id` int(11),
                    `attribute_id` int(11) NOT NULL,
                    `1_shadow_attribute_id` int(11) NOT NULL,
                    `event_id` int(11) NOT NULL,
                    `1_event_id` int(11) NOT NULL,
                    `info` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `org_id` (`org_id`),
                    KEY `attribute_id` (`attribute_id`),
                    KEY `a_sharing_group_id` (`a_sharing_group_id`),
                    KEY `event_id` (`event_id`),
                    KEY `1_event_id` (`event_id`),
                    KEY `sharing_group_id` (`sharing_group_id`),
                    KEY `1_shadow_attribute_id` (`1_shadow_attribute_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.25':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `feeds` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_bin NOT NULL,
                    `provider` varchar(255) COLLATE utf8_bin NOT NULL,
                    `url` varchar(255) COLLATE utf8_bin NOT NULL,
                    `rules` text COLLATE utf8_bin NOT NULL,
                    `enabled` BOOLEAN NOT NULL,
                    `distribution` tinyint(4) NOT NULL,
                    `sharing_group_id` int(11) NOT NULL,
                    `tag_id` int(11) NOT NULL,
                    `default` tinyint(1) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.32':
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_tag_editor` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'UPDATE `roles` SET `perm_tag_editor` = 1 WHERE `perm_tagger` = 1;';
                break;
            case '2.4.33':
                $sqlArray[] = "ALTER TABLE `users` ADD `force_logout` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case '2.4.38':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `warninglists` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_bin NOT NULL,
                    `type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'string',
                    `description` text COLLATE utf8_bin NOT NULL,
                    `version` int(11) NOT NULL DEFAULT 1,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `warninglist_entry_count` int(11) unsigned DEFAULT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `warninglist_entries` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `warninglist_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `warninglist_types` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` varchar(255) COLLATE utf8_bin NOT NULL,
                    `warninglist_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.39':
                $sqlArray[] = "ALTER TABLE `users` ADD `certif_public` longtext COLLATE utf8_bin AFTER `gpgkey`;";
                $sqlArray[] = 'ALTER TABLE `logs` MODIFY COLUMN `title` text, MODIFY COLUMN `change` text;';
                break;
            case '2.4.40':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `favourite_tags` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `tag_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `user_id` (`user_id`),
                    INDEX `tag_id` (`tag_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.42':
                $sqlArray[] = "ALTER TABLE `attributes` ADD `deleted` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case '2.4.44':
                $sqlArray[] = "UPDATE `servers` SET `url` = TRIM(TRAILING '/' FROM `url`);";
                break;
            case '2.4.45':
                $sqlArray[] = 'ALTER TABLE `users` CHANGE `newsread` `newsread` int(11) unsigned;';
                $sqlArray[] = 'UPDATE `users` SET `newsread` = 0;';
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `news` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `message` text COLLATE utf8_bin NOT NULL,
                    `title` text COLLATE utf8_bin NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `date_created` int(11) unsigned NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case '2.4.49':
                // table: users
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `server_id` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `autoalert` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `invited_by` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `nids_sid` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `termsaccepted` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `role_id` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `change_pw` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `contactalert` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` ALTER COLUMN `disabled` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `users` MODIFY `authkey` varchar(40) COLLATE utf8_bin DEFAULT NULL;";
                $sqlArray[] = "ALTER TABLE `users` MODIFY `gpgkey` longtext COLLATE utf8_bin;";
                // table: events
                $sqlArray[] = "ALTER TABLE `events` ALTER COLUMN `publish_timestamp` SET DEFAULT 0;";
                // table: jobs
                $sqlArray[] = "ALTER TABLE `jobs` ALTER COLUMN `org_id` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `jobs` MODIFY `process_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;";
                // table: organisations
                $sqlArray[] = "ALTER TABLE `organisations` ALTER COLUMN `created_by` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `organisations` MODIFY `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL;"; // https://github.com/MISP/MISP/pull/1260
                // table: logs
                $sqlArray[] = "ALTER TABLE `logs` MODIFY `title` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;";
                $sqlArray[] = "ALTER TABLE `logs` MODIFY `change` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;";
                $sqlArray[] = "ALTER TABLE `logs` MODIFY `description` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;";
                // table: servers
                $sqlArray[] = "ALTER TABLE `servers` DROP `lastfetchedid`;"; // git commit hash d4c393897e8666fbbf04443a97d60c508700f5b4
                $sqlArray[] = "ALTER TABLE `servers` MODIFY `cert_file` varchar(255) COLLATE utf8_bin DEFAULT NULL;";
                // table: feeds
                $sqlArray[] = "ALTER TABLE `feeds` ALTER COLUMN `sharing_group_id` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `feeds` ALTER COLUMN `tag_id` SET DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `feeds` MODIFY `rules` text COLLATE utf8_bin DEFAULT NULL;";
                // DB changes to support https://github.com/MISP/MISP/pull/1334
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_delegate` tinyint(1) NOT NULL DEFAULT 0 AFTER `perm_publish`;";
                $sqlArray[] = "UPDATE `roles` SET `perm_delegate` = 1 WHERE `perm_publish` = 1;";
                // DB changes to solve https://github.com/MISP/MISP/issues/1354
                $sqlArray[] = "ALTER TABLE `taxonomy_entries` MODIFY `expanded` text COLLATE utf8_bin;";
                $sqlArray[] = "ALTER TABLE `taxonomy_predicates` MODIFY `expanded` text COLLATE utf8_bin;";
                // Sharing group propagate to instances freely setting
                $sqlArray[] = "ALTER TABLE `sharing_groups` ADD `roaming` tinyint(1) NOT NULL DEFAULT 0;";
                // table: shadow_attributes
                $sqlArray[] = "ALTER TABLE `shadow_attributes` MODIFY `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL;";
                // table: tasks
                $sqlArray[] = "ALTER TABLE `tasks` CHANGE `job_id` `process_id` varchar(32) DEFAULT NULL;";
                // Adding tag org restrictions
                $sqlArray[] = "ALTER TABLE `tags` ADD `org_id` int(11) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'ALTER TABLE `tags` ADD INDEX `org_id` (`org_id`);';
                $this->__dropIndex('tags', 'org_id');
                break;
            case '2.4.50':
                $sqlArray[] = 'ALTER TABLE `cake_sessions` ADD INDEX `expires` (`expires`);';
                $sqlArray[] = "ALTER TABLE `users` ADD `certif_public` longtext COLLATE utf8_bin AFTER `gpgkey`;";
                $sqlArray[] = "ALTER TABLE `servers` ADD `client_cert_file` varchar(255) COLLATE utf8_bin DEFAULT NULL;";
                $this->__dropIndex('cake_sessions', 'expires');
                break;
            case '2.4.51':
                $sqlArray[] = 'ALTER TABLE `servers` ADD `internal` tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE `roles` ADD `default_role` tinyint(1) NOT NULL DEFAULT 0;';
                break;
            case '2.4.52':
                $sqlArray[] = "ALTER TABLE feeds ADD source_format varchar(255) COLLATE utf8_bin DEFAULT 'misp';";
                $sqlArray[] = 'ALTER TABLE feeds ADD fixed_event tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds ADD delta_merge tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds ADD event_id int(11) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds ADD publish tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds ADD override_ids tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = "ALTER TABLE feeds ADD settings text NOT NULL DEFAULT '';";
                break;
            case '2.4.56':
                $sqlArray[] =
                    "CREATE TABLE IF NOT EXISTS galaxies (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(255) COLLATE utf8_bin NOT NULL,
                    `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
                    `type` varchar(255) COLLATE utf8_bin NOT NULL,
                    `description` text COLLATE utf8_bin NOT NULL,
                    `version` varchar(255) COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (id)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $this->__addIndex('galaxies', 'name');
                $this->__addIndex('galaxies', 'uuid');
                $this->__addIndex('galaxies', 'type');

                $sqlArray[] =
                    "CREATE TABLE IF NOT EXISTS galaxy_clusters (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(255) COLLATE utf8_bin NOT NULL,
                    `type` varchar(255) COLLATE utf8_bin NOT NULL,
                    `value` text COLLATE utf8_bin NOT NULL,
                    `tag_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
                    `description` text COLLATE utf8_bin NOT NULL,
                    `galaxy_id` int(11) NOT NULL,
                    `source` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
                    `authors` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (id)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $this->__addIndex('galaxy_clusters', 'value', 255);
                $this->__addIndex('galaxy_clusters', 'tag_name');
                $this->__addIndex('galaxy_clusters', 'uuid');
                $this->__addIndex('galaxy_clusters', 'type');

                $sqlArray[] =
                    "CREATE TABLE IF NOT EXISTS galaxy_elements (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `galaxy_cluster_id` int(11) NOT NULL,
                    `key` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
                    `value` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (id)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $this->__addIndex('galaxy_elements', 'key');
                $this->__addIndex('galaxy_elements', 'value', 255);

                $sqlArray[] =
                    "CREATE TABLE IF NOT EXISTS galaxy_reference (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `galaxy_cluster_id` int(11) NOT NULL,
                    `referenced_galaxy_cluster_id` int(11) NOT NULL,
                    `referenced_galaxy_cluster_uuid` varchar(255) COLLATE utf8_bin NOT NULL,
                    `referenced_galaxy_cluster_type` text COLLATE utf8_bin NOT NULL,
                    `referenced_galaxy_cluster_value` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $this->__addIndex('galaxy_reference', 'galaxy_cluster_id');
                $this->__addIndex('galaxy_reference', 'referenced_galaxy_cluster_id');
                $this->__addIndex('galaxy_reference', 'referenced_galaxy_cluster_value', 255);
                $this->__addIndex('galaxy_reference', 'referenced_galaxy_cluster_type', 255);

                break;
            case '2.4.57':
                $sqlArray[] = 'ALTER TABLE tags ADD hide_tag tinyint(1) NOT NULL DEFAULT 0;';
                // new indeces to match the changes in #1766
                $this->__dropIndex('correlations', '1_event_id');
                $this->__addIndex('correlations', '1_event_id');
                $this->__addIndex('warninglist_entries', 'warninglist_id');
                break;
            case '2.4.58':
                $sqlArray[] = "ALTER TABLE `events` ADD `disable_correlation` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `attributes` ADD `disable_correlation` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case '2.4.59':
                $sqlArray[] = "ALTER TABLE taxonomy_entries ADD colour varchar(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';";
                $sqlArray[] = "ALTER TABLE taxonomy_predicates ADD colour varchar(7) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '';";
                break;
            case '2.4.60':
                if ($dataSource == 'Database/Mysql' || $dataSource == 'Database/MysqlObserver') {
                    $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `attribute_tags` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `attribute_id` int(11) NOT NULL,
                                `event_id` int(11) NOT NULL,
                                `tag_id` int(11) NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
                    $sqlArray[] = 'ALTER TABLE `attribute_tags` ADD INDEX `attribute_id` (`attribute_id`);';
                    $sqlArray[] = 'ALTER TABLE `attribute_tags` ADD INDEX `event_id` (`event_id`);';
                    $sqlArray[] = 'ALTER TABLE `attribute_tags` ADD INDEX `tag_id` (`tag_id`);';
                } elseif ($dataSource == 'Database/Postgres') {
                    $sqlArray[] = 'CREATE TABLE IF NOT EXISTS attribute_tags (
                                id bigserial NOT NULL,
                                attribute_id bigint NOT NULL,
                                event_id bigint NOT NULL,
                                tag_id bigint NOT NULL,
                                PRIMARY KEY (id)
                            );';
                    $sqlArray[] = 'CREATE INDEX idx_attribute_tags_attribute_id ON attribute_tags (attribute_id);';
                    $sqlArray[] = 'CREATE INDEX idx_attribute_tags_event_id ON attribute_tags (event_id);';
                    $sqlArray[] = 'CREATE INDEX idx_attribute_tags_tag_id ON attribute_tags (tag_id);';
                }
                break;
            case '2.4.61':
                $sqlArray[] = 'ALTER TABLE feeds ADD input_source varchar(255) COLLATE utf8_bin NOT NULL DEFAULT "network";';
                $sqlArray[] = 'ALTER TABLE feeds ADD delete_local_file tinyint(1) DEFAULT 0;';
                $indexArray[] = array('feeds', 'input_source');
                break;
            case '2.4.62':
                $sqlArray[] = 'ALTER TABLE logs CHANGE `org` `org` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT "";';
                $sqlArray[] = 'ALTER TABLE logs CHANGE `email` `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT "";';
                $sqlArray[] = 'ALTER TABLE logs CHANGE `change` `change` text COLLATE utf8_bin NOT NULL DEFAULT "";';
                break;
            case '2.4.63':
                $sqlArray[] = 'ALTER TABLE events DROP COLUMN org;';
                $sqlArray[] = 'ALTER TABLE events DROP COLUMN orgc;';
                $sqlArray[] = 'ALTER TABLE event_blacklists CHANGE comment comment TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
                break;
            case '2.4.64':
                $indexArray[] = array('feeds', 'input_source');
                $indexArray[] = array('attributes', 'value1', 255);
                $indexArray[] = array('attributes', 'value2', 255);
                $indexArray[] = array('attributes', 'type');
                $indexArray[] = array('galaxy_reference', 'galaxy_cluster_id');
                $indexArray[] = array('galaxy_reference', 'referenced_galaxy_cluster_id');
                $indexArray[] = array('galaxy_reference', 'referenced_galaxy_cluster_value', 255);
                $indexArray[] = array('galaxy_reference', 'referenced_galaxy_cluster_type', 255);
                $indexArray[] = array('correlations', '1_event_id');
                $indexArray[] = array('warninglist_entries', 'warninglist_id');
                $indexArray[] = array('galaxy_clusters', 'value', 255);
                $indexArray[] = array('galaxy_clusters', 'tag_name');
                $indexArray[] = array('galaxy_clusters', 'uuid');
                $indexArray[] = array('galaxy_clusters', 'type');
                $indexArray[] = array('galaxies', 'name');
                $indexArray[] = array('galaxies', 'uuid');
                $indexArray[] = array('galaxies', 'type');
                break;
            case '2.4.65':
                $sqlArray[] = 'ALTER TABLE feeds CHANGE `enabled` `enabled` tinyint(1) DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds CHANGE `default` `default` tinyint(1) DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds CHANGE `distribution` `distribution` tinyint(4) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE feeds CHANGE `sharing_group_id` `sharing_group_id` int(11) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE attributes CHANGE `comment` `comment` text COLLATE utf8_bin;';
                break;
            case '2.4.66':
                $sqlArray[] = 'ALTER TABLE shadow_attributes CHANGE old_id old_id int(11) DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE sightings ADD COLUMN uuid varchar(255) COLLATE utf8_bin DEFAULT "";';
                $sqlArray[] = 'ALTER TABLE sightings ADD COLUMN source varchar(255) COLLATE utf8_bin DEFAULT "";';
                $sqlArray[] = 'ALTER TABLE sightings ADD COLUMN type int(11) DEFAULT 0;';
                $indexArray[] = array('sightings', 'uuid');
                $indexArray[] = array('sightings', 'source');
                $indexArray[] = array('sightings', 'type');
                $indexArray[] = array('attributes', 'category');
                $indexArray[] = array('shadow_attributes', 'category');
                $indexArray[] = array('shadow_attributes', 'type');
                break;
            case '2.4.67':
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_sighting` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'UPDATE `roles` SET `perm_sighting` = 1 WHERE `perm_add` = 1;';
                break;
            case '2.4.68':
                $sqlArray[] = 'ALTER TABLE events CHANGE attribute_count attribute_count int(11) unsigned DEFAULT 0;';
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `event_blacklists` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `event_uuid` varchar(40) COLLATE utf8_bin NOT NULL,
                  `created` datetime NOT NULL,
                  `event_info` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                  `event_orgc` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
                $indexArray[] = array('event_blacklists', 'event_uuid');
                $indexArray[] = array('event_blacklists', 'event_orgc');
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `org_blacklists` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `org_uuid` varchar(40) COLLATE utf8_bin NOT NULL,
                  `created` datetime NOT NULL,
                  `org_name` varchar(255) COLLATE utf8_bin NOT NULL,
                  `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                  PRIMARY KEY (`id`),
                  INDEX `org_uuid` (`org_uuid`),
                  INDEX `org_name` (`org_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;';
                $indexArray[] = array('org_blacklists', 'org_uuid');
                $indexArray[] = array('org_blacklists', 'org_name');
                $sqlArray[] = "ALTER TABLE shadow_attributes CHANGE proposal_to_delete proposal_to_delete BOOLEAN DEFAULT 0";
                $sqlArray[] = "ALTER TABLE taxonomy_predicates CHANGE colour colour varchar(7) CHARACTER SET utf8 COLLATE utf8_bin;";
                $sqlArray[] = "ALTER TABLE taxonomy_entries CHANGE colour colour varchar(7) CHARACTER SET utf8 COLLATE utf8_bin;";
                break;
            case '2.4.69':
                $sqlArray[] = "ALTER TABLE taxonomy_entries CHANGE colour colour varchar(7) CHARACTER SET utf8 COLLATE utf8_bin;";
                $sqlArray[] = "ALTER TABLE users ADD COLUMN date_created bigint(20);";
                $sqlArray[] = "ALTER TABLE users ADD COLUMN date_modified bigint(20);";
                break;
            case '2.4.71':
                $sqlArray[] = "UPDATE attributes SET comment = '' WHERE comment is NULL;";
                $sqlArray[] = "ALTER TABLE attributes CHANGE comment comment text COLLATE utf8_bin NOT NULL;";
                break;
            case '2.4.72':
                $sqlArray[] = 'ALTER TABLE feeds ADD lookup_visible tinyint(1) DEFAULT 0;';
                break;
            case '2.4.73':
                $sqlArray[] = 'ALTER TABLE `servers` ADD `unpublish_event` tinyint(1) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE `servers` ADD `publish_without_email` tinyint(1) NOT NULL DEFAULT 0;';
                break;
            case '2.4.75':
                $this->__dropIndex('attributes', 'value1');
                $this->__dropIndex('attributes', 'value2');
                $this->__addIndex('attributes', 'value1', 255);
                $this->__addIndex('attributes', 'value2', 255);
                break;
            case '2.4.77':
                $sqlArray[] = 'ALTER TABLE `users` CHANGE `password` `password` VARCHAR(255) COLLATE utf8_bin NOT NULL;';
                break;
            case '2.4.78':
                $sqlArray[] = "ALTER TABLE galaxy_clusters ADD COLUMN version int(11) DEFAULT 0;";
                $this->__addIndex('galaxy_clusters', 'version');
                $this->__addIndex('galaxy_clusters', 'galaxy_id');
                $this->__addIndex('galaxy_elements', 'galaxy_cluster_id');
                break;
            case '2.4.80':
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS objects (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `meta-category` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `template_uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `template_version` int(11) NOT NULL,
                    `event_id` int(11) NOT NULL,
                    `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    `distribution` tinyint(4) NOT NULL DEFAULT 0,
                    `sharing_group_id` int(11),
                    `comment` text COLLATE utf8_bin NOT NULL,
                    `deleted` TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `name` (`name`),
                    INDEX `template_uuid` (`template_uuid`),
                    INDEX `template_version` (`template_version`),
                    INDEX `meta-category` (`meta-category`),
                    INDEX `event_id` (`event_id`),
                    INDEX `uuid` (`uuid`),
                    INDEX `timestamp` (`timestamp`),
                    INDEX `distribution` (`distribution`),
                    INDEX `sharing_group_id` (`sharing_group_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS object_references (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    `object_id` int(11) NOT NULL,
                    `event_id` int(11) NOT NULL,
                    `object_uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `referenced_uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `referenced_id` int(11) NOT NULL,
                    `referenced_type` int(11) NOT NULL DEFAULT 0,
                    `relationship_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `comment` text COLLATE utf8_bin NOT NULL,
                    `deleted` TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `object_uuid` (`object_uuid`),
                  INDEX `referenced_uuid` (`referenced_uuid`),
                  INDEX `timestamp` (`timestamp`),
                  INDEX `object_id` (`object_id`),
                  INDEX `referenced_id` (`referenced_id`),
                  INDEX `relationship_type` (`relationship_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS object_relationships (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `version` int(11) NOT NULL,
                    `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `description` text COLLATE utf8_bin NOT NULL,
                    `format` text COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


                $sqlArray[] = "CREATE TABLE IF NOT EXISTS object_templates (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `org_id` int(11) NOT NULL,
                    `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `meta-category` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `description` text COLLATE utf8_bin,
                    `version` int(11) NOT NULL,
                    `requirements` text COLLATE utf8_bin,
                    `fixed` tinyint(1) NOT NULL DEFAULT 0,
                    `active` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `user_id` (`user_id`),
                    INDEX `org_id` (`org_id`),
                    INDEX `uuid` (`uuid`),
                    INDEX `name` (`name`),
                    INDEX `meta-category` (`meta-category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS object_template_elements (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `object_template_id` int(11) NOT NULL,
                    `object_relation` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `ui-priority` int(11) NOT NULL,
                    `categories` text COLLATE utf8_bin,
                    `sane_default` text COLLATE utf8_bin,
                    `values_list` text COLLATE utf8_bin,
                    `description` text COLLATE utf8_bin,
                    `disable_correlation` tinyint(1) NOT NULL DEFAULT 0,
                    `multiple` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `object_relation` (`object_relation`),
                    INDEX `type` (`type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                $sqlArray[] = 'ALTER TABLE `logs` CHANGE `model` `model` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;';
                $sqlArray[] = 'ALTER TABLE `logs` CHANGE `action` `action` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;';

                $sqlArray[] = 'ALTER TABLE attributes ADD object_id int(11) NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE attributes ADD object_relation varchar(255) COLLATE utf8_bin;';

                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_object_template` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'UPDATE `roles` SET `perm_object_template` = 1 WHERE `perm_site_admin` = 1;';

                $indexArray[] = array('attributes', 'object_id');
                $indexArray[] = array('attributes', 'object_relation');
                break;
            case '2.4.81':
                $sqlArray[] = 'ALTER TABLE `galaxy_clusters` ADD `version` INT NOT NULL DEFAULT 0;';
                $sqlArray[] = 'ALTER TABLE `galaxies` ADD `icon` VARCHAR(255) COLLATE utf8_bin DEFAULT "";';
                break;
            case '2.4.82':
                $sqlArray[] = "ALTER TABLE organisations ADD restricted_to_domain text COLLATE utf8_bin;";
                break;
            case '2.4.83':
                $sqlArray[] = "ALTER TABLE object_template_elements CHANGE `disable_correlation` `disable_correlation` text COLLATE utf8_bin;";
                break;
            case '2.4.84':
                $sqlArray[] = "ALTER TABLE `tags` ADD `user_id` int(11) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'ALTER TABLE `tags` ADD INDEX `user_id` (`user_id`);';
                break;
            case '2.4.85':
                $sqlArray[] = "ALTER TABLE `shadow_attributes` ADD `disable_correlation` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE object_template_elements CHANGE `disable_correlation` `disable_correlation` text COLLATE utf8_bin;";
                // yes, this may look stupid as hell to index a boolean flag - but thanks to the stupidity of MySQL/MariaDB this will
                // stop blocking other indexes to be used in queries where we also tests for the deleted flag.
                $indexArray[] = array('attributes', 'deleted');
                break;
            case '2.4.86':
                break;
            case '2.4.87':
                $sqlArray[] = "ALTER TABLE `feeds` ADD `headers` TEXT COLLATE utf8_bin;";
                break;
            case 1:
                $sqlArray[] = "ALTER TABLE `tags` ADD `user_id` int(11) NOT NULL DEFAULT 0;";
                $sqlArray[] = 'ALTER TABLE `tags` ADD INDEX `user_id` (`user_id`);';
                break;
            case 2:
            // rerun missing db entries
                $sqlArray[] = "ALTER TABLE users ADD COLUMN date_created bigint(20);";
                $sqlArray[] = "ALTER TABLE users ADD COLUMN date_modified bigint(20);";
                break;
            case 3:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `fuzzy_correlate_ssdeep` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
                                            `chunk` varchar(12) NOT NULL,
                                            `attribute_id` int(11) NOT NULL,
                                            PRIMARY KEY (`id`)
                                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $this->__addIndex('fuzzy_correlate_ssdeep', 'chunk');
                $this->__addIndex('fuzzy_correlate_ssdeep', 'attribute_id');
                break;
            case 4:
                $sqlArray[] = 'ALTER TABLE `roles` ADD `memory_limit` VARCHAR(255) COLLATE utf8_bin DEFAULT "";';
                $sqlArray[] = 'ALTER TABLE `roles` ADD `max_execution_time` VARCHAR(255) COLLATE utf8_bin DEFAULT "";';
                $sqlArray[] = "ALTER TABLE `roles` ADD `restricted_to_site_admin` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 5:
                $sqlArray[] = "ALTER TABLE `feeds` ADD `caching_enabled` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 6:
                $sqlArray[] = "ALTER TABLE `events` ADD `extends_uuid` varchar(40) COLLATE utf8_bin DEFAULT '';";
                $indexArray[] = array('events', 'extends_uuid');
                break;
            case 7:
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `noticelists` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                        `expanded_name` text COLLATE utf8_unicode_ci NOT NULL,
                        `ref` text COLLATE utf8_unicode_ci,
                        `geographical_area` varchar(255) COLLATE utf8_unicode_ci,
                        `version` int(11) NOT NULL DEFAULT 1,
                        `enabled` tinyint(1) NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id`),
                        INDEX `name` (`name`),
                        INDEX `geographical_area` (`geographical_area`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
                $sqlArray[] = 'CREATE TABLE IF NOT EXISTS `noticelist_entries` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `noticelist_id` int(11) NOT NULL,
                        `data` text COLLATE utf8_unicode_ci NOT NULL,
                        PRIMARY KEY (`id`),
                        INDEX `noticelist_id` (`noticelist_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            break;
            case 9:
                $sqlArray[] = 'ALTER TABLE galaxies ADD namespace varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "misp";';
                $indexArray[] = array('galaxies', 'namespace');
                break;
            case 10:
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_publish_zmq` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 11:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS event_locks (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `event_id` (`event_id`),
                    INDEX `user_id` (`user_id`),
                    INDEX `timestamp` (`timestamp`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 12:
                $sqlArray[] = "ALTER TABLE `servers` ADD `skip_proxy` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 13:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS event_graph (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `org_id` int(11) NOT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    `network_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `network_json` MEDIUMTEXT NOT NULL,
                    `preview_img` MEDIUMTEXT,
                    PRIMARY KEY (id),
                    INDEX `event_id` (`event_id`),
                    INDEX `user_id` (`user_id`),
                    INDEX `org_id` (`org_id`),
                    INDEX `timestamp` (`timestamp`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 14:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `user_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `setting` varchar(255) COLLATE utf8_bin NOT NULL,
                    `value` text COLLATE utf8_bin NOT NULL,
                    `user_id` int(11) NOT NULL,
                    INDEX `setting` (`setting`),
                    INDEX `user_id` (`user_id`),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 15:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS event_graph (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `org_id` int(11) NOT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    `network_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `network_json` MEDIUMTEXT NOT NULL,
                    `preview_img` MEDIUMTEXT,
                    PRIMARY KEY (id),
                    INDEX `event_id` (`event_id`),
                    INDEX `user_id` (`user_id`),
                    INDEX `org_id` (`org_id`),
                    INDEX `timestamp` (`timestamp`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 18:
                $sqlArray[] = 'ALTER TABLE `taxonomy_predicates` ADD COLUMN description text CHARACTER SET UTF8 collate utf8_bin;';
                $sqlArray[] = 'ALTER TABLE `taxonomy_entries` ADD COLUMN description text CHARACTER SET UTF8 collate utf8_bin;';
                $sqlArray[] = 'ALTER TABLE `taxonomy_predicates` ADD COLUMN exclusive tinyint(1) DEFAULT 0;';
                break;
            case 19:
                $sqlArray[] = 'ALTER TABLE `taxonomies` ADD COLUMN exclusive tinyint(1) DEFAULT 0;';
                break;
            case 20:
                $sqlArray[] = "ALTER TABLE `servers` ADD `skip_proxy` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 21:
                $sqlArray[] = 'ALTER TABLE `tags` ADD COLUMN numerical_value int(11) NULL;';
                $sqlArray[] = 'ALTER TABLE `taxonomy_predicates` ADD COLUMN numerical_value int(11) NULL;';
                $sqlArray[] = 'ALTER TABLE `taxonomy_entries` ADD COLUMN numerical_value int(11) NULL;';
                break;
            case 22:
                $sqlArray[] = 'ALTER TABLE `object_references` MODIFY `deleted` tinyint(1) NOT NULL default 0;';
                break;
            case 24:
                $this->GalaxyCluster = ClassRegistry::init('GalaxyCluster');
                if (empty($this->GalaxyCluster->schema('collection_uuid'))) {
                    $sqlArray[] = 'ALTER TABLE `galaxy_clusters` CHANGE `uuid` `collection_uuid` varchar(255) COLLATE utf8_bin NOT NULL;';
                    $sqlArray[] = 'ALTER TABLE `galaxy_clusters` ADD COLUMN `uuid` varchar(255) COLLATE utf8_bin NOT NULL default \'\';';
                }
                break;
            case 25:
                $this->__dropIndex('galaxy_clusters', 'uuid');
                $this->__addIndex('galaxy_clusters', 'uuid');
                $this->__addIndex('galaxy_clusters', 'collection_uuid');
                break;
            case 26:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS tag_collections (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `user_id` int(11) NOT NULL,
                    `org_id` int(11) NOT NULL,
                    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `all_orgs` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `uuid` (`uuid`),
                    INDEX `user_id` (`user_id`),
                    INDEX `org_id` (`org_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS tag_collection_tags (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `tag_collection_id` int(11) NOT NULL,
                    `tag_id` int(11) NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `uuid` (`tag_collection_id`),
                    INDEX `user_id` (`tag_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 27:
                $sqlArray[] = 'ALTER TABLE `tags` CHANGE `org_id` `org_id` int(11) NOT NULL DEFAULT 0;';
                break;
            case 28:
                $sqlArray[] = "ALTER TABLE `servers` ADD `caching_enabled` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 29:
                $sqlArray[] = "ALTER TABLE `galaxies` ADD `kill_chain_order` text NOT NULL;";
                break;
            case 30:
                $sqlArray[] = "ALTER TABLE `galaxies` MODIFY COLUMN `kill_chain_order` text";
                $sqlArray[] = "ALTER TABLE `feeds` ADD `force_to_ids` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 31:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `rest_client_histories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `org_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `headers` text,
                    `body` text,
                    `url` text,
                    `http_method` varchar(255),
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    `use_full_path` tinyint(1) DEFAULT 0,
                    `show_result` tinyint(1) DEFAULT 0,
                    `skip_ssl` tinyint(1) DEFAULT 0,
                    `outcome` int(11) NOT NULL,
                    `bookmark` tinyint(1) NOT NULL DEFAUlT 0,
                    `bookmark_name` varchar(255) NULL DEFAULT '',
                    PRIMARY KEY (`id`),
                    KEY `org_id` (`org_id`),
                    KEY `user_id` (`user_id`),
                    KEY `timestamp` (`timestamp`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                break;
            case 32:
                $sqlArray[] = "ALTER TABLE `taxonomies` ADD `required` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 33:
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_publish_kafka` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 35:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `notification_logs` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `org_id` int(11) NOT NULL,
                    `type` varchar(255) COLLATE utf8_bin NOT NULL,
                    `timestamp` int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `org_id` (`org_id`),
                    KEY `type` (`type`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
                    break;
            case 36:
                $sqlArray[] = "ALTER TABLE `event_tags` ADD `local` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `attribute_tags` ADD `local` tinyint(1) NOT NULL DEFAULT 0;";
                break;
            case 37:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS decaying_models (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin DEFAULT NULL,
                    `name` varchar(255) COLLATE utf8_bin NOT NULL,
                    `parameters` text,
                    `attribute_types` text,
                    `description` text,
                    `org_id` int(11),
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `all_orgs` tinyint(1) NOT NULL DEFAULT 1,
                    `ref` text COLLATE utf8_unicode_ci,
                    `formula` varchar(255) COLLATE utf8_bin NOT NULL,
                    `version` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
                    `default` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `uuid` (`uuid`),
                    INDEX `name` (`name`),
                    INDEX `org_id` (`org_id`),
                    INDEX `enabled` (`enabled`),
                    INDEX `all_orgs` (`all_orgs`),
                    INDEX `version` (`version`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS decaying_model_mappings (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `attribute_type` varchar(255) COLLATE utf8_bin NOT NULL,
                    `model_id` int(11) NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `model_id` (`model_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_decaying` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "UPDATE `roles` SET `perm_decaying`=1 WHERE `perm_sighting`=1;";
                break;
            case 38:
                $sqlArray[] = "ALTER TABLE servers ADD  priority int(11) NOT NULL DEFAULT 0;";
                $indexArray[] = array('servers', 'priority');
                break;
            case 39:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS user_settings (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `setting` varchar(255) COLLATE utf8_bin NOT NULL,
                    `value` text,
                    `user_id` int(11) NOT NULL,
                    `timestamp` int(11) NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `key` (`key`),
                    INDEX `user_id` (`user_id`),
                    INDEX `timestamp` (`timestamp`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                break;
            case 40:
                $sqlArray[] = "ALTER TABLE `user_settings` ADD `timestamp` int(11) NOT NULL;";
                $indexArray[] = array('user_settings', 'timestamp');
                break;
            case 41:
                $sqlArray[] = "ALTER TABLE `roles` ADD `enforce_rate_limit` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `roles` ADD `rate_limit_count` int(11) NOT NULL DEFAULT 0;";
                break;
            case 42:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS sightingdbs (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `description` text,
                    `owner` varchar(255) DEFAULT '',
                    `host` varchar(255) DEFAULT 'http://localhost',
                    `port` int(11) DEFAULT 9999,
                    `timestamp` int(11) NOT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `skip_proxy` tinyint(1) NOT NULL DEFAULT 0,
                    `ssl_skip_verification` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    INDEX `name` (`name`),
                    INDEX `owner` (`owner`),
                    INDEX `host` (`host`),
                    INDEX `port` (`port`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS sightingdb_orgs (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `sightingdb_id` int(11) NOT NULL,
                    `org_id` int(11) NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `sightingdb_id` (`sightingdb_id`),
                    INDEX `org_id` (`org_id`)
                ) ENGINE=InnoDB;";
                break;
            case 43:
                $sqlArray[] = "ALTER TABLE sightingdbs ADD namespace varchar(255) DEFAULT '';";
                break;
            case 44:
                $sqlArray[] = "ALTER TABLE object_template_elements CHANGE `disable_correlation` `disable_correlation` tinyint(1);";
                break;
            case 45:
                $sqlArray[] = "ALTER TABLE `events` ADD `sighting_timestamp` int(11) NOT NULL DEFAULT 0 AFTER `publish_timestamp`;";
                $sqlArray[] = "ALTER TABLE `servers` ADD `push_sightings` tinyint(1) NOT NULL DEFAULT 0 AFTER `pull`;";
                break;
            case 47:
                $this->__addIndex('tags', 'numerical_value');
                $this->__addIndex('taxonomy_predicates', 'numerical_value');
                $this->__addIndex('taxonomy_entries', 'numerical_value');
                break;
            case 49:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS dashboards (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin NOT NULL,
                    `name` varchar(191) NOT NULL,
                    `description` text,
                    `default` tinyint(1) NOT NULL DEFAULT 0,
                    `selectable` tinyint(1) NOT NULL DEFAULT 0,
                    `user_id` int(11) NOT NULL DEFAULT 0,
                    `restrict_to_org_id` int(11) NOT NULL DEFAULT 0,
                    `restrict_to_role_id` int(11) NOT NULL DEFAULT 0,
                    `restrict_to_permission_flag` varchar(191) NOT NULL DEFAULT '',
                    `value` text,
                    `timestamp` int(11) NOT NULL,
                    PRIMARY KEY (id),
                    INDEX `name` (`name`),
                    INDEX `uuid` (`uuid`),
                    INDEX `user_id` (`user_id`),
                    INDEX `restrict_to_org_id` (`restrict_to_org_id`),
                    INDEX `restrict_to_permission_flag` (`restrict_to_permission_flag`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                break;
            case 50:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS inbox (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin NOT NULL,
                    `title` varchar(191) NOT NULL,
                    `type` varchar(191) NOT NULL,
                    `ip` varchar(191) NOT NULL,
                    `user_agent` text,
                    `user_agent_sha256` varchar(64) NOT NULL,
                    `comment` text,
                    `deleted` tinyint(1) NOT NULL DEFAULT 0,
                    `timestamp` int(11) NOT NULL,
                    `store_as_file` tinyint(1) NOT NULL DEFAULT 0,
                    `data` longtext,
                    PRIMARY KEY (id),
                    INDEX `title` (`title`),
                    INDEX `type` (`type`),
                    INDEX `uuid` (`uuid`),
                    INDEX `user_agent_sha256` (`user_agent_sha256`),
                    INDEX `ip` (`ip`),
                    INDEX `timestamp` (`timestamp`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                break;
            case 51:
                $sqlArray[] = "ALTER TABLE `feeds` ADD `orgc_id` int(11) NOT NULL DEFAULT 0";
                $indexArray[] = array('feeds', 'orgc_id');
                break;
            case 52:
                if (!empty($this->query("SHOW COLUMNS FROM `admin_settings` LIKE 'key';"))) {
                    $sqlArray[] = "ALTER TABLE admin_settings CHANGE `key` `setting` varchar(255) COLLATE utf8_bin NOT NULL;";
                    $indexArray[] = array('admin_settings', 'setting');
                }
                break;
            case 53:
                if (!empty($this->query("SHOW COLUMNS FROM `user_settings` LIKE 'key';"))) {
                    $sqlArray[] = "ALTER TABLE user_settings CHANGE `key` `setting` varchar(255) COLLATE utf8_bin NOT NULL;";
                    $indexArray[] = array('user_settings', 'setting');
                }
                break;
            case 54:
                $sqlArray[] = "ALTER TABLE `sightingdbs` MODIFY `timestamp` int(11) NOT NULL DEFAULT 0;";
                break;
            case 55:
                // index is not used in any SQL query
                $this->__dropIndex('correlations', 'value');
                // these index can be theoretically used, but probably just in very rare occasion
                $this->__dropIndex('correlations', 'org_id');
                $this->__dropIndex('correlations', 'sharing_group_id');
                $this->__dropIndex('correlations', 'a_sharing_group_id');
                break;
            case 56:
                //rename tables
                $sqlArray[] = "RENAME TABLE `org_blacklists` TO `org_blocklists`;";
                $sqlArray[] = "RENAME TABLE `event_blacklists` TO `event_blocklists`;";
                $sqlArray[] = "RENAME TABLE `whitelist` TO `allowedlist`;";
                break;
            case 57:
                $sqlArray[] = sprintf("INSERT INTO `admin_settings` (`setting`, `value`) VALUES ('fix_login', %s);", time());
                break;
            case 58:
                $sqlArray[] = "ALTER TABLE `warninglists` MODIFY COLUMN `warninglist_entry_count` int(11) unsigned NOT NULL DEFAULT 0;";
                break;
            case 59:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS event_reports (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8_bin NOT NULL ,
                    `event_id` int(11) NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `content` text,
                    `distribution` tinyint(4) NOT NULL DEFAULT 0,
                    `sharing_group_id` int(11),
                    `timestamp` int(11) NOT NULL,
                    `deleted` tinyint(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (id),
                    CONSTRAINT u_uuid UNIQUE (uuid),
                    INDEX `name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                break;
            case 60:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `attachment_scans` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `type` varchar(40) COLLATE utf8_bin NOT NULL,
                    `attribute_id` int(11) NOT NULL,
                    `infected` tinyint(1) NOT NULL,
                    `malware_name`  varchar(191) NULL,
                    `timestamp` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `index` (`type`, `attribute_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                break;
            case 61:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `auth_keys` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `uuid` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `authkey` varchar(72) CHARACTER SET ascii DEFAULT NULL,
                    `authkey_start` varchar(4) CHARACTER SET ascii DEFAULT NULL,
                    `authkey_end` varchar(4) CHARACTER SET ascii DEFAULT NULL,
                    `created` int(10) unsigned NOT NULL,
                    `expiration` int(10) unsigned NOT NULL,
                    `user_id` int(10) unsigned NOT NULL,
                    `comment` text COLLATE utf8mb4_unicode_ci,
                    PRIMARY KEY (`id`),
                    KEY `authkey_start` (`authkey_start`),
                    KEY `authkey_end` (`authkey_end`),
                    KEY `created` (`created`),
                    KEY `expiration` (`expiration`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                break;
            case 62:
                $sqlArray[] = "ALTER TABLE `auth_keys` MODIFY COLUMN `authkey` varchar(72) CHARACTER SET ascii NOT NULL";
                $sqlArray[] = "ALTER TABLE `auth_keys` MODIFY COLUMN `authkey_start` varchar(4) CHARACTER SET ascii NOT NULL";
                $sqlArray[] = "ALTER TABLE `auth_keys` MODIFY COLUMN `authkey_end` varchar(4) CHARACTER SET ascii NOT NULL";
                $sqlArray[] = "ALTER TABLE `auth_keys` MODIFY COLUMN `comment` text COLLATE utf8mb4_unicode_ci";
                $sqlArray[] = "ALTER TABLE `attachment_scans` MODIFY COLUMN `malware_name` varchar(191) NULL";
                break;
            case 63:
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `distribution` tinyint(4) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `sharing_group_id` int(11);";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `org_id` int(11) NOT NULL;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `orgc_id` int(11) NOT NULL;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `default` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `locked` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `extends_uuid` varchar(40) COLLATE utf8_bin DEFAULT '';";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `extends_version` int(11) DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `published` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` ADD `deleted` TINYINT(1) NOT NULL DEFAULT 0";
                $sqlArray[] = "ALTER TABLE `roles` ADD `perm_galaxy_editor` tinyint(1) NOT NULL DEFAULT 0;";

                $sqlArray[] = "UPDATE `roles` SET `perm_galaxy_editor`=1 WHERE `perm_tag_editor`=1;";
                $sqlArray[] = "UPDATE `galaxy_clusters` SET `distribution`=3, `default`=1 WHERE `org_id`=0;";

                $sqlArray[] = "ALTER TABLE `galaxy_reference` RENAME `galaxy_cluster_relations`;";
                $sqlArray[] = "ALTER TABLE `galaxy_cluster_relations` ADD `galaxy_cluster_uuid` varchar(40) COLLATE utf8_bin NOT NULL;";
                $sqlArray[] = "ALTER TABLE `galaxy_cluster_relations` ADD `distribution` tinyint(4) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_cluster_relations` ADD `sharing_group_id` int(11);";
                $sqlArray[] = "ALTER TABLE `galaxy_cluster_relations` ADD `default` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `galaxy_cluster_relations` DROP COLUMN `referenced_galaxy_cluster_value`;";
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `galaxy_cluster_relation_tags` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `galaxy_cluster_relation_id` int(11) NOT NULL,
                    `tag_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                $sqlArray[] = "ALTER TABLE `tags` ADD `is_galaxy` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "ALTER TABLE `tags` ADD `is_custom_galaxy` tinyint(1) NOT NULL DEFAULT 0;";
                $sqlArray[] = "UPDATE `tags` SET `is_galaxy`=1 WHERE `name` LIKE 'misp-galaxy:%';";
                $sqlArray[] = "UPDATE `tags` SET `is_custom_galaxy`=1 WHERE `name` REGEXP '^misp-galaxy:[^:=\"]+=\"[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}\"$';";

                $sqlArray[] = "ALTER TABLE `servers` ADD `push_galaxy_clusters` tinyint(1) NOT NULL DEFAULT 0 AFTER `push_sightings`;";
                $sqlArray[] = "ALTER TABLE `servers` ADD `pull_galaxy_clusters` tinyint(1) NOT NULL DEFAULT 0 AFTER `push_galaxy_clusters`;";

                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `galaxy_cluster_blocklists` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `cluster_uuid` varchar(40) COLLATE utf8_bin NOT NULL,
                    `created` datetime NOT NULL,
                    `cluster_info` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                    `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                    `cluster_orgc` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

                $indexArray[] = array('galaxy_clusters', 'org_id');
                $indexArray[] = array('galaxy_clusters', 'orgc_id');
                $indexArray[] = array('galaxy_clusters', 'sharing_group_id');
                $indexArray[] = array('galaxy_clusters', 'extends_uuid');
                $indexArray[] = array('galaxy_clusters', 'extends_version');
                $indexArray[] = array('galaxy_clusters', 'default');
                $indexArray[] = array('galaxy_cluster_relations', 'galaxy_cluster_uuid');
                $indexArray[] = array('galaxy_cluster_relations', 'sharing_group_id');
                $indexArray[] = array('galaxy_cluster_relations', 'default');
                $indexArray[] = array('galaxy_cluster_relation_tags', 'galaxy_cluster_relation_id');
                $indexArray[] = array('galaxy_cluster_relation_tags', 'tag_id');
                $indexArray[] = array('galaxy_cluster_blocklists', 'cluster_uuid');
                $indexArray[] = array('galaxy_cluster_blocklists', 'cluster_orgc');
                break;
            case 64:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `cerebrates` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(191) NOT NULL,
                    `url` varchar(255) NOT NULL,
                    `authkey` varchar(40) CHARACTER SET ascii COLLATE ascii_general_ci NULL,
                    `open` tinyint(1) DEFAULT 0,
                    `org_id` int(11) NOT NULL,
                    `pull_orgs` tinyint(1) DEFAULT 0,
                    `pull_sharing_groups` tinyint(1) DEFAULT 0,
                    `self_signed` tinyint(1) DEFAULT 0,
                    `cert_file` varchar(255) DEFAULT NULL,
                    `client_cert_file` varchar(255) DEFAULT NULL,
                    `internal` tinyint(1) NOT NULL DEFAULT 0,
                    `skip_proxy` tinyint(1) NOT NULL DEFAULT 0,
                    `description` text,
                    PRIMARY KEY (`id`),
                    KEY `url` (`url`),
                    KEY `org_id` (`org_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                break;
            case 65:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `correlation_exclusions` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `value` text NOT NULL,
                    `from_json` tinyint(1) default 0,
                    PRIMARY KEY (`id`),
                    INDEX `value` (`value`(255))
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                break;
            case 66:
                $sqlArray[] = "ALTER TABLE `galaxy_clusters` MODIFY COLUMN `tag_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';";
                $indexArray[] = ['event_reports', 'event_id'];
                break;
            case 67:
                $sqlArray[] = "ALTER TABLE `auth_keys` ADD `allowed_ips` text DEFAULT NULL;";
                break;
            case 68:
                $sqlArray[] = "ALTER TABLE `correlation_exclusions` ADD `comment` text DEFAULT NULL;";
                break;
            case 69:
                $sqlArray[] = "CREATE TABLE IF NOT EXISTS `audit_logs` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `created` datetime NOT NULL,
                      `user_id` int(11) NOT NULL,
                      `org_id` int(11) NOT NULL,
                      `authkey_id` int(11) DEFAULT NULL,
                      `ip` varbinary(16) DEFAULT NULL,
                      `request_type` tinyint NOT NULL,
                      `request_id` varchar(255) DEFAULT NULL,
                      `action` varchar(20) NOT NULL,
                      `model` varchar(80) NOT NULL,
                      `model_id` int(11) NOT NULL,
                      `model_title` text DEFAULT NULL,
                      `event_id` int(11) NULL,
                      `change` blob,
                      PRIMARY KEY (`id`),
                      INDEX `event_id` (`event_id`),
                      INDEX `model_id` (`model_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                break;
            case 'fixNonEmptySharingGroupID':
                $sqlArray[] = 'UPDATE `events` SET `sharing_group_id` = 0 WHERE `distribution` != 4;';
                $sqlArray[] = 'UPDATE `attributes` SET `sharing_group_id` = 0 WHERE `distribution` != 4;';
                break;
            case 'cleanupAfterUpgrade':
                $sqlArray[] = 'ALTER TABLE `events` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `events` DROP `orgc`;';
                $sqlArray[] = 'ALTER TABLE `correlations` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `jobs` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `servers` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `servers` DROP `organization`;';
                $sqlArray[] = 'ALTER TABLE `shadow_attributes` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `shadow_attributes` DROP `event_org`;';
                $sqlArray[] = 'ALTER TABLE `threads` DROP `org`;';
                $sqlArray[] = 'ALTER TABLE `users` DROP `org`;';
                break;
            case 'seenOnAttributeAndObject':
                $sqlArray[] =
                    "ALTER TABLE `attributes`
                        DROP INDEX uuid,
                        DROP INDEX event_id,
                        DROP INDEX sharing_group_id,
                        DROP INDEX type,
                        DROP INDEX category,
                        DROP INDEX value1,
                        DROP INDEX value2,
                        DROP INDEX object_id,
                        DROP INDEX object_relation;
                    ";
                $sqlArray[] = "ALTER TABLE `attributes` DROP INDEX deleted"; // deleted index may not be present
                $sqlArray[] = "ALTER TABLE `attributes` DROP INDEX comment"; // for replayability
                $sqlArray[] = "ALTER TABLE `attributes` DROP INDEX first_seen"; // for replayability
                $sqlArray[] = "ALTER TABLE `attributes` DROP INDEX last_seen"; // for replayability
                $sqlArray[] =
                    "ALTER TABLE `attributes`
                        ADD COLUMN `first_seen` BIGINT(20) NULL DEFAULT NULL,
                        ADD COLUMN `last_seen` BIGINT(20) NULL DEFAULT NULL,
                        MODIFY comment TEXT COLLATE utf8_unicode_ci
                    ;";
                $indexArray[] = array('attributes', 'uuid');
                $indexArray[] = array('attributes', 'event_id');
                $indexArray[] = array('attributes', 'sharing_group_id');
                $indexArray[] = array('attributes', 'type');
                $indexArray[] = array('attributes', 'category');
                $indexArray[] = array('attributes', 'value1', 255);
                $indexArray[] = array('attributes', 'value2', 255);
                $indexArray[] = array('attributes', 'object_id');
                $indexArray[] = array('attributes', 'object_relation');
                $indexArray[] = array('attributes', 'deleted');
                $indexArray[] = array('attributes', 'first_seen');
                $indexArray[] = array('attributes', 'last_seen');
                $sqlArray[] = "
                    ALTER TABLE `objects`
                        ADD `first_seen` BIGINT(20) NULL DEFAULT NULL,
                        ADD `last_seen` BIGINT(20) NULL DEFAULT NULL,
                        MODIFY comment TEXT COLLATE utf8_unicode_ci
                    ;";
                $indexArray[] = array('objects', 'first_seen');
                $indexArray[] = array('objects', 'last_seen');
                $sqlArray[] = "
                    ALTER TABLE `shadow_attributes`
                        ADD `first_seen` BIGINT(20) NULL DEFAULT NULL,
                        ADD `last_seen` BIGINT(20) NULL DEFAULT NULL,
                        MODIFY comment TEXT COLLATE utf8_unicode_ci
                    ;";
                $indexArray[] = array('shadow_attributes', 'first_seen');
                $indexArray[] = array('shadow_attributes', 'last_seen');
                break;
            default:
                return false;
        }

        // switch MISP instance live to false
        if ($liveOff) {
            $this->Server = Classregistry::init('Server');
            $liveSetting = 'MISP.live';
            $this->Server->serverSettingsSaveValue($liveSetting, false);
        }
        $sql_update_count = count($sqlArray);
        $index_update_count = count($indexArray);
        $total_update_count = $sql_update_count + $index_update_count;
        $this->__setUpdateProgress(0, $total_update_count, $command);
        $str_index_array = array();
        foreach($indexArray as $toIndex) {
            $str_index_array[] = __('Indexing %s -> %s', $toIndex[0], $toIndex[1]);
        }
        $this->__setUpdateCmdMessages(array_merge($sqlArray, $str_index_array));
        $flagStop = false;
        $errorCount = 0;

        // execute test before update. Exit if it fails
        if (isset($this->advanced_updates_description[$command]['preUpdate'])) {
            $function_name = $this->advanced_updates_description[$command]['preUpdate'];
            try {
                $this->{$function_name}();
            } catch (Exception $e) {
                $this->__setPreUpdateTestState(false);
                $this->__setUpdateProgress(0, false);
                $this->__setUpdateResMessages(0, sprintf(__('Issues executing the pre-update test `%s`. The returned error is: %s'), $function_name, $e->getMessage()) . PHP_EOL);
                $this->__setUpdateError(0);
                $errorCount++;
                $exitOnError = true;
                $flagStop = true;
            }
        }

        if (!$flagStop) {
            $this->__setPreUpdateTestState(true);
            foreach ($sqlArray as $i => $sql) {
                try {
                    $this->__setUpdateProgress($i, false);
                    $this->query($sql);
                    $this->Log->create();
                    $this->Log->save(array(
                        'org' => 'SYSTEM',
                        'model' => 'Server',
                        'model_id' => 0,
                        'email' => 'SYSTEM',
                        'action' => 'update_database',
                        'user_id' => 0,
                        'title' => __('Successfully executed the SQL query for ') . $command,
                        'change' => sprintf(__('The executed SQL query was: %s'), $sql)
                    ));
                    $this->__setUpdateResMessages($i, sprintf(__('Successfully executed the SQL query for %s'), $command));
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $this->Log->create();
                    $logMessage = array(
                        'org' => 'SYSTEM',
                        'model' => 'Server',
                        'model_id' => 0,
                        'email' => 'SYSTEM',
                        'action' => 'update_database',
                        'user_id' => 0,
                        'title' => sprintf(__('Issues executing the SQL query for %s'), $command),
                        'change' => __('The executed SQL query was: ') . $sql . PHP_EOL . __(' The returned error is: ') . $errorMessage
                    );
                    $this->__setUpdateResMessages($i, sprintf(__('Issues executing the SQL query for `%s`. The returned error is: ' . PHP_EOL . '%s'), $command, $errorMessage));
                    if (!$this->isAcceptedDatabaseError($errorMessage, $dataSource)) {
                        $this->__setUpdateError($i);
                        $errorCount++;
                        if ($exitOnError) {
                            $flagStop = true;
                            break;
                        }
                    } else {
                        $logMessage['change'] = $logMessage['change'] . PHP_EOL . __('However, as this error is allowed, the update went through.');
                    }
                    $this->Log->save($logMessage);
                }
            }
        }
        if (!$flagStop) {
            if (!empty($indexArray)) {
                if ($clean) {
                    $this->cleanCacheFiles();
                }
                foreach ($indexArray as $i => $iA) {
                    $this->__setUpdateProgress(count($sqlArray)+$i, false);
                    if (isset($iA[2])) {
                        $indexSuccess = $this->__addIndex($iA[0], $iA[1], $iA[2]);
                    } else {
                        $indexSuccess = $this->__addIndex($iA[0], $iA[1]);
                    }
                    if ($indexSuccess['success']) {
                        $this->__setUpdateResMessages(count($sqlArray)+$i, __('Successfuly indexed ') . sprintf('%s -> %s', $iA[0], $iA[1]));
                    } else {
                        $this->__setUpdateResMessages(count($sqlArray)+$i, sprintf('%s %s %s %s',
                            __('Failed to add index'),
                            sprintf('%s -> %s', $iA[0], $iA[1]),
                            __('The returned error is:') . PHP_EOL,
                            $indexSuccess['errorMessage']
                        ));
                        $this->__setUpdateError(count($sqlArray)+$i);
                    }
                }
            }
            $this->__setUpdateProgress(count($sqlArray) + count($indexArray), false);
         }
        if ($clean) {
            $this->cleanCacheFiles();
        }
        if ($liveOff) {
            $this->Server->serverSettingsSaveValue('MISP.live', true);
        }
        if (!$flagStop && $errorCount == 0) {
            $this->__postUpdate($command);
        }
        if ($flagStop && $errorCount > 0) {
            $this->Log->create();
            $this->Log->save(array(
                    'org' => 'SYSTEM',
                    'model' => 'Server',
                    'model_id' => 0,
                    'email' => 'SYSTEM',
                    'action' => 'update_database',
                    'user_id' => 0,
                    'title' => sprintf(__('Issues executing the SQL query for %s'), $command),
                    'change' => __('Database updates stopped as some errors occured and the stop flag is enabled.')
            ));
            return false;
        }
        return true;
    }

    // check whether the adminSetting should be updated after the update
    private function __postUpdate($command) {
        if (isset($this->advanced_updates_description[$command]['record'])) {
            if($this->advanced_updates_description[$command]['record']) {
                $this->AdminSetting->changeSetting($command, 1);
            }
        }
    }

    private function __dropIndex($table, $field)
    {
        $dataSourceConfig = ConnectionManager::getDataSource('default')->config;
        $dataSource = $dataSourceConfig['datasource'];
        $this->Log = ClassRegistry::init('Log');
        $indexCheckResult = array();
        if ($dataSource == 'Database/Mysql' || $dataSource == 'Database/MysqlObserver') {
            $indexCheck = "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema=DATABASE() AND table_name='" . $table . "' AND index_name LIKE '" . $field . "%';";
            $indexCheckResult = $this->query($indexCheck);
        } elseif ($dataSource == 'Database/Postgres') {
            $pgIndexName = 'idx_' . $table . '_' . $field;
            $indexCheckResult[] = array('STATISTICS' => array('INDEX_NAME' => $pgIndexName));
        }
        foreach ($indexCheckResult as $icr) {
            if ($dataSource == 'Database/Mysql' || $dataSource == 'Database/MysqlObserver') {
                $dropIndex = 'ALTER TABLE ' . $table . ' DROP INDEX ' . $icr['STATISTICS']['INDEX_NAME'] . ';';
            } elseif ($dataSource == 'Database/Postgres') {
                $dropIndex = 'DROP INDEX IF EXISTS ' . $icr['STATISTICS']['INDEX_NAME'] . ';';
            }
            $result = true;
            try {
                $this->query($dropIndex);
            } catch (Exception $e) {
                $result = false;
            }
            $this->Log->create();
            $this->Log->save(array(
                    'org' => 'SYSTEM',
                    'model' => 'Server',
                    'model_id' => 0,
                    'email' => 'SYSTEM',
                    'action' => 'update_database',
                    'user_id' => 0,
                    'title' => ($result ? 'Removed index ' : 'Failed to remove index ') . $icr['STATISTICS']['INDEX_NAME'] . ' from ' . $table,
                    'change' => ($result ? 'Removed index ' : 'Failed to remove index ') . $icr['STATISTICS']['INDEX_NAME'] . ' from ' . $table,
            ));
        }
    }

    private function __addIndex($table, $field, $length = null, $unique = false)
    {
        $dataSourceConfig = ConnectionManager::getDataSource('default')->config;
        $dataSource = $dataSourceConfig['datasource'];
        $this->Log = ClassRegistry::init('Log');
        $index = $unique ? 'UNIQUE INDEX' : 'INDEX';
        if ($dataSource == 'Database/Postgres') {
            $addIndex = "CREATE $index idx_" . $table . "_" . $field . " ON " . $table . " (" . $field . ");";
        } else {
            if (!$length) {
                $addIndex = "ALTER TABLE `" . $table . "` ADD $index `" . $field . "` (`" . $field . "`);";
            } else {
                $addIndex = "ALTER TABLE `" . $table . "` ADD $index `" . $field . "` (`" . $field . "`(" . $length . "));";
            }
        }
        $result = true;
        $duplicate = false;
        $errorMessage = '';
        try {
            $this->query($addIndex);
        } catch (Exception $e) {
            $duplicate = strpos($e->getMessage(), '1061') !== false;
            $errorMessage = $e->getMessage();
            $result = false;
        }
        $this->Log->create();
        $this->Log->save(array(
                'org' => 'SYSTEM',
                'model' => 'Server',
                'model_id' => 0,
                'email' => 'SYSTEM',
                'action' => 'update_database',
                'user_id' => 0,
                'title' => ($result ? 'Added index ' : 'Failed to add index ') . $field . ' to ' . $table . ($duplicate ? ' (index already set)' : $errorMessage),
                'change' => ($result ? 'Added index ' : 'Failed to add index ') . $field . ' to ' . $table . ($duplicate ? ' (index already set)' : $errorMessage),
        ));
        $additionResult = array('success' => $result || $duplicate);
        if (!$result) {
            $additionResult['errorMessage'] = $errorMessage;
        }
        return $additionResult;
    }

    public function cleanCacheFiles()
    {
        Cache::clear();
        Cache::clear(false, '_cake_core_');
        Cache::clear(false, '_cake_model_');
        clearCache();

        $files = glob(CACHE . 'models' . DS . 'myapp*');
        $files = array_merge($files, glob(CACHE . 'persistent' . DS . 'myapp*'));
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function getPythonVersion()
    {
        if (!empty(Configure::read('MISP.python_bin'))) {
            return Configure::read('MISP.python_bin');
        } else {
            return 'python3';
        }
    }

    public function validateAuthkey($value)
    {
        if (empty($value['authkey'])) {
            return 'Empty authkey found. Make sure you set the 40 character long authkey.';
        }
        if (!preg_match('/[a-z0-9]{40}/i', $value['authkey'])) {
            return 'The authkey has to be exactly 40 characters long and consist of alphanumeric characters.';
        }
        return true;
    }

    // alternative to the build in notempty/notblank validation functions, compatible with cakephp <= 2.6 and cakephp and cakephp >= 2.7
    public function valueNotEmpty($value)
    {
        $field = array_keys($value);
        $field = $field[0];
        $value[$field] = trim($value[$field]);
        if (!empty($value[$field])) {
            return true;
        }
        return ucfirst($field) . ' cannot be empty.';
    }

    public function valueIsJson($value)
    {
        $field = array_keys($value);
        $field = $field[0];
        $json_decoded = json_decode($value[$field]);
        if ($json_decoded === null) {
            return __('Invalid JSON.');
        }
        return true;
    }

    public function valueIsJsonOrNull($value)
    {
        $field = array_keys($value);
        $field = $field[0];
        if (!is_null($value[$field])) {
            $json_decoded = json_decode($value[$field]);
            if ($json_decoded === null) {
                return __('Invalid JSON.');
            }
        }
        return true;
    }

    public function valueIsID($value)
    {
        $field = array_keys($value);
        $field = $field[0];
        if (!is_numeric($value[$field]) || $value[$field] < 0) {
            return 'Invalid ' . ucfirst($field) . ' ID';
        }
        return true;
    }

    public function stringNotEmpty($value)
    {
        $field = array_keys($value);
        $field = $field[0];
        $value[$field] = trim($value[$field]);
        if (!isset($value[$field]) || ($value[$field] == false && $value[$field] !== "0")) {
            return ucfirst($field) . ' cannot be empty.';
        }
        return true;
    }

    // Try to create a table with a BIGINT(20)
    public function seenOnAttributeAndObjectPreUpdate() {
        $sqlArray[] = "CREATE TABLE IF NOT EXISTS testtable (
            `testfield` BIGINT(6) NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        try {
            foreach($sqlArray as $i => $sql) {
                $this->query($sql);
            }
        } catch (Exception $e) {
            throw new Exception('Pre update test failed: ' . PHP_EOL . $sql . PHP_EOL . ' The returned error is: ' . $e->getMessage());
        }
        // clean up
        $sqlArray[] = "DROP TABLE testtable;";
        foreach($sqlArray as $i => $sql) {
            $this->query($sql);
        }
    }

    public function failingPreUpdate() {
        throw new Exception('Yolo fail');
    }

    public function runUpdates($verbose = false, $useWorker = true, $processId = false)
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $this->Job = ClassRegistry::init('Job');
        $this->Log = ClassRegistry::init('Log');
        $this->Server = ClassRegistry::init('Server');
        $db = ConnectionManager::getDataSource('default');
        $tables = $db->listSources();
        $requiresLogout = false;
        // if we don't even have an admin table, time to create it.
        if (!in_array('admin_settings', $tables)) {
            $this->updateDatabase('adminTable');
            $requiresLogout = true;
        } else {
            $this->__runCleanDB();
            $db_version = $this->AdminSetting->find('all', array('conditions' => array('setting' => 'db_version')));
            if (count($db_version) > 1) {
                // we rgan into a bug where we have more than one db_version entry. This bug happened in some rare circumstances around 2.4.50-2.4.57
                foreach ($db_version as $k => $v) {
                    if ($k > 0) {
                        $this->AdminSetting->delete($v['AdminSetting']['id']);
                    }
                }
            }
            $db_version = $db_version[0];
            $updates = $this->findUpgrades($db_version['AdminSetting']['value']);
            if ($processId) {
                $job = $this->Job->find('first', array(
                    'conditions' => array('Job.id' => $processId)
                ));
            } else {
                $job = null;
            }
            if (!empty($updates)) {
                // Exit if updates are locked.
                // This is not as reliable as a real lock implementation
                // However, as all updates are re-playable, there is no harm if they
                // get played multiple time. The purpose of this lightweight lock
                // is only to limit the load.
                if ($this->isUpdateLocked()) { // prevent creation of useless workers
                    $this->Log->create();
                    $this->Log->save(array(
                            'org' => 'SYSTEM',
                            'model' => 'Server',
                            'model_id' => 0,
                            'email' => 'SYSTEM',
                            'action' => 'update_db_worker',
                            'user_id' => 0,
                            'title' => __('Issues executing run_updates'),
                            'change' => __('Database updates are locked. Worker not spawned')
                    ));
                    if (!empty($job)) { // if multiple prio worker is enabled, want to mark them as done
                        $job['Job']['progress'] = 100;
                        $job['Job']['message'] = __('Update done');
                       $this->Job->save($job);
                    }
                    return true;
                }

                // restart this function by a worker
                if ($useWorker && Configure::read('MISP.background_jobs')) {
                    $workerIssueCount = 0;
                    $workerDiagnostic = $this->Server->workerDiagnostics($workerIssueCount);
                    $workerType = '';
                    if (isset($workerDiagnostic['update']['ok']) && $workerDiagnostic['update']['ok']) {
                        $workerType = 'update';
                    } else { // update worker not running, doing the update inline
                        return $this->runUpdates($verbose, false);
                    }
                    $this->Job->create();
                    $data = array(
                        'worker' => $workerType,
                        'job_type' => 'run_updates',
                        'job_input' => 'command: ' . implode(',', $updates),
                        'status' => 0,
                        'retries' => 0,
                        'org_id' => 0,
                        'org' => '',
                        'message' => 'Updating.',
                    );
                    $this->Job->save($data);
                    $jobId = $this->Job->id;
                    $processId = CakeResque::enqueue(
                            'prio',
                            'AdminShell',
                            array('runUpdates', $jobId),
                            true
                    );
                    $this->Job->saveField('process_id', $processId);
                    return true;
                }

                // See comment above for `isUpdateLocked()`
                // prevent continuation of job if worker was already spawned
                // (could happens if multiple prio workers are up)
                if ($this->isUpdateLocked()) {
                    $this->Log->create();
                    $this->Log->save(array(
                            'org' => 'SYSTEM',
                            'model' => 'Server',
                            'model_id' => 0,
                            'email' => 'SYSTEM',
                            'action' => 'update_db_worker',
                            'user_id' => 0,
                            'title' => __('Issues executing run_updates'),
                            'change' => __('Updates are locked. Stopping worker gracefully')
                    ));
                    if (!empty($job)) {
                        $job['Job']['progress'] = 100;
                        $job['Job']['message'] = __('Update done');
                        $this->Job->save($job);
                    }
                    return true;
                }
                $this->changeLockState(time());
                $this->__resetUpdateProgress();

                $update_done = 0;
                foreach ($updates as $update => $temp) {
                    if ($verbose) {
                        echo str_pad('Executing ' . $update, 30, '.');
                    }
                    if (!empty($job)) {
                        $job['Job']['progress'] = floor($update_done / count($updates) * 100);
                        $job['Job']['message'] = sprintf(__('Running update %s'), $update);
                        $this->Job->save($job);
                    }
                    $dbUpdateSuccess = $this->updateMISP($update);
                    if ($temp) {
                        $requiresLogout = true;
                    }
                    if ($dbUpdateSuccess) {
                        $db_version['AdminSetting']['value'] = $update;
                        $this->AdminSetting->save($db_version);
                        $this->resetUpdateFailNumber();
                    } else {
                        $this->__increaseUpdateFailNumber();
                    }
                    if ($verbose) {
                        echo "\033[32mDone\033[0m" . PHP_EOL;
                    }
                    $update_done++;
                }
                if (!empty($job)) {
                    $job['Job']['message'] = __('Update done');
                }
                $this->changeLockState(false);
                $this->__queueCleanDB();
            } else {
                if (!empty($job)) {
                    $job['Job']['message'] = __('Update done in another worker. Gracefuly stopping.');
                }
            }
            // mark current worker as done, as well as queued workers than manages to pass the locks
            // (happens if user hit reload before first worker start its job)
            if (!empty($job)) {
                $job['Job']['progress'] = 100;
                $this->Job->save($job);
            }
        }
        if ($requiresLogout) {
            $this->refreshSessions();
        }
        return true;
    }

    /**
     * Update date_modified for all users, this will ensure that all users will refresh their session data.
     */
    private function refreshSessions()
    {
        $this->User = ClassRegistry::init('User');
        $this->User->updateAll(['date_modified' => time()]);
    }

    private function __setUpdateProgress($current, $total=false, $toward_db_version=false)
    {
        $updateProgress = $this->getUpdateProgress();
        $updateProgress['current'] = $current;
        if ($total !== false) {
            $updateProgress['total'] = $total;
        } else {
            $now = new DateTime();
            $updateProgress['time']['started'][$current] = $now->format('Y-m-d H:i:s');
        }
        if ($toward_db_version !== false) {
            $updateProgress['toward_db_version'] = $toward_db_version;
        }
        $this->__saveUpdateProgress($updateProgress);
    }

    private function __setPreUpdateTestState($state)
    {
        $updateProgress = $this->getUpdateProgress();
        $updateProgress['preTestSuccess'] = $state;
        $this->__saveUpdateProgress($updateProgress);
    }

    private function __setUpdateError($index)
    {
        $updateProgress = $this->getUpdateProgress();
        $updateProgress['failed_num'][] = $index;
        $this->__saveUpdateProgress($updateProgress);
    }

    private function __getEmptyUpdateMessage()
    {
        return array(
            'commands' => array(),
            'results' => array(),
            'time' => array('started' => array(), 'elapsed' => array()),
            'current' => '',
            'total' => '',
            'failed_num' => array(),
            'toward_db_version' => ''
        );
    }

    private function __resetUpdateProgress()
    {
        $updateProgress = $this->__getEmptyUpdateMessage();
        $this->__saveUpdateProgress($updateProgress);
    }

    private function __setUpdateCmdMessages($messages)
    {
        $updateProgress = $this->getUpdateProgress();
        $updateProgress['commands'] = $messages;
        $this->__saveUpdateProgress($updateProgress);
    }

    private function __setUpdateResMessages($index, $message)
    {
        $updateProgress = $this->getUpdateProgress();
        $updateProgress['results'][$index] = $message;
        $temp = new DateTime();
        $diff = $temp->diff(new DateTime($updateProgress['time']['started'][$index]));
        $updateProgress['time']['elapsed'][$index] = $diff->format('%H:%I:%S');
        $this->__saveUpdateProgress($updateProgress);
    }

    public function getUpdateProgress()
    {
        if (!isset($this->AdminSetting)) {
            $this->AdminSetting = ClassRegistry::init('AdminSetting');
        }
        $updateProgress = $this->AdminSetting->getSetting('update_progress');
        if ($updateProgress !== false) {
            $updateProgress = json_decode($updateProgress, true);
        } else {
            $updateProgress = $this->__getEmptyUpdateMessage();
        }
        foreach($updateProgress as $setting => $value) {
            if (!is_array($value)) {
                if (is_numeric($value)) {
                    $value = intval($value);
                }
            }
            $updateProgress[$setting] = $value;
        }
        return $updateProgress;
    }

    private function __saveUpdateProgress($updateProgress)
    {
        if (!isset($this->AdminSetting)) {
            $this->AdminSetting = ClassRegistry::init('AdminSetting');
        }
        $data = json_encode($updateProgress);
        $this->AdminSetting->changeSetting('update_progress', $data);
    }

    public function changeLockState($locked)
    {
        if (!isset($this->AdminSetting)) {
            $this->AdminSetting = ClassRegistry::init('AdminSetting');
        }
        $this->AdminSetting->changeSetting('update_locked', $locked);
    }

    private function getUpdateLockState()
    {
        if (!isset($this->AdminSetting)) {
            $this->AdminSetting = ClassRegistry::init('AdminSetting');
        }
        $locked = $this->AdminSetting->getSetting('update_locked');
        return is_null($locked) ? false : $locked;
    }

    public function getLockRemainingTime()
    {
        $lockState = $this->getUpdateLockState();
        if ($lockState !== false && $lockState !== '') {
            // if lock is old, still allows the update
            // This can be useful if the update process crashes
            $diffSec = time() - intval($lockState);
            if (Configure::read('MISP.updateTimeThreshold')) {
                $updateWaitThreshold = intval(Configure::read('MISP.updateTimeThreshold'));
            } else {
                $this->Server = ClassRegistry::init('Server');
                $updateWaitThreshold = intval($this->Server->serverSettings['MISP']['updateTimeThreshold']['value']);
            }
            $remainingTime = $updateWaitThreshold - $diffSec;
            return $remainingTime > 0 ? $remainingTime : 0;
        } else {
            return 0;
        }
    }

    public function isUpdateLocked()
    {
        $remainingTime = $this->getLockRemainingTime();
        $failThresholdReached = $this->UpdateFailNumberReached();
        return $remainingTime > 0 || $failThresholdReached;
    }

    public function getUpdateFailNumber()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $updateFailNumber = $this->AdminSetting->getSetting('update_fail_number');
        return ($updateFailNumber !== false && $updateFailNumber !== '') ? $updateFailNumber : 0;
    }

    public function resetUpdateFailNumber()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $this->AdminSetting->changeSetting('update_fail_number', 0);
    }

    public function __increaseUpdateFailNumber()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $updateFailNumber = $this->AdminSetting->getSetting('update_fail_number');
        $this->AdminSetting->changeSetting('update_fail_number', $updateFailNumber+1);
    }

    public function UpdateFailNumberReached()
    {
        return $this->getUpdateFailNumber() > 3;
    }

    private function __queueCleanDB()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $cleanDB = $this->AdminSetting->find('first', array('conditions' => array('setting' => 'clean_db')));
        if (empty($cleanDB)) {
            $this->AdminSetting->create();
            $cleanDB = array('AdminSetting' => array('setting' => 'clean_db', 'value' => 1));
        } else {
            $cleanDB['AdminSetting']['value'] = 1;
        }
        $this->AdminSetting->save($cleanDB);
    }

    private function __runCleanDB()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $cleanDB = $this->AdminSetting->find('first', array('conditions' => array('setting' => 'clean_db')));
        if (empty($cleanDB) || $cleanDB['AdminSetting']['value'] == 1) {
            $this->cleanCacheFiles();
            if (empty($cleanDB)) {
                $this->AdminSetting->create();
                $cleanDB = array('AdminSetting' => array('setting' => 'clean_db', 'value' => 0));
            } else {
                $cleanDB['AdminSetting']['value'] = 0;
            }
            $this->AdminSetting->save($cleanDB);
        }
    }

    public function findUpgrades($db_version)
    {
        $updates = array();
        if (strpos($db_version, '.')) {
            $version = explode('.', $db_version);
            foreach ($this->old_db_changes as $major => $rest) {
                if ($major < $version[0]) {
                    continue;
                } elseif ($major == $version[0]) {
                    foreach ($rest as $minor => $hotfixes) {
                        if ($minor < $version[1]) {
                            continue;
                        } elseif ($minor == $version[1]) {
                            foreach ($hotfixes as $hotfix => $requiresLogout) {
                                if ($hotfix > $version[2]) {
                                    $updates[$major . '.' . $minor . '.' . $hotfix] = $requiresLogout;
                                }
                            }
                        } else {
                            foreach ($hotfixes as $hotfix => $requiresLogout) {
                                $updates[$major . '.' . $minor . '.' . $hotfix] = $requiresLogout;
                            }
                        }
                    }
                }
            }
            $db_version = 0;
        }
        foreach ($this->db_changes as $db_change => $requiresLogout) {
            if ($db_version < $db_change) {
                $updates[$db_change] = $requiresLogout;
            }
        }
        return $updates;
    }

    private function __generateCorrelations()
    {
        if (Configure::read('MISP.background_jobs')) {
            $Job = ClassRegistry::init('Job');
            $Job->create();
            $data = array(
                    'worker' => 'default',
                    'job_type' => 'generate correlation',
                    'job_input' => 'All attributes',
                    'status' => 0,
                    'retries' => 0,
                    'org' => 'ADMIN',
                    'message' => 'Job created.',
            );
            $Job->save($data);
            $jobId = $Job->id;
            $process_id = CakeResque::enqueue(
                    'default',
                    'AdminShell',
                    array('jobGenerateCorrelation', $jobId),
                    true
            );
            $Job->saveField('process_id', $process_id);
        }
        return true;
    }

    public function populateNotifications($user, $mode = 'full')
    {
        $notifications = array();
        list($notifications['proposalCount'], $notifications['proposalEventCount']) = $this->_getProposalCount($user, $mode);
        $notifications['total'] = $notifications['proposalCount'];
        if (Configure::read('MISP.delegation')) {
            $notifications['delegationCount'] = $this->_getDelegationCount($user);
            $notifications['total'] += $notifications['delegationCount'];
        }
        return $notifications;
    }

    // if not using $mode === 'full', simply check if an entry exists. We really don't care about the real count for the top menu.
    private function _getProposalCount($user, $mode = 'full')
    {
        $this->ShadowAttribute = ClassRegistry::init('ShadowAttribute');
        $results[0] = $this->ShadowAttribute->find(
            'count',
            array(
                'recursive' => -1,
                'conditions' => array(
                        'ShadowAttribute.event_org_id' => $user['org_id'],
                        'ShadowAttribute.deleted' => 0,
                )
            )
        );
        if ($mode === 'full') {
            $results[1] = $this->ShadowAttribute->find(
                'count',
                array(
                    'recursive' => -1,
                    'conditions' => array(
                            'ShadowAttribute.event_org_id' => $user['org_id'],
                            'ShadowAttribute.deleted' => 0,
                    ),
                    'fields' => 'distinct event_id'
                )
            );
        } else {
            $results[1] = $results[0];
        }
        return $results;
    }

    private function _getDelegationCount($user)
    {
        $this->EventDelegation = ClassRegistry::init('EventDelegation');
        $delegations = $this->EventDelegation->find('count', array(
            'recursive' => -1,
            'conditions' => array('EventDelegation.org_id' => $user['org_id'])
        ));
        return $delegations;
    }

    public function checkFilename($filename)
    {
        return preg_match('@^([a-z0-9_.]+[a-z0-9_.\- ]*[a-z0-9_.\-]|[a-z0-9_.])+$@i', $filename);
    }

    /**
     * Similar method as `setupRedis`, but this method throw exception if Redis cannot be reached.
     * @return Redis
     * @throws Exception
     */
    public function setupRedisWithException()
    {
        if (self::$__redisConnection) {
            return self::$__redisConnection;
        }

        if (!class_exists('Redis')) {
            throw new Exception("Class Redis doesn't exists.");
        }

        $host = Configure::read('MISP.redis_host') ?: '127.0.0.1';
        $port = Configure::read('MISP.redis_port') ?: 6379;
        $database = Configure::read('MISP.redis_database') ?: 13;
        $pass = Configure::read('MISP.redis_password');

        $redis = new Redis();
        if (!$redis->connect($host, $port)) {
            throw new Exception("Could not connect to Redis: {$redis->getLastError()}");
        }
        if (!empty($pass)) {
            if (!$redis->auth($pass)) {
                throw new Exception("Could not authenticate to Redis: {$redis->getLastError()}");
            }
        }
        if (!$redis->select($database)) {
            throw new Exception("Could not select Redis database $database: {$redis->getLastError()}");
        }

        self::$__redisConnection = $redis;
        return $redis;
    }

    /**
     * Method for backward compatibility.
     * @deprecated
     * @see AppModel::setupRedisWithException
     * @return bool|Redis
     */
    public function setupRedis()
    {
        try {
            return $this->setupRedisWithException();
        } catch (Exception $e) {
            return false;
        }
    }

    public function getKafkaPubTool()
    {
        if (!$this->loadedKafkaPubTool) {
            $this->loadKafkaPubTool();
        }
        return $this->loadedKafkaPubTool;
    }

    public function loadKafkaPubTool()
    {
        App::uses('KafkaPubTool', 'Tools');
        $kafkaPubTool = new KafkaPubTool();
        $rdkafkaIni = Configure::read('Plugin.Kafka_rdkafka_config');
        $kafkaConf = array();
        if (!empty($rdkafkaIni)) {
            $kafkaConf = parse_ini_file($rdkafkaIni);
        }
        $brokers = Configure::read('Plugin.Kafka_brokers');
        $kafkaPubTool->initTool($brokers, $kafkaConf);
        $this->loadedKafkaPubTool = $kafkaPubTool;
        return true;
    }

    public function publishKafkaNotification($topicName, $data, $action = false) {
        $kafkaTopic = Configure::read('Plugin.Kafka_' . $topicName . '_notifications_topic');
        if (Configure::read('Plugin.Kafka_enable') && Configure::read('Plugin.Kafka_' . $topicName . '_notifications_enable') && !empty($kafkaTopic)) {
            $this->getKafkaPubTool()->publishJson($kafkaTopic, $data, $action);
        }
    }

    public function getPubSubTool()
    {
        if (!$this->loadedPubSubTool) {
            App::uses('PubSubTool', 'Tools');
            $pubSubTool = new PubSubTool();
            $pubSubTool->initTool();
            $this->loadedPubSubTool = $pubSubTool;
        }
        return $this->loadedPubSubTool;
    }

    public function getElasticSearchTool()
    {
        if (!$this->elasticSearchClient) {
            $this->loadElasticSearchTool();
        }
        return $this->elasticSearchClient;
    }

    public function loadElasticSearchTool()
    {
        App::uses('ElasticSearchClient', 'Tools');
        $client = new ElasticSearchClient();
        $client->initTool();
        $this->elasticSearchClient = $client;
    }

    // generate a generic subquery - options needs to include conditions
    public function subQueryGenerator($model, $options, $lookupKey, $negation = false)
    {
        $db = $model->getDataSource();
        $defaults = array(
            'fields' => array('*'),
            'table' => $model->table,
            'alias' => $model->alias,
            'limit' => null,
            'offset' => null,
            'joins' => array(),
            'conditions' => array(),
            'group' => false,
            'recursive' => -1
        );
        $params = array();
        foreach (array_keys($defaults) as $key) {
            if (isset($options[$key])) {
                $params[$key] = $options[$key];
            } else {
                $params[$key] = $defaults[$key];
            }
        }
        $subQuery = $db->buildStatement(
            $params,
            $model
        );
        if ($negation) {
            $subQuery = $lookupKey . ' NOT IN (' . $subQuery . ') ';
        } else {
            $subQuery = $lookupKey . ' IN (' . $subQuery . ') ';
        }
        $conditions = array(
            $db->expression($subQuery)->value
        );
        return $conditions;
    }

    // start a benchmark run for the given bench name
    public function benchmarkInit($name = 'default')
    {
        $this->__profiler[$name]['start'] = microtime(true);
        if (empty($this->__profiler[$name]['memory_start'])) {
            $this->__profiler[$name]['memory_start'] = memory_get_usage();
        }
        return true;
    }

    // calculate the duration from the init time to the current point in execution. Aggregate flagged executions will increment the duration instead of just setting it
    public function benchmark($name = 'default', $aggregate = false, $memory_chart = false)
    {
        if (!empty($this->__profiler[$name]['start'])) {
            if ($aggregate) {
                if (!isset($this->__profiler[$name]['duration'])) {
                    $this->__profiler[$name]['duration'] = 0;
                }
                if (!isset($this->__profiler[$name]['executions'])) {
                    $this->__profiler[$name]['executions'] = 0;
                }
                $this->__profiler[$name]['duration'] += microtime(true) - $this->__profiler[$name]['start'];
                $this->__profiler[$name]['executions']++;
                $currentUsage = memory_get_usage();
                if ($memory_chart) {
                    $this->__profiler[$name]['memory_chart'][] = $currentUsage - $this->__profiler[$name]['memory_start'];
                }
                if (
                    empty($this->__profiler[$name]['memory_peak']) ||
                    $this->__profiler[$name]['memory_peak'] < ($currentUsage - $this->__profiler[$name]['memory_start'])
                ) {
                    $this->__profiler[$name]['memory_peak'] = $currentUsage - $this->__profiler[$name]['memory_start'];
                }
            } else {
                $this->__profiler[$name]['memory_peak'] = memory_get_usage() - $this->__profiler[$name]['memory_start'];
                $this->__profiler[$name]['duration'] = microtime(true) - $this->__profiler[$name]['start'];
            }
        }
        return true;
    }

    // return the results of the benchmark(s). If no name is set all benchmark results are returned in an array.
    public function benchmarkResult($name = false)
    {
        if ($name) {
            return array($name => $this->__profiler[$name]['duration']);
        } else {
            $results = array();
            foreach ($this->__profiler as $name => $benchmark) {
                if (!empty($benchmark['duration'])) {
                    $results[$name] = $benchmark;
                    unset($results[$name]['start']);
                    unset($results[$name]['memory_start']);
                }
            }
            return $results;
        }
    }

    public function getRowCount($table = false)
    {
        if (empty($table)) {
            $table = $this->table;
        }
        $table_data = $this->query("show table status like '" . $table . "'");
        return $table_data[0]['TABLES']['Rows'];
    }

    public function benchmarkCustomAdd($valueToAdd = 0, $name = 'default', $customName = 'custom')
    {
        if (empty($this->__profiler[$name]['custom'][$customName])) {
            $this->__profiler[$name]['custom'][$customName] = 0;
        }
        $this->__profiler[$name]['custom'][$customName] += $valueToAdd;
    }

    private function __forceSettings()
    {
        $settingsToForce = array(
            'Session.autoRegenerate' => false,
            'Session.checkAgent' => false
        );
        $server = ClassRegistry::init('Server');
        foreach ($settingsToForce as $setting => $value) {
            $server->serverSettingsSaveValue($setting, $value);
        }
        return true;
    }

    public function setupHttpSocket($server, $HttpSocket = null, $timeout = false)
    {
        if (empty($HttpSocket)) {
            App::uses('SyncTool', 'Tools');
            $syncTool = new SyncTool();
            $HttpSocket = $syncTool->setupHttpSocket($server, $timeout);
        }
        return $HttpSocket;
    }

    /**
     * @param array $server
     * @param string $model
     * @return array[]
     * @throws JsonException
     */
    protected function setupSyncRequest(array $server, $model = 'Server')
    {
        $version = implode('.', $this->checkMISPVersion());
        $commit = $this->checkMIPSCommit();
        $request = array(
            'header' => array(
                'Authorization' => $server[$model]['authkey'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'MISP-version' => $version,
                'User-Agent' => 'MISP ' . $version . (empty($commit) ? '' : ' - #' . $commit),
            )
        );
        if ($commit) {
            $request['header']['commit'] = $commit;
        }
        return $request;
    }

    /**
     * Returns MISP version from VERSION.json file as array with major, minor and hotfix keys.
     *
     * @return array
     * @throws JsonException
     */
    public function checkMISPVersion()
    {
        static $versionArray;
        if ($versionArray === null) {
            $file = new File(ROOT . DS . 'VERSION.json');
            $versionArray = $this->jsonDecode($file->read());
            $file->close();
        }
        return $versionArray;
    }

    /**
     * Returns MISP commit hash.
     *
     * @return false|string
     */
    protected function checkMIPSCommit()
    {
        static $commit;
        if ($commit === null) {
            $commit = shell_exec('git log --pretty="%H" -n1 HEAD');
            if ($commit) {
                $commit = trim($commit);
            } else {
                $commit = false;
            }
        }
        return $commit;
    }

    // take filters in the {"OR" => [foo], "NOT" => [bar]} format along with conditions and set the conditions
    public function generic_add_filter($conditions, &$filter, $keys)
    {
        $operator_composition = array(
            'NOT' => 'AND',
            'OR' => 'OR',
            'AND' => 'AND'
        );
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        if (!isset($filter['OR']) && !isset($filter['AND']) && !isset($filter['NOT'])) {
            return $conditions;
        }
        foreach ($filter as $operator => $filters) {
            $temp = array();
            if (!is_array($filters)) {
                $filters = array($filters);
            }
            foreach ($filters as $f) {
                if ($f === -1) {
                    foreach ($keys as $key) {
                        $temp['OR'][$key][] = -1;
                    }
                    continue;
                }
                // split the filter params into two lists, one for substring searches one for exact ones
                if (is_string($f) && ($f[strlen($f) - 1] === '%' || $f[0] === '%')) {
                    foreach ($keys as $key) {
                        if ($operator === 'NOT') {
                            $temp[] = array($key . ' NOT LIKE' => $f);
                        } else {
                            $temp[] = array($key . ' LIKE' => $f);
                        }
                    }
                } else {
                    foreach ($keys as $key) {
                        if ($operator === 'NOT') {
                            $temp[$key . ' !='][] = $f;
                        } else {
                            $temp['OR'][$key][] = $f;
                        }
                    }
                }
            }
            $conditions['AND'][] = array($operator_composition[$operator] => $temp);
            if ($operator !== 'NOT') {
                unset($filter[$operator]);
            }
        }
        return $conditions;
    }

    /*
     * Get filters in one of the following formats:
     * [foo, bar]
     * ["OR" => [foo, bar], "NOT" => [baz]]
     * "foo"
     * "foo&&bar&&!baz"
     * and convert it into the same format ["OR" => [foo, bar], "NOT" => [baz]]
     */
    public function convert_filters($filter)
    {
        if (!is_array($filter)) {
            $temp = explode('&&', $filter);
            $filter = array();
            foreach ($temp as $f) {
                $f = strval($f);
                if ($f[0] === '!') {
                    $filter['NOT'][] = substr($f, 1);
                } else {
                    $filter['OR'][] = $f;
                }
            }
            return $filter;
        }
        if (!isset($filter['OR']) && !isset($filter['NOT']) && !isset($filter['AND'])) {
            $temp = array();
            foreach ($filter as $param) {
                $param = strval($param);
                if (!empty($param)) {
                    if ($param[0] === '!') {
                        $temp['NOT'][] = substr($param, 1);
                    } else {
                        $temp['OR'][] = $param;
                    }
                }
            }
            $filter = $temp;
        }
        return $filter;
    }

    public function convert_to_memory_limit_to_mb($val)
    {
        $val = trim($val);
        if ($val == -1) {
            // default to 8GB if no limit is set
            return 8 * 1024;
        }
        $unit = $val[strlen($val)-1];
        if (is_numeric($unit)) {
            $unit = 'b';
        } else {
            $val = intval($val);
        }
        $unit = strtolower($unit);
        switch ($unit) {
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }
        return $val / (1024 * 1024);
    }

    public function getDefaultAttachments_dir()
    {
        return APP . 'files';
    }

    public function getDefaultTmp_dir()
    {
        return sys_get_temp_dir();
    }

    private function __bumpReferences()
    {
        $this->Event = ClassRegistry::init('Event');
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $existingSetting = $this->AdminSetting->find('first', array(
            'conditions' => array('AdminSetting.setting' => 'update_23')
        ));
        if (empty($existingSetting)) {
            $this->AdminSetting->create();
            $data = array(
                'setting' => 'update_23',
                'value' => 1
            );
            $this->AdminSetting->save($data);
            $references = $this->Event->Object->ObjectReference->find('list', array(
                'recursive' => -1,
                'fields' => array('ObjectReference.event_id', 'ObjectReference.event_id'),
                'group' => array('ObjectReference.event_id')
            ));
            $event_ids = array();
            $object_ids = array();
            foreach ($references as $reference) {
                $event = $this->Event->find('first', array(
                    'conditions' => array(
                        'Event.id' => $reference,
                        'Event.locked' => 0
                    ),
                    'recursive' => -1,
                    'fields' => array('Event.id', 'Event.locked')
                ));
                if (!empty($event)) {
                    $event_ids[] = $event['Event']['id'];
                    $event_references = $this->Event->Object->ObjectReference->find('list', array(
                        'conditions' => array('ObjectReference.event_id' => $reference),
                        'recursive' => -1,
                        'fields' => array('ObjectReference.object_id', 'ObjectReference.object_id')
                    ));
                    $object_ids = array_merge($object_ids, array_values($event_references));
                }
            }
            if (!empty($object_ids)) {
                $this->Event->Object->updateAll(
                    array(
                    'Object.timestamp' => 'Object.timestamp + 1'
                    ),
                    array('Object.id' => $object_ids)
                );
                $this->Event->updateAll(
                    array(
                    'Event.timestamp' => 'Event.timestamp + 1'
                    ),
                    array('Event.id' => $event_ids)
                );
            }
            $this->Log = ClassRegistry::init('Log');
            $this->Log->create();
            $entry = array(
                    'org' => 'SYSTEM',
                    'model' => 'Server',
                    'model_id' => 0,
                    'email' => 'SYSTEM',
                    'action' => 'update_database',
                    'user_id' => 0,
                    'title' => 'Bumped the timestamps of locked events containing object references.',
                    'change' => sprintf('Event timestamps updated: %s; Object timestamps updated: %s', count($event_ids), count($object_ids))
            );
            $this->Log->save($entry);
        }
        return true;
    }

    public function generateRandomFileName()
    {
        return (new RandomTool())->random_str(false, 12);
    }

    public function resolveTimeDelta($delta)
    {
        if (is_numeric($delta)) {
            return $delta;
        }
        $multiplierArray = array('d' => 86400, 'h' => 3600, 'm' => 60, 's' => 1);
        $multiplier = $multiplierArray['d'];
        $lastChar = strtolower(substr($delta, -1));
        if (!is_numeric($lastChar) && array_key_exists($lastChar, $multiplierArray)) {
            $multiplier = $multiplierArray[$lastChar];
            $delta = substr($delta, 0, -1);
        } else if(strtotime($delta) !== false) {
            return strtotime($delta);
        } else {
            // invalid filter, make sure we don't return anything
            return time() + 1;
        }
        if (!is_numeric($delta)) {
            // Same here. (returning false dumps the whole database)
            return time() + 1;
        }
        return time() - ($delta * $multiplier);
    }

    private function __fixServerPullPushRules()
    {
        $this->Server = ClassRegistry::init('Server');
        $servers = $this->Server->find('all', array('recursive' => -1));
        foreach ($servers as $server) {
            $changed = false;
            if (empty($server['Server']['pull_rules'])) {
                $server['Server']['pull_rules'] = '[]';
                $changed = true;
            }
            if (empty($server['Server']['push_rules'])) {
                $server['Server']['push_rules'] = '[]';
                $changed = true;
            }
            if ($changed) {
                $this->Server->save($server);
            }
        }
    }

    /**
     * Optimised version of CakePHP _findList method when just one or two fields are set from same model
     * @param string $state
     * @param array $query
     * @param array $results
     * @return array
     */
    protected function _findList($state, $query, $results = [])
    {
        if ($state === 'before') {
            return parent::_findList($state, $query, $results);
        }

        if (empty($results)) {
            return [];
        }

        if ($query['list']['groupPath'] === null) {
            $keyPath = explode('.', $query['list']['keyPath']);
            $valuePath = explode('.', $query['list']['valuePath']);
            if ($keyPath[1] === $valuePath[1]) { // same model
                return array_column(array_column($results, $keyPath[1]), $valuePath[2], $keyPath[2]);
            }
        }

        return parent::_findList($state, $query, $results);
    }

    /**
     * Find method that allows to fetch just one column from database.
     * @param $state
     * @param $query
     * @param array $results
     * @return array
     * @throws Exception
     */
    protected function _findColumn($state, $query, $results = array())
    {
        if ($state === 'before') {
            if (count($query['fields']) === 1) {
                if (strpos($query['fields'][0], '.') === false) {
                    $query['fields'][0] = $this->alias . '.' . $query['fields'][0];
                }

                $query['column'] = $query['fields'][0];
                if (isset($query['unique']) && $query['unique']) {
                    $query['fields'] = array("DISTINCT {$query['fields'][0]}");
                } else {
                    $query['fields'] = array($query['fields'][0]);
                }
            } else {
                throw new Exception("Invalid number of column, expected one, " . count($query['fields']) . " given");
            }

            if (!isset($query['recursive'])) {
                $query['recursive'] = -1;
            }

            return $query;
        }

        // Faster version of `Hash::extract`
        foreach (explode('.', $query['column']) as $part) {
            $results = array_column($results, $part);
        }
        return $results;
    }

    /**
     * @param string $field
     * @param AppModel $model
     * @param array $conditions
     */
    public function addCountField($field, AppModel $model, array $conditions)
    {
        $db = $this->getDataSource();
        $subQuery = $db->buildStatement(
            array(
                'fields'     => ['COUNT(*)'],
                'table'      => $db->fullTableName($model),
                'alias'      => $model->alias,
                'conditions' => $conditions,
            ),
            $model
        );
        $this->virtualFields[$field] = $subQuery;
    }

    /**
     * Log exception with backtrace and with nested exceptions.
     *
     * @param string $message
     * @param Exception $exception
     * @param int $type
     * @return bool
     */
    protected function logException($message, Exception $exception, $type = LOG_ERR)
    {
        $message .= "\n";

        do {
            $message .= sprintf("[%s] %s",
                get_class($exception),
                $exception->getMessage()
            );
            $message .= "\nStack Trace:\n" . $exception->getTraceAsString();
            $exception = $exception->getPrevious();
        } while ($exception !== null);

        return $this->log($message, $type);
    }

    /**
     * Generates random file name in tmp dir.
     * @return string
     */
    protected function tempFileName()
    {
        return $this->tempDir() . DS . $this->generateRandomFileName();
    }

    /**
     * @return string
     */
    protected function tempDir()
    {
        return Configure::read('MISP.tmpdir') ?: sys_get_temp_dir();
    }

    /**
     * Decodes JSON string and throws exception if string is not valid JSON or if is not array.
     *
     * @param string $json
     * @return array
     * @throws JsonException
     * @throws UnexpectedValueException
     */
    public function jsonDecode($json)
    {
        if (defined('JSON_THROW_ON_ERROR')) {
            // JSON_THROW_ON_ERROR is supported since PHP 7.3
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $decoded = json_decode($json, true);
            if ($decoded === null) {
                throw new UnexpectedValueException('Could not parse JSON: ' . json_last_error_msg(), json_last_error());
            }
        }

        if (!is_array($decoded)) {
            throw new UnexpectedValueException('JSON must be array type, get ' . gettype($decoded));
        }
        return $decoded;
    }

    /*
     *  Temporary solution for utf8 columns until we migrate to utf8mb4
     *  via https://stackoverflow.com/questions/16496554/can-php-detect-4-byte-encoded-utf8-chars
     */
    public function handle4ByteUnicode($input)
    {
        return preg_replace(
            '%(?:
            \xF0[\x90-\xBF][\x80-\xBF]{2}
            | [\xF1-\xF3][\x80-\xBF]{3}
            | \xF4[\x80-\x8F][\x80-\xBF]{2}
            )%xs',
            '?',
            $input
        );
    }

    /**
     * @return AttachmentTool
     */
    protected function loadAttachmentTool()
    {
        if ($this->attachmentTool === null) {
            $this->attachmentTool = new AttachmentTool();
        }

        return $this->attachmentTool;
    }

    /**
     * @return AttachmentScan
     */
    protected function loadAttachmentScan()
    {
        if ($this->AttachmentScan === null) {
            $this->AttachmentScan = ClassRegistry::init('AttachmentScan');
        }

        return $this->AttachmentScan;
    }

    /**
     * @return Log
     */
    protected function loadLog()
    {
        if (!isset($this->Log)) {
            $this->Log = ClassRegistry::init('Log');
        }
        return $this->Log;
    }
}

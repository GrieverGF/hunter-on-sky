<?php
App::uses('AppModel', 'Model');

/**
 * @property User $User
 */
class Role extends AppModel
{
    public $recursive = -1;
    public $validate = array(
        'valueNotEmpty' => array(
            'rule' => array('valueNotEmpty'),
        ),
        'name' => array(
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'A role with this name already exists.' // TODO i18n?
            ),
            'valueNotEmpty' => array(
                'rule' => array('valueNotEmpty'),
            ),
        ),
    );

    public $hasMany = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'role_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        )
    );

    public $actsAs = array(
        'AuditLog',
            'Trim',
            'SysLogLogable.SysLogLogable' => array( // TODO Audit, logable
                    'roleModel' => 'Role',
                    'roleKey' => 'role_id',
                    'change' => 'full'
            ),
    );

    public $virtualFields = array(
        'permission' => "CASE WHEN (Role.perm_add + Role.perm_modify + Role.perm_publish = 3) THEN '3' WHEN (Role.perm_add + Role.perm_modify_org = 2) THEN '2' WHEN (Role.perm_add = 1) THEN '1' ELSE '0' END",
    );

    public $permissionConstants = array(
        'read_only' => 0,
        'manage_own' => 1,
        'manage_org' => 2,
        'publish' => 3
    );

    // #TODO i18n?
    public $permFlags = array(
        'perm_site_admin' => array(
            'id' => 'RolePermSiteAdmin',
            'text' => 'Site Admin',
            'readonlyenabled' => false,
            'title' => 'Unrestricted access to any data and functionality on this instance.'
        ),
        'perm_admin' => array(
            'id' => 'RolePermAdmin',
            'text' => 'Org Admin',
            'readonlyenabled' => false,
            'title' => 'Limited organisation admin - create, manage users of their own organisation'
        ),
        'perm_sync' => array(
            'id' => 'RolePermSync',
            'text' => 'Sync Actions',
            'readonlyenabled' => true,
            'title' => 'Synchronisation permission, can be used to connect two MISP instances create data on behalf of other users. Make sure that the role with this permission has also access to tagging and tag editing rights.'
        ),
        'perm_audit' => array(
            'id' => 'RolePermAudit',
            'text' => 'Audit Actions',
            'readonlyenabled' => true,
            'title' => 'Access to the audit logs of the user\'s organisation.'
        ),
        'perm_auth' => array(
            'id' => 'RolePermAuth',
            'text' => 'Auth key access',
            'readonlyenabled' => true,
            'title' => 'Users with this permission have access to authenticating via their Auth keys, granting them access to the API.',
            'site_admin_optional' => true
        ),
        'perm_regexp_access' => array(
            'id' => 'RolePermRegexpAccess',
            'text' => 'Regex Actions',
            'readonlyenabled' => false,
            'title' => 'Users with this role can modify the regex rules affecting how data is fed into MISP. Make sure that caution is advised with handing out roles that include this permission, user controlled executed regexes are dangerous.'
        ),
        'perm_tagger' => array(
            'id' => 'RolePermTagger',
            'text' => 'Tagger',
            'readonlyenabled' => false,
            'title' => 'Users with roles that include this permission can attach or detach existing tags to and from events/attributes.'
        ),
        'perm_tag_editor' => array(
            'id' => 'RolePermTagEditor',
            'text' => 'Tag Editor',
            'readonlyenabled' => false,
            'title' => 'This permission gives users the ability to create tags.'
        ),
        'perm_template' => array(
            'id' => 'RolePermTemplate',
            'text' => 'Template Editor',
            'readonlyenabled' => false,
            'title' => 'Create or modify templates, to be used when populating events.'
        ),
        'perm_sharing_group' => array(
            'id' => 'RolePermSharingGroup',
            'text' => 'Sharing Group Editor',
            'readonlyenabled' => false,
            'title' => 'Permission to create or modify sharing groups.'
        ),
        'perm_delegate' => array(
            'id' => 'RolePermDelegate',
            'text' => 'Delegations Access',
            'readonlyenabled' => false,
            'title' => 'Allow users to create delegation requests for their own org only events to trusted third parties.'
        ),
        'perm_sighting' => array(
            'id' => 'RolePermSighting',
            'text' => 'Sighting Creator',
            'readonlyenabled' => true,
            'title' => 'Permits the user to push feedback on attributes into MISP by providing sightings.'
        ),
        'perm_object_template' => array(
            'id' => 'RolePermObjectTemplate',
            'text' => 'Object Template Editor',
            'readonlyenabled' => false,
            'title' => 'Create or modify MISP Object templates'
        ),
        'perm_galaxy_editor' => array(
            'id' => 'RolePermGalaxyEditor',
            'text' => 'Galaxy Editor',
            'readonlyenabled' => false,
            'title' => 'Create or modify MISP Galaxies and MISP Galaxies Clusters'
        ),
        'perm_decaying' => array(
            'id' => 'RolePermDecaying',
            'text' => 'Decaying Model Editor',
            'readonlyenabled' => true,
            'title' => 'Create or modify MISP Decaying Models'
        ),
        'perm_publish_zmq' => array(
            'id' => 'RolePermPublishZmq',
            'text' => 'ZMQ publisher',
            'readonlyenabled' => false,
            'title' => 'Allow users to publish data to the ZMQ pubsub channel via the publish event to ZMQ button.'
        ),
        'perm_publish_kafka' => array(
            'id' => 'RolePermPublishKafka',
            'text' => 'Kafka publisher',
            'readonlyenabled' => false,
            'title' => 'Allow users to publish data to Kafka via the publish event to Kafka button.'
        )
    );

    public $premissionLevelName = array('Read Only', 'Manage Own Events', 'Manage Organisation Events', 'Manage and Publish Organisation Events');

    public function beforeSave($options = array())
    {
        //Conversion from the named data access permission levels
        if (empty($this->data['Role']['permission'])) {
            $this->data['Role']['permission'] = 0;
        } elseif (!is_numeric($this->data['Role']['permission'])) {
            // If a constant was passed via the API, convert it to the numeric value
            // For invalid entries, choose permission level 0
            if (isset($this->permissionConstants[$this->data['Role']['permission']])) {
                $this->data['Role']['permission'] = $this->permissionConstants[$this->data['Role']['permission']];
            } else {
                $this->data['Role']['permission'] = 0;
            }
        }
        switch ($this->data['Role']['permission']) {
            case '0':
                $this->data['Role']['perm_add'] = 0;
                $this->data['Role']['perm_modify'] = 0;
                $this->data['Role']['perm_modify_org'] = 0;
                $this->data['Role']['perm_publish'] = 0;
                break;
            case '1':
                $this->data['Role']['perm_add'] = 1;
                $this->data['Role']['perm_modify'] = 1;
                $this->data['Role']['perm_modify_org'] = 0;
                $this->data['Role']['perm_publish'] = 0;
                break;
            case '2':
                $this->data['Role']['perm_add'] = 1;
                $this->data['Role']['perm_modify'] = 1;
                $this->data['Role']['perm_modify_org'] = 1;
                $this->data['Role']['perm_publish'] = 0;
                break;
            case '3':
                $this->data['Role']['perm_add'] = 1;
                $this->data['Role']['perm_modify'] = 1;
                $this->data['Role']['perm_modify_org'] = 1;
                $this->data['Role']['perm_publish'] = 1;
                break;
            default:
                break;
        }
        if (empty($this->data['Role']['id'])) {
            foreach (array_keys($this->permFlags) as $permFlag) {
                if (!isset($this->data['Role'][$permFlag])) {
                    $this->data['Role'][$permFlag] = 0;
                }
            }
            if (!isset($this->data['Role']['max_execution_time'])) {
                $this->data['Role']['max_execution_time'] = '';
            } elseif ($this->data['Role']['max_execution_time'] !== '') {
                $this->data['Role']['max_execution_time'] = intval($this->data['Role']['max_execution_time']);
            }
            if (!isset($this->data['Role']['memory_limit'])) {
                $this->data['Role']['memory_limit'] = '';
            } elseif (
                $this->data['Role']['memory_limit'] !== '' &&
                !preg_match('/^[0-9]+[MG]$/i', $this->data['Role']['memory_limit']) &&
                $this->data['Role']['memory_limit'] != -1
            ) {
                $this->data['Role']['memory_limit'] = '';
            }
        }
        if (empty($this->data['Role']['rate_limit_count'])) {
            $this->data['Role']['rate_limit_count'] = 0;
        }
        return true;
    }

    public function afterSave($created, $options = array())
    {
        // After role change, update `date_modified` field for all user with this role to apply this change to already
        // logged users.
        if (!$created && !empty($this->data)) {
            $roleId = $this->data['Role']['id'];
            $this->User->updateAll(['date_modified' => time()], ['role_id' => $roleId]);
        }

        parent::afterSave($created, $options);
    }

    public function afterFind($results, $primary = false)
    {
        foreach ($results as $key => $val) {
            if (isset($results[$key]['Role'])) {
                unset($results[$key]['Role']['perm_full']);
                if (isset($results[$key]['Role']['permission'])) {
                    $results[$key]['Role']['permission_description'] =
                    array_flip($this->permissionConstants)[$results[$key]['Role']['permission']];
                }
            }
        }
        return $results;
    }

    public function setPublishZmq()
    {
        $roles = $this->find('all', array(
            'recursive' => -1,
            'conditions' => array(
                'perm_publish' => 1
            )
        ));
        foreach ($roles as $k => $role) {
            $role['Role']['perm_publish_zmq'] = 1;
            $this->save($role);
        }
        return true;
    }

    /*
     *  Helper function to find out if a list of registrations has the same role requirements
     *  If no role requirements have been passed yet, null is assumed for $suggestedRole
     *  Returns an array with the permission flags required
     */
    public function checkDesiredRole($suggestedRole, $registration)
    {
        if ($suggestedRole !== false) {
            $currentRole = array();
            $roleFlags = array('perm_publish', 'perm_admin', 'perm_sync');
            foreach ($roleFlags as $roleFlag) {
                if (isset($registration['Inbox']['data'][$roleFlag])) {
                    $currentRole[$roleFlag] = $registration['Inbox']['data'][$roleFlag];
                }
            }
            if ($suggestedRole !== null) {
                if (count($suggestedRole) != count($currentRole) || !empty(array_diff_key($suggestedRole, $currentRole))) {
                    return false;
                }
                foreach (array_keys($currentRole) as $perm) {
                    if ($currentRole[$perm] !== $suggestedRole[$perm]) {
                        return false;
                    }
                }
            }
            return $currentRole;
        }
        return $suggestedRole;
    }
}

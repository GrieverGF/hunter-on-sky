<?php
App::uses('AppController', 'Controller');
App::uses('AuditLog', 'Model');

/**
 * @property AuditLog $AuditLog
 */
class AuditLogsController extends AppController
{
    public $components = [
        'Security',
        'RequestHandler',
    ];

    /** @var array */
    private $actions;

    /** @var string[] */
    private $models = [
        'AdminSetting',
        'Attribute',
        'Allowedlist',
        'AuthKey',
        'Cerebrate',
        'CorrelationExclusion',
        'Event',
        'EventBlocklist',
        'Feed',
        'DecayingModel',
        'Object',
        'ObjectTemplate',
        'Organisation',
        'OrgBlocklist',
        'Post',
        'Regexp',
        'Role',
        'Server',
        'ShadowAttribute',
        'SharingGroup',
        'Tag',
        'TagCollection',
        'TagCollectionTag',
        'Task',
        'Taxonomy',
        'Template',
        'Thread',
        'User',
        'UserSetting',
        'Galaxy',
        'GalaxyCluster',
        'GalaxyClusterBlocklist',
        'GalaxyClusterRelation',
        'News',
        'Warninglist',
    ];

    public $paginate = [
        'recursive' => -1,
        'limit' => 60,
        'fields' => ['id', 'created', 'user_id', 'org_id', 'action', 'model', 'model_id', 'model_title', 'event_id', 'change'],
        'contain' => [
            'User' => ['fields' => ['id', 'email', 'org_id']],
            'Organisation' => ['fields' => ['id', 'name', 'uuid']],
        ],
        'order' => [
            'AuditLog.id' => 'DESC'
        ],
    ];

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->actions = [
            AuditLog::ACTION_ADD => __('Add'),
            AuditLog::ACTION_EDIT => __('Edit'),
            AuditLog::ACTION_SOFT_DELETE => __('Soft delete'),
            AuditLog::ACTION_DELETE => __('Delete'),
            AuditLog::ACTION_UNDELETE => __('Undelete'),
            AuditLog::ACTION_TAG => __('Tag'),
            AuditLog::ACTION_TAG_LOCAL => __('Tag'),
            AuditLog::ACTION_REMOVE_TAG => __('Remove tag'),
            AuditLog::ACTION_REMOVE_TAG_LOCAL => __('Remove tag'),
            AuditLog::ACTION_GALAXY => __('Galaxy cluster'),
            AuditLog::ACTION_GALAXY_LOCAL => __('Galaxy cluster'),
            AuditLog::ACTION_REMOVE_GALAXY => __('Remove galaxy cluster'),
            AuditLog::ACTION_REMOVE_GALAXY_LOCAL => __('Remove galaxy cluster'),
            AuditLog::ACTION_PUBLISH => __('Publish'),
            AuditLog::ACTION_PUBLISH_SIGHTINGS => __('Publish sightings'),
        ];
    }

    public function admin_index()
    {
        $this->paginate['fields'][] = 'ip';
        $this->paginate['fields'][] = 'request_type';
        $this->paginate['fields'][] = 'authkey_id';

        if ($this->_isRest()) {
            $this->paginate['fields'][] = 'request_id';
        }

        $this->paginate['conditions'] = $this->__searchConditions();
        $list = $this->paginate();

        if ($this->_isRest()) {
            return $this->RestResponse->viewData($list, 'json');
        }

        $list = $this->__appendModelLinks($list);
        foreach ($list as $k => $item) {
            $list[$k]['AuditLog']['action_human'] =  $this->actions[$item['AuditLog']['action']];
        }

        $this->set('list', $list);
        $this->set('actions', [
            AuditLog::ACTION_ADD => __('Add'),
            AuditLog::ACTION_EDIT => __('Edit'),
            AuditLog::ACTION_SOFT_DELETE => __('Soft delete'),
            AuditLog::ACTION_DELETE => __('Delete'),
            AuditLog::ACTION_UNDELETE => __('Undelete'),
            AuditLog::ACTION_TAG . '||' . AuditLog::ACTION_TAG_LOCAL => __('Tag'),
            AuditLog::ACTION_REMOVE_TAG . '||' . AuditLog::ACTION_REMOVE_TAG_LOCAL => __('Remove tag'),
            AuditLog::ACTION_GALAXY . '||' . AuditLog::ACTION_GALAXY_LOCAL  => __('Galaxy cluster'),
            AuditLog::ACTION_REMOVE_GALAXY . '||' . AuditLog::ACTION_REMOVE_GALAXY_LOCAL => __('Remove galaxy cluster'),
            AuditLog::ACTION_PUBLISH => __('Publish'),
            AuditLog::ACTION_PUBLISH_SIGHTINGS => $this->actions[AuditLog::ACTION_PUBLISH_SIGHTINGS],
        ]);
        $models = $this->models;
        sort($models);
        $this->set('models', $models);
        $this->set('title_for_layout', __('Audit logs'));
    }

    public function eventIndex($eventId, $org = null)
    {
        $this->loadModel('Event');
        $event = $this->Event->fetchSimpleEvent($this->Auth->user(), $eventId);
        if (empty($event)) {
            throw new NotFoundException('Invalid event.');
        }

        $this->paginate['conditions'] = $this->__createEventIndexConditions($event);

        if ($org) {
            $org = $this->AuditLog->Organisation->fetchOrg($org);
            if ($org) {
                $this->paginate['conditions']['AND']['org_id'] = $org['id'];
            } else {
                $this->paginate['conditions']['AND']['org_id'] = -1;
            }
        }

        $list = $this->paginate();

        if (!$this->_isSiteAdmin()) {
            // Remove all user info about users from different org
            $this->loadModel('User');
            $orgUserIds = $this->User->find('column', array(
                'conditions' => ['User.org_id' => $this->Auth->user('org_id')],
                'fields' => ['User.id'],
            ));
            foreach ($list as $k => $item) {
                if ($item['AuditLog']['user_id'] == 0) {
                    continue;
                }
                if (!in_array($item['User']['id'], $orgUserIds)) {
                    unset($list[$k]['User']);
                    unset($list[$k]['AuditLog']['user_id']);
                }
            }
        }

        if ($this->_isRest()) {
            return $this->RestResponse->viewData($list, 'json');
        }

        foreach ($list as $k => $item) {
            $list[$k]['AuditLog']['action_human'] = $this->actions[$item['AuditLog']['action']];
        }

        $this->set('list', $list);
        $this->set('event', $event);
        $this->set('mayModify', $this->__canModifyEvent($event));
        $this->set('title_for_layout', __('Audit logs for event #%s', $event['Event']['id']));
    }

    public function fullChange($id)
    {
        $log = $this->AuditLog->find('first', [
            'conditions' => ['id' => $id],
            'recursive' => -1,
            'fields' => ['change', 'action'],
        ]);
        if (empty($log)) {
            throw new Exception('Log not found.');
        }
        $this->set('log', $log);
    }

    public function returnDates($org = 'all')
    {
        if (!$this->Auth->user('Role')['perm_sharing_group'] && !empty(Configure::read('Security.hide_organisation_index_from_users'))) {
            if ($org !== 'all' && $org !== $this->Auth->user('Organisation')['name']) {
                throw new MethodNotAllowedException('Invalid organisation.');
            }
        }

        // Fetching dates can be slow, so to allow concurrent requests, we can close sessions to release session lock
        session_write_close();

        $data = $this->AuditLog->returnDates($org);
        return $this->RestResponse->viewData($data, $this->response->type());
    }

    /**
     * @return array
     */
    private function __searchConditions()
    {
        $params = $this->IndexFilter->harvestParameters([
            'ip',
            'user',
            'request_id',
            'authkey_id',
            'model',
            'model_id',
            'event_id',
            'model_title',
            'action',
            'org',
            'created',
            'request_type',
        ]);

        $qbRules = [];
        foreach ($params as $key => $value) {
            if ($key === 'model' && strpos($value, ':') !== false) {
                $parts = explode(':', $value);
                $qbRules[] = [
                    'id' => 'model',
                    'value' => $parts[0],
                ];
                $qbRules[] = [
                    'id' => 'model_id',
                    'value' => $parts[1],
                ];
            } elseif ($key === 'created') {
                $qbRules[] = [
                    'id' => $key,
                    'operator' => is_array($value) ? 'between' : 'greater_or_equal',
                    'value' => $value,
                ];
            } else {
                if (is_array($value)) {
                    $value = implode('||', $value);
                }
                $qbRules[] = [
                    'id' => $key,
                    'value' => $value,
                ];
            }
        }
        $this->set('qbRules', $qbRules);

        $conditions = [];
        if (isset($params['user'])) {
            if (strtoupper($params['user']) === 'SYSTEM') {
                $conditions['AuditLog.user_id'] = 0;
            } else if (is_numeric($params['user'])) {
                $conditions['AuditLog.user_id'] = $params['user'];
            } else {
                $user = $this->User->find('first', [
                    'conditions' => ['User.email' => $params['user']],
                    'fields' => ['id'],
                ]);
                if (!empty($user)) {
                    $conditions['AuditLog.user_id'] = $user['User']['id'];
                } else {
                    $conditions['AuditLog.user_id'] = -1;
                }
            }
        }
        if (isset($params['ip'])) {
            $conditions['AuditLog.ip'] = inet_pton($params['ip']);
        }
        if (isset($params['authkey_id'])) {
            $conditions['AuditLog.authkey_id'] = $params['authkey_id'];
        }
        if (isset($params['request_id'])) {
            $conditions['AuditLog.request_id'] = $params['request_id'];
        }
        if (isset($params['request_type'])) {
            $conditions['AuditLog.request_type'] = $params['request_type'];
        }
        if (isset($params['model'])) {
            $conditions['AuditLog.model'] = $params['model'];
        }
        if (isset($params['model_id'])) {
            $conditions['AuditLog.model_id'] = $params['model_id'];
        }
        if (isset($params['event_id'])) {
            $conditions['AuditLog.event_id'] = $params['event_id'];
        }
        if (isset($params['model_title'])) {
            $conditions['AuditLog.model_title LIKE'] = '%' . $params['model_title'] . '%';
        }
        if (isset($params['action'])) {
            $conditions['AuditLog.action'] = $params['action'];
        }
        if (isset($params['org'])) {
            if (is_numeric($params['org'])) {
                $conditions['AuditLog.org_id'] = $params['org'];
            } else {
                $org = $this->AuditLog->Organisation->fetchOrg($params['org']);
                if ($org) {
                    $conditions['AuditLog.org_id'] = $org['id'];
                } else {
                    $conditions['AuditLog.org_id'] = -1;
                }
            }
        }
        if (isset($params['created'])) {
            $tempData = is_array($params['created']) ? $params['created'] : [$params['created']];
            foreach ($tempData as $k => $v) {
                $tempData[$k] = $this->AuditLog->resolveTimeDelta($v);
            }
            if (count($tempData) === 1) {
                $conditions['AuditLog.created >='] = date("Y-m-d H:i:s", $tempData[0]);
            } else {
                if ($tempData[0] < $tempData[1]) {
                    $temp = $tempData[1];
                    $tempData[1] = $tempData[0];
                    $tempData[0] = $temp;
                }
                $conditions['AND'][] = ['AuditLog.created <=' => date("Y-m-d H:i:s", $tempData[0])];
                $conditions['AND'][] = ['AuditLog.created >=' => date("Y-m-d H:i:s", $tempData[1])];
            }
        }
        return $conditions;
    }

    /**
     * Create conditions that will include just events parts that user can see.
     * @param array $event
     * @return array
     */
    private function __createEventIndexConditions(array $event)
    {
        if ($this->_isSiteAdmin() || $event['Event']['orgc_id'] == $this->Auth->user('org_id')) {
            // Site admins and event owners can see all changes
            return ['event_id' => $event['Event']['id']];
        }

        $event = $this->Event->fetchEvent($this->Auth->user(), [
            'eventid' => $event['Event']['id'],
            'sgReferenceOnly' => 1,
            'deleted' => [0, 1],
            'deleted_proposals' => 1,
            'noSightings' => true,
            'includeEventCorrelations' => false,
            'excludeGalaxy' => true,
        ])[0];

        $attributeIds = [];
        $objectIds = [];
        $proposalIds = array_column($event['ShadowAttribute'], 'id');
        $objectReferenceId = [];
        foreach ($event['Attribute'] as $aa) {
            $attributeIds[] = $aa['id'];
            if (!empty($aa['ShadowAttribute'])) {
                foreach ($aa['ShadowAttribute'] as $sa) {
                    $proposalIds[] = $sa['id'];
                }
            }
        }
        unset($event['Attribute']);
        foreach ($event['Object'] as $ob) {
            foreach ($ob['Attribute'] as $aa) {
                $attributeIds[] = $aa['id'];
                if (!empty($aa['ShadowAttribute'])) {
                    foreach ($aa['ShadowAttribute'] as $sa) {
                        $proposalIds[] = $sa['id'];
                    }
                }
            }
            foreach ($ob['ObjectReference'] as $or) {
                $objectReferenceId[] = $or['id'];
            }
            $objectIds[] = $ob['id'];
        }
        unset($event['Object']);

        $conditions = [];
        $conditions['AND']['event_id'] = $event['Event']['id'];
        $conditions['AND']['OR'][] = ['model' => 'Event'];

        $parts = [
            'Attribute' => $attributeIds,
            'ShadowAttribute' => $proposalIds,
            'Object' => $objectIds,
            'ObjectReference' => $objectReferenceId,
            'EventReport' => array_column($event['EventReport'], 'id'),
        ];

        foreach ($parts as $model => $modelIds) {
            if (!empty($modelIds)) {
                $conditions['AND']['OR'][] = [
                    'AND' => [
                        'model' => $model,
                        'model_id' => $modelIds,
                    ],
                ];
            }
        }

        return $conditions;
    }

    /**
     * Generate link to model view if exists and use has permission to access it.
     * @param array $auditLogs
     * @return array
     */
    private function __appendModelLinks(array $auditLogs)
    {
        $models = [];
        foreach ($auditLogs as $auditLog) {
            if (isset($models[$auditLog['AuditLog']['model']])) {
                $models[$auditLog['AuditLog']['model']][] = $auditLog['AuditLog']['model_id'];
            } else {
                $models[$auditLog['AuditLog']['model']] = [$auditLog['AuditLog']['model_id']];
            }
        }

        $eventIds = isset($models['Event']) ? $models['Event'] : [];

        if (isset($models['ObjectReference'])) {
            $this->loadModel('ObjectReference');
            $objectReferences = $this->ObjectReference->find('list', [
                'conditions' => ['ObjectReference.id' => array_unique($models['ObjectReference'])],
                'fields' => ['ObjectReference.id', 'ObjectReference.object_id'],
            ]);
        }

        if (isset($models['Object']) || isset($objectReferences)) {
            $objectIds = array_unique(array_merge(
                isset($models['Object']) ? $models['Object'] : [],
                isset($objectReferences) ? array_values($objectReferences) : []
            ));
            $this->loadModel('MispObject');
            $conditions = $this->MispObject->buildConditions($this->Auth->user());
            $conditions['Object.id'] = $objectIds;
            $objects = $this->MispObject->find('all', [
                'conditions' => $conditions,
                'contain' => ['Event'],
                'fields' => ['Object.id', 'Object.event_id', 'Object.uuid', 'Object.deleted'],
            ]);
            $objects = array_column(array_column($objects, 'Object'), null, 'id');
            $eventIds = array_merge($eventIds, array_column($objects, 'event_id'));
        }

        if (isset($models['Attribute'])) {
            $this->loadModel('Attribute');
            $attributes = $this->Attribute->fetchAttributesSimple($this->Auth->user(), [
                'conditions' => ['Attribute.id' => array_unique($models['Attribute'])],
                'fields' => ['Attribute.id', 'Attribute.event_id', 'Attribute.uuid', 'Attribute.deleted'],
            ]);
            $attributes = array_column(array_column($attributes, 'Attribute'), null, 'id');
            $eventIds = array_merge($eventIds, array_column($attributes, 'event_id'));
        }

        if (isset($models['ShadowAttribute'])) {
            $this->loadModel('ShadowAttribute');
            $conditions = $this->ShadowAttribute->buildConditions($this->Auth->user());
            $conditions['AND'][] = ['ShadowAttribute.id' => array_unique($models['ShadowAttribute'])];
            $shadowAttributes = $this->ShadowAttribute->find('all', [
                'conditions' => $conditions,
                'fields' => ['ShadowAttribute.id', 'ShadowAttribute.event_id', 'ShadowAttribute.uuid', 'ShadowAttribute.deleted'],
                'contain' => ['Event', 'Attribute'],
            ]);
            $shadowAttributes = array_column(array_column($shadowAttributes, 'ShadowAttribute'), null, 'id');
            $eventIds = array_merge($eventIds, array_column($shadowAttributes, 'event_id'));
        }

        if (!empty($eventIds)) {
            $this->loadModel('Event');
            $events = $this->Event->fetchSimpleEvents($this->Auth->user(), [
                'conditions' => ['Event.id' => array_unique($eventIds)],
            ]);
            $events = array_column(array_column($events, 'Event'), null, 'id');
        }

        $existingObjects = [];
        foreach (['User', 'Organisation', 'Galaxy', 'GalaxyCluster', 'Warninglist', 'AuthKey', 'ObjectTemplate', 'Role'] as $modelName) {
            if (isset($models[$modelName])) {
                $this->loadModel($modelName);
                $data = $this->{$modelName}->find('column', [
                    'conditions' => ['id' => array_unique($models[$modelName])],
                    'fields' => ['id'],
                ]);
                $existingObjects[$modelName] = array_flip($data);
            }
        }

        $links = [
            'ObjectTemplate' => 'objectTemplates',
            'AuthKey' => 'auth_keys',
            'GalaxyCluster' => 'galaxy_clusters',
            'Galaxy' => 'galaxies',
            'Organisation' => 'organisation',
            'Warninglist' => 'warninglists',
            'User' => 'admin/user',
            'Role' => 'roles',
        ];

        foreach ($auditLogs as $k => $auditLog) {
            $auditLog = $auditLog['AuditLog'];
            $modelId = (int)$auditLog['model_id'];
            $url = null;
            $eventInfo = null;
            switch ($auditLog['model']) {
                case 'Event':
                    if (isset($events[$modelId])) {
                        $url = '/events/view/' . $modelId;
                        $eventInfo = $events[$modelId]['info'];
                    }
                    break;
                case 'ObjectReference':
                    if (isset($objectReferences[$modelId]) && isset($objects[$objectReferences[$modelId]])) {
                        $url = '/events/view/' . $objects[$objectReferences[$modelId]]['event_id'] . '/focus:' . $objects[$objectReferences[$modelId]]['uuid'];
                        if ($objects[$objectReferences[$modelId]]['deleted']) {
                            $url .= '/deleted:2';
                        }
                        if (isset($events[$objects[$objectReferences[$modelId]]['event_id']])) {
                            $eventInfo = $events[$objects[$objectReferences[$modelId]]['event_id']]['info'];
                        }
                    }
                    break;
                case 'Object':
                    if (isset($objects[$modelId])) {
                        $url = '/events/view/' . $objects[$modelId]['event_id'] . '/focus:' . $objects[$modelId]['uuid'];
                        if ($objects[$modelId]['deleted']) {
                            $url .= '/deleted:2';
                        }
                        if (isset($events[$objects[$modelId]['event_id']])) {
                            $eventInfo = $events[$objects[$modelId]['event_id']]['info'];
                        }
                    }
                    break;
                case 'Attribute':
                    if (isset($attributes[$modelId])) {
                        $url = '/events/view/' . $attributes[$modelId]['event_id'] . '/focus:' . $attributes[$modelId]['uuid'];
                        if ($attributes[$modelId]['deleted']) {
                            $url .= '/deleted:2';
                        }
                        if (isset($events[$attributes[$modelId]['event_id']])) {
                            $eventInfo = $events[$attributes[$modelId]['event_id']]['info'];
                        }
                    }
                    break;
                case 'ShadowAttribute':
                    if (isset($shadowAttributes[$modelId])) {
                        $url = '/events/view/' . $shadowAttributes[$modelId]['event_id'] . '/focus:' . $shadowAttributes[$modelId]['uuid'];
                        if (isset($events[$shadowAttributes[$modelId]['event_id']])) {
                            $eventInfo = $events[$shadowAttributes[$modelId]['event_id']]['info'];
                        }
                    }
                    break;
                default:
                    if (isset($existingObjects[$auditLog['model']][$modelId])) {
                        $url = '/' . $links[$auditLog['model']] . '/view/' . $modelId;
                    } else {
                        continue 2;
                    }
            }
            if ($url) {
                $auditLogs[$k]['AuditLog']['model_link'] = $this->baseurl . $url;
            }
            if ($eventInfo) {
                $auditLogs[$k]['AuditLog']['event_info'] = $eventInfo;
            }
        }

        return $auditLogs;
    }
}

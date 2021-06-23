<?php
App::uses('AppController', 'Controller');
App::uses('Xml', 'Utility');
App::uses('AttachmentTool', 'Tools');
App::uses('SecurityAudit', 'Tools');

/**
 * @property Server $Server
 */
class ServersController extends AppController
{
    public $components = array('Security' ,'RequestHandler');   // XXX ACL component

    public $paginate = array(
            'limit' => 60,
            'recursive' => -1,
            'contain' => array(
                    'User' => array(
                            'fields' => array('User.id', 'User.org_id', 'User.email'),
                    ),
                    'Organisation' => array(
                            'fields' => array('Organisation.name', 'Organisation.id'),
                    ),
                    'RemoteOrg' => array(
                            'fields' => array('RemoteOrg.name', 'RemoteOrg.id'),
                    ),
            ),
            'maxLimit' => 9999, // LATER we will bump here on a problem once we have more than 9999 events
            'order' => array(
                    'Server.priority' => 'ASC'
            ),
    );

    public $uses = array('Server', 'Event');

    public function beforeFilter()
    {
        $this->Auth->allow(['cspReport']); // cspReport must work without authentication

        parent::beforeFilter();
        $this->Security->unlockedActions[] = 'getApiInfo';
        $this->Security->unlockedActions[] = 'cspReport';
        // permit reuse of CSRF tokens on some pages.
        switch ($this->request->params['action']) {
            case 'push':
            case 'pull':
            case 'getVersion':
            case 'testConnection':
                $this->Security->csrfUseOnce = false;
        }
    }

    public function index()
    {
        if ($this->_isRest()) {
            $params = array(
                'recursive' => -1,
                'contain' => array(
                        'User' => array(
                                'fields' => array('User.id', 'User.org_id', 'User.email'),
                        ),
                        'Organisation' => array(
                                'fields' => array('Organisation.id', 'Organisation.name', 'Organisation.uuid', 'Organisation.nationality', 'Organisation.sector', 'Organisation.type'),
                        ),
                        'RemoteOrg' => array(
                                'fields' => array('RemoteOrg.id', 'RemoteOrg.name', 'RemoteOrg.uuid', 'RemoteOrg.nationality', 'RemoteOrg.sector', 'RemoteOrg.type'),
                        ),
                ),
            );
            $servers = $this->Server->find('all', $params);
            $servers = $this->Server->attachServerCacheTimestamps($servers);
            return $this->RestResponse->viewData($servers, $this->response->type());
        } else {
            $servers = $this->paginate();
            $servers = $this->Server->attachServerCacheTimestamps($servers);
            $this->set('servers', $servers);
            $collection = array();
            $collection['orgs'] = $this->Server->Organisation->find('list', array(
                  'fields' => array('id', 'name'),
            ));
            $this->loadModel('Tag');
            $collection['tags'] = $this->Tag->find('list', array(
                  'fields' => array('id', 'name'),
            ));
            $this->set('collection', $collection);
        }
    }

    public function previewIndex($id)
    {
        $urlparams = '';
        $passedArgs = array();

        $server = $this->Server->find('first', array('conditions' => array('Server.id' => $id), 'recursive' => -1));
        if (empty($server)) {
            throw new NotFoundException('Invalid server ID.');
        }
        $validFilters = $this->Server->validEventIndexFilters;
        foreach ($validFilters as $k => $filter) {
            if (isset($this->passedArgs[$filter])) {
                $passedArgs[$filter] = $this->passedArgs[$filter];
                if ($k != 0) {
                    $urlparams .= '/';
                }
                $urlparams .= $filter . ':' . $this->passedArgs[$filter];
            }
        }
        $combinedArgs = array_merge($this->passedArgs, $passedArgs);
        if (!isset($combinedArgs['sort'])) {
            $combinedArgs['sort'] = 'timestamp';
            $combinedArgs['direction'] = 'desc';
        }
        if (empty($combinedArgs['page'])) {
            $combinedArgs['page'] = 1;
        }
        if (empty($combinedArgs['limit'])) {
            $combinedArgs['limit'] = 60;
        }
        try {
            list($events, $total_count) = $this->Server->previewIndex($server, $this->Auth->user(), $combinedArgs);
        } catch (Exception $e) {
            $this->Flash->error(__('Download failed.') . ' ' . $e->getMessage());
            $this->redirect(array('action' => 'index'));
        }

        $this->loadModel('Event');
        $this->set('threatLevels', $this->Event->ThreatLevel->listThreatLevels());
        App::uses('CustomPaginationTool', 'Tools');
        $customPagination = new CustomPaginationTool();
        $params = $customPagination->createPaginationRules($events, $this->passedArgs, $this->alias);
        if (!empty($total_count)) {
            $params['pageCount'] = ceil($total_count / $params['limit']);
        }
        $this->params->params['paging'] = array($this->modelClass => $params);
        if (count($events) > 60) {
            $customPagination->truncateByPagination($events, $params);
        }
        $this->set('events', $events);
        $this->set('eventDescriptions', $this->Event->fieldDescriptions);
        $this->set('analysisLevels', $this->Event->analysisLevels);
        $this->set('distributionLevels', $this->Event->distributionLevels);

        $shortDist = array(0 => 'Organisation', 1 => 'Community', 2 => 'Connected', 3 => 'All', 4 => ' sharing Group');
        $this->set('shortDist', $shortDist);
        $this->set('id', $id);
        $this->set('urlparams', $urlparams);
        $this->set('passedArgs', json_encode($passedArgs));
        $this->set('passedArgsArray', $passedArgs);
        $this->set('server', $server);
    }

    public function previewEvent($serverId, $eventId, $all = false)
    {
        $server = $this->Server->find('first', array(
            'conditions' => array('Server.id' => $serverId),
            'recursive' => -1,
        ));
        if (empty($server)) {
            throw new NotFoundException('Invalid server ID.');
        }
        try {
            $event = $this->Server->previewEvent($server, $eventId);
        } catch (NotFoundException $e) {
            throw new NotFoundException(__("Event '%s' not found.", $eventId));
        } catch (Exception $e) {
            $this->Flash->error(__('Download failed. %s', $e->getMessage()));
            $this->redirect(array('action' => 'previewIndex', $serverId));
        }

        if ($this->_isRest()) {
            return $this->RestResponse->viewData($event, $this->response->type());
        }

        $this->loadModel('Warninglist');
        if (isset($event['Event']['Attribute'])) {
            $this->Warninglist->attachWarninglistToAttributes($event['Event']['Attribute']);
        }
        if (isset($event['Event']['ShadowAttribute'])) {
            $this->Warninglist->attachWarninglistToAttributes($event['Event']['ShadowAttribute']);
        }

        $this->loadModel('Event');
        $params = $this->Event->rearrangeEventForView($event, $this->passedArgs, $all);
        $this->__removeGalaxyClusterTags($event);
        $this->params->params['paging'] = array('Server' => $params);
        $this->set('event', $event);
        $this->set('server', $server);
        $dataForView = array(
                'Attribute' => array('attrDescriptions' => 'fieldDescriptions', 'distributionDescriptions' => 'distributionDescriptions', 'distributionLevels' => 'distributionLevels'),
                'Event' => array('eventDescriptions' => 'fieldDescriptions', 'analysisLevels' => 'analysisLevels'),
                'Object' => array()
        );
        foreach ($dataForView as $m => $variables) {
            if ($m === 'Event') {
                $currentModel = $this->Event;
            } elseif ($m === 'Attribute') {
                $currentModel = $this->Event->Attribute;
            } elseif ($m === 'Object') {
                $currentModel = $this->Event->Object;
            }
            foreach ($variables as $alias => $variable) {
                $this->set($alias, $currentModel->{$variable});
            }
        }
        $this->set('threatLevels', $this->Event->ThreatLevel->listThreatLevels());
        $this->set('title_for_layout', __('Remote event preview'));
    }

    private function __removeGalaxyClusterTags(array &$event)
    {
        $galaxyTagIds = [];
        foreach ($event['Galaxy'] as $galaxy) {
            foreach ($galaxy['GalaxyCluster'] as $galaxyCluster) {
                $galaxyTagIds[$galaxyCluster['tag_id']] = true;
            }
        }

        if (empty($galaxyTagIds)) {
            return;
        }

        foreach ($event['Tag'] as $k => $eventTag) {
            if (isset($galaxyTagIds[$eventTag['id']])) {
                unset($event['Tag'][$k]);
            }
        }
    }

    public function compareServers()
    {
        list($servers, $overlap) = $this->Server->serverEventsOverlap();
        $this->set('servers', $servers);
        $this->set('overlap', $overlap);
        $this->set('title_for_layout', __('Server overlap analysis matrix'));
    }

    public function filterEventIndex($id)
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException('You are not authorised to do that.');
        }
        $validFilters = $this->Server->validEventIndexFilters;
        $validatedFilterString = '';
        foreach ($this->passedArgs as $k => $v) {
            if (in_array('' . $k, $validFilters)) {
                if ($validatedFilterString != '') {
                    $validatedFilterString .= '/';
                }
                $validatedFilterString .= $k . ':' . $v;
            }
        }
        $this->set('id', $id);
        $this->set('validFilters', $validFilters);
        $this->set('filter', $validatedFilterString);
    }

    public function add()
    {
        if (!$this->_isSiteAdmin()) {
            $this->redirect(array('controller' => 'servers', 'action' => 'index'));
        }
        if ($this->request->is('post')) {
            if ($this->_isRest()) {
                if (!isset($this->request->data['Server'])) {
                    $this->request->data = array('Server' => $this->request->data);
                }
            }
            if (!empty($this->request->data['Server']['json'])) {
                $json = json_decode($this->request->data['Server']['json'], true);
            } elseif ($this->_isRest()) {
                if (empty($this->request->data['Server']['remote_org_id'])) {
                    throw new MethodNotAllowedException('No remote org ID set. Please pass it as remote_org_id');
                }
            }
            $fail = false;
            if (empty(Configure::read('MISP.host_org_id'))) {
                $this->request->data['Server']['internal'] = 0;
            }
            // test the filter fields
            if (!empty($this->request->data['Server']['pull_rules']) && !$this->Server->isJson($this->request->data['Server']['pull_rules'])) {
                $fail = true;
                $error_msg = __('The pull filter rules must be in valid JSON format.');
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'add', false, array('pull_rules' => $error_msg), $this->response->type());
                } else {
                    $this->Flash->error($error_msg);
                }
            }

            if (!$fail && !empty($this->request->data['Server']['push_rules']) && !$this->Server->isJson($this->request->data['Server']['push_rules'])) {
                $fail = true;
                $error_msg = __('The push filter rules must be in valid JSON format.');
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'add', false, array('push_rules' => $error_msg), $this->response->type());
                } else {
                    $this->Flash->error($error_msg);
                }
            }
            if (!$fail) {
                if ($this->_isRest()) {
                    $defaults = array(
                        'push' => 0,
                        'pull' => 0,
                        'push_sightings' => 0,
                        'push_galaxy_clusters' => 0,
                        'pull_galaxy_clusters' => 0,
                        'caching_enabled' => 0,
                        'json' => '[]',
                        'push_rules' => '[]',
                        'pull_rules' => '[]',
                        'self_signed' => 0
                    );
                    foreach ($defaults as $default => $dvalue) {
                        if (!isset($this->request->data['Server'][$default])) {
                            $this->request->data['Server'][$default] = $dvalue;
                        }
                    }
                }
                // force check userid and orgname to be from yourself
                $this->request->data['Server']['org_id'] = $this->Auth->user('org_id');
                if ($this->_isRest()) {
                    if (empty($this->request->data['Server']['remote_org_id'])) {
                        return $this->RestResponse->saveFailResponse('Servers', 'add', false, array('Organisation' => 'Remote Organisation\'s id/uuid not given (remote_org_id)'), $this->response->type());
                    }
                    if (Validation::uuid($this->request->data['Server']['remote_org_id'])) {
                        $orgCondition = array('uuid' => $this->request->data['Server']['remote_org_id']);
                    } else {
                        $orgCondition = array('id' => $this->request->data['Server']['remote_org_id']);
                    }
                    $existingOrgs = $this->Server->Organisation->find('first', array(
                            'conditions' => $orgCondition,
                            'recursive' => -1,
                            'fields' => array('id', 'uuid')
                    ));
                    if (empty($existingOrgs)) {
                        return $this->RestResponse->saveFailResponse('Servers', 'add', false, array('Organisation' => 'Invalid Remote Organisation'), $this->response->type());
                    }
                } else {
                    if ($this->request->data['Server']['organisation_type'] < 2) {
                        $this->request->data['Server']['remote_org_id'] = $json['id'];
                    } else {
                        $existingOrgs = $this->Server->Organisation->find('first', array(
                                'conditions' => array('uuid' => $json['uuid']),
                                'recursive' => -1,
                                'fields' => array('id', 'uuid')
                        ));
                        if (!empty($existingOrgs)) {
                            $fail = true;
                            $this->Flash->error(__('That organisation could not be created as the uuid is in use already.'));
                        }
                        if (!$fail) {
                            $this->Server->Organisation->create();
                            $orgSave = $this->Server->Organisation->save(array(
                                    'name' => $json['name'],
                                    'uuid' => $json['uuid'],
                                    'local' => 0,
                                    'created_by' => $this->Auth->user('id')
                            ));

                            if (!$orgSave) {
                                $this->Flash->error(__('Couldn\'t save the new organisation, are you sure that the uuid is in the correct format? Also, make sure the organisation\'s name doesn\'t clash with an existing one.'));
                                $fail = true;
                                $this->request->data['Server']['external_name'] = $json['name'];
                                $this->request->data['Server']['external_uuid'] = $json['uuid'];
                            } else {
                                $this->request->data['Server']['remote_org_id'] = $this->Server->Organisation->id;
                                $this->request->data['Server']['organisation_type'] = 1;
                            }
                        }
                    }
                }
                if (!$fail) {
                    if (Configure::read('MISP.host_org_id') == 0 || $this->request->data['Server']['remote_org_id'] != Configure::read('MISP.host_org_id')) {
                        $this->request->data['Server']['internal'] = 0;
                    }
                    $this->request->data['Server']['org_id'] = $this->Auth->user('org_id');
                    if (empty($this->request->data['Server']['push_rules'])) {
                        $this->request->data['Server']['push_rules'] = '[]';
                    }
                    if (empty($this->request->data['Server']['pull_rules'])) {
                        $this->request->data['Server']['pull_rules'] = '[]';
                    }
                    if ($this->Server->save($this->request->data)) {
                        if (isset($this->request->data['Server']['submitted_cert'])) {
                            $this->__saveCert($this->request->data, $this->Server->id, false);
                        }
                        if (isset($this->request->data['Server']['submitted_client_cert'])) {
                            $this->__saveCert($this->request->data, $this->Server->id, true);
                        }
                        if ($this->_isRest()) {
                            $server = $this->Server->find('first', array(
                                    'conditions' => array('Server.id' => $this->Server->id),
                                    'recursive' => -1
                            ));
                            return $this->RestResponse->viewData($server, $this->response->type());
                        } else {
                            $this->Flash->success(__('The server has been saved'));
                            $this->redirect(array('action' => 'index'));
                        }
                    } else {
                        if ($this->_isRest()) {
                            return $this->RestResponse->saveFailResponse('Servers', 'add', false, $this->Server->validationErrors, $this->response->type());
                        } else {
                            $this->Flash->error(__('The server could not be saved. Please, try again.'));
                        }
                    }
                }
            }
        }
        if ($this->_isRest()) {
            return $this->RestResponse->describe('Servers', 'add', false, $this->response->type());
        } else {
            $organisationOptions = array(0 => 'Local organisation', 1 => 'External organisation', 2 => 'New external organisation');
            $temp = $this->Server->Organisation->find('all', array(
                    'conditions' => array('local' => true),
                    'fields' => array('id', 'name'),
                    'order' => array('lower(Organisation.name) ASC')
            ));
            $localOrganisations = array();
            $allOrgs = array();
            foreach ($temp as $o) {
                $localOrganisations[$o['Organisation']['id']] = $o['Organisation']['name'];
                $allOrgs[] = array('id' => $o['Organisation']['id'], 'name' => $o['Organisation']['name']);
            }
            $temp = $this->Server->Organisation->find('all', array(
                    'conditions' => array('local' => false),
                    'fields' => array('id', 'name'),
                    'order' => array('lower(Organisation.name) ASC')
            ));
            $externalOrganisations = array();
            foreach ($temp as $o) {
                $externalOrganisations[$o['Organisation']['id']] = $o['Organisation']['name'];
                $allOrgs[] = array('id' => $o['Organisation']['id'], 'name' => $o['Organisation']['name']);
            }
            $this->set('host_org_id', Configure::read('MISP.host_org_id'));
            $this->set('organisationOptions', $organisationOptions);
            $this->set('localOrganisations', $localOrganisations);
            $this->set('externalOrganisations', $externalOrganisations);
            $this->set('allOrganisations', $allOrgs);

            $this->set('allTags', $this->__getTags());
            $this->set('host_org_id', Configure::read('MISP.host_org_id'));
            $this->render('edit');
        }
    }

    public function edit($id = null)
    {
        $this->Server->id = $id;
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        $s = $this->Server->read(null, $id);
        if (!$this->_isSiteAdmin()) {
            $this->redirect(array('controller' => 'servers', 'action' => 'index'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->_isRest()) {
                if (!isset($this->request->data['Server'])) {
                    $this->request->data = array('Server' => $this->request->data);
                }
            }
            if (empty(Configure::read('MISP.host_org_id'))) {
                $this->request->data['Server']['internal'] = 0;
            }
            if (isset($this->request->data['Server']['json'])) {
                $json = json_decode($this->request->data['Server']['json'], true);
            } else {
                $json = null;
            }
            $fail = false;

            // test the filter fields
            if (!empty($this->request->data['Server']['pull_rules']) && !$this->Server->isJson($this->request->data['Server']['pull_rules'])) {
                $fail = true;
                $error_msg = __('The pull filter rules must be in valid JSON format.');
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'edit', false, array('pull_rules' => $error_msg), $this->response->type());
                } else {
                    $this->Flash->error($error_msg);
                }
            }

            if (!$fail && !empty($this->request->data['Server']['push_rules']) && !$this->Server->isJson($this->request->data['Server']['push_rules'])) {
                $fail = true;
                $error_msg = __('The push filter rules must be in valid JSON format.');
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'edit', false, array('push_rules' => $error_msg), $this->response->type());
                } else {
                    $this->Flash->error($error_msg);
                }
            }
            if (!$fail) {
                // say what fields are to be updated
                $fieldList = array('id', 'url', 'push', 'pull', 'push_sightings', 'push_galaxy_clusters', 'pull_galaxy_clusters', 'caching_enabled', 'unpublish_event', 'publish_without_email', 'remote_org_id', 'name' ,'self_signed', 'cert_file', 'client_cert_file', 'push_rules', 'pull_rules', 'internal', 'skip_proxy');
                $this->request->data['Server']['id'] = $id;
                if (isset($this->request->data['Server']['authkey']) && "" != $this->request->data['Server']['authkey']) {
                    $fieldList[] = 'authkey';
                }
                if (isset($this->request->data['Server']['organisation_type']) && isset($json)) {
                    // adds 'remote_org_id' in the fields to update
                    $fieldList[] = 'remote_org_id';
                    if ($this->request->data['Server']['organisation_type'] < 2) {
                        $this->request->data['Server']['remote_org_id'] = $json['id'];
                    } else {
                        $existingOrgs = $this->Server->Organisation->find('first', array(
                                'conditions' => array('uuid' => $json['uuid']),
                                'recursive' => -1,
                                'fields' => array('id', 'uuid')
                        ));
                        if (!empty($existingOrgs)) {
                            $fail = true;
                            if ($this->_isRest()) {
                                return $this->RestResponse->saveFailResponse('Servers', 'edit', false, array('Organisation' => 'Remote Organisation\'s uuid already used'), $this->response->type());
                            } else {
                                $this->Flash->error(__('That organisation could not be created as the uuid is in use already.'));
                            }
                        }

                        if (!$fail) {
                            $this->Server->Organisation->create();
                            $orgSave = $this->Server->Organisation->save(array(
                                    'name' => $json['name'],
                                    'uuid' => $json['uuid'],
                                    'local' => 0,
                                    'created_by' => $this->Auth->user('id')
                            ));

                            if (!$orgSave) {
                                if ($this->_isRest()) {
                                    return $this->RestResponse->saveFailResponse('Servers', 'edit', false, $this->Server->Organisation->validationError, $this->response->type());
                                } else {
                                    $this->Flash->error(__('Couldn\'t save the new organisation, are you sure that the uuid is in the correct format?.'));
                                }
                                $fail = true;
                                $this->request->data['Server']['external_name'] = $json['name'];
                                $this->request->data['Server']['external_uuid'] = $json['uuid'];
                            } else {
                                $this->request->data['Server']['remote_org_id'] = $this->Server->Organisation->id;
                            }
                        }
                    }
                    if (empty(Configure::read('MISP.host_org_id')) || $this->request->data['Server']['remote_org_id'] != Configure::read('MISP.host_org_id')) {
                        $this->request->data['Server']['internal'] = 0;
                    }
                }
            }
            if (!$fail) {
                // Save the data
                if ($this->Server->save($this->request->data, true, $fieldList)) {
                    if (isset($this->request->data['Server']['submitted_cert']) && (!isset($this->request->data['Server']['delete_cert']) || !$this->request->data['Server']['delete_cert'])) {
                        $this->__saveCert($this->request->data, $this->Server->id, false);
                    } else {
                        if (isset($this->request->data['Server']['delete_cert']) && $this->request->data['Server']['delete_cert']) {
                            $this->__saveCert($this->request->data, $this->Server->id, false, true);
                        }
                    }
                    if (isset($this->request->data['Server']['submitted_client_cert']) && (!isset($this->request->data['Server']['delete_client_cert']) || !$this->request->data['Server']['delete_client_cert'])) {
                        $this->__saveCert($this->request->data, $this->Server->id, true);
                    } else {
                        if (isset($this->request->data['Server']['delete_client_cert']) && $this->request->data['Server']['delete_client_cert']) {
                            $this->__saveCert($this->request->data, $this->Server->id, true, true);
                        }
                    }
                    if ($this->_isRest()) {
                        $server = $this->Server->find('first', array(
                                'conditions' => array('Server.id' => $this->Server->id),
                                'recursive' => -1
                        ));
                        return $this->RestResponse->viewData($server, $this->response->type());
                    } else {
                        $this->Flash->success(__('The server has been saved'));
                        $this->redirect(array('action' => 'index'));
                    }
                } else {
                    if ($this->_isRest()) {
                        return $this->RestResponse->saveFailResponse('Servers', 'edit', false, $this->Server->validationError, $this->response->type());
                    } else {
                        $this->Flash->error(__('The server could not be saved. Please, try again.'));
                    }
                }
            }
        } else {
            $this->Server->read(null, $id);
            $this->Server->set('authkey', '');
            $this->request->data = $this->Server->data;
        }
        if ($this->_isRest()) {
            return $this->RestResponse->describe('Servers', 'edit', false, $this->response->type());
        } else {
            $organisationOptions = array(0 => 'Local organisation', 1 => 'External organisation', 2 => 'New external organisation');
            $temp = $this->Server->Organisation->find('all', array(
                    'conditions' => array('local' => true),
                    'fields' => array('id', 'name'),
                    'order' => array('lower(Organisation.name) ASC')
            ));
            $localOrganisations = array();
            $allOrgs = array();
            foreach ($temp as $o) {
                $localOrganisations[$o['Organisation']['id']] = $o['Organisation']['name'];
                $allOrgs[] = array('id' => $o['Organisation']['id'], 'name' => $o['Organisation']['name']);
            }
            $temp = $this->Server->Organisation->find('all', array(
                    'conditions' => array('local' => false),
                    'fields' => array('id', 'name'),
                    'order' => array('lower(Organisation.name) ASC')
            ));
            $externalOrganisations = array();
            foreach ($temp as $o) {
                $externalOrganisations[$o['Organisation']['id']] = $o['Organisation']['name'];
                $allOrgs[] = array('id' => $o['Organisation']['id'], 'name' => $o['Organisation']['name']);
            }

            $oldRemoteSetting = 0;
            if (!$this->Server->data['RemoteOrg']['local']) {
                $oldRemoteSetting = 1;
            }
            $this->set('host_org_id', Configure::read('MISP.host_org_id'));
            $this->set('oldRemoteSetting', $oldRemoteSetting);
            $this->set('oldRemoteOrg', $this->Server->data['RemoteOrg']['id']);

            $this->set('organisationOptions', $organisationOptions);
            $this->set('localOrganisations', $localOrganisations);
            $this->set('externalOrganisations', $externalOrganisations);
            $this->set('allOrganisations', $allOrgs);

            $this->set('allTags', $this->__getTags());
            $this->set('server', $s);
            $this->set('id', $id);
            $this->set('host_org_id', Configure::read('MISP.host_org_id'));
        }
    }

    public function delete($id = null)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException(__('This endpoint expects POST requests.'));
        }
        $this->Server->id = $id;
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        $s = $this->Server->read(null, $id);
        if (!$this->_isSiteAdmin()) {
            $message = __('You don\'t have the privileges to do that.');
            if ($this->_isRest()) {
                throw new MethodNotAllowedException($message);
            } else {
                $this->Flash->error($message);
                $this->redirect(array('controller' => 'servers', 'action' => 'index'));
            }
        }
        if ($this->Server->delete()) {
            $message = __('Server deleted');
            if ($this->_isRest()) {
                return $this->RestResponse->saveSuccessResponse('Servers', 'delete', $message, $this->response->type());
            } else {
                $this->Flash->success($message);
                $this->redirect(array('controller' => 'servers', 'action' => 'index'));
            }

        }
        $message = __('Server was not deleted');
        if ($this->_isRest()) {
            return $this->RestResponse->saveFailResponse('Servers', 'delete', $id, $message, $this->response->type());
        } else {
            $this->Flash->error($message);
            $this->redirect(array('action' => 'index'));
        }
    }

    public function eventBlockRule()
    {
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $setting = $this->AdminSetting->find('first', [
            'conditions' => ['setting' => 'eventBlockRule'],
            'recursive' => -1
        ]);
        if (empty($setting)) {
            $setting = ['setting' => 'eventBlockRule'];
            if ($this->request->is('post')) {
                $this->AdminSetting->create();
            }
        }
        if ($this->request->is('post')) {
            if (!empty($this->request->data['Server'])) {
                $this->request->data = $this->request->data['Server'];
            }
            $setting['AdminSetting']['setting'] = 'eventBlockRule';
            $setting['AdminSetting']['value'] = $this->request->data['value'];
            $result = $this->AdminSetting->save($setting);
            if ($result) {
                $message = __('Settings saved');
            } else {
                $message = __('Could not save the settings. Invalid input.');
            }
            if ($this->_isRest()) {
                if ($result) {
                    return $this->RestResponse->saveFailResponse('Servers', 'eventBlockRule', false, $message, $this->response->type());
                } else {
                    return $this->RestResponse->saveSuccessResponse('Servers', 'eventBlockRule', $message, $this->response->type());
                }
            } else {
                if ($result) {
                    $this->Flash->success($message);
                    $this->redirect('/');
                } else {
                    $this->Flash->error($message);
                }
            }
        }
        $this->set('setting', $setting);
    }

    /**
     * Pull one or more events with attributes from a remote instance.
     * Set $technique to
     *      full - download everything
     *      incremental - only new events
     *      <int>   - specific id of the event to pull
     */
    public function pull($id = null, $technique='full')
    {
        if (!empty($id)) {
            $this->Server->id = $id;
        } else if (!empty($this->request->data['id'])) {
            $this->Server->id = $this->request->data['id'];
        } else {
            throw new NotFoundException(__('Invalid server'));
        }
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        $s = $this->Server->read(null, $id);
        $error = false;
        if (!$this->_isSiteAdmin() && !($s['Server']['org_id'] == $this->Auth->user('org_id') && $this->_isAdmin())) {
            throw new MethodNotAllowedException(__('You are not authorised to do that.'));
        }
        $this->Server->id = $id;
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        if (false == $this->Server->data['Server']['pull'] && ($technique == 'full' || $technique == 'incremental')) {
            $error = __('Pull setting not enabled for this server.');
        }
        if (false == $this->Server->data['Server']['pull_galaxy_clusters'] && ($technique == 'pull_relevant_clusters')) {
            $error = __('Pull setting not enabled for this server.');
        }
        if (empty($error)) {
            if (!Configure::read('MISP.background_jobs')) {
                $result = $this->Server->pull($this->Auth->user(), $id, $technique, $s);
                if (is_array($result)) {
                    $success = sprintf(__('Pull completed. %s events pulled, %s events could not be pulled, %s proposals pulled, %s sightings pulled, %s clusters pulled.', count($result[0]), count($result[1]), $result[2], $result[3], $result[4]));
                } else {
                    $error = $result;
                }
                $this->set('successes', $result[0]);
                $this->set('fails', $result[1]);
                $this->set('pulledProposals', $result[2]);
                $this->set('pulledSightings', $result[3]);
            } else {
                $this->loadModel('Job');
                $this->Job->create();
                $data = array(
                        'worker' => 'default',
                        'job_type' => 'pull',
                        'job_input' => 'Server: ' . $id,
                        'status' => 0,
                        'retries' => 0,
                        'org' => $this->Auth->user('Organisation')['name'],
                        'message' => __('Pulling.'),
                );
                $this->Job->save($data);
                $jobId = $this->Job->id;
                $process_id = CakeResque::enqueue(
                        'default',
                        'ServerShell',
                        array('pull', $this->Auth->user('id'), $id, $technique, $jobId)
                );
                $this->Job->saveField('process_id', $process_id);
                $success = sprintf(__('Pull queued for background execution. Job ID: %s'), $jobId);
            }
        }
        if ($this->_isRest()) {
            if (!empty($error)) {
                return $this->RestResponse->saveFailResponse('Servers', 'pull', false, $error, $this->response->type());
            } else {
                return $this->RestResponse->saveSuccessResponse('Servers', 'pull', $success, $this->response->type());
            }
        } else {
            if (!empty($error)) {
                $this->Flash->error($error);
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Flash->success($success);
                $this->redirect($this->referer());
            }
        }
    }

    public function push($id = null, $technique=false)
    {
        if (!empty($id)) {
            $this->Server->id = $id;
        } else if (!empty($this->request->data['id'])) {
            $this->Server->id = $this->request->data['id'];
        } else {
            throw new NotFoundException(__('Invalid server'));
        }
        if (!empty($this->request->data['technique'])) {
            $technique = $this->request->data['technique'];
        }
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        $s = $this->Server->read(null, $id);
        if (!$this->_isSiteAdmin() && !($s['Server']['org_id'] == $this->Auth->user('org_id') && $this->_isAdmin())) {
            throw new MethodNotAllowedException(__('You are not authorised to do that.'));
        }
        if (!Configure::read('MISP.background_jobs')) {
            $server = $this->Server->read(null, $id);
            App::uses('SyncTool', 'Tools');
            $syncTool = new SyncTool();
            $HttpSocket = $syncTool->setupHttpSocket($server);
            $result = $this->Server->push($id, $technique, false, $HttpSocket, $this->Auth->user());
            if ($result === false) {
                $error = __('The remote server is too outdated to initiate a push towards it. Please notify the hosting organisation of the remote instance.');
            } elseif (!is_array($result)) {
                $error = $result;
            }
            if (!empty($error)) {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'push', false, $error, $this->response->type());
                } else {
                    $this->Flash->info($error);
                    $this->redirect(array('action' => 'index'));
                }
            }
            if ($this->_isRest()) {
                return $this->RestResponse->saveSuccessResponse('Servers', 'push', __('Push complete. %s events pushed, %s events could not be pushed.', count($result[0]), count($result[1])), $this->response->type());
            } else {
                $this->set('successes', $result[0]);
                $this->set('fails', $result[1]);
            }
        } else {
            $this->loadModel('Job');
            $this->Job->create();
            $data = array(
                    'worker' => 'default',
                    'job_type' => 'push',
                    'job_input' => 'Server: ' . $id,
                    'status' => 0,
                    'retries' => 0,
                    'org' => $this->Auth->user('Organisation')['name'],
                    'message' => __('Pushing.'),
            );
            $this->Job->save($data);
            $jobId = $this->Job->id;
            $process_id = CakeResque::enqueue(
                    'default',
                    'ServerShell',
                    array('push', $this->Auth->user('id'), $id, $jobId)
            );
            $this->Job->saveField('process_id', $process_id);
            $message = sprintf(__('Push queued for background execution. Job ID: %s'), $jobId);
            if ($this->_isRest()) {
                return $this->RestResponse->saveSuccessResponse('Servers', 'push', $message, $this->response->type());
            }
            $this->Flash->success($message);
            $this->redirect(array('action' => 'index'));
        }
    }

    private function __saveCert($server, $id, $client = false, $delete = false)
    {
        if ($client) {
            $subm = 'submitted_client_cert';
            $attr = 'client_cert_file';
            $ins  = '_client';
        } else {
            $subm = 'submitted_cert';
            $attr = 'cert_file';
            $ins  = '';
        }
        if (!$delete) {
            $ext = '';
            App::uses('File', 'Utility');
            App::uses('Folder', 'Utility');
            App::uses('FileAccessTool', 'Tools');
            if (isset($server['Server'][$subm]['name'])) {
                if ($this->request->data['Server'][$subm]['size'] != 0) {
                    if (!$this->Server->checkFilename($server['Server'][$subm]['name'])) {
                        throw new Exception(__('Filename not allowed'));
                    }
                    $file = new File($server['Server'][$subm]['name']);
                    $ext = $file->ext();
                    if (!$server['Server'][$subm]['size'] > 0) {
                        $this->Flash->error(__('Incorrect extension or empty file.'));
                        $this->redirect(array('action' => 'index'));
                    }

                    // read pem file data
                    $pemData = (new FileAccessTool())->readFromFile($server['Server'][$subm]['tmp_name'], $server['Server'][$subm]['size']);
                } else {
                    return true;
                }
            } else {
                $pemData = base64_decode($server['Server'][$subm]);
            }
            $destpath = APP . "files" . DS . "certs" . DS;
            $dir = new Folder(APP . "files" . DS . "certs", true);
            $pemfile = new File($destpath . $id . $ins . '.' . $ext);
            $result = $pemfile->write($pemData);
            $s = $this->Server->read(null, $id);
            $s['Server'][$attr] = $s['Server']['id'] . $ins . '.' . $ext;
            if ($result) {
                $this->Server->save($s);
            }
        } else {
            $s = $this->Server->read(null, $id);
            $s['Server'][$attr] = '';
            $this->Server->save($s);
        }
        return true;
    }

    public function serverSettingsReloadSetting($setting, $id)
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        $pathToSetting = explode('.', $setting);
        if (strpos($setting, 'Plugin.Enrichment') !== false || strpos($setting, 'Plugin.Import') !== false || strpos($setting, 'Plugin.Export') !== false || strpos($setting, 'Plugin.Cortex') !== false) {
            $settingObject = $this->Server->getCurrentServerSettings();
        } else {
            $settingObject = $this->Server->serverSettings;
        }
        foreach ($pathToSetting as $key) {
            if (!isset($settingObject[$key])) {
                throw new MethodNotAllowedException();
            }
            $settingObject = $settingObject[$key];
        }
        $result = $this->Server->serverSettingReadSingle($settingObject, $setting, $key);
        $this->set('setting', $result);
        $priorityErrorColours = array(0 => 'red', 1 => 'yellow', 2 => 'green');
        $this->set('priorityErrorColours', $priorityErrorColours);
        $priorities = array(0 => 'Critical', 1 => 'Recommended', 2 => 'Optional', 3 => 'Deprecated');
        $this->set('priorities', $priorities);
        $this->set('k', $id);
        $this->layout = false;

        $subGroup = 'general';
        if ($pathToSetting[0] === 'Plugin') {
            $subGroup = explode('_', $pathToSetting[1])[0];
        }
        $this->set('subGroup', $subGroup);

        $this->render('/Elements/healthElements/settings_row');
    }

    public function serverSettings($tab=false)
    {
        if (!$this->request->is('get')) {
            throw new MethodNotAllowedException('Just GET method is allowed.');
        }
        $tabs = array(
            'MISP' => array('count' => 0, 'errors' => 0, 'severity' => 5),
            'Encryption' => array('count' => 0, 'errors' => 0, 'severity' => 5),
            'Proxy' => array('count' => 0, 'errors' => 0, 'severity' => 5),
            'Security' => array('count' => 0, 'errors' => 0, 'severity' => 5),
            'Plugin' => array('count' => 0, 'errors' => 0, 'severity' => 5)
        );
        $writeableErrors = array(0 => __('OK'), 1 => __('not found'), 2 => __('is not writeable'));
        $readableErrors = array(0 => __('OK'), 1 => __('not readable'));
        $gpgErrors = array(0 => __('OK'), 1 => __('FAIL: settings not set'), 2 => __('FAIL: Failed to load GnuPG'), 3 => __('FAIL: Issues with the key/passphrase'), 4 => __('FAIL: sign failed'));
        $proxyErrors = array(0 => __('OK'), 1 => __('not configured (so not tested)'), 2 => __('Getting URL via proxy failed'));
        $zmqErrors = array(0 => __('OK'), 1 => __('not enabled (so not tested)'), 2 => __('Python ZeroMQ library not installed correctly.'), 3 => __('ZeroMQ script not running.'));
        $stixOperational = array(0 => __('Some of the libraries related to STIX are not installed. Make sure that all libraries listed below are correctly installed.'), 1 => __('OK'));
        $stixVersion = array(0 => __('Incorrect STIX version installed, found $current, expecting $expected'), 1 => __('OK'));
        $stix2Version = array(0 => __('Incorrect STIX2 version installed, found $current, expecting $expected'), 1 => __('OK'));
        $cyboxVersion = array(0 => __('Incorrect CyBox version installed, found $current, expecting $expected'), 1 => __('OK'));
        $mixboxVersion = array(0 => __('Incorrect mixbox version installed, found $current, expecting $expected'), 1 => __('OK'));
        $maecVersion = array(0 => __('Incorrect maec version installed, found $current, expecting $expected'), 1 => __('OK'));
        $pymispVersion = array(0 => __('Incorrect PyMISP version installed, found $current, expecting $expected'), 1 => __('OK'));
        $sessionErrors = array(0 => __('OK'), 1 => __('High'), 2 => __('Alternative setting used'), 3 => __('Test failed'));
        $moduleErrors = array(0 => __('OK'), 1 => __('System not enabled'), 2 => __('No modules found'));

        $finalSettings = $this->Server->serverSettingsRead();
        $issues = array(
            'errors' => array(
                0 => array(
                    'value' => 0,
                    'description' => __('MISP will not operate correctly or will be unsecure until these issues are resolved.')
                ),
                1 => array(
                    'value' => 0,
                    'description' => __('Some of the features of MISP cannot be utilised until these issues are resolved.')
                ),
                2 => array(
                    'value' => 0,
                    'description' => __('There are some optional tweaks that could be done to improve the looks of your MISP instance.')
                ),
            ),
            'deprecated' => array(),
            'overallHealth' => 3,
        );
        $dumpResults = array();
        $tempArray = array();
        foreach ($finalSettings as $k => $result) {
            if ($result['level'] == 3) {
                $issues['deprecated']++;
            }
            $tabs[$result['tab']]['count']++;
            if (isset($result['error']) && $result['level'] < 3) {
                $issues['errors'][$result['level']]['value']++;
                if ($result['level'] < $issues['overallHealth']) {
                    $issues['overallHealth'] = $result['level'];
                }
                $tabs[$result['tab']]['errors']++;
                if ($result['level'] < $tabs[$result['tab']]['severity']) {
                    $tabs[$result['tab']]['severity'] = $result['level'];
                }
            }
            if (isset($result['optionsSource']) && is_callable($result['optionsSource'])) {
                $result['options'] = $result['optionsSource']();
            }
            $dumpResults[] = $result;
            if ($result['tab'] == $tab) {
                if (isset($result['subGroup'])) {
                    $tempArray[$result['subGroup']][] = $result;
                } else {
                    $tempArray['general'][] = $result;
                }
            }
        }
        $finalSettings = $tempArray;
        // Diagnostics portion
        $diagnostic_errors = 0;
        App::uses('File', 'Utility');
        App::uses('Folder', 'Utility');
        if ($tab === 'files') {
            $files = $this->Server->grabFiles();
            $this->set('files', $files);
        }
        // Only run this check on the diagnostics tab
        if ($tab === 'diagnostics' || $tab === 'download' || $this->_isRest()) {
            $php_ini = php_ini_loaded_file();
            $this->set('php_ini', $php_ini);

            $attachmentTool = new AttachmentTool();
            try {
                $advanced_attachments = $attachmentTool->checkAdvancedExtractionStatus($this->Server->getPythonVersion());
            } catch (Exception $e) {
                $this->log($e->getMessage(), LOG_NOTICE);
                $advanced_attachments = false;
            }

            $this->set('advanced_attachments', $advanced_attachments);
            // check if the current version of MISP is outdated or not
            $version = $this->__checkVersion();
            $this->set('version', $version);
            $gitStatus = $this->Server->getCurrentGitStatus();
            $this->set('branch', $gitStatus['branch']);
            $this->set('commit', $gitStatus['commit']);
            $this->set('latestCommit', $gitStatus['latestCommit']);

            $phpSettings = array(
                'max_execution_time' => array(
                    'explanation' => 'The maximum duration that a script can run (does not affect the background workers). A too low number will break long running scripts like comprehensive API exports',
                    'recommended' => 300,
                    'unit' => 'seconds',
                ),
                'memory_limit' => array(
                    'explanation' => 'The maximum memory that PHP can consume. It is recommended to raise this number since certain exports can generate a fair bit of memory usage',
                    'recommended' => 2048,
                    'unit' => 'MB'
                ),
                'upload_max_filesize' => array(
                    'explanation' => 'The maximum size that an uploaded file can be. It is recommended to raise this number to allow for the upload of larger samples',
                    'recommended' => 50,
                    'unit' => 'MB'
                ),
                'post_max_size' => array(
                    'explanation' => 'The maximum size of a POSTed message, this has to be at least the same size as the upload_max_filesize setting',
                    'recommended' => 50,
                    'unit' => 'MB'
                )
            );

            foreach ($phpSettings as $setting => $settingArray) {
                $phpSettings[$setting]['value'] = $this->Server->getIniSetting($setting);
                if ($phpSettings[$setting]['value'] && $settingArray['unit'] && $settingArray['unit'] === 'MB') {
                    // convert basic unit to M
                    $phpSettings[$setting]['value'] = (int) floor($phpSettings[$setting]['value'] / 1024 / 1024);
                }
            }
            $this->set('phpSettings', $phpSettings);

            if ($version && (!$version['upToDate'] || $version['upToDate'] == 'older')) {
                $diagnostic_errors++;
            }

            // check if the STIX and Cybox libraries are working and the correct version using the test script stixtest.py
            $stix = $this->Server->stixDiagnostics($diagnostic_errors, $stixVersion, $cyboxVersion, $mixboxVersion, $maecVersion, $stix2Version, $pymispVersion);

            $yaraStatus = $this->Server->yaraDiagnostics($diagnostic_errors);

            // if GnuPG is set up in the settings, try to encrypt a test message
            $gpgStatus = $this->Server->gpgDiagnostics($diagnostic_errors);

            // if the message queue pub/sub is enabled, check whether the extension works
            $zmqStatus = $this->Server->zmqDiagnostics($diagnostic_errors);

            // if Proxy is set up in the settings, try to connect to a test URL
            $proxyStatus = $this->Server->proxyDiagnostics($diagnostic_errors);

            // get the DB diagnostics
            $dbDiagnostics = $this->Server->dbSpaceUsage();
            $dbSchemaDiagnostics = $this->Server->dbSchemaDiagnostic();

            $redisInfo = $this->Server->redisInfo();

            $moduleTypes = array('Enrichment', 'Import', 'Export', 'Cortex');
            foreach ($moduleTypes as $type) {
                $moduleStatus[$type] = $this->Server->moduleDiagnostics($diagnostic_errors, $type);
            }

            // check the size of the session table
            $sessionCount = 0;
            $sessionStatus = $this->Server->sessionDiagnostics($diagnostic_errors, $sessionCount);
            $this->set('sessionCount', $sessionCount);

            $this->loadModel('AttachmentScan');
            try {
                $attachmentScan = ['status' => true, 'software' => $this->AttachmentScan->diagnostic()];
            } catch (Exception $e) {
                $attachmentScan = ['status' => false, 'error' => $e->getMessage()];
            }

            $securityAudit = (new SecurityAudit())->run($this->Server);

            $view = compact('gpgStatus', 'sessionErrors', 'proxyStatus', 'sessionStatus', 'zmqStatus', 'stixVersion', 'cyboxVersion', 'mixboxVersion', 'maecVersion', 'stix2Version', 'pymispVersion', 'moduleStatus', 'yaraStatus', 'gpgErrors', 'proxyErrors', 'zmqErrors', 'stixOperational', 'stix', 'moduleErrors', 'moduleTypes', 'dbDiagnostics', 'dbSchemaDiagnostics', 'redisInfo', 'attachmentScan', 'securityAudit');
        } else {
            $view = [];
        }

        // check whether the files are writeable
        $writeableDirs = $this->Server->writeableDirsDiagnostics($diagnostic_errors);
        $writeableFiles = $this->Server->writeableFilesDiagnostics($diagnostic_errors);
        $readableFiles = $this->Server->readableFilesDiagnostics($diagnostic_errors);
        $extensions = $this->Server->extensionDiagnostics();

        // check if the encoding is not set to utf8
        $dbEncodingStatus = $this->Server->databaseEncodingDiagnostics($diagnostic_errors);

        $view = array_merge($view, compact('diagnostic_errors', 'tabs', 'tab', 'issues', 'finalSettings', 'writeableErrors', 'readableErrors', 'writeableDirs', 'writeableFiles', 'readableFiles', 'extensions', 'dbEncodingStatus'));
        $this->set($view);

        $workerIssueCount = 4;
        $worker_array = array();
        if (Configure::read('MISP.background_jobs')) {
            $workerIssueCount = 0;
            $worker_array = $this->Server->workerDiagnostics($workerIssueCount);
        }
        $this->set('worker_array', $worker_array);
        if ($tab === 'download' || $this->_isRest()) {
            foreach ($dumpResults as $key => $dr) {
                unset($dumpResults[$key]['description']);
            }
            $dump = array(
                'version' => $version,
                'phpSettings' => $phpSettings,
                'gpgStatus' => $gpgErrors[$gpgStatus['status']],
                'proxyStatus' => $proxyErrors[$proxyStatus],
                'zmqStatus' => $zmqStatus,
                'stix' => $stix,
                'moduleStatus' => $moduleStatus,
                'writeableDirs' => $writeableDirs,
                'writeableFiles' => $writeableFiles,
                'readableFiles' => $readableFiles,
                'dbDiagnostics' => $dbDiagnostics,
                'dbSchemaDiagnostics' => $dbSchemaDiagnostics,
                'redisInfo' => $redisInfo,
                'finalSettings' => $dumpResults,
                'extensions' => $extensions,
                'workers' => $worker_array
            );
            foreach ($dump['finalSettings'] as $k => $v) {
                if (!empty($v['redacted'])) {
                    $dump['finalSettings'][$k]['value'] = '*****';
                }
            }
            $this->response->body(json_encode($dump, JSON_PRETTY_PRINT));
            $this->response->type('json');
            $this->response->download('MISP.report.json');
            return $this->response;
        }

        $priorities = array(0 => 'Critical', 1 => 'Recommended', 2 => 'Optional', 3 => 'Deprecated');
        $this->set('priorities', $priorities);
        $this->set('workerIssueCount', $workerIssueCount);
        $priorityErrorColours = array(0 => 'red', 1 => 'yellow', 2 => 'green');
        $this->set('priorityErrorColours', $priorityErrorColours);
        $this->set('phpversion', phpversion());
        $this->set('phpmin', $this->phpmin);
        $this->set('phprec', $this->phprec);
        $this->set('phptoonew', $this->phptoonew);
        $this->set('pythonmin', $this->pythonmin);
        $this->set('pythonrec', $this->pythonrec);
        $this->set('pymisp', $this->pymisp);
    }

    public function startWorker($type)
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $validTypes = array('default', 'email', 'scheduler', 'cache', 'prio', 'update');
        if (!in_array($type, $validTypes)) {
            throw new MethodNotAllowedException('Invalid worker type.');
        }
        $prepend = '';
        if ($type != 'scheduler') {
            $workerIssueCount = 0;
            $workerDiagnostic = $this->Server->workerDiagnostics($workerIssueCount);
            if ($type == 'update' && isset($workerDiagnostic['update']['ok']) && $workerDiagnostic['update']['ok']) {
                $message = __('Only one `update` worker can run at a time');
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'startWorker', false, $message, $this->response->type());
                } else {
                    $this->Flash->error($message);
                    $this->redirect('/servers/serverSettings/workers');
                }
            }
            shell_exec($prepend . APP . 'Console' . DS . 'cake CakeResque.CakeResque start --interval 5 --queue ' . $type .' > /dev/null 2>&1 &');
        } else {
            shell_exec($prepend . APP . 'Console' . DS . 'cake CakeResque.CakeResque startscheduler -i 5 > /dev/null 2>&1 &');
        }
        $message = __('Worker start signal sent');
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Servers', 'startWorker', $type, $this->response->type(), $message);
        } else {
            $this->Flash->info($message);
            $this->redirect('/servers/serverSettings/workers');
        }
    }

    public function stopWorker($pid)
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Server->killWorker($pid, $this->Auth->user());
        $message = __('Worker stop signal sent');
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Servers', 'stopWorker', $pid, $this->response->type(), $message);
        } else {
            $this->Flash->info($message);
            $this->redirect('/servers/serverSettings/workers');
        }
    }

    public function getWorkers()
    {
        if (Configure::read('MISP.background_jobs')) {
            $workerIssueCount = 0;
            $worker_array = $this->Server->workerDiagnostics($workerIssueCount);
        } else {
            $worker_array = [__('Background jobs not enabled')];
        }
        return $this->RestResponse->viewData($worker_array);
    }

    private function __checkVersion()
    {
        App::uses('SyncTool', 'Tools');
        $syncTool = new SyncTool();
        try {
            $HttpSocket = $syncTool->setupHttpSocket();
            $response = $HttpSocket->get('https://api.github.com/repos/MISP/MISP/tags');
            $tags = $response->body;
        } catch (Exception $e) {
            return false;
        }
        if ($response->isOK() && !empty($tags)) {
            $json_decoded_tags = json_decode($tags);

            // find the latest version tag in the v[major].[minor].[hotfix] format
            for ($i = 0; $i < count($json_decoded_tags); $i++) {
                if (preg_match('/^v[0-9]+\.[0-9]+\.[0-9]+$/', $json_decoded_tags[$i]->name)) {
                    break;
                }
            }
            return $this->Server->checkVersion($json_decoded_tags[$i]->name);
        } else {
            return false;
        }
    }

    public function idTranslator($localId = null)
    {
        // We retrieve the list of remote servers that we can query
        $servers = $this->Server->find('all', [
            'conditions' => ['OR' => ['pull' => true, 'push' => true]],
            'recursive' => -1,
            'order' => ['Server.priority ASC'],
        ]);

        // We generate the list of servers for the dropdown
        $displayServers = array();
        foreach ($servers as $s) {
            $displayServers[] = [
                'name' => $s['Server']['name'],
                'value' => $s['Server']['id'],
            ];
        }
        $this->set('servers', $displayServers);

        if ($localId || $this->request->is('post')) {
            if ($localId && $this->request->is('get')) {
                $this->request->data['Event']['local'] = 'local';
                $this->request->data['Event']['uuid'] = $localId;
            }
            $remote_events = array();
            if (!empty($this->request->data['Event']['uuid']) && $this->request->data['Event']['local'] === "local") {
                $local_event = $this->Event->fetchSimpleEvent($this->Auth->user(), $this->request->data['Event']['uuid']);
            } else if (!empty($this->request->data['Event']['uuid']) && $this->request->data['Event']['local'] === "remote" && !empty($this->request->data['Server']['id'])) {
                //We check on the remote server for any event with this id and try to find a match locally
                $conditions = array('AND' => array('Server.id' => $this->request->data['Server']['id'], 'Server.pull' => true));
                $remote_server = $this->Server->find('first', array('conditions' => $conditions));
                if (!empty($remote_server)) {
                    try {
                        $remote_event = $this->Event->downloadEventFromServer($this->request->data['Event']['uuid'], $remote_server, null, true);
                    } catch (Exception $e) {
                        $this->Flash->error(__("Issue while contacting the remote server to retrieve event information"));
                        return;
                    }

                    $local_event = $this->Event->fetchSimpleEvent($this->Auth->user(), $remote_event[0]['uuid']);
                    // we record it to avoid re-querying the same server in the 2nd phase
                    if (!empty($local_event)) {
                        $remote_events[] = array(
                            "server_id" => $remote_server['Server']['id'],
                            "server_name" => $remote_server['Server']['name'],
                            "url" => $remote_server['Server']['url']."/events/view/".$remote_event[0]['id'],
                            "remote_id" => $remote_event[0]['id']
                        );
                    }
                }
            }
            if (empty($local_event)) {
                $this->Flash->error(__("This event could not be found or you don't have permissions to see it."));
                return;
            } else {
                $this->Flash->success(__('The event has been found.'));
            }

            // In the second phase, we query all configured sync servers to get their info on the event
            foreach ($servers as $server) {
                // We check if the server was not already contacted in phase 1
                if (count($remote_events) > 0 && $remote_events[0]['server_id'] == $server['Server']['id']) {
                    continue;
                }

                $exception = null;
                try {
                    $remoteEvent = $this->Event->downloadEventFromServer($local_event['Event']['uuid'], $server, null, true);
                } catch (Exception $e) {
                    $remoteEvent = null;
                    $exception = $e->getMessage();
                }
                $remoteEventId = isset($remoteEvent[0]['id']) ? $remoteEvent[0]['id'] : null;
                $remote_events[] = array(
                    "server_id" => $server['Server']['id'],
                    "server_name" => $server['Server']['name'],
                    "url" => isset($remoteEventId) ? $server['Server']['url'] . "/events/view/" . $remoteEventId : $server['Server']['url'],
                    "remote_id" => isset($remoteEventId) ? $remoteEventId : false,
                    "exception" => $exception,
                );
            }

            $this->set('local_event', $local_event);
            $this->set('remote_events', $remote_events);
        }
        $this->set('title_for_layout', __('Event ID translator'));
    }

    public function getSubmodulesStatus()
    {
        $this->set('submodules', $this->Server->getSubmodulesGitStatus());
        $this->render('ajax/submoduleStatus');
    }

    public function getSetting($setting_name)
    {
        $setting = $this->Server->getSettingData($setting_name);
        if (!empty($setting["redacted"])) {
            throw new MethodNotAllowedException(__('This setting is redacted.'));
        }
        if (Configure::check($setting_name)) {
            $setting['value'] = Configure::read($setting_name);
        }
        return $this->RestResponse->viewData($setting);
    }

    public function serverSettingsEdit($setting_name, $id = false, $forceSave = false)
    {
        if (!isset($setting_name)) {
            throw new MethodNotAllowedException();
        }
        if (!$this->_isRest()) {
            if (!isset($id)) {
                throw new MethodNotAllowedException();
            }
            $this->set('id', $id);
        }

        $setting = $this->Server->getSettingData($setting_name);
        if ($setting === false) {
            throw new NotFoundException(__('Setting %s is invalid.', $setting_name));
        }
        if (!empty($setting['cli_only'])) {
            throw new MethodNotAllowedException(__('This setting can only be edited via the CLI.'));
        }
        if ($this->request->is('get')) {
            $value = Configure::read($setting['name']);
            if (isset($value)) {
                $setting['value'] = $value;
            }
            $setting['setting'] = $setting['name'];
            if (isset($setting['optionsSource']) && is_callable($setting['optionsSource'])) {
                $setting['options'] = $setting['optionsSource']();
            }
            $subGroup = explode('.', $setting['name']);
            if ($subGroup[0] === 'Plugin') {
                $subGroup = explode('_', $subGroup[1])[0];
            } else {
                $subGroup = 'general';
            }
            if ($this->_isRest()) {
                return $this->RestResponse->viewData(array($setting['name'] => $setting['value']));
            } else {
                $this->set('subGroup', $subGroup);
                $this->set('setting', $setting);
                $this->render('ajax/server_settings_edit');
            }
        } else if ($this->request->is('post')) {
            if (!isset($this->request->data['Server'])) {
                $this->request->data = array('Server' => $this->request->data);
            }
            if (!isset($this->request->data['Server']['value']) || !is_scalar($this->request->data['Server']['value'])) {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'serverSettingsEdit', false, 'Invalid input. Expected: {"value": "new_setting"}', $this->response->type());
                }
            }
            if (!empty($this->request->data['Server']['force'])) {
                $forceSave = $this->request->data['Server']['force'];
            }
            if (trim($this->request->data['Server']['value']) === '*****') {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'serverSettingsEdit', false, 'No change.', $this->response->type());
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => 'No change.')), 'status'=>200, 'type' => 'json'));
                }
            }
            $this->autoRender = false;
            $this->loadModel('Log');
            if (!is_writeable(APP . 'Config/config.php')) {
                $this->Log->create();
                $this->Log->save(array(
                        'org' => $this->Auth->user('Organisation')['name'],
                        'model' => 'Server',
                        'model_id' => 0,
                        'email' => $this->Auth->user('email'),
                        'action' => 'serverSettingsEdit',
                        'user_id' => $this->Auth->user('id'),
                        'title' => 'Server setting issue',
                        'change' => 'There was an issue witch changing ' . $setting['name'] . ' to ' . $this->request->data['Server']['value']  . '. The error message returned is: app/Config.config.php is not writeable to the apache user. No changes were made.',
                ));
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'serverSettingsEdit', false, 'app/Config.config.php is not writeable to the apache user.', $this->response->type());
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => 'app/Config.config.php is not writeable to the apache user.')), 'status'=>200, 'type' => 'json'));
                }
            }
            $result = $this->Server->serverSettingsEditValue($this->Auth->user(), $setting, $this->request->data['Server']['value'], $forceSave);
            if ($result === true) {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveSuccessResponse('Servers', 'serverSettingsEdit', false, $this->response->type(), 'Field updated');
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => true, 'success' => 'Field updated.')), 'status'=>200, 'type' => 'json'));
                }
            } else {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'serverSettingsEdit', false, $result, $this->response->type());
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => $result)), 'status'=>200, 'type' => 'json'));
                }
            }
        }
    }

    public function killAllWorkers($force = false)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Server->killAllWorkers($this->Auth->user(), $force);
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Server', 'killAllWorkers', false, $this->response->type(), __('Killing workers.'));
        }
        $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'workers'));
    }

    public function restartWorkers()
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Server->restartWorkers($this->Auth->user());
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Server', 'restartWorkers', false, $this->response->type(), __('Restarting workers.'));
        }
        $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'workers'));
    }

    public function restartDeadWorkers()
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Server->restartDeadWorkers($this->Auth->user());
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Server', 'restartDeadWorkers', false, $this->response->type(), __('Restarting workers.'));
        }
        $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'workers'));
    }

    public function deleteFile($type, $filename)
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        if ($this->request->is('post')) {
            $validItems = $this->Server->getFileRules();
            App::uses('File', 'Utility');
            $existingFile = new File($validItems[$type]['path'] . DS . $filename);
            if (!$existingFile->exists()) {
                $this->Flash->error(__('File not found.', true), 'default', array(), 'error');
                $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
            }
            if ($existingFile->delete()) {
                $this->Flash->success('File deleted.');
            } else {
                $this->Flash->error(__('File could not be deleted.', true), 'default', array(), 'error');
            }
            $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
        } else {
            throw new MethodNotAllowedException('This action expects a POST request.');
        }
    }

    public function uploadFile($type)
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $validItems = $this->Server->getFileRules();

        // Check if there were problems with the file upload
        // only keep the last part of the filename, this should prevent directory attacks
        $filename = basename($this->request->data['Server']['file']['name']);
        if (!preg_match("/" . $validItems[$type]['regex'] . "/", $filename)) {
            $this->Flash->error($validItems[$type]['regex_error'], 'default', array(), 'error');
            $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
        }
        if (empty($this->request->data['Server']['file']['tmp_name']) || !is_uploaded_file($this->request->data['Server']['file']['tmp_name'])) {
            $this->Flash->error(__('Upload failed.', true), 'default', array(), 'error');
            $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
        }

        // check if the file already exists
        App::uses('File', 'Utility');
        $existingFile = new File($validItems[$type]['path'] . DS . $filename);
        if ($existingFile->exists()) {
            $this->Flash->info(__('File already exists. If you would like to replace it, remove the old one first.', true), 'default', array(), 'error');
            $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
        }

        $result = move_uploaded_file($this->request->data['Server']['file']['tmp_name'], $validItems[$type]['path'] . DS . $filename);
        if ($result) {
            $this->Flash->success('File uploaded.');
        } else {
            $this->Flash->error(__('Upload failed.', true), 'default', array(), 'error');
        }
        $this->redirect(array('controller' => 'servers', 'action' => 'serverSettings', 'files'));
    }

    public function fetchServersForSG($idList = '{}')
    {
        $id_exclusion_list = json_decode($idList, true);
        $temp = $this->Server->find('all', array(
                'conditions' => array(
                        'id !=' => $id_exclusion_list,
                ),
                'recursive' => -1,
                'fields' => array('id', 'name', 'url')
        ));
        $servers = array();
        foreach ($temp as $server) {
            $servers[] = array('id' => $server['Server']['id'], 'name' => $server['Server']['name'], 'url' => $server['Server']['url']);
        }
        $this->layout = false;
        $this->autoRender = false;
        $this->set('servers', $servers);
        $this->render('ajax/fetch_servers_for_sg');
    }

    public function postTest()
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException('Invalid request, expecting a POST request.');
        }
        // Fix for PHP-FPM / Nginx / etc
        // Fix via https://www.popmartian.com/tipsntricks/2015/07/14/howto-use-php-getallheaders-under-fastcgi-php-fpm-nginx-etc/
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) === 'HTTP_') {
                    $headers[strtolower(str_replace('_', '-', substr($name, 5)))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
            $headers = array_change_key_case($headers, CASE_LOWER);
        }
        $result = [
            'body' => $this->request->data,
            'headers' => [
                'Content-type' => isset($headers['content-type']) ? $headers['content-type'] : 0,
                'Accept' => isset($headers['accept']) ? $headers['accept'] : 0,
                'Authorization' => isset($headers['authorization']) ? 'OK' : 0,
            ],
        ];
        return new CakeResponse(array('body'=> json_encode($result), 'type' => 'json'));
    }

    public function getRemoteUser($id)
    {
        $this->Server->id = $id;
        if (!$this->Server->exists()) {
            throw new NotFoundException(__('Invalid server'));
        }
        $user = $this->Server->getRemoteUser($id);
        if (empty($user)) {
            throw new NotFoundException(__('Invalid user or user not found.'));
        } else {
            return $this->RestResponse->viewData($user);
        }
    }

    public function testConnection($id = false)
    {
        $server = $this->Server->find('first', ['conditions' => ['Server.id' => $id]]);
        if (!$server) {
            throw new NotFoundException(__('Invalid server'));
        }
        $result = $this->Server->runConnectionTest($server);
        if ($result['status'] == 1) {
            if (isset($result['info']['version']) && preg_match('/^[0-9]+\.+[0-9]+\.[0-9]+$/', $result['info']['version'])) {
                $perm_sync = false;
                if (isset($result['info']['perm_sync'])) {
                    $perm_sync = $result['info']['perm_sync'];
                }
                $perm_sighting = false;
                if (isset($result['info']['perm_sighting'])) {
                    $perm_sighting = $result['info']['perm_sighting'];
                }
                App::uses('Folder', 'Utility');
                $file = new File(ROOT . DS . 'VERSION.json', true);
                $local_version = json_decode($file->read(), true);
                $file->close();
                $version = explode('.', $result['info']['version']);
                $mismatch = false;
                $newer = false;
                $parts = array('major', 'minor', 'hotfix');
                if ($version[0] == 2 && $version[1] == 4 && $version[2] > 68) {
                    $post = $this->Server->runPOSTTest($server);
                }
                foreach ($parts as $k => $v) {
                    if (!$mismatch) {
                        if ($version[$k] > $local_version[$v]) {
                            $mismatch = $v;
                            $newer = 'remote';
                        } elseif ($version[$k] < $local_version[$v]) {
                            $mismatch = $v;
                            $newer = 'local';
                        }
                    }
                }
                if (!$mismatch && $version[2] < 111) {
                    $mismatch = 'proposal';
                }
                if (!$perm_sync && !$perm_sighting) {
                    $result['status'] = 7;
                    return new CakeResponse(array('body'=> json_encode($result), 'type' => 'json'));
                }
                if (!$perm_sync && $perm_sighting) {
                    $result['status'] = 8;
                    return new CakeResponse(array('body'=> json_encode($result), 'type' => 'json'));
                }
                return $this->RestResponse->viewData([
                    'status' => 1,
                    'local_version' => implode('.', $local_version),
                    'version' => implode('.', $version),
                    'mismatch' => $mismatch,
                    'newer' => $newer,
                    'post' => isset($post) ? $post['status'] : 'too old',
                    'response_encoding' => isset($post['content-encoding']) ? $post['content-encoding'] : null,
                    'request_encoding' => isset($result['info']['request_encoding']) ? $result['info']['request_encoding'] : null,
                    'client_certificate' => $result['client_certificate'],
                ], 'json');
            } else {
                $result['status'] = 3;
            }
        }
        return new CakeResponse(array('body'=> json_encode($result), 'type' => 'json'));
    }

    public function startZeroMQServer()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        $pubSubTool = $this->Server->getPubSubTool();
        $result = $pubSubTool->restartServer();
        if ($result === true) {
            return new CakeResponse(array('body'=> json_encode(array('saved' => true, 'success' => 'ZeroMQ server successfully started.')), 'status'=>200, 'type' => 'json'));
        } else {
            return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => $result)), 'status'=>200, 'type' => 'json'));
        }
    }

    public function stopZeroMQServer()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        $pubSubTool = $this->Server->getPubSubTool();
        $result = $pubSubTool->killService();
        if ($result === true) {
            return new CakeResponse(array('body'=> json_encode(array('saved' => true, 'success' => 'ZeroMQ server successfully killed.')), 'status'=>200, 'type' => 'json'));
        } else {
            return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => 'Could not kill the previous instance of the ZeroMQ script.')), 'status'=>200, 'type' => 'json'));
        }
    }

    public function statusZeroMQServer()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        $pubSubTool = $this->Server->getPubSubTool();
        $result = $pubSubTool->statusCheck();
        if (!empty($result)) {
            $this->set('events', $result['publishCount']);
            $this->set('messages', $result['messageCount']);
            $this->set('time', date('Y/m/d H:i:s', $result['timestamp']));
            $this->set('time2', date('Y/m/d H:i:s', $result['timestampSettings']));
        }
        $this->render('ajax/zeromqstatus');
    }

    public function purgeSessions()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        if ($this->Server->updateDatabase('cleanSessionTable') == false) {
            $this->Flash->error('Could not purge the session table.');
        }
        $this->redirect('/servers/serverSettings/diagnostics');
    }

    public function clearWorkerQueue($worker)
    {
        if (!$this->_isSiteAdmin() || !$this->request->is('Post') || $this->request->is('ajax')) {
            throw new MethodNotAllowedException();
        }
        $worker_array = array('cache', 'default', 'email', 'prio');
        if (!in_array($worker, $worker_array)) {
            throw new MethodNotAllowedException('Invalid worker');
        }
        $redis = Resque::redis();
        $redis->del('queue:' . $worker);
        $this->Flash->success('Queue cleared.');
        $this->redirect($this->referer());
    }

    public function getVersion()
    {
        $versionArray = $this->Server->checkMISPVersion();
        $response = [
            'version' => $versionArray['major'] . '.' . $versionArray['minor'] . '.' . $versionArray['hotfix'],
            'perm_sync' => $this->userRole['perm_sync'],
            'perm_sighting' => $this->userRole['perm_sighting'],
            'perm_galaxy_editor' => $this->userRole['perm_galaxy_editor'],
            'request_encoding' => $this->CompressedRequestHandler->supportedEncodings(),
        ];
        return $this->RestResponse->viewData($response, $this->response->type());
    }

    public function getPyMISPVersion()
    {
        $this->set('response', array('version' => $this->pyMispVersion));
        $this->set('_serialize', 'response');
    }

    public function checkout()
    {
        $result = $this->Server->checkoutMain();
    }

    public function update($branch = false)
    {
        if ($this->request->is('post')) {
            $branch = false;
            $filterData = array(
                'request' => $this->request,
                'named_params' => $this->params['named'],
                'paramArray' => ['branch'],
                'ordered_url_params' => @compact($paramArray),
                'additional_delimiters' => PHP_EOL
            );
            $exception = false;
            $settings = $this->_harvestParameters($filterData, $exception);
            $status = $this->Server->getCurrentGitStatus();
            $raw = array();
            if (empty($status['branch'])) { // do not try to update if you are not on branch
                $msg = 'Update failed, you are not on branch';
                $raw[] = $msg;
                $update = $msg;
            } else {
                if ($settings === false) {
                    $settings = [];
                }
                $update = $this->Server->update($status, $raw, $settings);
            }
            if ($this->_isRest()) {
                return $this->RestResponse->viewData(array('results' => $raw), $this->response->type());
            } else {
                return new CakeResponse(array('body' => $update, 'type' => 'txt'));
            }
        } else {
            $branch = $this->Server->getCurrentBranch();
            $this->set('branch', $branch);
            $this->render('ajax/update');
        }
    }

    public function ondemandAction()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException('You are not authorised to do that.');
        }
        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $actions = $this->Server->actions_description;
        $default_fields = array(
            'title' => '',
            'description' => '',
            'liveOff' => false,
            'recommendBackup' => false,
            'exitOnError' => false,
            'requirements' => '',
            'url' => $this->baseurl . '/'
        );
        foreach($actions as $id => $action) {
            foreach($default_fields as $field => $value) {
                if (!isset($action[$field])) {
                    $actions[$id][$field] = $value;
                }
            }
            $done = $this->AdminSetting->getSetting($id);
            $actions[$id]['done'] = ($done == '1');
        }
        $this->set('actions', $actions);
        $this->set('updateLocked', $this->Server->isUpdateLocked());
    }

    public function updateProgress($ajaxHtml=false)
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException('You are not authorised to do that.');
        }

        $this->AdminSetting = ClassRegistry::init('AdminSetting');
        $dbVersion = $this->AdminSetting->getSetting('db_version');
        $updateProgress = $this->Server->getUpdateProgress();
        $updateProgress['db_version'] = $dbVersion;
        $maxUpdateNumber = max(array_keys($this->Server->db_changes));
        $updateProgress['complete_update_remaining'] = max($maxUpdateNumber - $dbVersion, 0);
        $updateProgress['update_locked'] = $this->Server->isUpdateLocked();
        $updateProgress['lock_remaining_time'] = $this->Server->getLockRemainingTime();
        $updateProgress['update_fail_number_reached'] = $this->Server->UpdateFailNumberReached();
        $currentIndex = $updateProgress['current'];
        $currentCommand = !isset($updateProgress['commands'][$currentIndex]) ? '' : $updateProgress['commands'][$currentIndex];
        $lookupString = preg_replace('/\s{2,}/', '', substr($currentCommand, 0, -1));
        $sqlInfo = $this->Server->query("SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST;");
        if (empty($sqlInfo)) {
            $updateProgress['process_list'] = array();
        } else {
            // retrieve current update process
            foreach($sqlInfo as $row) {
                if (preg_replace('/\s{2,}/', '', $row['PROCESSLIST']['INFO']) == $lookupString) {
                    $sqlInfo = $row['PROCESSLIST'];
                    break;
                }
            }
            $updateProgress['process_list'] = array();
            $updateProgress['process_list']['STATE'] = isset($sqlInfo['STATE']) ? $sqlInfo['STATE'] : '';
            $updateProgress['process_list']['PROGRESS'] = isset($sqlInfo['PROGRESS']) ? $sqlInfo['PROGRESS'] : 0;
            $updateProgress['process_list']['STAGE'] = isset($sqlInfo['STAGE']) ? $sqlInfo['STAGE'] : 0;
            $updateProgress['process_list']['MAX_STAGE'] = isset($sqlInfo['MAX_STAGE']) ? $sqlInfo['MAX_STAGE'] : 0;
        }
        $this->set('ajaxHtml', $ajaxHtml);
        if ($this->request->is('ajax') && $ajaxHtml) {
            $this->set('updateProgress', $updateProgress);
            $this->layout = false;
        } elseif ($this->request->is('ajax') || $this->_isRest()) {
            return $this->RestResponse->viewData(h($updateProgress), $this->response->type());
        } else {
            $this->set('updateProgress', $updateProgress);
        }
    }


    public function getSubmoduleQuickUpdateForm($submodule_path=false) {
        $this->set('submodule', base64_decode($submodule_path));
        $this->render('ajax/submodule_quick_update_form');
    }

    public function updateSubmodule()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException();
        }
        if ($this->request->is('post')) {
            $request = $this->request->data;
            $submodule = $request['Server']['submodule'];
            $res = $this->Server->updateSubmodule($this->Auth->user(), $submodule);
            return new CakeResponse(array('body'=> json_encode($res), 'type' => 'json'));
        } else {
            throw new MethodNotAllowedException();
        }
    }

    public function getInstanceUUID()
    {
        return $this->RestResponse->viewData(array('uuid' => Configure::read('MISP.uuid')), $this->response->type());
    }

    public function rest()
    {
        $allValidApis = $this->RestResponse->getAllApis($this->Auth->user());
        $allValidApisFieldsContraint = $this->RestResponse->getAllApisFieldsConstraint($this->Auth->user());
        if ($this->request->is('post')) {
            $request = $this->request->data;
            if (!empty($request['Server'])) {
                $request = $this->request->data['Server'];
            }
            $curl = '';
            $python = '';
            try {
                $result = $this->__doRestQuery($request, $curl, $python);
                $this->set('curl', $curl);
                $this->set('python', $python);
                if (!$result) {
                    $this->Flash->error('Something went wrong. Make sure you set the http method, body (when sending POST requests) and URL correctly.');
                } else {
                    $this->set('data', $result);
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Something went wrong. %s', $e->getMessage()));
            }
        }
        $header = sprintf(
            "Authorization: %s \nAccept: application/json\nContent-type: application/json",
            empty(Configure::read('Security.advanced_authkeys')) ? $this->Auth->user('authkey') : __('YOUR_API_KEY')
        );
        $this->set('header', $header);
        $this->set('allValidApis', $allValidApis);
        // formating for optgroup
        $allValidApisFormated = array();
        foreach ($allValidApis as $endpoint_url => $endpoint_data) {
            $allValidApisFormated[$endpoint_data['controller']][] = array('url' => $endpoint_url, 'action' => $endpoint_data['action']);
        }
        $this->set('allValidApisFormated', $allValidApisFormated);
        $this->set('allValidApisFieldsContraint', $allValidApisFieldsContraint);
    }

    /**
     * @param array $request
     * @param string $curl
     * @param string $python
     * @return array|false
     */
    private function __doRestQuery(array $request, &$curl = false, &$python = false)
    {
        App::uses('SyncTool', 'Tools');
        $params = array();
        $this->loadModel('RestClientHistory');
        $this->RestClientHistory->create();
        $date = new DateTime();
        $logHeaders = $request['header'];
        if (!empty(Configure::read('Security.advanced_authkeys'))) {
            $logHeaders = explode("\n", $request['header']);
            foreach ($logHeaders as $k => $header) {
                if (strpos($header, 'Authorization') !== false) {
                    $logHeaders[$k] = 'Authorization: ' . __('YOUR_API_KEY');
                }
            }
            $logHeaders = implode("\n", $logHeaders);
        }
        $rest_history_item = array(
            'org_id' => $this->Auth->user('org_id'),
            'user_id' => $this->Auth->user('id'),
            'headers' => $logHeaders,
            'body' => empty($request['body']) ? '' : $request['body'],
            'url' => $request['url'],
            'http_method' => $request['method'],
            'use_full_path' => empty($request['use_full_path']) ? false : $request['use_full_path'],
            'show_result' => $request['show_result'],
            'skip_ssl' => $request['skip_ssl_validation'],
            'bookmark' => $request['bookmark'],
            'bookmark_name' => $request['name'],
            'timestamp' => $date->getTimestamp()
        );
        if (!empty($request['url'])) {
            if (empty($request['use_full_path']) || empty(Configure::read('Security.rest_client_enable_arbitrary_urls'))) {
                $path = preg_replace('#^(://|[^/?])+#', '', $request['url']);
                $url = empty(Configure::read('Security.rest_client_baseurl')) ? (Configure::read('MISP.baseurl') . $path) : (Configure::read('Security.rest_client_baseurl') . $path);
                unset($request['url']);
            } else {
                $url = $request['url'];
            }
        } else {
            throw new InvalidArgumentException('URL not set.');
        }
        if (!empty($request['skip_ssl_validation'])) {
            $params['ssl_verify_peer'] = false;
            $params['ssl_verify_host'] = false;
            $params['ssl_verify_peer_name'] = false;
            $params['ssl_allow_self_signed'] = true;
        }
        $params['timeout'] = 300;
        App::uses('HttpSocket', 'Network/Http');
        $HttpSocket = new HttpSocket($params);
        $view_data = array();

        $temp_headers = empty($request['header']) ? [] : explode("\n", $request['header']);
        $request['header'] = array(
            'Authorization' => $this->Auth->user('authkey'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        foreach ($temp_headers as $header) {
            $header = explode(':', $header);
            $header[0] = trim($header[0]);
            $header[1] = trim($header[1]);
            $request['header'][$header[0]] = $header[1];
        }
        $start = microtime(true);
        if (
            !empty($request['method']) &&
            $request['method'] === 'GET'
        ) {
            if ($curl !== false) {
                $curl = $this->__generateCurlQuery('get', $request, $url);
            }
            if ($python !== false) {
                $python = $this->__generatePythonScript($request, $url);
            }
            $response = $HttpSocket->get($url, false, array('header' => $request['header']));
        } elseif (
            !empty($request['method']) &&
            $request['method'] === 'POST' &&
            !empty($request['body'])
        ) {
            if ($curl !== false) {
                $curl = $this->__generateCurlQuery('post', $request, $url);
            }
            if ($python !== false) {
                $python = $this->__generatePythonScript($request, $url);
            }
            $response = $HttpSocket->post($url, $request['body'], array('header' => $request['header']));
        } elseif (
            !empty($request['method']) &&
            $request['method'] === 'DELETE'
        ) {
            if ($curl !== false) {
                $curl = $this->__generateCurlQuery('delete', $request, $url);
            }
            if ($python !== false) {
                $python = $this->__generatePythonScript($request, $url);
            }
            $response = $HttpSocket->delete($url, false, array('header' => $request['header']));
        } else {
            return false;
        }
        $view_data['duration'] = microtime(true) - $start;
        $view_data['duration'] = round($view_data['duration'] * 1000, 2) . ' ms';
        $view_data['url'] = $url;
        $view_data['code'] =  $response->code;
        $view_data['headers'] = $response->headers;
        if (!empty($request['show_result'])) {
            $view_data['data'] = $response->body;
        } else {
            if ($response->isOk()) {
                $view_data['data'] = 'Success.';
            } else {
                $view_data['data'] = 'Something went wrong.';
            }
        }
        $rest_history_item['outcome'] = $response->code;
        $this->RestClientHistory->save($rest_history_item);
        $this->RestClientHistory->cleanup($this->Auth->user('id'));
        return $view_data;
    }

    private function __generatePythonScript($request, $url)
    {
        $slashCounter = 0;
        $baseurl = '';
        $relative = '';
        $verifyCert = ($url[4] === 's') ? 'True' : 'False';
        for ($i = 0; $i < strlen($url); $i++) {
            //foreach ($url as $url[$i]) {
            if ($url[$i] === '/') {
                $slashCounter += 1;
                if ($slashCounter == 3) {
                    continue;
                }
            }
            if ($slashCounter < 3) {
                $baseurl .= $url[$i];
            } else {
                $relative .= $url[$i];
            }
        }
        $python_script =
        sprintf(
'misp_url = \'%s\'
misp_key = \'%s\'
misp_verifycert = %s
relative_path = \'%s\'
body = %s

from pymisp import ExpandedPyMISP

misp = ExpandedPyMISP(misp_url, misp_key, misp_verifycert)
misp.direct_call(relative_path, body)
',
            $baseurl,
            $request['header']['Authorization'],
            $verifyCert,
            $relative,
            (empty($request['body']) ? 'None' : $request['body'])
        );
        return $python_script;
    }

    private function __generateCurlQuery($type, $request, $url)
    {
        if ($type === 'get') {
            $curl = sprintf(
                'curl \%s -H "Authorization: %s" \%s -H "Accept: %s" \%s -H "Content-type: %s" \%s %s',
                PHP_EOL,
                $request['header']['Authorization'],
                PHP_EOL,
                $request['header']['Accept'],
                PHP_EOL,
                $request['header']['Content-Type'],
                PHP_EOL,
                $url
            );
        } else {
            $curl = sprintf(
                'curl \%s -d \'%s\' \%s -H "Authorization: %s" \%s -H "Accept: %s" \%s -H "Content-type: %s" \%s -X POST %s',
                PHP_EOL,
                json_encode(json_decode($request['body'])),
                PHP_EOL,
                $request['header']['Authorization'],
                PHP_EOL,
                $request['header']['Accept'],
                PHP_EOL,
                $request['header']['Content-Type'],
                PHP_EOL,
                $url
            );
        }
        return $curl;
    }

    public function getApiInfo()
    {
        $relative_path = $this->request->data['url'];
        $result = $this->RestResponse->getApiInfo($relative_path);
        if ($this->_isRest()) {
            if (!empty($result)) {
                $result['api_info'] = $result;
            }
            return $this->RestResponse->viewData($result, $this->response->type());
        } else {
            if (empty($result)) {
                return $this->RestResponse->viewData('&nbsp;', $this->response->type());
            }
            $this->layout = false;
            $this->autoRender = false;
            $this->set('api_info', $result);
            $this->render('ajax/get_api_info');
        }
    }

    public function cache($id = 'all')
    {
        if (Configure::read('MISP.background_jobs')) {
            $this->loadModel('Job');
            $this->Job->create();
            $data = array(
                    'worker' => 'default',
                    'job_type' => 'cache_servers',
                    'job_input' => intval($id) ? $id : 'all',
                    'status' => 0,
                    'retries' => 0,
                    'org' => $this->Auth->user('Organisation')['name'],
                    'message' => __('Starting server caching.'),
            );
            $this->Job->save($data);
            $jobId = $this->Job->id;
            $process_id = CakeResque::enqueue(
                    'default',
                    'ServerShell',
                    array('cacheServer', $this->Auth->user('id'), $id, $jobId),
                    true
            );
            $this->Job->saveField('process_id', $process_id);
            $message = 'Server caching job initiated.';
        } else {
            $result = $this->Server->cacheServerInitiator($this->Auth->user(), $id);
            if (!$result) {
                $this->Flash->error(__('Caching the servers has failed.'));
                $this->redirect(array('action' => 'index'));
            }
            $message = __('Caching the servers has successfully completed.');
        }
        if ($this->_isRest()) {
            return $this->RestResponse->saveSuccessResponse('Server', 'cache', false, $this->response->type(), $message);
        } else {
            $this->Flash->info($message);
            $this->redirect(array('action' => 'index'));
        }
    }

    public function updateJSON()
    {
        $results = $this->Server->updateJSON();
        return $this->RestResponse->viewData($results, $this->response->type());
    }

    public function createSync()
    {
        if ($this->_isSiteAdmin()) {
            throw new MethodNotAllowedException('Site admin accounts cannot be used to create server sync configurations.');
        }
        $baseurl = Configure::read('MISP.external_baseurl');
        if (empty($baseurl)) {
            $baseurl = Configure::read('MISP.baseurl');
            if (empty($baseurl)) {
                $baseurl = Router::url('/', true);
            }
        }
        $host_org_id = Configure::read('MISP.host_org_id');
        if (empty($host_org_id)) {
            throw new MethodNotAllowedException(__('Cannot create sync config - no host org ID configured for the instance.'));
        }
        $this->loadModel('Organisation');
        $host_org = $this->Organisation->find('first', array(
            'conditions' => array('Organisation.id' => $host_org_id),
            'recursive' => -1,
            'fields' => array('name', 'uuid')
        ));
        if (empty($host_org)) {
            throw new MethodNotAllowedException(__('Configured host org not found. Please make sure that the setting is current on the instance.'));
        }
        $server = array(
            'Server' => array(
                'url' => $baseurl,
                'uuid' => Configure::read('MISP.uuid'),
                'authkey' => $this->Auth->user('authkey'),
                'Organisation' => array(
                    'name' => $host_org['Organisation']['name'],
                    'uuid' => $host_org['Organisation']['uuid'],
                )
            )
        );
        if ($this->_isRest()) {
            return $this->RestResponse->viewData($server, $this->response->type());
        } else {
            $this->set('server', $server);
        }
    }

    public function import()
    {
        if ($this->request->is('post')) {
            $server = $this->request->data;
            if (isset($server['Server'])) {
                $server = $server['Server'];
            }
            if (isset($server['json'])) {
                $server = json_decode($server['json'], true)['Server'];
            }
            $this->loadModel('Organisation');
            $org_id = $this->Organisation->captureOrg($server['Organisation'], $this->Auth->user());
            $toSave = array(
                'push' => 0,
                'pull' => 0,
                'caching_enabled' => 0,
                'json' => '[]',
                'push_rules' => '[]',
                'pull_rules' => '[]',
                'self_signed' => 0,
                'org_id' => $this->Auth->user('org_id'),
                'remote_org_id' => $org_id,
                'name' => empty($server['name']) ? $server['url'] : $server['name'],
                'url' => $server['url'],
                'uuid' => $server['uuid'],
                'authkey' => $server['authkey']
            );
            $this->Server->create();
            $result = $this->Server->save($toSave);
            if ($result) {
                if ($this->_isRest()) {
                    $server = $this->Server->find('first', array(
                        'conditions' => array('Server.id' => $this->Server->id),
                        'recursive' => -1
                    ));
                    return $this->RestResponse->viewData($server, $this->response->type());
                } else {
                    $this->Flash->success(__('The server has been saved'));
                    $this->redirect(array('action' => 'index', $this->Server->id));
                }
            } else {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('Servers', 'addFromJson', false, $this->Server->validationErrors, $this->response->type());
                } else {
                    $this->Flash->error(__('Could not save the server. Error: %s', json_encode($this->Server->validationErrors, true)));
                    $this->redirect(array('action' => 'index'));
                }
            }
        }
    }

    public function resetRemoteAuthKey($id)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException(__('This endpoint expects POST requests.'));
        }
        $result = $this->Server->resetRemoteAuthkey($id);
        if ($result !== true) {
            if (!$this->_isRest()) {
                $this->Flash->error($result);
                $this->redirect(array('action' => 'index'));
            } else {
                return $this->RestResponse->saveFailResponse('Servers', 'resetRemoteAuthKey', $id, $message, $this->response->type());
            }
        } else {
            $message = __('API key updated.');
            if (!$this->_isRest()) {
                $this->Flash->success($message);
                $this->redirect(array('action' => 'index'));
            } else {
                return $this->RestResponse->saveSuccessResponse('Servers', 'resetRemoteAuthKey', $message, $this->response->type());
            }
        }
    }

    public function changePriority($id = false, $direction = 'down') {
        $this->Server->id = $id;
        if (!$this->Server->exists()) {
            throw new InvalidArgumentException(__('ID has to be a valid server connection'));
        }
        if ($direction !== 'up' && $direction !== 'down') {
            throw new InvalidArgumentException(__('Invalid direction. Valid options: ', 'up', 'down'));
        }
        $success = $this->Server->reprioritise($id, $direction);
        if ($success) {
            $message = __('Priority changed.');
            return $this->RestResponse->saveSuccessResponse('Servers', 'changePriority', $message, $this->response->type());
        } else {
            $message = __('Priority could not be changed.');
            return $this->RestResponse->saveFailResponse('Servers', 'changePriority', $id, $message, $this->response->type());
        }
    }

    public function releaseUpdateLock()
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException(__('This endpoint expects POST requests.'));
        }
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException(__('Only site admin accounts can release the update lock.'));
        }
        $this->Server->changeLockState(false);
        $this->Server->resetUpdateFailNumber();
        $this->redirect(array('action' => 'updateProgress'));
    }

    public function dbSchemaDiagnostic()
    {
        if (!$this->_isSiteAdmin()) {
            throw new MethodNotAllowedException(__('Only site admin accounts get the DB schema diagnostic.'));
        }
        $dbSchemaDiagnostics = $this->Server->dbSchemaDiagnostic();
        if ($this->_isRest()) {
            return $this->RestResponse->viewData($dbSchemaDiagnostics, $this->response->type());
        } else {
            $this->set('checkedTableColumn', $dbSchemaDiagnostics['checked_table_column']);
            $this->set('dbSchemaDiagnostics', $dbSchemaDiagnostics['diagnostic']);
            $this->set('dbIndexDiagnostics', $dbSchemaDiagnostics['diagnostic_index']);
            $this->set('expectedDbVersion', $dbSchemaDiagnostics['expected_db_version']);
            $this->set('actualDbVersion', $dbSchemaDiagnostics['actual_db_version']);
            $this->set('error', $dbSchemaDiagnostics['error']);
            $this->set('remainingLockTime', $dbSchemaDiagnostics['remaining_lock_time']);
            $this->set('updateFailNumberReached', $dbSchemaDiagnostics['update_fail_number_reached']);
            $this->set('updateLocked', $dbSchemaDiagnostics['update_locked']);
            $this->set('dataSource', $dbSchemaDiagnostics['dataSource']);
            $this->set('columnPerTable', $dbSchemaDiagnostics['columnPerTable']);
            $this->set('indexes', $dbSchemaDiagnostics['indexes']);
            $this->render('/Elements/healthElements/db_schema_diagnostic');
        }
    }

    public function cspReport()
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException('This action expects a POST request.');
        }

        $report = $this->Server->jsonDecode($this->request->input());
        if (!isset($report['csp-report'])) {
            throw new RuntimeException("Invalid report");
        }

        $message = 'CSP reported violation';
        $remoteIp = $this->_remoteIp();
        if ($remoteIp) {
            $message .= ' from IP ' . $remoteIp;
        }
        $this->log("$message: " . json_encode($report['csp-report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return new CakeResponse(['statusCodes' => 204]);
    }

    public function viewDeprecatedFunctionUse()
    {
        $data = $this->Deprecation->getDeprecatedAccessList($this->Server);
        if ($this->_isRest()) {
            return $this->RestResponse->viewData($data, $this->response->type());
        } else {
            $this->layout = false;
            $this->set('data', $data);
        }
    }

    /**
     * List all tags for the rule picker.
     *
     * @return array
     */
    private function __getTags()
    {
        $this->loadModel('Tag');
        $list = $this->Tag->find('list', array(
            'recursive' => -1,
            'order' => array('LOWER(TRIM(Tag.name))' => 'ASC'),
            'fields' => array('name'),
        ));
        $allTags = array();
        foreach ($list as $id => $name) {
            $allTags[] = array('id' => $id, 'name' => trim($name));
        }
        return $allTags;
    }

    public function removeOrphanedCorrelations()
    {
        $success = $this->Server->removeOrphanedCorrelations();
        $message = __('Orphaned correlation removed');
        if ($this->_isRest()) {
            return $this->RestResponse->viewData($message, $this->response->type());
        } else {
            $this->Flash->success($message);
            $this->redirect(array('action' => 'serverSettings', 'diagnostics'));
        }
    }

    public function queryAvailableSyncFilteringRules($serverID)
    {
        if (!$this->_isRest()) {
            throw new MethodNotAllowedException(__('This method can only be access via REST'));
        }
        $server = $this->Server->find('first', ['conditions' => ['Server.id' => $serverID]]);
        if (!$server) {
            throw new NotFoundException(__('Invalid server'));
        }
        $syncFilteringRules = $this->Server->queryAvailableSyncFilteringRules($server);
        return $this->RestResponse->viewData($syncFilteringRules);
    }

    public function getAvailableSyncFilteringRules()
    {
        if (!$this->_isRest()) {
            throw new MethodNotAllowedException(__('This method can only be access via REST'));
        }
        $syncFilteringRules = $this->Server->getAvailableSyncFilteringRules($this->Auth->user());
        return $this->RestResponse->viewData($syncFilteringRules);
    }
}

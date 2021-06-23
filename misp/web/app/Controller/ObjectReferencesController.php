<?php

App::uses('AppController', 'Controller');

class ObjectReferencesController extends AppController
{
    public $components = array('Security' ,'RequestHandler', 'Session');

    public $paginate = array(
            'limit' => 20,
            'order' => array(
                    'ObjectReference.id' => 'desc'
            ),
    );

    public function add($objectId = false)
    {
        if (empty($objectId)) {
            if ($this->request->is('post') && !empty($this->request->data['object_uuid'])) {
                $objectId = $this->request->data['object_uuid'];
            }
        }
        if (empty($objectId)) {
            throw new MethodNotAllowedException('No object defined.');
        }
        if (Validation::uuid($objectId)) {
            $temp = $this->ObjectReference->Object->find('first', array(
                'recursive' => -1,
                'fields' => array('Object.id'),
                'conditions' => array('Object.uuid' => $objectId, 'Object.deleted' => 0)
            ));
            if (empty($temp)) {
                throw new NotFoundException('Invalid Object');
            }
            $objectId = $temp['Object']['id'];
        } elseif (!is_numeric($objectId)) {
            throw new NotFoundException(__('Invalid object'));
        }
        $object = $this->ObjectReference->Object->find('first', array(
            'conditions' => array('Object.id' => $objectId, 'Object.deleted' => 0),
            'recursive' => -1,
            'contain' => array(
                'Event' => array(
                    'fields' => array('Event.id', 'Event.orgc_id', 'Event.user_id', 'Event.extends_uuid')
                )
            )
        ));
        if (empty($object) || !$this->__canModifyEvent($object)) {
            throw new NotFoundException('Invalid object.');
        }
        $this->set('objectId', $objectId);
        if ($this->request->is('post')) {
            $data = array();
            if (!isset($this->request->data['ObjectReference'])) {
                $this->request->data['ObjectReference'] = $this->request->data;
            }
            list($referenced_id, $referenced_uuid, $referenced_type) = $this->ObjectReference->getReferencedInfo($this->request->data['ObjectReference']['referenced_uuid'], $object, true, $this->Auth->user());
            $relationship_type = empty($this->request->data['ObjectReference']['relationship_type']) ? '' : $this->request->data['ObjectReference']['relationship_type'];
            if (!empty($this->request->data['ObjectReference']['relationship_type_select']) && $this->request->data['ObjectReference']['relationship_type_select'] !== 'custom') {
                $relationship_type = $this->request->data['ObjectReference']['relationship_type_select'];
            }
            $data = array(
                'referenced_id' => $referenced_id,
                'referenced_uuid' => $referenced_uuid,
                'relationship_type' => $relationship_type,
                'comment' => !empty($this->request->data['ObjectReference']['comment']) ? $this->request->data['ObjectReference']['comment'] : '',
                'event_id' => $object['Event']['id'],
                'object_uuid' => $object['Object']['uuid'],
                'source_uuid' => $object['Object']['uuid'],
                'object_id' => $objectId,
                'referenced_type' => $referenced_type,
                'uuid' => CakeText::uuid()
            );
            $object_uuid = $object['Object']['uuid'];
            $this->ObjectReference->create();
            $result = $this->ObjectReference->save(array('ObjectReference' => $data));
            if ($result) {
                $this->ObjectReference->updateTimestamps($this->id, $data);
                if ($this->_isRest()) {
                    $object = $this->ObjectReference->find("first", array(
                        'recursive' => -1,
                        'conditions' => array('ObjectReference.id' => $this->ObjectReference->id)
                    ));
                    $object['ObjectReference']['object_uuid'] = $object_uuid;
                    return $this->RestResponse->viewData($object, $this->response->type());
                } elseif ($this->request->is('ajax')) {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => true, 'success' => 'Object reference added.')),'status'=>200, 'type' => 'json'));
                }
            } else {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('ObjectReferences', 'add', false, $this->ObjectReference->validationErrors, $this->response->type());
                } elseif ($this->request->is('ajax')) {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => 'Object reference could not be added.')),'status'=>200, 'type' => 'json'));
                }
            }
        } else {
            if ($this->_isRest()) {
                return $this->RestResponse->describe('ObjectReferences', 'add', false, $this->response->type());
            } else {
                $events = $this->ObjectReference->Object->Event->find('all', array(
                    'conditions' => array(
                        'OR' => array(
                            'Event.id' => $object['Event']['id'],
                            'AND' => array(
                                'Event.uuid' => $object['Event']['extends_uuid'],
                                $this->ObjectReference->Object->Event->createEventConditions($this->Auth->user())
                            )
                        ),
                    ),
                    'recursive' => -1,
                    'fields' => array('Event.id'),
                    'contain' => array(
                        'Attribute' => array(
                            'conditions' => array('Attribute.deleted' => 0, 'Attribute.object_id' => 0),
                            'fields' => array('Attribute.id', 'Attribute.uuid', 'Attribute.type', 'Attribute.category', 'Attribute.value', 'Attribute.to_ids')
                        ),
                        'Object' => array(
                            'conditions' => array('NOT' => array('Object.id' => $objectId), 'Object.deleted' => 0),
                            'fields' => array('Object.id', 'Object.uuid', 'Object.name', 'Object.meta-category'),
                            'Attribute' => array(
                                'conditions' => array('Attribute.deleted' => 0),
                                'fields' => array('Attribute.id', 'Attribute.uuid', 'Attribute.type', 'Attribute.category', 'Attribute.value', 'Attribute.to_ids')
                            )
                        )
                    )
                ));
                if (!empty($events)) {
                    $event = $events[0];
                }
                for ($i=1; $i < count($events); $i++) { 
                    $event['Attribute'] = array_merge($event['Attribute'], $events[$i]['Attribute']);
                    $event['Object'] = array_merge($event['Object'], $events[$i]['Object']);
                }
                $toRearrange = array('Attribute', 'Object');
                foreach ($toRearrange as $d) {
                    if (!empty($event[$d])) {
                        $temp = array();
                        foreach ($event[$d] as $data) {
                            $temp[$data['uuid']] = $data;
                        }
                        $event[$d] = $temp;
                    }
                }
                $this->loadModel('ObjectRelationship');
                $relationshipsTemp = $this->ObjectRelationship->find('all', array(
                    'recursive' => -1
                ));
                $relationships = array();
                $relationshipMetadata = array();
                foreach ($relationshipsTemp as $k => $v) {
                    $relationshipMetadata[$v['ObjectRelationship']['name']] = $v;
                    $relationships[$v['ObjectRelationship']['name']] = $v['ObjectRelationship']['name'];
                }
                $relationships['custom'] = 'custom';
                ksort($relationships);
                $this->set('relationships', $relationships);
                $this->set('event', $event);
                $this->set('objectId', $objectId);
                $this->layout = 'ajax';
                $this->render('ajax/add');
            }
        }
    }

    public function delete($id, $hard = false)
    {
        if (Validation::uuid($id)) {
            $temp = $this->ObjectReference->find('first', array(
                'recursive' => -1,
                'fields' => array('ObjectReference.id'),
                'conditions' => array('ObjectReference.uuid' => $id)
            ));
            if (empty($temp)) {
                throw new NotFoundException('Invalid object reference');
            }
            $id = $temp['ObjectReference']['id'];
        } elseif (!is_numeric($id)) {
            throw new NotFoundException(__('Invalid object reference'));
        }
        $objectReference = $this->ObjectReference->find('first', array(
            'conditions' => array('ObjectReference.id' => $id),
            'recursive' => -1,
            'contain' => array('Object' => array('Event'))
        ));
        if (empty($objectReference)) {
            throw new NotFoundException(__('Invalid object reference.'));
        }
        if (!$this->__canModifyEvent($objectReference['Object'])) {
            throw new ForbiddenException(__('Invalid object reference.'));
        }
        if ($this->request->is('post') || $this->request->is('put') || $this->request->is('delete')) {
            $result = $this->ObjectReference->smartDelete($objectReference['ObjectReference']['id'], $hard);
            if ($result === true) {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveSuccessResponse('ObjectReferences', 'delete', $id, $this->response->type());
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => true, 'success' => 'Object reference deleted.')), 'status'=>200, 'type' => 'json'));
                }
            } else {
                if ($this->_isRest()) {
                    return $this->RestResponse->saveFailResponse('ObjectReferences', 'delete', $id, $result, $this->response->type());
                } else {
                    return new CakeResponse(array('body'=> json_encode(array('saved' => false, 'errors' => 'Object reference was not deleted.')), 'status'=>200, 'type' => 'json'));
                }
            }
        } else {
            if (!$this->request->is('ajax')) {
                throw new MethodNotAllowedException('This action is only accessible via POST request.');
            }
            $this->set('hard', $hard);
            $this->set('id', $id);
            $this->set('event_id', $objectReference['Object']['Event']['id']);
            $this->render('ajax/delete');
        }
    }

    public function view($id)
    {
        if (Validation::uuid($id)) {
            $temp = $this->ObjectReference->find('first', array(
                'recursive' => -1,
                'fields' => array('ObjectReference.id'),
                'conditions' => array('ObjectReference.uuid' => $id)
            ));
            if (empty($temp)) {
                throw new NotFoundException('Invalid object reference');
            }
            $id = $temp['ObjectReference']['id'];
        } elseif (!is_numeric($id)) {
            throw new NotFoundException(__('Invalid object reference'));
        }
        $objectReference = $this->ObjectReference->find('first', array(
            'conditions' => array('ObjectReference.id' => $id),
            'recursive' => -1,
        ));
        if (empty($objectReference)) {
            throw new NotFoundException(__('Invalid object reference.'));
        }
        $event = $this->ObjectReference->Object->Event->fetchSimpleEvent($this->Auth->user(), $objectReference['ObjectReference']['event_id'], ['contain' => ['Orgc']]);
        if (!$event) {
            throw new NotFoundException(__('Invalid event'));
        }
        return $this->RestResponse->viewData($objectReference, 'application/json');
    }
}

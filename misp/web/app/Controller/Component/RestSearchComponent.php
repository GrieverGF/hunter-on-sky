<?php

App::uses('Component', 'Controller');

class RestSearchComponent extends Component
{
    //This array determines the order for ordered_url_params, as a result it is not advised to remove or change existing values
    public $paramArray = array(
        'Attribute' => array(
            //following parameters are not used for attributes anymore: attackGalaxy
            'returnFormat', 'value' , 'type', 'category', 'org', 'tags', 'from', 'to', 'last', 'eventid', 'withAttachments', 'uuid', 'publish_timestamp',
            'published', 'timestamp','enforceWarninglist', 'to_ids', 'deleted', 'includeEventUuid', 'event_timestamp', 'threat_level_id', 'includeEventTags',
            'includeProposals', 'returnFormat', 'limit', 'page', 'requested_attributes', 'includeContext', 'headerless',
            'includeWarninglistHits', 'attackGalaxy', 'object_relation', 'includeSightings', 'includeCorrelations', 'includeDecayScore',
            'decayingModel', 'excludeDecayed', 'modelOverrides', 'includeFullModel', 'score', 'attribute_timestamp', 'first_seen', 'last_seen',
            'threat_level_id'
        ),
        'Event' => array(
            'returnFormat', 'value', 'type', 'category', 'org', 'tags', 'searchall', 'from', 'to', 'last', 'eventid', 'withAttachments',
            'metadata', 'uuid', 'publish_timestamp', 'timestamp', 'published', 'enforceWarninglist', 'sgReferenceOnly',
            'limit', 'page', 'requested_attributes', 'includeContext', 'headerless', 'includeWarninglistHits', 'attackGalaxy', 'to_ids', 'deleted',
            'excludeLocalTags', 'date', 'includeSightingdb', 'tag', 'object_relation', 'threat_level_id'
        ),
        'Object' => array(
            'returnFormat', 'value' , 'type', 'category', 'org', 'tags', 'from', 'to', 'last', 'eventid', 'withAttachments', 'uuid', 'publish_timestamp',
            'published', 'timestamp','enforceWarninglist', 'to_ids', 'deleted', 'includeEventUuid', 'event_timestamp', 'threat_level_id', 'includeEventTags',
            'includeProposals', 'returnFormat', 'limit', 'page', 'requested_attributes', 'includeContext', 'headerless',
            'includeWarninglistHits', 'attackGalaxy', 'object_relation'
        ),
        'Sighting' => array(
            'context', 'returnFormat', 'id', 'type', 'from', 'to', 'last', 'org_id', 'source', 'includeAttribute', 'includeEvent'
        ),
        'GalaxyCluster' => array(
            'page', 'limit', 'id', 'uuid', 'galaxy_id', 'galaxy_uuid', 'version', 'distribution', 'org', 'orgc', 'tag', 'custom', 'sgReferenceOnly', 'minimal',
        )
    );

    public function getFilename($filters, $scope, $responseType)
    {
        $filename = false;
        if ($scope === 'Event') {
            $filename = 'misp.event.';
            if (!empty($filters['eventid']) && !is_array($filters['eventid'])) {
                if (Validation::uuid(trim($filters['eventid']))) {
                    $filename .= trim($filters['eventid']);
                } else if (!empty(intval(trim($filters['eventid'])))) {
                    $filename .= intval(trim($filters['eventid']));
                }
            } else {
                $filename .= 'list';
            }
        }
        if ($filename !== false) {
            $filename .= '.' . $responseType;
        }
        return $filename;
    }
}

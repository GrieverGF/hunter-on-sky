<?php
$jsonArray = array();
foreach ($results as $result) {
    $result['Event']['Attribute'] = $result['Attribute'];
    $result['Event']['ShadowAttribute'] = $result['ShadowAttribute'];
    $result['Event']['RelatedEvent'] = $result['RelatedEvent'];

    //
    // cleanup the array from things we do not want to expose
    //
    unset($result['Event']['user_id']);
    // hide the org field is we are not in showorg mode
    if (!Configure::read('MISP.showorg') && !$isSiteAdmin) {
        unset($result['Event']['Org']);
        unset($result['Event']['Orgc']);
        unset($result['Event']['from']);
    }
    // remove value1 and value2 from the output and remove invalid utf8 characters for the xml parser
    foreach ($result['Event']['Attribute'] as $key => $value) {
        $result['Event']['Attribute'][$key]['value'] = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $result['Event']['Attribute'][$key]['value']);
        unset($result['Event']['Attribute'][$key]['value1']);
        unset($result['Event']['Attribute'][$key]['value2']);
    }
    // remove invalid utf8 characters for the xml parser
    foreach ($result['Event']['ShadowAttribute'] as $key => $value) {
        $result['Event']['ShadowAttribute'][$key]['value'] = preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $result['Event']['ShadowAttribute'][$key]['value']);
    }

    if (isset($result['Event']['RelatedEvent'])) {
        foreach ($result['Event']['RelatedEvent'] as $key => $value) {
            unset($result['Event']['RelatedEvent'][$key]['user_id']);
            if (!Configure::read('MISP.showorg') && !$isAdmin) {
                unset($result['Event']['RelatedEvent'][$key]['Org']);
                unset($result['Event']['RelatedEvent'][$key]['orgc']);
            }
        }
    }
    $jsonArray['response']['Event'][] = $result['Event'];
}
echo json_encode($jsonArray);

<div class="communities view">
    <?php
        $table_data = array();
        $table_data[] = array('key' => __('Id'), 'value' => $community['id']);
        $table_data[] = array('key' => __('UUID'), 'value' => $community['uuid']);
        $table_data[] = array('key' => __('Name'), 'value' => $community['name']);
        $table_data[] = array('key' => __('Url'), 'url' => $community['url']);
        $table_data[] = array('key' => __('Host organisation'), 'value' => $community['org_name'] . ' (' . $community['org_uuid'] . ')');
        $table_data[] = array(
            'key' => __('Vetted by MISP-project'),
            'html' => sprintf(
                '<dd><span class="%s bold">%s</span></dd>',
                $community['misp_project_vetted'] ? 'green' : 'red',
                $community['misp_project_vetted'] ? __('Yes') : __('No')
            )
        );
        $optional_fields = array(
            'type', 'description', 'rules', 'email', 'sector', 'nationality', 'eligibility',
        );
        foreach ($optional_fields as $field) {
            if (!empty($community[$field])) {
                $table_data[] = array('key' => Inflector::humanize($field), 'value' => $community[$field]);
            }
        }
        if (!empty($community['pgp_key'])) {
            $table_data[] = array(
                'key' => __('GnuPG key'),
                'element' => 'genericElements/key',
                'element_params' => array('key' => $community['pgp_key']),
            );
        }
        echo sprintf(
            '<div class="row-fluid"><div class="span8" style="margin:0px;">%s</div></div>',
            sprintf(
                '%s<h2>%s</h2>%s',
                sprintf(
                    '<img src="https://misp-project.org/org-logos/%s.png" title="%s" aria-label="%s" style="max-height: 100px;"/>',
                    h($community['org_uuid']),
                    h($community['org_name']),
                    h($community['org_name'])
                ),
                __('Community ') . h($community['name']),
                $this->element('genericElements/viewMetaTable', array('table_data' => $table_data))
            )
        );
        echo sprintf(
            '<a href="%s%s%s" class="btn btn-primary">%s</a>',
            $baseurl,
            '/communities/requestAccess/',
            h($community['uuid']),
            __('Request Access')
        );
    ?>

</div>
<?php
    echo $this->element('/genericElements/SideMenu/side_menu', array('menuList' => 'sync', 'menuItem' => 'view_community'));
?>

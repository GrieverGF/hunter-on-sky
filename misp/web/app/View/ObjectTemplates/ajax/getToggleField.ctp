<?php
echo $this->Form->create('ObjectTemplate', array('url' => $baseurl . '/ObjectTemplates/activate', 'id' => 'ObjectTemplateIndexForm'));
echo $this->Form->input('data', array('label' => false, 'style' => 'display:none;'));
echo $this->Form->end();

<?php

class OpendataExport
{
    public $non_restrictive_export = true;
    public $use_default_filters = true;
    public $mock_query_only = true;
    private $__default_filters = null;

    private $__auth = null;
    private $__delete = false;
    private $__scope = null;
    private $__search = false;
    private $__setup = array();
    private $__url = null;

    private $__scripts_dir = APP . 'files/scripts/';
    private $__script_name = 'misp-opendata/opendata.py';

    public function setDefaultFilters($filters)
    {
        $this->__default_filters = $filters;
    }

    public function header($options = array())
    {
        $this->__scope = $options['scope'];
        if (!empty($this->__default_filters['auth'])) {
            $this->__auth = $this->__default_filters['auth'];
            unset($this->__default_filters['auth']);
        }
        if (empty($this->__default_filters['setup'])) {
            throw new Exception(__('Missing "setup" filter containing the dataset and resource(s) information.'));
        }
        $this->__setup = $this->__default_filters['setup'];
        $this->__check_setup_filter();
        unset($this->__default_filters['setup']);
        if (empty($this->__default_filters['misp-url'])) {
            $external_baseurl = Configure::read('MISP.external_baseurl');
            $baseurl = !empty($external_baseurl) ? $external_baseurl : Configure::read('MISP.baseurl');
            if (empty($baseurl)) {
                throw new Exception(__('Missing url of the MISP instance, and baseurl is not set.'));
            }
            $this->__url = $baseurl;
        } else {
            $this->__url = $this->__default_filters['misp-url'];
            unset($this->__default_filters['misp-url']);
        }
        $simple_query = false;
        if (!empty($this->__default_filters['delete'])) {
            $this->__delete = true;
            unset($this->__default_filters['delete']);
            $simple_query = true;
        }
        if (!empty($this->__default_filters['search'])) {
            $this->__search = true;
            unset($this->__default_filters['search']);
            $simple_query = true;
        }
        if (!empty($this->__default_filters['portal-url'])) {
            if ($simple_query) {
                $this->__url = ' --portal_url ' . $this->__default_filters['portal-url'];
            } else {
                $this->__url .= ' --portal_url ' . $this->__default_filters['portal-url'];
            }
            unset($this->__default_filters['portal-url']);
        }
        return '';
    }

    public function footer()
    {
        $my_server = ClassRegistry::init('Server');
        $cmd = $my_server->getPythonVersion() . ' ' . $this->__scripts_dir . $this->__script_name;
        if (!empty($this->__auth)) {
            $cmd .= ' --auth ' . $this->__auth;
        }
        if ($this->__search){
            return $this->__search_query($cmd);
        }
        return $this->__delete ? $this->__delete_query($cmd) : $this->__add_query($cmd);
    }

    public function separator()
    {
        return '';
    }

    private function __add_query($cmd)
    {
        unset($this->__default_filters['returnFormat']);
        $body = json_encode($this->__default_filters);
        $bodyFilename = $this->__generateSetupFile($body);
        $bodyParam = ' --body ' . $bodyFilename;
        $levelParam = ' --level ' . strtolower($this->__scope) . 's';
        $setup = json_encode($this->__setup);
        $setupFilename = $this->__generateSetupFile($setup);
        $setupParam = ' --setup ' . $setupFilename;
        $urlParam = ' --misp_url ' . $this->__url;
        $cmd .= $bodyParam . $setupParam . $levelParam . $urlParam;
        $results = shell_exec($cmd);
        unlink($bodyFilename);
        unlink($setupFilename);
        return $results;
    }

    private function __check_setup_filter()
    {
        if (empty($this->__setup['dataset'])) {
            throw new Exception(__('Missing dataset filter in the setup filter. Please provide the dataset setup.'));
        }
        if (!empty($this->__setup['resources']) && !empty($this->__setup['resource'])) {
            throw new Exception(__('Please provide the resource setup in a single field called "resources".'));
        }
        if (!empty($this->__setup['resource']) && empty($this->__setup['resources'])) {
            $this->__setup['resources'] = $this->__setup['resource'];
            unset($this->__setup['resource']);
        }
    }

    private function __delete_query($cmd)
    {
        $cmd .= $this->__url . " -d '" . $this->__setup['dataset'] . "'";
        return $this->__simple_query($cmd);
    }

    private function __search_query($cmd)
    {
        $cmd .= $this->__url . " -s '" . $this->__setup['dataset'] . "'";
        return $this->__simple_query($cmd);
    }

    private function __simple_query($cmd)
    {
        if (!empty($this->__setup['resources'])) {
            if (is_array($this->__setup['resources'])) {
                foreach ($this->__setup['resources'] as $resource) {
                    $cmd .= " '" . $resource . "'";
                }
            } else {
                $cmd .= " '" . $this->__setup['resources'] . "'";
            }
        }
        return shell_exec($cmd);
    }

    private function __generateRandomFileName()
    {
        return (new RandomTool())->random_str(false, 12);
    }

    private function __generateSetupFile($to_write)
    {
        $filename = $this->__scripts_dir . 'tmp/' . $this->__generateRandomFileName();
        $tmpFile = new File($filename, true, 0644);
        $tmpFile->write($to_write);
        $tmpFile->close();
        return $filename;
    }
}

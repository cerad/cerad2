<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\Assign;

use Symfony\Component\Yaml\Yaml;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/* =========================================================
 * The actual workflow is encoded in a yaml file
 * 
 * TODO: Should be possible to have project specific workflows
 */
class AssignWorkflow
{
    protected $dispatcher;
    
    public function __construct($configFilePath)
    {
        $config = Yaml::parse(file_get_contents($configFilePath));
        
        $this->assigneeStateTransitions  = $config['assigneeStateTransitions'];
        $this->assignorStateTransitions  = $config['assignorStateTransitions'];
        
        // Bi directional mappings bewteen what the application uses for states
        // And what the actual tables have
        // Really need this? Makes the YAML file easier to understand
        $this->mapInternalToPostedStates = $config['assignStateMap'];
        
        $map = array();
        foreach($this->mapInternalToPostedStates as $key => $value)
        {
            $map[$value] = $key;
        }
        $this->mapPostedToInternalStates = $map;
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    public function mapInternalStateToPostedState($state)
    {
        if (isset( $this->mapInternalToPostedStates[$state])) {
            return $this->mapInternalToPostedStates[$state];
        }
        return 'Open';
        echo sprintf("Missing State: %s<br />\n",$state);
        print_r(array_keys($this->mapInternalToPostedStates));
        die();
    }
    public function mapPostedStateToInternalState($state)
    {
        if (isset( $this->mapPostedToInternalStates[$state])) {
            return $this->mapPostedToInternalStates[$state];
        }
        // Prevent lockups on code errors
        return 'StateOpen';
        
        echo sprintf("Missing State: %s<br />\n",$state);
        print_r(array_keys($this->mapPostedToInternalStates));
        die();
    }
    protected function getStateOptions($state,$transitions)
    {
        $state = $this->mapPostedStateToInternalState($state);
        
        $items = $transitions[$state];
        $options = array();
        foreach($items as $state => $item)
        {   
            $state = $this->mapInternalStateToPostedState($state);   
            $options[$state] = $item['desc'];
        }
        return $options;
    }
    // Mark as abstract
    public function process($gameOfficial, $projectOfficial) {}   
 }
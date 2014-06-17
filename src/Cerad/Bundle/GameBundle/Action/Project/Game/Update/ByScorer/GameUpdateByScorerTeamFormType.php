<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

// Really should be GameTeam
class GameUpdateByScorerTeamFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game__update__by_scorer__team'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameTeam',
        ));
    }
    protected $physicalTeams;
    
    /* ==================================================
     * Passed a list of entities
     * TODO: Figure out how to wrap them for a better display
     * 
     * Or maybe convert game object to array
     */
    public function __construct($teams)
    {
        $this->teams = $teams;
    }
    protected function genTeamChoices()
    {
        $teamChoices = array();
        $teamChoices[0] = 'Select Team';
        foreach($this->teams as $team)
        {
            $teamChoices[$team->getKey()] = $team->getDesc();
        }
        return $teamChoices;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('teamKey', 'choice', array(
            'choices'  => $this->genTeamChoices(),
            'expanded' => false,
            'multiple' => false,
            'required' => true,
        ));
        return;
        
        $builder->add('team', 'entity', array(
            'class'    => 'Cerad\Bundle\GameBundle\Doctrine\Entity\Team',
            'choices'  => $this->physicalTeams,
            'property' => 'desc',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'empty_data'  => null,
            'empty_value' => 'Physical Team',
        ));
        
        return; $options;
    }
}


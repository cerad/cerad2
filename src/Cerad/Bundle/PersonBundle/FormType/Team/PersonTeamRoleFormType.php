<?php
namespace Cerad\Bundle\PersonBundle\FormType\Team;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Cerad\Bundle\PersonBundle\Model\PersonTeam;

class PersonTeamRoleFormType extends AbstractType
{   
    public function getParent() { return 'choice'; }
    public function getName()   { return 'cerad_person__person_team__role'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          
            'label'    => 'Team Relation',
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
            'required' => true,
            
            'empty_value' => 'Team Relation',
            'empty_data'  => null
            
        ));
    }    
    protected $choices = array
    (
        PersonTeam::RoleParent => 'Parent',
        PersonTeam::RolePlayer => 'Player',
        PersonTeam::RoleSpec   => 'Spectator',
        
        PersonTeam::RoleHeadCoach  => 'Head Coach',
        PersonTeam::RoleAsstCoach  => 'Asst Coach',
        PersonTeam::RoleManager    => 'Team Manager',
    );    
}

?>

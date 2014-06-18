<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonTeams\Show;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

// Really should be GameTeam
class PersonTeamsShowPersonTeamFormType extends AbstractType
{
    public function getName() { return 'cerad_person__person_teams__person_team_show'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          //'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameTeam',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('remove', 'checkbox');
        
        return; if ($options);
    }
}


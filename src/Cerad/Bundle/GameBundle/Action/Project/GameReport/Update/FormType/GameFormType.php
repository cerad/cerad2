<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game_report__update_game'; }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\Game'
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        // Status is the only game field changed
        $builder->add('status', 'choice', array('label' => 'Game Status',
        'choices' => array
        (
            'Normal'     => 'Normal',
            'InProgress' => 'In Progress',
            'Played'     => 'Played',
            
            'ForfeitByHomeTeam' => 'Forfeit By Home Team', 
            'ForfeitByAwayTeam' => 'Forfeit By Away Team', 
            'Cancelled'  => 'Cancelled', 
            'Suspended'  => 'Suspended', 
            'Terminated' => 'Terminated',
            'StormedOut' => 'Stormed Out',
            'HeatedOut'  => 'Heated Out',
            ),
        ));
        return; if($options);
    }
}
?>

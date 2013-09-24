<?php
namespace Cerad\Bundle\GameBundle\FormType\GameReport\Update;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameFormType extends AbstractType
{
    public function getName() { return 'cerad_game_report_update_game'; }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\Game'
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
          //'StormedOut' => 'Stormed Out',
          //'HeatedOut'  => 'Heated Out',
            ),
        ));
        return;
        $builder->add('gameReport',new GameReportFormType());
        $builder->add('homeTeam',  new GameTeamFormType());
        $builder->add('awayTeam',  new GameTeamFormType());
       
    }
}
?>

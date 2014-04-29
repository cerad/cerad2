<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Cerad\Library\CommonLibrary\ValidatorConstraint\NullOrPositiveIntegerConstraint;

class GameTeamReportFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game_report__update_game_team_report'; }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameTeamReport',
            'required'   => false,
            'attr'       => array('size' => 4),
            'error_bubbling' => true, // Does not seem to work
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $npi = new NullOrPositiveIntegerConstraint();
      //$npi = new NotBlank();
        
        $params = array(
            'constraints'    => $npi,
            'error_bubbling' => true,  // Bubbles to top form
            'attr' => array('size' => 4),
        );
        $builder->add('goalsScored',    'integer', $params);
        $builder->add('sportsmanship',  'integer', $params);
        $builder->add('injuries',       'integer', $params);
        $builder->add('fudgeFactor',    'integer', array('attr' => array('size' => 4)));
        
        $builder->add('playerWarnings', 'integer', $params);
        $builder->add('playerEjections','integer', $params);
        
        $builder->add('coachWarnings',  'integer', $params);
        $builder->add('coachEjections', 'integer', $params);
        
        $builder->add('benchEjections', 'integer', $params);
        $builder->add('specEjections',  'integer', $params);

        $builder->add('pointsEarned',   'integer', array('attr' => array('size' => 4),'read_only' => true));
        $builder->add('pointsMinus',    'integer', array('attr' => array('size' => 4),'read_only' => true));
    }
}
?>

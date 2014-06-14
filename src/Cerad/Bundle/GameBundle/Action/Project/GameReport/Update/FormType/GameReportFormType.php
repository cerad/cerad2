<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameReportFormType extends AbstractType
{
    
    public function getName() { return 'cerad_game__game_report__update_game_report'; }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameReport'
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        
        // Status Workflow
        $builder->add('status', 'choice', array('label' => 'Report Status',
        'choices' => array
        (
            'Pending'   => 'Pending',
            'Submitted' => 'Submitted',
            'Verified'  => 'Verified',
            'Clear'     => 'Clear',
           ),
        ));
        $builder->add('text','textarea',array('label' => 'Text', 'required' => false, 
            'attr' => array('rows' => 4, 'cols' => 42, 'wrap' => 'hard', 'class' =>'textarea')));

    }
}
?>

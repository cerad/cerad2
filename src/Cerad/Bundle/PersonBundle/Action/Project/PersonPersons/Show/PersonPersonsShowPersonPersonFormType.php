<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonPersons\Show;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

// Really should be GameTeam
class PersonPersonsShowPersonPersonFormType extends AbstractType
{
    public function getName() { return 'cerad_person__person_persons__person_person_show'; }
    
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


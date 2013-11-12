<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameFormType extends AbstractType
{
    protected $person;
    
    public function getName() { return 'cerad_tourn_schedule_my_game'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\Game',
        ));
    }    
    public function __construct($person)
    {
        $this->person = $person;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('officials', 'collection', array(
            'type' => new GameOfficialFormType($this->person),
        ));
    }
}
?>

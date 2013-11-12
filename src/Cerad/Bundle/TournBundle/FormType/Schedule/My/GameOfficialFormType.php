<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameOfficialFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_schedule_my_game_official'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\GameOfficial',
        ));
    }    
    public function __construct($person)
    {
        $this->person = $person;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subscriber = new GameOfficialSubscriber($builder->getFormFactory(),$this->person);
        $builder->addEventSubscriber($subscriber);
    }
}
?>

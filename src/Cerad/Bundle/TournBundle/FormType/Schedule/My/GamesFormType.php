<?php
namespace Cerad\Bundle\TournBundle\FormType\Schedule\My;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GamesFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_schedule_my_games'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }    
    public function __construct()
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('games', 'collection', array(
            'type' => new GameFormType(),
        ));
    }
}
?>

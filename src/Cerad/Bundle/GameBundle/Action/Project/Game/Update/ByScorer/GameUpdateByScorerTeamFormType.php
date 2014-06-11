<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameUpdateByScorerTeamFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game__update__by_scorer__team'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Doctrine\Entity\GameTeam',
        ));
    }
    protected $teamNameChoices;
    
    public function __construct($teamNameChoices)
    {
        $this->teamNameChoices = $teamNameChoices;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'choice', array(
            'choices' => $this->teamNameChoices,
        ));
        
        return; $options;
    }
}


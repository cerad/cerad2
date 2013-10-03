<?php
namespace Cerad\Bundle\GameBundle\FormType\GameReport\Update;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MasterFormType extends AbstractType
{
    public function getName() { return 'cerad_game_report_update_master'; }
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('game',           new GameFormType());
        $builder->add('gameReport',     new GameReportFormType());
        $builder->add('homeTeamReport', new GameTeamReportFormType());
        $builder->add('awayTeamReport', new GameTeamReportFormType());
    }
}
?>

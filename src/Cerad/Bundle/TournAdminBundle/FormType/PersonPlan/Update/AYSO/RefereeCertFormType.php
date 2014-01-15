<?php
namespace Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/* ===================================================================
 * Try having some standard admin forms
 * Might end up flattening everything and just moving to controller
 */
class RefereeCertFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_aysov_referee_cert'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonFedCert',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('badge',    'cerad_person_aysov_referee_badge');
        $builder->add('badgeUser','cerad_person_aysov_referee_badge');
        $builder->add('upgrading','cerad_person_upgrading');
        
        $builder->add('roleDate', 'cerad_person_date');
        $builder->add('badgeDate','cerad_person_date');
    }
}
?>

<?php
namespace Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/* ===================================================================
 * Try having some standard admin forms
 * Might end up flattening everything and just moving to controller
 */
class VolFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_aysov_vol'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonFed',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fedKey',         'cerad_person_aysov_id');
        $builder->add('fedKeyVerified', 'cerad_person_verified');
        $builder->add('orgKey',         'cerad_person_ayso_region_id');
        $builder->add('orgKeyVerified', 'cerad_person_verified');
        $builder->add('memYear',        'cerad_person_ayso_mem_year');
        $builder->add('personVerified', 'cerad_person_verified');
    }
}
?>

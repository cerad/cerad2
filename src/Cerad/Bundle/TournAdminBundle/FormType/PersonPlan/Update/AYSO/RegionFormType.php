<?php
namespace Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/* ===================================================================
 * Try having some standard admin forms
 * Might end up flattening everything and just moving to controller
 */
class RegionFormType extends AbstractType
{
    public function getName() { return 'cerad_tourn_admin_aysov_region'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\PersonBundle\Model\PersonFedOrg',
        ));
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orgId',    'cerad_person_ayso_region_id');
        $builder->add('memYear',  'cerad_person_ayso_mem_year');
    }
}
?>

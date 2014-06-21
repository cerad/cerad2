<?php
namespace Cerad\Bundle\PersonBundle\FormType\Person;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Cerad\Bundle\PersonBundle\Model\PersonPerson;

class PersonPersonRoleFormType extends AbstractType
{   
    public function getParent() { return 'choice'; }
    public function getName()   { return 'cerad_person__person_person__role'; }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
          
            'label'    => 'Person Relation',
            'choices'  => $this->choices,
            'multiple' => false,
            'expanded' => false,
            'required' => true,
            
            'empty_value' => 'Person Relation',
            'empty_data'  => 'Family',
            
        ));
    }    
    protected $choices = array
    (
        PersonPerson::RoleFamily => 'Family',
        PersonPerson::RolePeer   => 'Peer',
    );    
}

?>

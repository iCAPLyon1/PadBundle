<?php

namespace Icap\PadBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Icap\PadBundle\Form\ChoiceList\ProgramChoiceList;
use Icap\PadBundle\Form\ChoiceList\UnitChoiceList;

class PadType extends AbstractType
{
    protected $endpointRoot;

    public function __construct ($endpointRoot)
    {
        $this->endpointRoot = $endpointRoot;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
            /*->add('padUsers', 'collection', array(
                'type'   => 'email',
                'options'  => array(
                    'required'  => false,
                )
            ))*/
            ->add('program', 'choice', array(
                'choice_list' => new ProgramChoiceList($this->endpointRoot),
                'required' => true
            ))
            ->add('unit', 'choice', array(
                'choice_list' => new UnitChoiceList($this->endpointRoot),
                'required' => true
            ))
            ->add('title', null, array(
                'label' => 'Titre',
                'attr' => array('placeholder' => 'Entrez un titre ici')
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'pad'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Icap_padbundle_pad';
    }
}

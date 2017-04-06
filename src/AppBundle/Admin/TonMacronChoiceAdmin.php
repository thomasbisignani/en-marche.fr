<?php

namespace AppBundle\Admin;

use AppBundle\Entity\TonMacronChoice;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TonMacronChoiceAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('contentKey', null, [
                'label' => 'Clé',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('content', null, [
                'label' => 'Message',
            ])
            ->add('step', ChoiceType::class, [
                'label' => 'Etape',
                'choices' => TonMacronChoice::STEPS,
                'choice_translation_domain' => 'forms',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('contentKey', null, [
                'label' => 'Clé',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('content', null, [
                'label' => 'Message',
            ])
            ->add('step', null, [
                'label' => 'Etape',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}

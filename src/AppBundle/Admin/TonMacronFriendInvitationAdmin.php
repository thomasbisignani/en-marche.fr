<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class TonMacronFriendInvitationAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('friendEmailAddress', null, [
                'label' => 'Email de l’ami',
            ])
            ->add('authorEmailAddress', null, [
                'label' => 'Email de l’auteur',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('friendFirstName', null, [
                'label' => 'Prénom de l’ami',
            ])
            ->add('friendAge', null, [
                'label' => 'Age de l’ami',
            ])
            ->add('friendGender', null, [
                'label' => 'Genre de l’ami',
            ])
            ->add('friendPosition', null, [
                'label' => 'Statut professionnel de l’ami',
            ])
            ->add('friendEmailAddress', null, [
                'label' => 'Email de l’ami',
            ])
            ->add('authorFirstName', null, [
                'label' => 'Prénom de l’auteur',
            ])
            ->add('authorLastName', null, [
                'label' => 'Nom de l’auteur',
            ])
            ->add('authorEmailAddress', null, [
                'label' => 'Email de l’auteur',
            ])
            ->add('mailSubject', null, [
                'label' => 'Sujet',
            ])
            ->add('mailBody', null, [
                'label' => 'Contenu',
                'template' => 'admin/ton_macron_friend_invitation_mail_body.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }
}

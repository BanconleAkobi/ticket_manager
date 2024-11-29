<?php

namespace App\Form\Ticket;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('status')
            ->add('priority')
            ->add('deadline', null, [
                'widget' => 'single_text',
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('updated_at', null, [
                'widget' => 'single_text',
            ])
            ->add('assigned_to', EntityType::class, [
                'class' => User::class,
                'choices' => $options['user_list'],
                'choice_label' => 'email',
            ])
            ->add('created_by', EntityType::class, [
                'class' => User::class,
                'choices' => $options['tech_support_list'],
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'user_list' => [],
            'tech_support_list' => [],
        ]);

        $resolver->setAllowedTypes('user_list', 'array');
        $resolver->setAllowedTypes('tech_support_list', 'array');
    }
}

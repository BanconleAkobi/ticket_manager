<?php

namespace App\Form\Ticket;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\Status;
use App\Enum\Priority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('status', EnumType::class, [
                'class' => Status::class,

            ])
            ->add('priority', EnumType::class, [
                'class' => Priority::class,
            ])
            ->add('deadline', null, [
                'widget' => 'single_text',
            ])
            ->add('assigned_to', EntityType::class, [
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
            'tech_support_list' => [],
        ]);

        $resolver->setAllowedTypes('tech_support_list', 'array');
    }
}

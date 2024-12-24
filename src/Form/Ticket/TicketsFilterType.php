<?php

namespace App\Form\Ticket;

use App\Entity\User;
use App\Enum\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class TicketsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $start =  new \DateTime(('first day of this month'));
        $end   =  new \DateTime(('today'));
        $builder

            ->add('StartDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Start',
                'constraints' => [
                    new LessThanOrEqual('today'),
                ],
                'data' => $start
            ])
            ->add('EndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'End',
                'data' => $end,
            ])
            ->add('status', EnumType::class, [
                'label' => 'Status',
                'class' => Status::class,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit','attr' => ['class' => 'btn btn-primary']])
        ;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $start = $form->get('StartDate')->getData();
            $end = $form->get('EndDate')->getData();
            if ($start > $end){
                $form->get('EndDate')->addError(new \Symfony\Component\Form\FormError(
                    'The end date must be greater than start date'
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

<?php

namespace App\Form\User;

use App\Entity\User;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => ['class' => 'text-center-label text-nowrap'],
                'attr' => [
                    'placeholder' => 'Enter your email address',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Firstname',
                'label_attr' => ['class' => 'text-center-label text-nowrap'],
                'attr' => [
                    'placeholder' => 'Enter your firstname',
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Lastname',
                'label_attr' => ['class' => 'text-center-label text-nowrap'],
                'attr' => [
                    'placeholder' => 'Enter your lastname',
                ]
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Password',
                    'label_attr' => ['class' => 'text-center-label text-nowrap'],


                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Enter your password',
                    ],

                    'toggle' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Please enter a password',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ]
                    ],

                'second_options' => [
                    'label' => 'Repeat Password',

                    'label_attr' =>[
                        'class' => 'text-center-label text-nowrap',
                    ],
                    'toggle' => true
                ],
            ])


            // ->add('plainPassword', PasswordType::class, [
            //     // instead of being set onto the object directly,
            //     // this is read and encoded in the controller
            //     'mapped' => false,
            //     'attr' => ['autocomplete' => 'new-password'],
                // 'constraints' => [
                //     new NotBlank([
                //         'message' => 'Please enter a password',
                //     ]),
                //     new Length([
                //         'min' => 6,
                //         'minMessage' => 'Your password should be at least {{ limit }} characters',
                //         // max length allowed by Symfony for security reasons
                //         'max' => 4096,
                //     ]),
                // ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

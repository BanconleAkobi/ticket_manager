<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', FormType::class, [
                'mapped' => false,
                'label' => 'Roles'
            ]);

        foreach ($options['appRoles'] as $role) {
            $builder->add($role, CheckboxType::class, [
                'mapped' => false,
                'label' => $role,
                'required' => false,
                'data' => in_array($role, $options['userRoles'])
            ]);
        }

        $builder
            ->add('firstname')
            ->add('lastname');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'appRoles' => [],
            'userRoles' => []
        ]);
    }
}

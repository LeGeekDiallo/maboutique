<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, [
                'required'=>true,
                'attr'=>['placeholder'=>'Ex: DIALLO Alpha']
            ])
            ->add('email', EmailType::class, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: exemple@gmail.com'
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: 625242010'
                ]
            ])
            ->add('userType', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    'CLIENT'=>'ROLE_CLIENT',
                    'MARCHANT'=>'ROLE_MERCHANT'
                ]
            ])
            ->add('password', PasswordType::class, [
                'required'=>true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

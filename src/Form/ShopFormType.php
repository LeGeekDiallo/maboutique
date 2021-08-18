<?php

namespace App\Form;

use App\Entity\Shop;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shopName', null, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: ASOS '
                ]
            ])
            ->add('city', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    "Conakry"=>"Conakry",
                    "Boké"=>"Boké",
                    "Faranah"=>"Faranah",
                    "Guékedou"=>"Guékedou",
                    "KanKan"=>"KanKan",
                    "Kindia"=>"Kindia",
                    "Labé"=>"Labé",
                    "Mamou"=>"Mamou",
                    "N'Zérékoré"=>"N'Zérékoré",
                ]
            ])
            ->add('municipality', null, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: Ratoma'
                ]
            ])
            ->add('district', null, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: Bambeto'
                ]
            ])
            ->add('email', EmailType::class, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: nomboutique@gamil.com'
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: 665252010'
                ]
            ])
            ->add('shopLogo', FileType::class, [
                'required'=>true,
                'mapped'=>false,
            ])
            ->add('otherInfos', CKEditorType::class, [
                'required'=>true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}

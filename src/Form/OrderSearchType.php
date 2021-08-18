<?php

namespace App\Form;

use App\Entity\OrderSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderNumber', TextType::class, [
                'required'=>true,
                'attr'=>['placeholder'=>'Numéro de commande']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderSearch::class,
            'csrf_protection'=>false,
            'method'=>'GET'
        ]);
    }

    public function getBlockPrefix():string
    {
        return "";
    }
}

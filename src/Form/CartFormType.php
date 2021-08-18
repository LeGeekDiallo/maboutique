<?php

namespace App\Form;

use App\Entity\Availability;
use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductSize;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productQuantity', NumberType::class, [
                'required'=>true,
                'data'=>1
            ])
            ->add('productSize', EntityType::class, [
                'class'=>Availability::class,
                'choices'=>$options['product_sizes']->getSizeAvailable()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cart::class,
            'product_sizes'=>Product::class,
            'csrf_protection'=>false
        ]);
        $resolver->setAllowedTypes('product_sizes', 'App\Entity\Product');
    }

    public function getBlockPrefix():string
    {
        return "";
    }
}

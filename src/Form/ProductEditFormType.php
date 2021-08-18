<?php

namespace App\Form;

use App\Entity\ProductEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productName', null, [
                'required'=>true,
                'attr'=>[
                    'placeholder'=>'Ex: Tee Shirt slim'
                ]
            ])
            ->add('productCategory', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    'ACCESSOIRES'=>'ACCESSOIRES',
                    'CHAUSSURES'=>'CHAUSSURES',
                    'VETEMENTS'=>'VETEMENTS',
                ]
            ])
            ->add('productType', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    'ACCESSOIRES'=>[
                        'Accessoires de mode'=>'Accessoires de mode',
                        'Bonnets'=>'Bonnets',
                        'Bracelets'=>'Bracelets',
                        'Ceintures'=>'Ceintures',
                        'Colliers'=>'Colliers',
                        'Echarpes-Foulards'=>'Echarpes-Foulards',
                        'Gants'=>'Gants',
                        'Lunettes de Soleil'=>'Lunettes de Soleil',
                        'Montres'=>'Montres',
                        'Portefeuilles'=>'Portefeuilles',
                        'Sacs Banane'=>'Sacs Banane',
                        'Sacs-Sacoches'=>'Sacs-Sacoches',
                    ],
                    'CHAUSSURES'=>[
                        'Baskets Basses'=>'Baskets Basses',
                        'Baskets Montantes'=>'Baskets Montantes',
                        'Bottes - Boots'=>'Bottes - Boots',
                        'Chelsea Boots'=>'Chelsea Boots',
                        'Chaussures'=>'Chaussures',
                        'Claquettes - Sandales'=>'Claquettes - Sandales',
                        'Tongs'=>'Tongs',
                    ],
                    'VÊTEMENTS'=>[
                        'Blousons-Vestes'=>'Blousons-Vestes',
                        'Chemises'=>'Chemises',
                        'Débardeurs'=>'Débardeurs',
                        'Jeans-Pantalons'=>'Jeans-Pantalons',
                        'Joggings'=>'Joggings',
                        'Polos'=>'Polos',
                        'Shorts-Bermudas'=>'Shorts-Bermudas',
                        'Sous-Vêtements'=>'Sous-Vêtements',
                        'Robes-Jupes'=>'Robes-Jupes',
                        'Sweats-Pulls'=>'Sweats-Pulls',
                        'T-shirts'=>'T-shirts',
                    ]
                ]
            ])
            ->add('productBrand', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    "Aarhon"=>"Aarhon",
                    "adidas"=>"adidas",
                    "Armita"=>"Armita",
                    "Asics"=>"Asics",
                    "Capslab"=>"Capslab",
                    "BOSS"=>"BOSS",
                    "Calvin Klein"=>"Calvin Klein",
                    "Champion"=>"Champion",
                    "Classic Series"=>"Classic Series",
                    "Diesel"=>"Diesel",
                    "Ellesse"=>"Ellesse",
                    "Fila"=>"Fila",
                    "Final Club"=>"Final Club",
                    "Frilivin"=>"Frilivin",
                    "Emporio Armani"=>"Emporio Armani",
                    "Guess"=>"Guess",
                    "HUGO"=>"HUGO",
                    "Jack And Jones"=>"Jack And Jones",
                    "Le Coq Sportif"=>"Le Coq Sportif",
                    "New Balance"=>"New Balance",
                    "New Era"=>"New Era",
                    "Nike"=>"Nike",
                    "Puma"=>"Puma",
                    "Ray-Ban"=>"Ray-Ban",
                    "Reebok"=>"Reebok",
                    "Replay"=>"Replay",
                    "Sergio Tacchini"=>"Sergio Tacchini",
                    "Teddy Smith"=>"Teddy Smith",
                    "The North Face"=>"The North Face",
                    "Tommy Hilfiger"=>"Tommy Hilfiger",
                    "Uniplay"=>"Uniplay",
                    "Urban Classics"=>"Urban Classics",
                    "Vans"=>"Vans",
                ]
            ])
            ->add('productPrice')
            ->add('productGender', ChoiceType::class, [
                'required'=>true,
                'choices'=>[
                    'FEMME'=>'FEMME',
                    'HOMME'=>'HOMME',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductEdit::class,
        ]);
    }
}

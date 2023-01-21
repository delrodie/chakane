<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('reference')
            ->add('titre', TextType::class,[
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom du produit', 'autocomplete'=>'off'],
                'label' => 'Nom'
            ])
            ->add('description', CKEditorType::class)
            ->add('cfaPrix', IntegerType::class,[
                'attr'=>['class' => 'form-control', 'placeholder' => "Prix normal", 'autocomplete'=>'off'],
                'label' => "Prix Normal"
            ])
            ->add('cfaSolde', IntegerType::class,[
                'attr'=>['class' => 'form-control', 'placeholder' => "Prix solde", 'autocomplete'=>'off'],
                'label' => "Prix Solde",
                'required' => false
            ])
            ->add('media', FileType::class,[
                'attr'=>['class'=>"dropify", 'data-preview' => ".preview"],
                'label' => "Télécharger la photo d'illustration",
                'mapped' => false,
                'multiple' => false,
                'constraints' => [
                    new File([
                        'maxSize' => "20000k",
                        'mimeTypes' =>[
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                        //'mimeTypesMessage' => "Votre fichier doit être de type image",
                        //'maxSizeMessage' => "La taille de votre image doit être inférieure à 2Mo",
                    ])
                ],
                'required' => false
            ])
            ->add('tags', TextType::class,[
                'attr'=>['class'=>'form-control', 'data-role'=>'tagsinput'],
                'required' => true,
                'label' => "Mots clés"
            ])
            ->add('promotion', CheckboxType::class,[
                'attr' => ['class' => 'custom-control-input'],
                'label_attr' => ['class' => 'custom-control-label'],
                'required' => false
            ])
            ->add('niveau', ChoiceType::class,[
                'attr' => ['class' => 'form-control'],
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5
                ]
            ])
            //->add('slug')
            ->add('taille', ChoiceType::class,[
                'attr' => ['class'=>'form-control'],
                'choices' => [
                    '-- Selectionnez --' => ' ',
                    'S' => 'S',
                    'M' => "M",
                    'L' => "L",
                    'XL' => "XL",
                    'XXL' => "XXL",
                ],
                'required' => false
            ])
            ->add('coleur', TextType::class,[
                'attr' => ['class' => 'form-control', 'placeholder' => "La couleur", 'autcomplete'=>"off"],
                'label' => "Couleur",
                'required' => false
            ])
            ->add('poids', NumberType::class,[
                'attr' => ['class'=>'form-control', 'placeholder'=>'Le poids du produit', 'autocomplete'=>"off"],
                'required' => false
            ])
            //->add('updatedAt')
            ->add('categorie', EntityType::class,[
                'attr' => ['class' => 'form-control select2'],
                'class' => Categorie::class,
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('c')->orderBy('c.titre', 'ASC');
                },
                'choice_label' => 'titre',
                'multiple' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

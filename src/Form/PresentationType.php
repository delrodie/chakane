<?php

namespace App\Form;

use App\Entity\Presentation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PresentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class,[
                'attr' => ['class'=>'form-control', 'autocomplete'=>"off", 'placeholder' => "Titre la présentationd de la marque"]
            ])
            //->add('resume')
            ->add('contenu', CKEditorType::class, ['attr' => ['class'=>'form-control']])
            ->add('tags', TextType::class,[
                'attr'=>['class'=>'form-control', 'data-role'=>'tagsinput'],
                'required' => true,
                'label' => "Mots clés"
            ])
            ->add('media', FileType::class,[
                'attr'=>['class'=>"dropify-fr", 'data-preview' => ".preview"],
                'label' => "Télécharger le logo",
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
            //->add('slug')
            //->add('createdAt')
            //->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Presentation::class,
        ]);
    }
}

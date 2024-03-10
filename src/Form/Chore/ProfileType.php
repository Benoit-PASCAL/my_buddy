<?php

namespace App\Form\Chore;

use App\Entity\Chore\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
                'empty_data' => '',
                'help' => 'This has to be unique',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'label' => 'Email Address',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'Last Name',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'required' => false,
                'sanitize_html' => true,
                'sanitizer' => 'app.form_sanitizer',
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'First Name',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'required' => false,
                'sanitize_html' => true,
                'sanitizer' => 'app.form_sanitizer',
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'Phone Number',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'required' => false,
                'sanitize_html' => true,
                'sanitizer' => 'app.form_sanitizer',
            ])
            ->add('profilePicture', FileType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
                'label' => 'Profile Picture',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'mapped' => false,
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

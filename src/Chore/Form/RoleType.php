<?php

namespace App\Chore\Form;

use App\Chore\Entity\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'help' => 'Do not use accents or special characters. Example: treasurer.',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'label' => 'Label',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'sanitize_html' => true,
                'sanitizer' => 'app.form_sanitizer',
            ])
            ->add('color', ColorType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'Color',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('icon', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'help' => 'Bootstrap 5 icon name. Example: bi-123.',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'label' => 'Icon',
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
            ->add('permissions', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => true,
                'entry_options' => ['label' => false],
                'entry_type' => PermissionType::class,
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'prototype' => true,
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Status::class,
        ]);
    }
}

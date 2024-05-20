<?php

namespace App\App\Form;

use App\App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'Title',
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
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'label' => 'Description',
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
            ->add('startDate', DateTimeType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => date('Y-m-d h:i', strtotime('now')),
                'help' => 'Leave empty to set the current date',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'input' => 'datetime',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => date('Y-m-d h:i', strtotime('now')),
                'input' => 'datetime',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'required' => false,
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

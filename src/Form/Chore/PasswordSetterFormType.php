<?php

namespace App\Form\Chore;

use App\Entity\Chore\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordSetterFormType extends AbstractType
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
                'label' => 'Email Address',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'label' => 'Password',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'label' => 'Confirm Password',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('secretToken', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your secret key should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'empty_data' => '',
                'help' => 'Ask your administrator for the secret key to set up your account.',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'label' => 'Secret Key',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'mapped' => false,
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'error_mapping' => [
                'plainPassword' => 'confirmPassword',
            ],
        ]);
    }
}

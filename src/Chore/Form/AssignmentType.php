<?php

namespace App\Chore\Form;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Status;
use App\Chore\Repository\StatusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class AssignmentType extends AbstractType
{
    private StatusRepository $statusRepository;
    public function __construct(
        StatusRepository $statusRepository
    )
    {
        $this->statusRepository = $statusRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', EntityType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => Status::class,
                'constraints' => [
                    new Choice([
                        'choices' => $this->statusRepository->findAllRoles(),
                    ])
                ],
                'label' => 'Role',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'multiple' => false,
                'query_builder' => function (StatusRepository $statusRepository) {
                    return $statusRepository->createQueryBuilder('s')
                        ->where('s.type = :type')
                        ->setParameter('type', Status::ROLE_TYPE);
                },
                'row_attr' => [
                    'class' => 'form-group'
                ]
            ])
            ->add('startDate', DateType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => date('Y-m-d', strtotime('now')),
                'help' => 'Leave empty to set the current date',
                'help_attr' => [
                    'class' => 'form-text'
                ],
                'input' => 'datetime_immutable',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'empty_data' => '',
                'input' => 'datetime_immutable',
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
            'data_class' => Assignment::class,
            'attr' => [
                'class' => 'form-control'
            ]
        ]);
    }
}

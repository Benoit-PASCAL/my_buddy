<?php

namespace App\Chore\Form;

use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Repository\StatusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class PermissionType extends AbstractType
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
            ->add('controller', EntityType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'class' => Status::class,
                'constraints' => [
                    new Choice([
                        'choices' => Permission::CONTROLLER_LIST,
                    ])
                ],
                'disabled' => true,
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'multiple' => false,
                'query_builder' => function (StatusRepository $statusRepository) {
                    return $statusRepository->createQueryBuilder('s')
                        ->where('s.type = :type')
                        ->setParameter('type', Status::CONTROLLER_TYPE);
                },
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
            ->add('access', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    'No access' => Permission::CAN_NOTHING,
                    'Can See' => Permission::CAN_VIEW,
                    'Can Edit' => Permission::CAN_EDIT,
                    'Can Create' => Permission::CAN_CREATE,
                    'Full Access' => Permission::CAN_ALL,
                ],
                'constraints' => [
                    new Choice([
                        'choices' => [
                            Permission::CAN_NOTHING,
                            Permission::CAN_VIEW,
                            Permission::CAN_EDIT,
                            Permission::CAN_CREATE,
                            Permission::CAN_ALL,
                        ],
                    ])
                ],
                'label' => false,
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}

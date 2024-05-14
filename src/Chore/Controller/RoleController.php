<?php

namespace App\Chore\Controller;

use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Form\RoleType;
use App\Chore\Repository\PermissionRepository;
use App\Chore\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * RoleController is a controller that handles role related actions.
 * It extends the RightsController to check for permissions.
 *
 */
#[Route('/dashboard/role')]
class RoleController extends RightsController
{
    private PermissionRepository $permissionRepository;
    private StatusRepository $statusRepository;

    /**
     * Constructor for RoleController.
     *
     * @param PermissionRepository $permissionRepository
     * @param StatusRepository $statusRepository
     */
    public function __construct(
        PermissionRepository $permissionRepository,
        StatusRepository $statusRepository
    )
    {
        $this->permissionRepository = $permissionRepository;
        $this->statusRepository = $statusRepository;
    }

    /**
     * Display all roles.
     *
     * @param StatusRepository $statusRepository
     * @return Response
     */
    #[Route('/', name: 'app_role_index', methods: ['GET'])]
    public function index(StatusRepository $statusRepository): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('chore/role/index.html.twig', [
            'statuses' => $statusRepository->findAllRoles(),
        ]);
    }

    /**
     * Create a new role.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_CREATE);

        $role = (new Status())
            ->setType(Status::ROLE_TYPE);
        $entityManager->persist($role);

        foreach (Permission::CONTROLLER_LIST as $controllerName) {
            $controller = $this->statusRepository->findOneBy([
                'label' => $controllerName
            ]) ?? (new Status())
                ->setType(Status::CONTROLLER_TYPE)
                ->setLabel($controllerName);

            $entityManager->persist($controller);

            $permission = (new Permission())
                ->setController($controller)
                ->setRole($role);

            $role
                ->addPermission($permission);
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/role/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * Display a specific role.
     *
     * @param Status $role
     * @return Response
     */
    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function show(Status $role): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('chore/role/show.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * Edit a specific role.
     *
     * @param Request $request
     * @param Status $role
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Status $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_EDIT);

        foreach (Permission::CONTROLLER_LIST as $controllerName) {
            $controller = $this->statusRepository->findOneBy([
                'label' => $controllerName
            ]) ?? (new Status())
                ->setType(Status::CONTROLLER_TYPE)
                ->setLabel($controllerName);

            $entityManager->persist($controller);

            $permission = $this->permissionRepository->findOneBy(['controller' => $controller, 'role' => $role]) ?? (new Permission())
                ->setController($controller);

            if($role->getLabel() == 'admin') {
                $permission->setAccess(Permission::CAN_ALL);
            }

            $role
                ->addPermission($permission);
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * Delete a specific role.
     *
     * @param Request $request
     * @param Status $role
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Status $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_DELETE);

        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {

            foreach ($role->getPermissions() as $permission) {
                $entityManager->remove($permission);
            }

            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\App\Controller;

use App\App\Entity\Event;
use App\App\Form\EventType;
use App\App\Repository\EventRepository;
use App\Chore\Controller\RightsController;
use App\Chore\Entity\Permission;
use App\Chore\Service\RequestAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * EventController is a controller that handles event related actions.
 * It extends the RightsController to check for permissions.
 *
 */
#[Route('/dashboard/event')]
class EventController extends RightsController
{
    private SluggerInterface $slugger;

    /**
     * Constructor for EventController.
     *
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Display the list of events.
     * Can be sorted by title, description, start date, and end date.
     *
     * @param EventRepository $eventRepository
     * @return Response
     */
    #[Route('/', name: 'app_event_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $this->checkRights(Permission::CAN_VIEW);
        Permission::getAppControllersList();

        $sort = RequestAnalyzer::getSortParams($request, new Event());
        //dd($sort);
        $events = $eventRepository->findBy([], $sort);

        return $this->render('app/event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * Create a new event.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_CREATE);

        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('app/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * Display a specific event.
     *
     * @param Event $event
     * @return Response
     */
    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('app/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Edit a specific event.
     *
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_EDIT);
        //dd($request->request, $event, $entityManager);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('app/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    /**
     * Delete a specific event.
     *
     * @param Request $request
     * @param Event $event
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_DELETE);

        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}

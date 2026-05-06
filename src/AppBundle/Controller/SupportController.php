<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ChatMessage;
use AppBundle\Entity\Ticket;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportController extends Controller
{
    /**
     * @Route("/support/dashboard", name="support_dashboard")
     */
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tickets = $em->getRepository('AppBundle:Ticket')->findBy([], ['createdAt' => 'DESC']);
        $messages = $em->getRepository('AppBundle:ChatMessage')->findBy([], ['createdAt' => 'DESC']);

        return $this->render('support/dashboard.html.twig', [
            'tickets' => $tickets,
            'messages' => $messages,
            'embedScriptUrl' => $this->generateUrl('support_widget_js', [], true),
            'apiBaseUrl' => $this->generateUrl('support_get_messages', [], true),
        ]);
    }

    /** @Route("/widget/support.js", name="support_widget_js") */
    public function widgetJsAction()
    {
        $js = file_get_contents($this->getParameter('kernel.project_dir').'/web/js/support-widget.js');
        return new Response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    /** @Route("/api/support/messages", name="support_get_messages", methods={"GET"}) */
    public function messagesAction(Request $request)
    {
        $siteKey = $request->query->get('siteKey');
        $visitorId = $request->query->get('visitorId');

        $messages = $this->getDoctrine()->getRepository('AppBundle:ChatMessage')
            ->findBy(['siteKey' => $siteKey, 'visitorId' => $visitorId], ['createdAt' => 'ASC']);

        $data = array_map(function (ChatMessage $message) {
            return [
                'sender' => $message->getSender(),
                'message' => $message->getMessage(),
                'createdAt' => $message->getCreatedAt()->format(DATE_ATOM),
            ];
        }, $messages);

        return new JsonResponse(['messages' => $data]);
    }

    /** @Route("/api/support/messages", name="support_post_message", methods={"POST"}) */
    public function postMessageAction(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?: [];

        $message = (new ChatMessage())
            ->setSiteKey($payload['siteKey'])
            ->setVisitorId($payload['visitorId'])
            ->setSender(isset($payload['sender']) ? $payload['sender'] : 'visitor')
            ->setMessage($payload['message']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        return new JsonResponse(['ok' => true]);
    }

    /** @Route("/api/support/tickets", name="support_post_ticket", methods={"POST"}) */
    public function postTicketAction(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?: [];

        $ticket = (new Ticket())
            ->setSiteKey($payload['siteKey'])
            ->setName($payload['name'])
            ->setEmail($payload['email'])
            ->setSubject($payload['subject'])
            ->setDescription($payload['description']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($ticket);
        $em->flush();

        return new JsonResponse(['ok' => true, 'ticketId' => $ticket->getId()]);
    }
}
